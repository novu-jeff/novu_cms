
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Committee Dashboard - Committee Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          rel="stylesheet">

    <style>
        :root { --committee-ink:#172033; --committee-muted:#667085; --committee-line:#e7ebf2; --committee-primary:#3157d5; }
        body { background:#f6f8fb; color:var(--committee-ink); }
        .navbar { background:#248fc5 !important; box-shadow:0 3px 14px rgba(16,24,40,.08); }
        .navbar .nav-link { font-size: 12px; color:rgba(255,255,255,.72); padding:10px 14px; border-radius:9px; transition:all .18s ease; }
        .navbar .nav-link:hover,.navbar .nav-link.active { color:#fff; background:rgba(255,255,255,.12); }
        .page-header { background:#fff; color:var(--committee-ink); border:1px solid var(--committee-line); border-radius:16px; box-shadow:0 5px 20px rgba(16,24,40,.035); }
        .page-header .small { color:var(--committee-primary) !important; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .page-header h3 { font-size:clamp(1.5rem,2.4vw,2rem); font-weight:750; letter-spacing:-.035em; }
        .page-header p { color:var(--committee-muted) !important; }
        .stat-card,.workspace-card { border:1px solid var(--committee-line); border-radius:16px; box-shadow:0 5px 20px rgba(16,24,40,.035); overflow:hidden; }
        .stat-label { color:var(--committee-muted); font-size:.78rem; font-weight:700; }
        .stat-value { font-size:1.8rem; font-weight:750; letter-spacing:-.03em; color:var(--committee-ink); }
        .section-title { font-size:1rem; font-weight:750; margin-bottom:0; color:var(--committee-ink); }
        .committee-card { border:1px solid var(--committee-line); border-radius:14px; background:#fff; transition:all .18s ease; }
        .committee-card:hover { border-color:#b8c7fb; box-shadow:0 6px 20px rgba(16,24,40,.06); transform:translateY(-2px); }
        .committee-icon { width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;color:var(--committee-primary); }
        .meeting-row,.agenda-row { border-bottom:1px solid #eef1f5; padding:17px 0; }
        .meeting-row:last-child,.agenda-row:last-child { border-bottom:0; }
        .meeting-title { color:var(--committee-ink); font-weight:750; }
        .small-meta { color:var(--committee-muted); font-size:.82rem; }
        .status-count-card { min-height:105px; }
        .status-count { font-size:1.7rem;font-weight:750;color:var(--committee-ink); }
        .table thead th { background:#f8fafc;color:#667085;font-size:.72rem;font-weight:750;text-transform:uppercase;letter-spacing:.055em;white-space:nowrap;border-bottom:1px solid var(--committee-line); }
        .table td { font-size:.88rem;color:#475467;border-color:#eef1f5; }
        .empty-state { padding:38px 20px; }
        .empty-icon { font-size:2.25rem;color:#cbd5e1; }
        .pagination { margin-bottom:0; }
        .pagination .page-link { color:var(--committee-primary); }
        .pagination .active .page-link { background:var(--committee-primary);border-color:var(--committee-primary);color:#fff; }
        .dashboard-stat { height:100%; padding:1.15rem 1.25rem; border:1px solid var(--committee-line); border-radius:15px; background:#fff; box-shadow:0 5px 18px rgba(16,24,40,.035); }
        .dashboard-stat .stat-icon { width:2.6rem;height:2.6rem;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;color:var(--committee-primary);font-size:1.05rem; }
        .dashboard-stat .stat-number { font-size:1.8rem;font-weight:750;letter-spacing:-.03em;line-height:1.2;color:var(--committee-ink);margin-top:.9rem; }
        .dashboard-stat .stat-caption { color:var(--committee-muted);font-size:.8rem;margin-top:.25rem; }
        .card-header { border-bottom:1px solid var(--committee-line) !important; }
        .btn-outline-primary { --bs-btn-color:var(--committee-primary);--bs-btn-border-color:#c7d2fe;--bs-btn-hover-bg:var(--committee-primary);--bs-btn-hover-border-color:var(--committee-primary); }
        @media (max-width:767px) { .page-header { padding:1.25rem !important; } .dashboard-action,.meeting-action { width:100%; } }
    </style>
</head>

<body>

@php
    $statusColors = [
        'referred' => 'secondary',
        'under_review' => 'primary',
        'report_submitted' => 'info',
        'returned_to_council' => 'success',
        'closed' => 'dark',
    ];

    $itemStatusColors = [
        'pending' => 'secondary',
        'in_progress' => 'primary',
        'completed' => 'success',
        'deferred' => 'warning',
        'returned_for_revision' => 'danger',
        'for_further_study' => 'info',
    ];

    $memberName = Auth::guard('member')->user()?->member?->name
        ?? 'Committee Member';
@endphp

@include('committee-portal.partials.navbar')

<div class="container-fluid px-3 px-lg-4 py-4">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="page-header p-4 mb-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <div class="small text-white-50 mb-2">
                    COMMITTEE PORTAL / REFERRALS
                </div>

                <h3 class="fw-bold mb-2">
                    <i class="fas fa-file-lines me-2"></i>
                    Referral Details
                </h3>

                <p class="mb-0 text-white-50">
                    Review the referred document, committee deliberations,
                    recommendations, and related reports.
                </p>
            </div>

            <div>
                <span class="badge text-bg-{{ $statusColors[$referral->status] ?? 'secondary' }} fs-6 px-3 py-2">
                    {{ ucwords(str_replace('_', ' ', $referral->status)) }}
                </span>
            </div>

        </div>
    </div>

    {{-- Referral Summary --}}
    <div class="page-header p-4 mb-4 shadow-sm">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">

                <div>
                    <span class="info-label">REFERRED DOCUMENT</span>

                    <h4 class="fw-bold mb-2">
                        {{ $referral->sessionDocument?->document?->title ?? 'Untitled Document' }}
                    </h4>

                    <div class="text-muted">
                        <i class="fas fa-building me-1"></i>
                        {{ $referral->committee?->name ?? 'No committee assigned' }}
                    </div>
                </div>

                <div class="text-md-end">
                    <span class="info-label">REFERRAL ID</span>
                    <span class="fw-bold fs-5">#{{ $referral->id }}</span>
                </div>

            </div>

            <hr class="my-4">

            <div class="row g-3">

                <div class="col-md-6">
                    <span class="info-label">Referred Date</span>
                    <div class="info-value">
                        {{ $referral->referred_at?->format('F d, Y h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <span class="info-label">Committee</span>
                    <div class="info-value">
                        {{ $referral->committee?->name ?? '—' }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-7">

            {{-- Referral Remarks --}}
            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="section-title">
                        <i class="fas fa-comment-alt me-2"></i>
                        Referral Remarks
                    </h5>

                    <p class="text-muted mb-0 content-text">
                        {{ $referral->referral_remarks ?: 'No referral remarks provided.' }}
                    </p>

                </div>
            </div>

            {{-- Committee Deliberation --}}
            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="section-title">
                        <i class="fas fa-gavel me-2"></i>
                        Committee Deliberation
                    </h5>

                    @forelse($agendaItems as $item)

                        <div class="history-item">

                            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">

                                <div>
                                    <div class="fw-bold">
                                        {{ $item->meeting?->title ?? 'Committee Meeting' }}
                                    </div>

                                    <div class="small text-muted mt-1">
                                        <i class="far fa-calendar me-1"></i>
                                        {{ $item->meeting?->meeting_date?->format('F d, Y') ?? '—' }}

                                        <span class="mx-1">&middot;</span>

                                        Agenda Item #{{ $item->agenda_order }}
                                    </div>
                                </div>

                                <span class="badge text-bg-{{ $itemStatusColors[$item->status] ?? 'secondary' }}">
                                    {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                </span>

                            </div>

                            <hr>

                            @forelse($item->actions as $action)

                                @php
                                    $isFinalized = in_array($item->status, [
                                        'completed',
                                        'deferred',
                                        'returned_for_revision',
                                        'for_further_study',
                                    ]);

                                    $isFinalAction = $isFinalized && $loop->last;
                                @endphp

                                <div class="mb-3">

                                    <div class="fw-semibold mb-1">
                                        <i class="fas fa-clipboard-check me-1 text-primary"></i>
                                        {{ $isFinalAction ? 'Final Committee Action' : 'Deliberation Record' }}
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $action->acted_at?->format('M d, Y h:i A') ?? '—' }}

                                        <span class="mx-1">&middot;</span>

                                        <i class="far fa-user me-1"></i>
                                        {{ $action->actor?->name ?? 'Committee Secretary' }}
                                    </div>

                                    <span class="badge bg-dark">
                                        {{ ucwords(str_replace('_', ' ', $action->action)) }}
                                    </span>

                                    @if($action->reason)
                                        <div class="mt-3">
                                            <span class="info-label">Discussion / Reason</span>
                                            <p class="mb-0 content-text">
                                                {{ $action->reason }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($action->recommendation)
                                        <div class="mt-3">
                                            <span class="info-label">Recommendation</span>
                                            <p class="mb-0 content-text">
                                                {{ $action->recommendation }}
                                            </p>
                                        </div>
                                    @endif

                                </div>

                                @if(!$loop->last)
                                    <hr>
                                @endif

                            @empty
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    No deliberation records have been submitted yet.
                                </p>
                            @endforelse

                        </div>

                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-xmark fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">
                                This referral has not yet been included in a committee meeting agenda.
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Committee Recommendation --}}
            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="section-title">
                        <i class="fas fa-file-signature me-2"></i>
                        Committee Recommendation
                    </h5>

                    @if($referral->status === 'under_review')

                        <p class="text-muted small mb-4">
                            Prepare the committee's findings and recommendation.
                            Attach the signed committee report in PDF format.
                        </p>

                        <form method="POST"
                              action="{{ route('committee-portal.referrals.submit-report', $referral->id) }}"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="mb-3">
                                <label for="recommendation" class="form-label fw-semibold">
                                    Recommendation and Findings
                                </label>

                                <textarea
                                    name="recommendation"
                                    id="recommendation"
                                    class="form-control @error('recommendation') is-invalid @enderror"
                                    rows="6"
                                    maxlength="10000"
                                    required>{{ old('recommendation', $referral->recommendation) }}</textarea>

                                @error('recommendation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="report" class="form-label fw-semibold">
                                    Signed Committee Report (PDF)
                                </label>

                                <input
                                    type="file"
                                    name="report"
                                    id="report"
                                    class="form-control @error('report') is-invalid @enderror"
                                    accept="application/pdf"
                                    required>

                                <small class="text-muted">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    PDF only. Maximum file size: 10 MB.
                                </small>

                                @error('report')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit"
                                    class="btn btn-primary px-4"
                                    onclick="return confirm('Submit the committee recommendation and signed report?')">
                                <i class="fas fa-paper-plane me-1"></i>
                                Submit Committee Report
                            </button>

                        </form>

                    @elseif($referral->recommendation)

                        <p class="mb-0 content-text">
                            {{ $referral->recommendation }}
                        </p>

                        @if($referral->report_submitted_at)
                            <div class="mt-3 small text-muted">
                                <i class="far fa-calendar-check me-1"></i>
                                Submitted on:
                                {{ $referral->report_submitted_at->format('F d, Y h:i A') }}
                            </div>
                        @endif

                    @else

                        <p class="text-muted mb-0">
                            No recommendation submitted yet.
                        </p>

                    @endif

                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-5">

            {{-- Documents --}}
            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="section-title">
                        <i class="fas fa-folder-open me-2"></i>
                        Documents
                    </h5>

                    {{-- Shared Meeting-Level Committee Reports --}}
                    @forelse($referral->meetingReports as $meetingReport)

                        <div class="document-card">

                            <div class="fw-semibold mb-2">
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                Consolidated Committee Report
                            </div>

                            <div class="small text-muted mb-3">
                                {{ $meetingReport->meeting?->title ?? 'Committee Meeting' }}

                                @if($meetingReport->uploaded_at)
                                    <br>
                                    Uploaded:
                                    {{ $meetingReport->uploaded_at->format('F d, Y h:i A') }}
                                @endif
                            </div>

                            <a href="{{ route(
                                'committee-portal.referrals.meeting-reports.download',
                                [$referral->id, $meetingReport->id]
                            ) }}"
                               target="_blank"
                               rel="noopener"
                               class="btn btn-primary w-100">

                                <i class="fas fa-file-pdf me-1"></i>
                                View Consolidated Committee Report
                            </a>

                        </div>

                    @empty

                        <p class="text-muted small">
                            No consolidated committee report has been uploaded for this referral's meeting yet.
                        </p>

                    @endforelse

                    <hr class="my-4">

                    {{-- Existing Individual Report / Original Document --}}
                    @if($referral->report_file_path)

                        <a href="{{ route('committee-portal.referrals.document', $referral->id) }}"
                           target="_blank"
                           rel="noopener"
                           class="btn btn-outline-primary w-100 mb-2">

                            <i class="fas fa-file-pdf me-1"></i>
                            View Individual Committee Report
                        </a>

                    @elseif($referral->sessionDocument?->document?->file_path)

                        <a href="{{ route('committee-portal.referrals.document', $referral->id) }}"
                           target="_blank"
                           rel="noopener"
                           class="btn btn-outline-secondary w-100 mb-2">

                            <i class="fas fa-file-alt me-1"></i>
                            View Original Referred Document
                        </a>

                    @elseif($referral->meetingReports->isEmpty())

                        <p class="text-muted small mb-0">
                            No document file available.
                        </p>

                    @endif

                </div>
            </div>

            {{-- Referral Information --}}
            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="section-title">
                        <i class="fas fa-circle-info me-2"></i>
                        Referral Information
                    </h5>

                    <div class="mb-3">
                        <span class="info-label">Referral ID</span>
                        <div class="info-value">#{{ $referral->id }}</div>
                    </div>

                    <div class="mb-3">
                        <span class="info-label">Report Submitted</span>
                        <div class="info-value">
                            {{ $referral->report_submitted_at?->format('F d, Y h:i A') ?? 'Not yet submitted' }}
                        </div>
                    </div>

                    <div>
                        <span class="info-label">Last Updated</span>
                        <div class="info-value">
                            {{ $referral->updated_at?->format('F d, Y h:i A') ?? '—' }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <footer class="page-footer">
        Committee Portal &copy; {{ date('Y') }}
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
