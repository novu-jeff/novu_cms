<?php

namespace App\Http\Controllers\CommitteePortal;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\DocumentSigningRequest;
use App\Models\MembersAccount;
use App\Services\DocumentSigning\DocumentSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommitteePortalSigningController extends Controller
{
    /**
     * Resolve the authenticated Committee Portal account
     * to its corresponding CMS User.
     */
    private function resolveCmsUser(Request $request)
    {
        /** @var MembersAccount $account */
        $account = $request->user('member');

        abort_unless($account && $account->is_active, 403);

        $member = $account->member;

        abort_unless($member && $member->isActive, 403);

        $user = $member->user;

        abort_unless(
            $user &&
            $user->isCouncilMember(),
            403,
            'Your account is not authorized for committee signing.'
        );

        return [$account, $member, $user];
    }

    /**
     * Ensure the CMS user is the assigned committee chairperson.
     */
    private function authorizeCommitteeSigner(
        DocumentSigningRequest $signingRequest,
        $user
    ): void {
        $signer = $signingRequest->signers
            ->firstWhere('user_id', $user->id);

        abort_unless($signer, 403);

        abort_unless(
            in_array($signingRequest->document_type, [
                'committee_report',
                'consolidated_committee_report',
            ], true),
            403
        );

        $assignment = CommitteeMember::query()
            ->active()
            ->chairperson()
            ->where('member_id', $user->member_id)
            ->where(
                'standing_comittee_id',
                $signingRequest->committee_id
            )
            ->exists();

        abort_unless(
            $assignment,
            403,
            'You are not the active chairperson of this committee.'
        );
    }

    /**
     * List committee signing requests.
     */
    public function index(Request $request)
    {
        [$account, $member, $user] = $this->resolveCmsUser($request);

        $query = DocumentSigningRequest::query()
            ->with([
                'initiator',
                'signers.user',
                'signers.signature',
            ])
            ->whereIn('document_type', [
                'committee_report',
                'consolidated_committee_report',
            ])
            ->whereHas('signers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest();

        $requests = $query->paginate(15);

        return view(
            'committee-portal.signing.index',
            compact('requests', 'member')
        );
    }

    /**
     * Show an assigned committee signing request.
     */
    public function show(Request $request, DocumentSigningRequest $signingRequest)
    {
        [$account, $member, $user] = $this->resolveCmsUser($request);

        $signingRequest->load([
            'initiator',
            'signers.user',
            'signers.signature',
            'audits.user',
        ]);

        $this->authorizeCommitteeSigner($signingRequest, $user);

        $currentSigner = $signingRequest->signers
            ->firstWhere('status', 'pending');

        $isNextSigner = $currentSigner &&
            (int) $currentSigner->user_id === (int) $user->id;

        return view(
            'committee-portal.signing.show',
            compact(
                'signingRequest',
                'member',
                'user',
                'isNextSigner'
            )
        );
    }

    /**
     * Preview the original PDF.
     */
    public function preview(
        Request $request,
        DocumentSigningRequest $signingRequest
    ) {
        [$account, $member, $user] = $this->resolveCmsUser($request);

        $signingRequest->load('signers');

        $this->authorizeCommitteeSigner($signingRequest, $user);

        $disk = Storage::disk('private');

        abort_unless(
            $disk->exists($signingRequest->document_path),
            404,
            'Original document not found.'
        );

        return response()->file(
            $disk->path($signingRequest->document_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="committee-report.pdf"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    /**
     * Sign an assigned committee report using the Committee Portal account.
     */
    public function sign(
        Request $request,
        DocumentSigningRequest $signingRequest,
        DocumentSigningService $service
    ) {
        [$account, $member, $user] = $this->resolveCmsUser($request);

        $signingRequest->load('signers');

        $this->authorizeCommitteeSigner($signingRequest, $user);

        $validated = $request->validate([
            'password' => ['required', 'string'],
            'consent' => ['required', 'accepted'],
            'signature' => [
                'nullable',
                'image',
                'mimes:png',
                'max:2048',
            ],
        ]);

        $nextSigner = $signingRequest->signers
            ->firstWhere('status', 'pending');

        abort_unless(
            $nextSigner &&
            (int) $nextSigner->user_id === (int) $user->id,
            403,
            'The previous signer must complete signing first.'
        );

        $signaturePath = null;

        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store(
                'signing/signatures',
                'private'
            );
        }

        try {
            $service->sign(
                (int) $signingRequest->id,
                $user,
                $validated['password'],
                $signaturePath,
                $account
            );
        } catch (\Throwable $exception) {
            if ($signaturePath) {
                Storage::disk('private')->delete($signaturePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('committee-portal.signing.show', $signingRequest)
            ->with(
                'success',
                'Your committee report signature has been recorded successfully.'
            );
    }

    /**
     * Download the final signed PDF.
     */
    public function download(
        Request $request,
        DocumentSigningRequest $signingRequest
    ) {
        [$account, $member, $user] = $this->resolveCmsUser($request);

        $signingRequest->load('signers');

        $this->authorizeCommitteeSigner($signingRequest, $user);

        abort_unless(
            $signingRequest->status === 'fully_signed',
            404,
            'The final signed PDF is not yet available.'
        );

        $disk = Storage::disk('private');
        $path = $signingRequest->signed_document_path;

        abort_unless($path && $disk->exists($path), 404);

        $actualHash = hash_file('sha256', $disk->path($path));

        abort_unless(
            $actualHash &&
            $signingRequest->signed_document_hash &&
            hash_equals(
                $signingRequest->signed_document_hash,
                $actualHash
            ),
            409,
            'Signed document integrity verification failed.'
        );

        return $disk->download(
            $path,
            Str::slug($signingRequest->document_title) . '-signed.pdf',
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}