
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

    {{-- Page header --}}
    <div class="page-header p-4 mb-4 shadow-sm">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <div class="small text-white-50 mb-2">
                    <i class="fas fa-users me-1"></i>
                    Committee Member Workspace
                </div>

                <h3 class="fw-bold mb-2">
                    Committee Session Hall
                </h3>

                <p class="text-white-50 mb-0">
                    View your committee meetings, agendas, and official proceedings.
                </p>
            </div>

            <div class="text-white-50">
                <i class="fas fa-calendar-alt fa-3x"></i>
            </div>

        </div>

    </div>

    @php
        /*
         * These counts are based on the current paginator page.
         * They are page counts, not global database totals.
         */
        $currentMeetings = $meetings->getCollection();

        $ongoingMeetings = $currentMeetings->where('status', 'ongoing');
        $upcomingMeetings = $currentMeetings->where('status', 'scheduled');
        $completedMeetings = $currentMeetings->where('status', 'completed');
    @endphp

    {{-- Meeting statistics --}}
    <div class="row g-3 mb-4">

        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="stat-label">
                                Ongoing Sessions
                            </div>

                            <div class="stat-value text-success">
                                {{ $ongoingMeetings->count() }}
                            </div>
                        </div>

                        <div class="text-success opacity-50">
                            <i class="fas fa-broadcast-tower fa-2x"></i>
                        </div>

                    </div>

                    <small class="text-muted">
                        Currently active sessions on this page
                    </small>

                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="stat-label">
                                Upcoming Sessions
                            </div>

                            <div class="stat-value text-primary">
                                {{ $upcomingMeetings->count() }}
                            </div>
                        </div>

                        <div class="text-primary opacity-50">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>

                    </div>

                    <small class="text-muted">
                        Scheduled sessions on this page
                    </small>

                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <div class="stat-label">
                                Completed Sessions
                            </div>

                            <div class="stat-value text-secondary">
                                {{ $completedMeetings->count() }}
                            </div>
                        </div>

                        <div class="text-secondary opacity-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>

                    </div>

                    <small class="text-muted">
                        Completed sessions on this page
                    </small>

                </div>
            </div>
        </div>

    </div>

    {{-- Meeting list --}}
    <div class="card workspace-card">

        <div class="card-header bg-white border-0 py-3 px-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <h5 class="section-title">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    Committee Meetings
                </h5>

                <span class="badge bg-light text-dark border">
                    {{ $meetings->total() }} Total Meeting(s)
                </span>

            </div>

        </div>

        <div class="card-body p-3 p-lg-4">

            @forelse($meetings as $meeting)

                @php
                    $statusClass = match ($meeting->status) {
                        'ongoing' => 'bg-success',
                        'scheduled' => 'bg-primary',
                        'completed' => 'bg-secondary',
                        default => 'bg-light text-dark',
                    };

                    $statusIcon = match ($meeting->status) {
                        'ongoing' => 'fa-broadcast-tower',
                        'scheduled' => 'fa-calendar-check',
                        'completed' => 'fa-check-circle',
                        default => 'fa-calendar',
                    };
                @endphp

                <div class="meeting-card p-3 p-lg-4 mb-3">

                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                        {{-- Meeting information --}}
                        <div class="flex-grow-1">

                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">

                                <h5 class="meeting-title mb-0">
                                    {{ $meeting->title }}
                                </h5>

                                <span class="badge {{ $statusClass }}">
                                    <i class="fas {{ $statusIcon }} me-1"></i>
                                    {{ ucwords(str_replace('_', ' ', $meeting->status)) }}
                                </span>

                            </div>

                            <div class="text-muted small mb-3">
                                <i class="fas fa-users me-1"></i>
                                {{ $meeting->committee?->name ?? 'Committee' }}
                            </div>

                            <div class="d-flex flex-wrap gap-3 meeting-meta">

                                <span>
                                    <i class="fas fa-calendar me-1 text-primary"></i>
                                    {{ $meeting->meeting_date?->format('F d, Y') ?? 'Date not set' }}
                                </span>

                                <span>
                                    <i class="fas fa-clock me-1 text-primary"></i>
                                    {{ $meeting->start_time ?? 'Time not set' }}

                                    @if($meeting->end_time)
                                        – {{ $meeting->end_time }}
                                    @endif
                                </span>

                                <span>
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                    {{ $meeting->venue ?: 'Venue not specified' }}
                                </span>

                            </div>

                            <div class="mt-3 d-flex flex-wrap gap-3">

                                <span class="small text-muted">
                                    <i class="fas fa-list-ol me-1"></i>
                                    {{ $meeting->agendaItems->count() }}
                                    Agenda Item(s)
                                </span>

                                <span class="small text-muted">
                                    <i class="fas fa-user-check me-1"></i>
                                    {{ $meeting->attendances->count() }}
                                    Attendance Record(s)
                                </span>

                            </div>

                        </div>

                        {{-- Meeting action --}}
                        <div class="d-flex align-items-center">

                            <a href="{{ route(
                                'committee-portal.live-session.show',
                                $meeting->id
                            ) }}"
                               class="btn btn-session {{ $meeting->status === 'ongoing'
                                    ? 'btn-success'
                                    : 'btn-outline-primary' }}">

                                @if($meeting->status === 'ongoing')

                                    <i class="fas fa-sign-in-alt me-1"></i>
                                    Enter Session

                                @elseif($meeting->status === 'completed')

                                    <i class="fas fa-folder-open me-1"></i>
                                    View Proceedings

                                @else

                                    <i class="fas fa-eye me-1"></i>
                                    View Meeting

                                @endif

                            </a>

                        </div>

                    </div>

                </div>

            @empty

                {{-- Empty state --}}
                <div class="empty-state text-center">

                    <div class="empty-icon mb-3">
                        <i class="fas fa-calendar-xmark"></i>
                    </div>

                    <h5 class="fw-bold">
                        No Committee Meetings Found
                    </h5>

                    <p class="text-muted mb-0">
                        Meetings scheduled for your assigned committees
                        will appear here.
                    </p>

                </div>

            @endforelse

            {{-- Pagination --}}
            @if($meetings->hasPages())

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">

                    <small class="text-muted">
                        Showing {{ $meetings->firstItem() ?? 0 }}
                        to {{ $meetings->lastItem() ?? 0 }}
                        of {{ $meetings->total() }} meetings
                    </small>

                    {{ $meetings->links() }}

                </div>

            @endif

        </div>

    </div>

    {{-- Footer --}}
    <div class="text-center text-muted small py-4">
        Committee Portal &copy; {{ date('Y') }}
    </div>

</div>

</body>
</html>
