
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('document-signing.index') }}"
               class="text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i> Back to Signing Requests
            </a>

            <h3 class="fw-bold mt-2 mb-1">
                {{ $signingRequest->document_title }}
            </h3>

            <div class="text-muted">
                {{ $signingRequest->reference_no ?: 'DS-' . str_pad($signingRequest->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <span class="badge fs-6 text-bg-{{
            [
                'pending' => 'warning',
                'partially_signed' => 'info',
                'fully_signed' => 'success',
                'rejected' => 'danger',
                'revoked' => 'dark',
                'draft' => 'secondary',
            ][$signingRequest->status] ?? 'secondary'
        }}">
            {{ ucwords(str_replace('_', ' ', $signingRequest->status)) }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Document Information</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Document Type</small>
                            <div class="fw-semibold">
                                {{ config("document_signing.document_types.{$signingRequest->document_type}") }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Initiated By</small>
                            <div class="fw-semibold">
                                {{ $signingRequest->initiator->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Date Submitted</small>
                            <div class="fw-semibold">
                                {{ $signingRequest->submitted_at?->format('F d, Y h:i A') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Completed At</small>
                            <div class="fw-semibold">
                                {{ $signingRequest->completed_at?->format('F d, Y h:i A') ?? 'Not completed' }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('document-signing.preview', $signingRequest) }}"
                           target="_blank"
                           class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>
                            View Original PDF
                        </a>
                        @if($signingRequest->status === 'fully_signed'
                            && $signingRequest->signed_document_path)

                            <a href="{{ route('document-signing.download', $signingRequest) }}"
                            class="btn btn-success ms-2">
                                <i class="fas fa-download me-2"></i>
                                Download Final Signed PDF
                            </a>

                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Signing Sequence</h5>
                </div>

                <div class="card-body">
                    @foreach($signingRequest->signers as $signer)
                        <div class="d-flex align-items-start gap-3 mb-4">

                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center"
                                 style="width:42px;height:42px;flex-shrink:0;">
                                @if($signer->status === 'signed')
                                    <i class="fas fa-check text-success"></i>
                                @elseif($signer->status === 'rejected')
                                    <i class="fas fa-times text-danger"></i>
                                @else
                                    <span>{{ $signer->signing_order }}</span>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-semibold">
                                    {{ $signer->user->name ?? 'Unknown Signer' }}
                                </div>

                                <small class="text-muted">
                                    {{ ucwords(str_replace('_', ' ', $signer->signer_role)) }}
                                </small>

                                <div class="mt-1">
                                    @if($signer->status === 'signed')
                                        <span class="badge text-bg-success">Signed</span>
                                        <small class="text-muted ms-2">
                                            {{ $signer->signed_at?->format('M d, Y h:i A') }}
                                        </small>
                                    @elseif($signer->status === 'rejected')
                                        <span class="badge text-bg-danger">Rejected</span>
                                        <div class="small text-danger mt-1">
                                            {{ $signer->remarks }}
                                        </div>
                                    @else
                                        <span class="badge text-bg-secondary">Pending</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Signing Audit Trail</h5>
                </div>

                <div class="card-body">
                    @forelse($signingRequest->audits as $audit)
                        <div class="border-start ps-3 pb-3 mb-3">
                            <div class="fw-semibold">
                                {{ ucwords(str_replace('_', ' ', $audit->event)) }}
                            </div>

                            <div class="small text-muted">
                                {{ $audit->user->name ?? 'System' }}
                                · {{ $audit->created_at?->format('M d, Y h:i A') }}
                            </div>

                            <div class="small mt-1">
                                {{ $audit->description }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No audit records available.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <div class="col-lg-4">

            @php
                $currentSigner = $signingRequest->signers
                    ->firstWhere('status', 'pending');

                $canSign = $currentSigner
                    && (int) $currentSigner->user_id === (int) auth()->id()
                    && in_array($signingRequest->status, ['pending', 'partially_signed']);
            @endphp

            @if($canSign)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Your Signing Action</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted small">
                            Review the original PDF before signing. Your action will be recorded in the document audit trail.
                        </p>

                        <form method="POST"
                              action="{{ route('document-signing.sign', $signingRequest) }}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Signature Image (PNG, optional)
                                </label>
                                <input type="file"
                                       name="signature"
                                       class="form-control"
                                       accept="image/png">
                                <small class="text-muted">
                                    This is an electronic signature image, not a certificate-based digital signature.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Confirm Your Password
                                </label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       required
                                       autocomplete="current-password">
                            </div>

                            
                            <div class="form-check mb-3">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="consent"
                                    id="signingConsent"
                                    value="1"
                                    required>

                                <label class="form-check-label small" for="signingConsent">
                                    I have reviewed this document and intend to electronically sign it.
                                </label>
                            </div>


                            <button type="submit"
                                    class="btn btn-primary w-100">
                                <i class="fas fa-signature me-1"></i>
                                Confirm and Sign
                            </button>
                        </form>

                        <hr>

                        <form method="POST"
                              action="{{ route('document-signing.reject', $signingRequest) }}">
                            @csrf

                            <label class="form-label fw-semibold">
                                Rejection Reason
                            </label>

                            <textarea name="reason"
                                      class="form-control mb-2"
                                      rows="3"
                                      minlength="5"
                                      maxlength="2000"
                                      required></textarea>

                            <button type="submit"
                                    class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Reject this signing request?')">
                                Reject Document
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($signingRequest->status === 'fully_signed')
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5 class="fw-bold">Signing Completed</h5>
                        <p class="text-muted small">
                            All assigned signers have completed their signing actions.
                        </p>
                    </div>
                </div>
            @endif

            @can('revoke', $signingRequest)
                @if(! in_array($signingRequest->status, ['fully_signed', 'revoked']))
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h6 class="fw-bold">Revoke Request</h6>

                            <form method="POST"
                                  action="{{ route('document-signing.revoke', $signingRequest) }}">
                                @csrf

                                <textarea name="reason"
                                          class="form-control mb-2"
                                          rows="3"
                                          minlength="5"
                                          maxlength="2000"
                                          placeholder="Reason for revocation"
                                          required></textarea>

                                <button type="submit"
                                        class="btn btn-outline-secondary w-100"
                                        onclick="return confirm('Revoke this signing request?')">
                                    Revoke Signing Request
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

        </div>
    </div>
</div>
@endsection
