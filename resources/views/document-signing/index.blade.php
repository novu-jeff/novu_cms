
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Document Signing</h3>
            <p class="text-muted mb-0">
                Centralized electronic signing and document verification
            </p>
        </div>
    </div>

    @php
        $currentUser = auth()->user();
    @endphp

    <div class="row g-3 mb-4">
        @foreach([
            'pending' => 'Pending',
            'partially_signed' => 'Partially Signed',
            'fully_signed' => 'Fully Signed',
            'rejected' => 'Rejected',
        ] as $status => $label)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ $label }}</div>
                        <h3 class="fw-bold mb-0">
                            {{ $requests->getCollection()->where('status', $status)->count() }}
                        </h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">Signing Requests</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Initiated By</th>
                        <th>Signing Progress</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $item)
                        @php
                            $totalSigners = $item->signers->count();
                            $signedCount = $item->signers
                                ->where('status', 'signed')->count();

                            $progress = $totalSigners > 0
                                ? round(($signedCount / $totalSigners) * 100)
                                : 0;

                            $statusClasses = [
                                'draft' => 'secondary',
                                'pending' => 'warning',
                                'partially_signed' => 'info',
                                'fully_signed' => 'success',
                                'rejected' => 'danger',
                                'revoked' => 'dark',
                            ];
                        @endphp

                        <tr>
                            <td>
                                {{ $item->reference_no ?: 'DS-' . str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $item->document_title }}
                                </div>
                                <small class="text-muted">
                                    {{ $item->initiator->name ?? 'Unknown' }}
                                </small>
                            </td>

                            <td>
                                {{ config("document_signing.document_types.{$item->document_type}", $item->document_type) }}
                            </td>

                            <td>{{ $item->initiator->name ?? 'N/A' }}</td>

                            <td style="min-width: 140px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $signedCount }}/{{ $totalSigners }}</span>
                                    <span>{{ $progress }}%</span>
                                </div>

                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="width: {{ $progress }}%">
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge text-bg-{{ $statusClasses[$item->status] ?? 'secondary' }}">
                                    {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>

                            <td>
                                {{ $item->submitted_at?->format('M d, Y h:i A') ?? '—' }}
                            </td>

                            <td class="text-end">
                                <a href="{{ route('document-signing.show', $item) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No document signing requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white">
            {{ $requests->links() }}
        </div>
    </div>

</div>
@endsection
