@extends('layouts.app')

@section('styles')
<style>
    .secretary-dashboard {
        --sd-border: #e2e8f0;
        --sd-muted: #64748b;
        --sd-heading: #0f172a;
        color: var(--sd-heading);
    }
    .secretary-dashboard .page-header {
        display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between;
        gap:1rem; padding-bottom:1.25rem; margin-bottom:1.5rem; border-bottom:1px solid var(--sd-border);
    }
    .secretary-dashboard .eyebrow {
        margin-bottom:.35rem; color:var(--sd-muted); font-size:.72rem; font-weight:700;
        letter-spacing:.09em; text-transform:uppercase;
    }
    .secretary-dashboard .page-title {
        margin:0 0 .35rem; font-size:clamp(1.5rem,2vw,2rem); font-weight:750; letter-spacing:-.035em;
    }
    .secretary-dashboard .page-description { margin:0; color:var(--sd-muted); font-size:.925rem; }
    .secretary-dashboard .section-heading {
        margin:0; color:#0f172a; font-size:1rem; font-weight:700;
    }
    .secretary-dashboard .section-description { margin:.25rem 0 0; color:var(--sd-muted); font-size:.825rem; }
    .secretary-dashboard .metric-card,
    .secretary-dashboard .panel {
        height:100%; border:1px solid var(--sd-border); border-radius:.85rem;
        background:#fff; box-shadow:0 4px 18px rgba(15,23,42,.035);
    }
    .secretary-dashboard .metric-card { padding:1.1rem 1.15rem; }
    .secretary-dashboard .metric-top { display:flex; justify-content:space-between; align-items:center; gap:.75rem; }
    .secretary-dashboard .metric-label { color:var(--sd-muted); font-size:.82rem; font-weight:600; }
    .secretary-dashboard .metric-icon {
        display:inline-flex; align-items:center; justify-content:center; width:2.45rem; height:2.45rem;
        border-radius:.7rem; background:#f1f5f9; color:#334155; font-size:1rem;
    }
    .secretary-dashboard .metric-value { margin-top:.7rem; font-size:1.9rem; line-height:1.1; font-weight:750; letter-spacing:-.04em; }
    .secretary-dashboard .metric-note { margin-top:.4rem; color:var(--sd-muted); font-size:.78rem; }
    .secretary-dashboard .panel-header {
        display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.75rem;
        padding:1.05rem 1.2rem; border-bottom:1px solid var(--sd-border);
    }
    .secretary-dashboard .panel-body { padding:1.1rem 1.2rem; }
    .secretary-dashboard .data-table { margin:0; }
    .secretary-dashboard .data-table thead th {
        padding:.8rem 1rem; color:#475569; background:#f8fafc; border-bottom:1px solid var(--sd-border);
        font-size:.7rem; font-weight:700; letter-spacing:.055em; text-transform:uppercase; white-space:nowrap;
    }
    .secretary-dashboard .data-table tbody td { padding:.9rem 1rem; border-color:#f1f5f9; vertical-align:middle; font-size:.875rem; }
    .secretary-dashboard .data-table tbody tr:last-child td { border-bottom:0; }
    .secretary-dashboard .status-badge {
        display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .6rem;
        border:1px solid transparent; border-radius:999px; font-size:.72rem; font-weight:650; white-space:nowrap;
    }
    .secretary-dashboard .status-forwarded { color:#92400e; background:#fffbeb; border-color:#fde68a; }
    .secretary-dashboard .status-approved { color:#047857; background:#ecfdf5; border-color:#a7f3d0; }
    .secretary-dashboard .status-ongoing { color:#1d4ed8; background:#eff6ff; border-color:#bfdbfe; }
    .secretary-dashboard .status-completed { color:#475569; background:#f1f5f9; border-color:#e2e8f0; }
    .secretary-dashboard .status-cancelled { color:#b91c1c; background:#fef2f2; border-color:#fecaca; }
    .secretary-dashboard .empty-state { padding:2rem 1rem !important; text-align:center; color:var(--sd-muted); }
    .secretary-dashboard .empty-icon {
        display:inline-flex; align-items:center; justify-content:center; width:2.8rem; height:2.8rem;
        margin-bottom:.65rem; border-radius:.8rem; background:#f1f5f9; color:#64748b; font-size:1.15rem;
    }
    .secretary-dashboard .workflow-item { display:flex; align-items:flex-start; gap:.85rem; padding:.9rem 0; }
    .secretary-dashboard .workflow-item + .workflow-item { border-top:1px solid #f1f5f9; }
    .secretary-dashboard .workflow-icon {
        display:flex; align-items:center; justify-content:center; flex:0 0 2.3rem; height:2.3rem;
        border-radius:.65rem; background:#f1f5f9; color:#334155;
    }
    .secretary-dashboard .workflow-title { margin:0; font-size:.875rem; font-weight:650; }
    .secretary-dashboard .workflow-text { margin:.25rem 0 0; color:var(--sd-muted); font-size:.8rem; line-height:1.5; }
    .secretary-dashboard .note {
        display:flex; align-items:flex-start; gap:.7rem; padding:.9rem 1rem;
        border:1px solid #dbeafe; border-radius:.75rem; background:#eff6ff; color:#1e40af; font-size:.82rem;
    }
    @media (max-width:767.98px) {
        .secretary-dashboard .page-header { align-items:stretch; }
        .secretary-dashboard .panel-header, .secretary-dashboard .panel-body { padding:1rem; }
        .secretary-dashboard .data-table thead th, .secretary-dashboard .data-table tbody td { padding:.8rem; }
    }
</style>
@endsection

@section('content')
@php
    /*
     * Optional dashboard data:
     * $documents, $sessions, $pendingAgendaItems, and $attendanceSummary.
     * Pass these from the SB Secretary dashboard controller when available.
     * This view safely falls back to empty collections so it can be introduced
     * before the dashboard controller is fully wired.
     */
    $dashboardDocuments = collect($documents ?? []);
    $dashboardSessions = collect($sessions ?? []);
    $dashboardAgendaItems = collect($pendingAgendaItems ?? []);

    $pendingDocumentsCount = $dashboardDocuments->where('status', 'forwarded')->count();
    $approvedDocumentsCount = $dashboardDocuments->where('status', 'approved')->count();

    $upcomingSessions = $dashboardSessions
        ->filter(function ($session) {
            return ($session->status ?? null) === 'upcoming';
        })
        ->sortBy('session_date')
        ->take(5);

    $activeSessionsCount = $dashboardSessions->where('status', 'ongoing')->count();
    $upcomingSessionsCount = $dashboardSessions->where('status', 'upcoming')->count();
@endphp

<div class="container py-4 py-lg-5 secretary-dashboard">
    <header class="page-header">
        <div>
            <div class="eyebrow">Paperless Session Management System</div>
            <h1 class="page-title">SB Secretary Dashboard</h1>
            <p class="page-description">Monitor session schedules, review member submissions, and prepare legislative agenda items.</p>
        </div>
        <div class="text-md-end">
            <div class="small text-secondary">Today</div>
            <div class="fw-semibold">{{ now()->format('l, M d, Y') }}</div>
        </div>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-top">
                    <div class="metric-label">Pending document reviews</div>
                    <span class="metric-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $pendingDocumentsCount }}</div>
                <div class="metric-note">Forwarded submissions awaiting a decision</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-top">
                    <div class="metric-label">Approved documents</div>
                    <span class="metric-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $approvedDocumentsCount }}</div>
                <div class="metric-note">Approved submissions in the provided document list</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-top">
                    <div class="metric-label">Upcoming sessions</div>
                    <span class="metric-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $upcomingSessionsCount }}</div>
                <div class="metric-note">Scheduled sessions in the provided session list</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-top">
                    <div class="metric-label">Active sessions</div>
                    <span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">{{ $activeSessionsCount }}</div>
                <div class="metric-note">Sessions marked as ongoing</div>
            </div>
        </div>
    </div>


{{-- Voting Overview --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="section-heading">Voting Overview</h2>
                    <p class="section-description">
                        Monitor active motions, participation, and finalized results.
                    </p>
                </div>
                <span class="badge text-bg-light border">
                    {{ $recentVotes->count() }} recent votes
                </span>
            </div>

            <div class="panel-body">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-4">
                        <div class="metric-card">
                            <div class="metric-label">Open Votes</div>
                            <div class="metric-value">
                                {{ $votingStats['open'] }}
                            </div>
                            <div class="metric-note">
                                Currently accepting votes
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="metric-card">
                            <div class="metric-label">Closed Votes</div>
                            <div class="metric-value">
                                {{ $votingStats['closed'] }}
                            </div>
                            <div class="metric-note">
                                Awaiting finalization
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="metric-card">
                            <div class="metric-label">Finalized Votes</div>
                            <div class="metric-value">
                                {{ $votingStats['finalized'] }}
                            </div>
                            <div class="metric-note">
                                Voting results recorded
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="section-heading mb-3">Recent Voting Activity</h3>

                <div class="table-responsive">
                    <table class="table table-hover align-middle data-table">
                        <thead>
                            <tr>
                                <th>Motion</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Participation</th>
                                <th>Results</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentVotes as $vote)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $vote->motion_title }}
                                        </div>
                                        @if($vote->motion_description)
                                            <div class="small text-secondary mt-1">
                                                {{ \Illuminate\Support\Str::limit(
                                                    $vote->motion_description,
                                                    70
                                                ) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $vote->voting_method === 'secret'
                                            ? 'Secret Ballot'
                                            : 'Roll Call' }}
                                    </td>

                                    <td>
                                        @if($vote->status === 'open')
                                            <span class="status-badge status-ongoing">
                                                Open
                                            </span>
                                        @elseif($vote->status === 'closed')
                                            <span class="status-badge status-forwarded">
                                                Closed
                                            </span>
                                        @else
                                            <span class="status-badge status-approved">
                                                Finalized
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $vote->ballot_count }}
                                        /
                                        {{ $vote->eligible_count }}
                                        <div class="small text-secondary">
                                            Members voted
                                        </div>
                                    </td>

                                    <td>
                                        @if($vote->status === 'finalized')
                                            <span class="text-success">
                                                Yes: {{ $vote->yes_count }}
                                            </span>
                                            <br>
                                            <span class="text-danger">
                                                No: {{ $vote->no_count }}
                                            </span>
                                            <br>
                                            <span class="text-secondary">
                                                Abstain: {{ $vote->abstain_count }}
                                            </span>
                                        @else
                                            <span class="text-secondary">
                                                Available after finalization
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <a
                                            href="{{ route(
                                                'secretary.votes.manage',
                                                $vote->session_id
                                            ) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <div class="empty-icon">
                                            <i class="bi bi-check2-square"
                                               aria-hidden="true"></i>
                                        </div>
                                        <div class="fw-semibold text-dark mb-1">
                                            No voting activity yet
                                        </div>
                                        <div class="small">
                                            Votes created for session motions
                                            will appear here.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>


    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h2 class="section-heading">Upcoming Sessions</h2>
                        <p class="section-description">Review the next scheduled sessions and their current status.</p>
                    </div>
                    <span class="badge text-bg-light border">{{ $upcomingSessions->count() }} listed</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle data-table">
                        <thead>
                            <tr>
                                <th scope="col">Session</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingSessions as $session)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $session->title ?? 'Untitled session' }}</div>
                                        @if(!empty($session->description))
                                            <div class="small text-secondary mt-1">{{ \Illuminate\Support\Str::limit($session->description, 75) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if(!empty($session->session_date))
                                            {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
                                        @else
                                            <span class="text-secondary">Date not set</span>
                                        @endif
                                    </td>
                                    <td><span class="status-badge status-forwarded">Upcoming</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-calendar2-x" aria-hidden="true"></i></div>
                                        <div class="fw-semibold text-dark mb-1">No upcoming sessions to display</div>
                                        <div class="small">Upcoming sessions will appear here when the controller passes the session list.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel mt-4">
                <div class="panel-header">
                    <div>
                        <h2 class="section-heading">Document Status</h2>
                        <p class="section-description">Recent submissions available to the Secretary.</p>
                    </div>
                    <span class="badge text-bg-light border">{{ $dashboardDocuments->count() }} listed</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle data-table">
                        <thead>
                            <tr>
                                <th scope="col">Document</th>
                                <th scope="col">Submitted by</th>
                                <th scope="col">Date submitted</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dashboardDocuments->sortByDesc('created_at')->take(5) as $doc)
                                <tr>
                                    <td><div class="fw-semibold">{{ $doc->title ?? 'Untitled document' }}</div></td>
                                    <td>{{ optional($doc->member)->name ?? 'Unknown member' }}</td>
                                    <td class="text-nowrap">
                                        @if(!empty($doc->created_at))
                                            {{ \Carbon\Carbon::parse($doc->created_at)->format('M d, Y') }}
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($doc->status ?? '') === 'approved')
                                            <span class="status-badge status-approved">Approved</span>
                                        @elseif(($doc->status ?? '') === 'forwarded')
                                            <span class="status-badge status-forwarded">Forwarded</span>
                                        @else
                                            <span class="status-badge status-completed">{{ ucfirst($doc->status ?? 'Unknown') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></div>
                                        <div class="fw-semibold text-dark mb-1">No document records to display</div>
                                        <div class="small">Pass the Secretary's document collection to this view to populate this section.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="panel mb-4">
                <div class="panel-header">
                    <div>
                        <h2 class="section-heading">Secretary's Workflow</h2>
                        <p class="section-description">Core responsibilities for session preparation.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="workflow-item">
                        <span class="workflow-icon"><i class="bi bi-inbox" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="workflow-title">Review forwarded documents</h3>
                            <p class="workflow-text">Check each submission for completeness, then approve it for a session or reject it with remarks.</p>
                        </div>
                    </div>
                    <div class="workflow-item">
                        <span class="workflow-icon"><i class="bi bi-list-check" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="workflow-title">Prepare the session agenda</h3>
                            <p class="workflow-text">Organize approved submissions under the appropriate upcoming session before the meeting.</p>
                        </div>
                    </div>
                    <div class="workflow-item">
                        <span class="workflow-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="workflow-title">Record attendance and quorum</h3>
                            <p class="workflow-text">Confirm attendance and quorum when the attendance feature is connected to the dashboard.</p>
                        </div>
                    </div>
                    <div class="workflow-item">
                        <span class="workflow-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="workflow-title">Maintain session records</h3>
                            <p class="workflow-text">Keep agenda items, remarks, minutes, and other official proceedings organized for reference.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel mb-4">
                <div class="panel-header">
                    <div>
                        <h2 class="section-heading">Pending Submissions</h2>
                        <p class="section-description">Forwarded documents awaiting the Secretary’s review.</p>
                    </div>
                    <span class="badge text-bg-light border">{{ $dashboardAgendaItems->count() }}</span>
                </div>
                <div class="panel-body">
                    @forelse($dashboardAgendaItems->take(5) as $item)
                        <div class="workflow-item">
                            <span class="workflow-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                            <div>
                                <h3 class="workflow-title">{{ $item->title ?? $item->document->title ?? 'Agenda item' }}</h3>
                                <p class="workflow-text">
                                    {{ $item->status ? ucfirst($item->status) : 'Pending review' }}
                                    @if(!empty($item->session->title))
                                        · {{ $item->session->title }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <div class="empty-icon"><i class="bi bi-list-task" aria-hidden="true"></i></div>
                            <div class="fw-semibold text-dark mb-1">No pending submissions</div>
                            <div class="small text-secondary">Forwarded documents awaiting review will appear here.</div>
                        </div>
                    @endforelse
                </div>
            </section>

            <div class="note">
                <i class="bi bi-info-circle mt-1" aria-hidden="true"></i>
                <div>
                    <strong>Implementation note</strong>
                    <div class="mt-1">This is a new dashboard view. The existing Member Documents page and its approve/reject/session-assignment actions are not replaced. The controller must pass the collections used by the dashboard for live counts and lists.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
