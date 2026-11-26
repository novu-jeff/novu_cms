
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Committee Referrals</h1>
            <p class="text-muted mb-0">
                Track referred agenda items and committee reports.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $statuses = [
            'referred' => 'Referred',
            'under_review' => 'Under Review',
            'report_submitted' => 'Report Submitted',
            'returned_to_council' => 'Returned to Council',
            'closed' => 'Closed',
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($statuses as $key => $label)
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted">{{ $label }}</div>
                        <div class="fs-3 fw-bold">
                            {{ $counts[$key] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Committee</label>
                    <select name="committee_id" class="form-select">
                        <option value="">All committees</option>
                        @foreach ($committees as $committee)
                            <option
                                value="{{ $committee->id }}"
                                @selected(request('committee_id') == $committee->id)
                            >
                                {{ $committee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $key => $label)
                            <option
                                value="{{ $key }}"
                                @selected(request('status') === $key)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary">Apply Filters</button>
                    <a
                        href="{{ route('secretary.committee-referrals.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Agenda Item</th>
                        <th>Committee</th>
                        <th>Referred On</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        @php
                            $document = $referral->sessionDocument?->document;
                            $statusLabel = $statuses[$referral->status]
                                ?? ucfirst(str_replace('_', ' ', $referral->status));
                        @endphp

                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $document->title ?? $document->subject ?? 'Agenda item #' . $referral->session_document_id }}
                                </div>
                                <div class="small text-muted">
                                    Referral #{{ $referral->id }}
                                </div>
                            </td>
                            <td>{{ $referral->committee?->name ?? 'Unknown committee' }}</td>
                            <td>
                                {{ $referral->referred_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td>
                                <span class="badge text-bg-light border">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a
                                    href="{{ route('secretary.committee-referrals.show', $referral->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View Referral
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                No committee referrals found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            {{ $referrals->links() }}
        </div>
    </div>
</div>
@endsection
