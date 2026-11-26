<?php

namespace App\Services\DocumentSigning;

use App\Models\DocumentSigner;
use App\Models\DocumentSignature;
use App\Models\DocumentSigningAudit;
use App\Models\DocumentSigningRequest;
use App\Models\Committee;
use App\Models\SessionMeeting;
use App\Models\MembersAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;
use RuntimeException;

class DocumentSigningService
{
    public function __construct(
        protected DocumentTypeRegistry $registry,
        protected DocumentSignerResolver $signerResolver,
        protected SignedDocumentPdfGenerator $signedPdfGenerator
    ) {}

    
        /**
     * Create a signing request using server-resolved signers.
     *
     * The source PDF must already exist on the private disk.
     */
    public function createRequest(
        string $documentType,
        int $documentId,
        string $documentTitle,
        string $documentPath,
        User $initiator,
        ?int $committeeId = null,
        ?string $referenceNo = null
    ): DocumentSigningRequest {

        $this->registry->get($documentType);

        if (! Storage::disk('private')->exists($documentPath)) {
            throw ValidationException::withMessages([
                'document' => 'The source PDF could not be found.',
            ]);
        }

        $absolutePath = Storage::disk('private')->path($documentPath);

        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw ValidationException::withMessages([
                'document' => 'The source PDF is not readable.',
            ]);
        }

        if (strtolower(pathinfo($documentPath, PATHINFO_EXTENSION)) !== 'pdf') {
            throw ValidationException::withMessages([
                'document' => 'Only PDF documents can be submitted for signing.',
            ]);
        }

        $documentHash = hash_file('sha256', $absolutePath);

        if (! $documentHash) {
            throw new RuntimeException('Unable to calculate the source document hash.');
        }

        // Resolve official signers from the registry and database.
        // Never accept signer IDs from frontend input.
        $signers = $this->signerResolver->resolve(
            $documentType,
            $committeeId
        );

        if (empty($signers)) {
            throw ValidationException::withMessages([
                'signers' => 'No valid signers were assigned to this document.',
            ]);
        }

        return DB::transaction(function () use (
            $documentType,
            $documentId,
            $documentTitle,
            $documentPath,
            $documentHash,
            $initiator,
            $signers,
            $committeeId,
            $referenceNo
        ) {

            // Prevent another active request for the same document.
            $existingRequest = DocumentSigningRequest::query()
                ->where('document_type', $documentType)
                ->where('document_id', $documentId)
                ->whereIn('status', [
                    'draft',
                    'pending',
                    'partially_signed',
                ])
                ->lockForUpdate()
                ->first();

            if ($existingRequest) {
                throw ValidationException::withMessages([
                    'document' => 'An active signing request already exists for this document.',
                ]);
            }

            $request = DocumentSigningRequest::create([
                'document_type' => $documentType,
                'document_id' => $documentId,
                'committee_id' => $committeeId,
                'reference_no' => $referenceNo,
                'document_title' => $documentTitle,
                'document_path' => $documentPath,
                'document_hash' => $documentHash,
                'status' => 'pending',
                'initiated_by' => $initiator->id,
                'submitted_at' => now(),
            ]);

            foreach (array_values($signers) as $index => $signer) {

                $user = User::find($signer['user_id']);

                if (! $user) {
                    throw ValidationException::withMessages([
                        'signers' => 'One of the assigned signers no longer exists.',
                    ]);
                }

                $request->signers()->create([
                    'user_id' => $user->id,
                    'signer_role' => $signer['role'],
                    'signing_order' => $index + 1,
                    'status' => 'pending',
                ]);
            }

            $this->audit(
                $request,
                $initiator,
                'submitted',
                'Document submitted for sequential signing.',
                [
                    'document_type' => $documentType,
                    'document_id' => $documentId,
                    'document_hash' => $documentHash,
                    'signer_count' => count($signers),
                ]
            );

            return $request->load('signers.user');
        });
    }

    
    /**
     * Sign a document.
     *
     * The signature image must already have been validated
     * and stored on the private disk by the controller.
     */
    public function sign(
        int $signingRequestId,
        User $user,
        string $password,
        ?string $signaturePath = null,
        ?MembersAccount $memberAccount = null
    ): DocumentSigningRequest {

        if ($memberAccount) {
            // Committee Portal authentication.
            // Ensure the portal account belongs to the CMS user.
            if (
                ! $user->member_id ||
                (int) $memberAccount->member_id !== (int) $user->member_id ||
                ! Hash::check($password, $memberAccount->password)
            ) {
                throw ValidationException::withMessages([
                    'password' => 'Committee Portal password verification failed.',
                ]);
            }
        } elseif (! Hash::check($password, $user->password)) {
            // Existing CMS authentication.
            throw ValidationException::withMessages([
                'password' => 'Password verification failed.',
            ]);
        }

        // If the transaction fails after PDF storage, remove the generated file.
        $generatedPath = null;

        try {
            return DB::transaction(function () use (
                $signingRequestId,
                $user,
                $signaturePath,
                &$generatedPath
            ) {

                $request = DocumentSigningRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($signingRequestId);

                if (! in_array($request->status, [
                    'pending',
                    'partially_signed',
                ], true)) {
                    throw ValidationException::withMessages([
                        'signing' => 'This document is not available for signing.',
                    ]);
                }

                // Lock all signer rows to prevent concurrent signing.
                $signers = DocumentSigner::query()
                    ->where('signing_request_id', $request->id)
                    ->orderBy('signing_order')
                    ->lockForUpdate()
                    ->get();

                $signer = $signers->firstWhere('user_id', $user->id);

                if (! $signer || $signer->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'signing' => 'You are not an eligible pending signer for this document.',
                    ]);
                }

                $nextSigner = $signers->firstWhere('status', 'pending');

                if (! $nextSigner || (int) $nextSigner->id !== (int) $signer->id) {
                    throw ValidationException::withMessages([
                        'signing' => 'The previous signer must complete signing first.',
                    ]);
                }

                // Verify the original PDF has not changed.
                $absolutePath = Storage::disk('private')
                    ->path($request->document_path);

                if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
                    throw new RuntimeException('The original document is missing.');
                }

                $currentHash = hash_file('sha256', $absolutePath);

                if (! $currentHash ||
                    ! hash_equals($request->document_hash, $currentHash)) {
                    throw ValidationException::withMessages([
                        'document' => 'The document has changed since it was submitted for signing.',
                    ]);
                }

                // Validate the stored signature image.
                if ($signaturePath) {
                    if (! Storage::disk('private')->exists($signaturePath)) {
                        throw ValidationException::withMessages([
                            'signature' => 'The signature image could not be found.',
                        ]);
                    }

                    $signatureAbsolutePath = Storage::disk('private')
                        ->path($signaturePath);

                    $imageInfo = @getimagesize($signatureAbsolutePath);

                    if (! $imageInfo || $imageInfo['mime'] !== 'image/png') {
                        throw ValidationException::withMessages([
                            'signature' => 'The signature must be a valid PNG image.',
                        ]);
                    }
                }

                $signatureHash = $signaturePath
                    ? hash_file(
                        'sha256',
                        Storage::disk('private')->path($signaturePath)
                    )
                    : null;

                if ($signaturePath && ! $signatureHash) {
                    throw new RuntimeException('Unable to calculate the signature image hash.');
                }

                $signedAt = now();

                // Create the electronic signature record.
                DocumentSignature::create([
                    'document_signer_id' => $signer->id,
                    'signature_type' => 'electronic',
                    'signature_path' => $signaturePath,
                    'document_hash' => $request->document_hash,
                    'signature_hash' => $signatureHash,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'signed_at' => $signedAt,
                ]);

                // Mark this signer as signed.
                $signer->update([
                    'status' => 'signed',
                    'signed_at' => $signedAt,
                ]);

                $this->audit(
                    $request,
                    $user,
                    'signed',
                    'Signer completed electronic signing.',
                    [
                        'signer_id' => $signer->id,
                        'signing_order' => $signer->signing_order,
                        'document_hash' => $request->document_hash,
                        'signature_hash' => $signatureHash,
                    ]
                );

                // Check the database state after updating this signer.
                $pendingSigners = DocumentSigner::query()
                    ->where('signing_request_id', $request->id)
                    ->where('status', 'pending')
                    ->count();

                if ($pendingSigners === 0) {

                    // Reload the completed signing records.
                    $request->load([
                        'signers.user',
                        'signers.signature',
                    ]);

                    // Generate the final PDF with all signature records.
                    $finalPdf = $this->signedPdfGenerator->generate($request);

                    if (empty($finalPdf)) {
                        throw new RuntimeException(
                            'The final signed PDF generation returned empty content.'
                        );
                    }

                    // Save the final PDF on the private disk.
                    $generatedPath = sprintf(
                        'signed-documents/%d/signed-%s.pdf',
                        $request->id,
                        (string) Str::uuid()
                    );

                    $stored = Storage::disk('private')->put(
                        $generatedPath,
                        $finalPdf
                    );

                    if (! $stored ||
                        ! Storage::disk('private')->exists($generatedPath)) {
                        throw new RuntimeException(
                            'Unable to store the final signed PDF.'
                        );
                    }

                    $finalAbsolutePath = Storage::disk('private')
                        ->path($generatedPath);

                    $finalHash = hash_file('sha256', $finalAbsolutePath);

                    if (! $finalHash) {
                        throw new RuntimeException(
                            'Unable to calculate the final signed PDF hash.'
                        );
                    }

                    // Mark fully signed only after successful PDF generation and storage.
                    $request->update([
                        'signed_document_path' => $generatedPath,
                        'signed_document_hash' => $finalHash,
                        'status' => 'fully_signed',
                        'completed_at' => now(),
                    ]);

                    // Complete the Meeting Minutes workflow only after the final
                    // signed PDF has been generated, stored, and hashed successfully.
                    if ($request->document_type === 'meeting_minutes') {

                        $session = SessionMeeting::query()
                            ->lockForUpdate()
                            ->findOrFail($request->document_id);

                        if ($session->minutes_status !== 'reviewed') {
                            throw new RuntimeException(
                                'The meeting minutes are no longer in the reviewed state.'
                            );
                        }

                        $session->minutes_status = 'finalized';
                        $session->minutes_finalized_by = $user->id;
                        $session->minutes_finalized_at = now();

                        // Store the final signed PDF path, not the unsigned source PDF.
                        $session->minutes_pdf_path = $generatedPath;

                        $session->save();

                        $this->audit(
                            $request,
                            $user,
                            'meeting_minutes_finalized',
                            'Meeting minutes finalized after all required signers completed electronic signing.',
                            [
                                'session_id' => $session->id,
                                'signed_document_path' => $generatedPath,
                                'signed_document_hash' => $finalHash,
                            ]
                        );
                    }

                    $this->audit(
                        $request,
                        $user,
                        'final_pdf_generated',
                        'Final signed PDF successfully generated and stored.',
                        [
                            'signed_document_path' => $generatedPath,
                            'signed_document_hash' => $finalHash,
                            'original_document_hash' => $request->document_hash,
                        ]
                    );

                } else {
                    $request->update([
                        'status' => 'partially_signed',
                    ]);
                }

                return $request->fresh()->load([
                    'initiator',
                    'signers.user',
                    'signers.signature',
                    'audits.user',
                ]);
            });

        } catch (Throwable $exception) {

            try {
                if (
                    $generatedPath &&
                    Storage::disk('private')->exists($generatedPath)
                ) {
                    Storage::disk('private')->delete($generatedPath);
                }
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            throw $exception;
        }
    }



    public function reject(
        int $signingRequestId,
        User $user,
        string $reason
    ): DocumentSigningRequest {
        return DB::transaction(function () use (
            $signingRequestId,
            $user,
            $reason
        ) {
            $request = DocumentSigningRequest::query()
                ->lockForUpdate()
                ->findOrFail($signingRequestId);

            if (! in_array($request->status, [
                'pending',
                'partially_signed',
            ], true)) {
                throw ValidationException::withMessages([
                    'signing' => 'This request cannot be rejected.',
                ]);
            }

            $signers = $request->signers()
                ->lockForUpdate()
                ->get();

            $nextSigner = $signers->firstWhere('status', 'pending');

            if (! $nextSigner || (int) $nextSigner->user_id !== (int) $user->id) {
                throw ValidationException::withMessages([
                    'signing' => 'You are not the current signer.',
                ]);
            }

            $nextSigner->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'remarks' => $reason,
            ]);

            $request->update([
                'status' => 'rejected',
            ]);

            $this->audit(
                $request,
                $user,
                'rejected',
                $reason,
                [
                    'signer_id' => $nextSigner->id,
                    'signing_order' => $nextSigner->signing_order,
                ]
            );

            return $request->fresh()->load('signers.user');
        });
    }


    public function revoke(
        int $signingRequestId,
        User $user,
        string $reason
    ): DocumentSigningRequest {
        return DB::transaction(function () use (
            $signingRequestId,
            $user,
            $reason
        ) {
            $request = DocumentSigningRequest::query()
                ->lockForUpdate()
                ->findOrFail($signingRequestId);

            if (in_array($request->status, [
                'revoked',
                'fully_signed',
            ], true)) {
                throw ValidationException::withMessages([
                    'signing' => 'This request can no longer be revoked.',
                ]);
            }

            $request->update([
                'status' => 'revoked',
                'revoked_at' => now(),
            ]);

            $this->audit(
                $request,
                $user,
                'revoked',
                $reason
            );

            return $request->fresh();
        });
    }



    protected function audit(
        DocumentSigningRequest $request,
        ?User $user,
        string $event,
        string $description,
        array $metadata = []
    ): void {
        DocumentSigningAudit::create([
            'signing_request_id' => $request->id,
            'user_id' => $user?->id,
            'event' => $event,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }


    /**
     * Resolve the active committee chairperson's LIS user account.
     */
    public function getCommitteeChairperson(int $committeeId): User
    {
        $committee = Committee::with([
            'chairperson.member.user',
        ])->findOrFail($committeeId);

        if (! $committee->isActive) {
            throw ValidationException::withMessages([
                'committee' => 'This committee is inactive.',
            ]);
        }

        $chairperson = $committee->chairperson;

        if (! $chairperson || ! $chairperson->member) {
            throw ValidationException::withMessages([
                'committee' => 'No valid chairperson is assigned to this committee.',
            ]);
        }

        $user = $chairperson->member->user;

        if (
            ! $user ||
            ! $user->isCouncilMember()
        ) {
            throw ValidationException::withMessages([
                'committee' => 'The committee chairperson does not have a valid LIS signing account.',
            ]);
        }

        return $user;
    }

}
