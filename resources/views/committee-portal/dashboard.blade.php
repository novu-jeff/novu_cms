
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

@include('committee-portal.partials.navbar')



<div class="container-fluid px-3 px-lg-4 py-4">

    {{-- Welcome header --}}
    <div class="page-header p-4 mb-4 shadow-sm">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <div class="small mb-2">
                    <i class="fas fa-user-shield me-1"></i>
                    Standing Committee Member Workspace
                </div>

                <h3 class="fw-bold mb-2">
                    Welcome, {{ $member->name }}
                </h3>

                <p class="text-muted mb-0">
                    Monitor your committee assignments, meetings, deliberations,
                    and referred documents.
                </p>
            </div>

            <a href="{{ route('committee-portal.live-session.index') }}"
               class="btn btn-primary dashboard-action">

                <i class="fas fa-calendar-alt me-2"></i>
                View Committee Meetings

            </a>

        </div>

    </div>

    {{-- Dashboard overview --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="dashboard-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label">Assigned Committees</div>
                    <span class="stat-icon"><i class="fas fa-landmark"></i></span>
                </div>
                <div class="stat-number">{{ $committees->count() }}</div>
                <div class="stat-caption">Active committee assignments</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="dashboard-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label">Committee Meetings</div>
                    <span class="stat-icon"><i class="fas fa-calendar-alt"></i></span>
                </div>
                <div class="stat-number">{{ $meetings->count() }}</div>
                <div class="stat-caption">Meetings in this dashboard</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="dashboard-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label">Active Deliberations</div>
                    <span class="stat-icon"><i class="fas fa-comments"></i></span>
                </div>
                <div class="stat-number">{{ $activeAgendaItems->count() }}</div>
                <div class="stat-caption">Agenda items in progress</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="dashboard-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label">Committee Referrals</div>
                    <span class="stat-icon"><i class="fas fa-file-signature"></i></span>
                </div>
                <div class="stat-number">{{ $referrals->total() }}</div>
                <div class="stat-caption">Total referrals</div>
            </div>
        </div>
    </div>

    {{-- Assigned committees --}}
    <div class="mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title">
                <i class="fas fa-users text-primary me-2"></i>
                Assigned Committees
            </h5>

            <span class="badge bg-light text-dark border">
                {{ $committees->count() }} Assignment(s)
            </span>
        </div>

        <div class="row g-3">

            @forelse($committees as $committee)

                <div class="col-lg-4 col-md-6">

                    <div class="committee-card p-3 h-100">

                        <div class="d-flex align-items-center gap-3">

                            <div class="committee-icon">
                                <i class="fas fa-landmark"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $committee->name }}
                                </div>

                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Active Assignment
                                </small>
                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No active committee assignments found.
                    </div>
                </div>

            @endforelse

        </div>

    </div>

    {{-- Committee meetings --}}
    <div class="card workspace-card mb-4">

        <div class="card-header bg-white border-0 py-3 px-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <h5 class="section-title">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    Committee Meetings
                </h5>

                <a href="{{ route('committee-portal.live-session.index') }}"
                   class="btn btn-sm btn-outline-primary">

                    View All Meetings
                    <i class="fas fa-arrow-right ms-1"></i>

                </a>

            </div>

        </div>

        <div class="card-body px-3 px-lg-4">

            @forelse($meetings as $meeting)

                @php
                    $meetingStatusClass = match ($meeting->status) {
                        'ongoing' => 'bg-success',
                        'completed' => 'bg-secondary',
                        'scheduled' => 'bg-primary',
                        default => 'bg-light text-dark',
                    };
                @endphp

                <div class="meeting-row">

                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                        <div class="flex-grow-1">

                            <div class="meeting-title mb-2">
                                {{ $meeting->title }}
                            </div>

                            <div class="small-meta mb-2">
                                <i class="fas fa-users me-1 text-primary"></i>
                                {{ $meeting->committee?->name ?? 'Committee' }}
                            </div>

                            <div class="d-flex flex-wrap gap-3 small-meta">

                                <span>
                                    <i class="fas fa-calendar me-1 text-primary"></i>
                                    {{ $meeting->meeting_date?->format('F d, Y') ?? 'Date not set' }}
                                </span>

                                <span>
                                    <i class="fas fa-clock me-1 text-primary"></i>
                                    {{ $meeting->start_time ?? 'Time not set' }}
                                </span>

                                <span>
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                    {{ $meeting->venue ?: 'Venue not specified' }}
                                </span>

                            </div>

                            <div class="small text-muted mt-2">
                                <i class="fas fa-list-ol me-1"></i>
                                {{ $meeting->agendaItems->count() }} Agenda Item(s)
                            </div>

                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">

                            <span class="badge {{ $meetingStatusClass }}">
                                {{ ucwords(str_replace('_', ' ', $meeting->status)) }}
                            </span>

                            <a href="{{ route(
                                'committee-portal.live-session.show',
                                $meeting->id
                            ) }}"
                               class="btn btn-sm btn-outline-primary meeting-action">

                                <i class="fas fa-folder-open me-1"></i>
                                Open Meeting

                            </a>

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state text-center">

                    <div class="empty-icon mb-3">
                        <i class="fas fa-calendar-xmark"></i>
                    </div>

                    <h6 class="fw-bold">
                        No Committee Meetings
                    </h6>

                    <p class="text-muted mb-0 small">
                        No scheduled or ongoing committee meetings are currently available.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

    {{-- Active deliberations --}}
    <div class="card workspace-card mb-4">

        <div class="card-header bg-white border-0 py-3 px-4">

            <h5 class="section-title">
                <i class="fas fa-comments text-primary me-2"></i>
                Currently Under Deliberation
            </h5>

        </div>

        <div class="card-body px-3 px-lg-4">

            @forelse($activeAgendaItems as $item)

                <div class="agenda-row">

                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                        <div class="flex-grow-1">

                            <div class="fw-bold mb-2">
                                {{ $item->referral?->sessionDocument?->document?->title
                                    ?? 'Untitled Document' }}
                            </div>

                            <div class="small-meta">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $item->meeting?->title }}

                                &middot;

                                {{ $item->meeting?->committee?->name }}
                            </div>

                            <div class="small text-muted mt-2">
                                Agenda Item #{{ $item->agenda_order }}
                            </div>

                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">

                            <span class="badge bg-primary">
                                <i class="fas fa-circle me-1"></i>
                                In Progress
                            </span>

                            <a href="{{ route(
                                'committee-portal.referrals.show',
                                $item->committee_referral_id
                            ) }}"
                               class="btn btn-sm btn-outline-primary">

                                View Deliberation

                            </a>

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state text-center">

                    <div class="empty-icon mb-3">
                        <i class="fas fa-comments"></i>
                    </div>

                    <h6 class="fw-bold">
                        No Active Deliberations
                    </h6>

                    <p class="text-muted small mb-0">
                        There are currently no agenda items under deliberation.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

    {{-- Referral overview --}}
    <div class="mb-3">

        <h5 class="section-title">
            <i class="fas fa-chart-pie text-primary me-2"></i>
            Referral Overview
        </h5>

    </div>

    @php
        $statuses = [
            'referred' => [
                'label' => 'Referred',
                'icon' => 'fa-share-square',
                'color' => 'text-primary',
            ],
            'under_review' => [
                'label' => 'Under Review',
                'icon' => 'fa-search',
                'color' => 'text-warning',
            ],
            'report_submitted' => [
                'label' => 'Report Submitted',
                'icon' => 'fa-file-circle-check',
                'color' => 'text-success',
            ],
            'returned_to_council' => [
                'label' => 'Returned to Council',
                'icon' => 'fa-reply',
                'color' => 'text-info',
            ],
            'closed' => [
                'label' => 'Closed',
                'icon' => 'fa-check-circle',
                'color' => 'text-secondary',
            ],
        ];
    @endphp

    <div class="row g-3 mb-4">

        @foreach($statuses as $key => $status)

            <div class="col-6 col-md">

                <div class="card stat-card status-count-card h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start gap-2">

                            <div>
                                <div class="stat-label">
                                    {{ $status['label'] }}
                                </div>

                                <div class="status-count mt-2">
                                    {{ $counts[$key] ?? 0 }}
                                </div>
                            </div>

                            <i class="fas {{ $status['icon'] }} {{ $status['color'] }} opacity-75"></i>

                        </div>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

    {{-- Committee referrals --}}
    <div class="card workspace-card">

        <div class="card-header bg-white border-0 py-3 px-4">

            <h5 class="section-title">
                <i class="fas fa-file-alt text-primary me-2"></i>
                Committee Referrals
            </h5>

        </div>

        <div class="card-body px-3 px-lg-4">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Committee</th>
                            <th>Referred Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($referrals as $referral)

                            <tr>

                                <td>
                                    <a href="{{ route(
                                        'committee-portal.referrals.show',
                                        $referral->id
                                    ) }}"
                                       class="fw-semibold text-decoration-none">

                                        {{ $referral->sessionDocument?->document?->title
                                            ?? 'Session Document #' . $referral->session_document_id }}

                                    </a>

                                    <div class="small text-muted">
                                        Referral #{{ $referral->id }}
                                    </div>
                                </td>

                                <td>
                                    {{ $referral->committee?->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $referral->referred_at?->format('M d, Y') ?? '—' }}
                                </td>

                                <td>

                                    @php
                                        $referralStatusClass = match ($referral->status) {
                                            'referred' => 'bg-primary',
                                            'under_review' => 'bg-warning text-dark',
                                            'report_submitted' => 'bg-success',
                                            'returned_to_council' => 'bg-info text-dark',
                                            'closed' => 'bg-secondary',
                                            default => 'bg-light text-dark',
                                        };
                                    @endphp

                                    <span class="badge {{ $referralStatusClass }}">
                                        {{ ucwords(str_replace('_', ' ', $referral->status)) }}
                                    </span>

                                </td>

                                <td class="text-end">

                                    <a href="{{ route(
                                        'committee-portal.referrals.show',
                                        $referral->id
                                    ) }}"
                                       class="btn btn-sm btn-outline-primary">

                                        <i class="fas fa-eye me-1"></i>
                                        View Details

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">

                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>

                                    No committee referrals available.

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Referral pagination --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">

                <small class="text-muted">
                    Showing {{ $referrals->firstItem() ?? 0 }}
                    to {{ $referrals->lastItem() ?? 0 }}
                    of {{ $referrals->total() }} referrals
                </small>

                {{ $referrals->links() }}

            </div>

        </div>

    </div>

    {{-- Footer --}}
    <div class="text-center text-muted small py-4">
        Committee Portal &copy; {{ date('Y') }}
    </div>

</div>

</body>
</html>
