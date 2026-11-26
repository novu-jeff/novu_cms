<?php

namespace App\Http\Controllers;

use App\Models\DocumentSigningRequest;
use App\Services\DocumentSigning\DocumentSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentSigningController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();

        $query = DocumentSigningRequest::with([
            'initiator',
            'signers.user',
        ]);

        // Administrators can see all signing requests.
        // Other users can only see requests they initiated
        // or requests where they are assigned as signers.
        if (! $user->isAdministrator()) {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhereHas('signers', function ($sq) use ($user) {
                        $sq->where('user_id', $user->id);
                    });
            });
        }

        $requests = $query->latest()->paginate(15);

        return view('document-signing.index', compact('requests'));
    }


    public function show(DocumentSigningRequest $signingRequest)
    {
        $this->authorize('view', $signingRequest);

        $signingRequest->load([
            'initiator',
            'signers.user',
            'signers.signature',
            'audits.user',
        ]);

        return view('document-signing.show', compact('signingRequest'));
    }

    public function download(DocumentSigningRequest $signingRequest)
    {
        // Only users authorized to view this request may download it.
        $this->authorize('view', $signingRequest);

        // Only completed signing requests may be downloaded.
        abort_unless(
            $signingRequest->status === 'fully_signed',
            404,
            'The final signed PDF is not yet available.'
        );

        $disk = Storage::disk('private');
        $path = $signingRequest->signed_document_path;

        abort_unless(
            $path && $disk->exists($path),
            404,
            'The final signed PDF could not be found.'
        );

        // Verify the final PDF has not been modified.
        $absolutePath = $disk->path($path);
        $actualHash = hash_file('sha256', $absolutePath);

        abort_if(
            ! $actualHash ||
            ! $signingRequest->signed_document_hash ||
            ! hash_equals($signingRequest->signed_document_hash, $actualHash),
            409,
            'The signed PDF integrity check failed. Please contact the system administrator.'
        );

        $filename = \Illuminate\Support\Str::slug(
            $signingRequest->document_title ?: 'signed-document'
        ) . '-signed.pdf';

        return $disk->download($path, $filename, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function preview(DocumentSigningRequest $signingRequest)
    {
        $this->authorize('view', $signingRequest);

        abort_unless(
            Storage::disk('private')->exists($signingRequest->document_path),
            404
        );

        return response()->file(
            Storage::disk('private')->path($signingRequest->document_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document-preview.pdf"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    
    public function sign(
        Request $request,
        DocumentSigningRequest $signingRequest,
        DocumentSigningService $service
    ) {
        // 1. Check whether the authenticated user is authorized to sign.
        $this->authorize('sign', $signingRequest);

        // 2. Validate submitted credentials and signature.
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'signature' => [
                'nullable',
                'image',
                'mimes:png',
                'max:2048',
            ],
            'consent' => ['required', 'accepted'],
        ]);

        // 3. Store the signature privately.
        $signaturePath = null;

        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store(
                'signing/signatures',
                'private'
            );
        }

        // 4. Process the signing request.
        try {
            $service->sign(
                $signingRequest->id,
                $request->user(),
                $validated['password'],
                $signaturePath
            );
        } catch (\Throwable $exception) {
            // Remove uploaded signature if signing fails.
            if ($signaturePath) {
                Storage::disk('private')->delete($signaturePath);
            }

            throw $exception;
        }

        // 5. Return to the document details page.
        return redirect()
            ->route('document-signing.show', $signingRequest)
            ->with('success', 'Your signature has been recorded.');
    }



    public function reject(
        Request $request,
        DocumentSigningRequest $signingRequest,
        DocumentSigningService $service
    ) {
        $this->authorize('reject', $signingRequest);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $service->reject(
            $signingRequest->id,
            $request->user(),
            $validated['reason']
        );

        return redirect()
            ->route('document-signing.show', $signingRequest)
            ->with('success', 'The signing request has been rejected.');
    }

    public function revoke(
        Request $request,
        DocumentSigningRequest $signingRequest,
        DocumentSigningService $service
    ) {
        $this->authorize('revoke', $signingRequest);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $service->revoke(
            $signingRequest->id,
            $request->user(),
            $validated['reason']
        );

        return redirect()
            ->route('document-signing.show', $signingRequest)
            ->with('success', 'The signing request has been revoked.');
    }

}
