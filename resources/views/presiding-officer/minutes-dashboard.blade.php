@extends('layouts.app')

@section('content')
<style>
    .presiding-dashboard {
        --pd-ink: #172033;
        --pd-muted: #667085;
        --pd-line: #e7ebf2;
        --pd-primary: #3157d5;
        color: var(--pd-ink);
    }
    .presiding-dashboard .page-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .presiding-dashboard .eyebrow {
        color: var(--pd-primary);
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .09em;
        text-transform: uppercase;
        margin-bottom: .45rem;
    }
    .presiding-dashboard .page-title {
        font-size: clamp(1.5rem, 2.4vw, 2rem);
        font-weight: 750;
        letter-spacing: -.035em;
        margin: 0 0 .4rem;
    }
    .presiding-dashboard .page-subtitle {
        color: var(--pd-muted);
        margin: 0;
        font-size: .95rem;
    }
    .presiding-dashboard .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        border: 1px solid var(--pd-line);
        background: #fff;
        border-radius: 999px;
        padding: .55rem .85rem;
        color: #475467;
        font-size: .85rem;
        font-weight: 650;
        white-space: nowrap;
    }
    .presiding-dashboard .summary-chip .dot {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: #e6a23c;
    }
    .presiding-dashboard .notice {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: .9rem 1rem;
        margin-bottom: 1rem;
    }
    .presiding-dashboard .notice .notice-icon { font-size: 1.05rem; line-height: 1.4; }
    .presiding-dashboard .notice-success { color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
    .presiding-dashboard .notice-error { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
    .presiding-dashboard .dashboard-card {
        border: 1px solid var(--pd-line);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 5px 20px rgba(16, 24, 40, .035);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .presiding-dashboard .dashboard-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid var(--pd-line);
    }
    .presiding-dashboard .section-title {
        display: flex;
        align-items: center;
        gap: .7rem;
        margin: 0;
        font-size: 1rem;
        font-weight: 750;
    }
    .presiding-dashboard .section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 10px;
        background: #eef2ff;
        color: var(--pd-primary);
        flex-shrink: 0;
    }
    .presiding-dashboard .count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        background: #fff4d6;
        color: #8a5700;
        font-size: .78rem;
        font-weight: 750;
    }
    .presiding-dashboard .table { --bs-table-bg: transparent; }
    .presiding-dashboard .table thead th {
        background: #f8fafc;
        color: #667085;
        font-size: .72rem;
        font-weight: 750;
        letter-spacing: .055em;
        text-transform: uppercase;
        white-space: nowrap;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid var(--pd-line);
    }
    .presiding-dashboard .table tbody td {
        padding: 1rem 1.25rem;
        border-color: #eef1f5;
        color: #475467;
        font-size: .9rem;
    }
    .presiding-dashboard .table tbody tr:last-child td { border-bottom: 0; }
    .presiding-dashboard .session-name { color: var(--pd-ink); font-weight: 700; }
    .presiding-dashboard .date-value { white-space: nowrap; }
    .presiding-dashboard .date-value i { color: #98a2b3; margin-right: .4rem; }
    .presiding-dashboard .btn-review {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border-radius: 9px;
        padding: .55rem .8rem;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
    }
    .presiding-dashboard .btn-review-primary {
        background: var(--pd-primary);
        color: #fff;
        border: 1px solid var(--pd-primary);
    }
    .presiding-dashboard .btn-review-primary:hover { background: #2547b8; color: #fff; transform: translateY(-1px); }
    .presiding-dashboard .btn-review-secondary {
        color: #344054;
        background: #fff;
        border: 1px solid #d0d5dd;
    }
    .presiding-dashboard .btn-review-secondary:hover { background: #f8fafc; border-color: #98a2b3; color: #172033; }
    .presiding-dashboard .empty-state { padding: 2.75rem 1.25rem; text-align: center; }
    .presiding-dashboard .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 16px;
        margin: 0 auto .9rem;
        background: #f2f4f7;
        color: #667085;
        font-size: 1.25rem;
    }
    .presiding-dashboard .empty-title { color: #344054; font-weight: 750; margin-bottom: .3rem; }
    .presiding-dashboard .empty-copy { color: var(--pd-muted); font-size: .88rem; margin: 0; }
    .presiding-dashboard .pagination { margin-bottom: 0; }
    .presiding-dashboard .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid #eef1f5; }
    @media (max-width: 767.98px) {
        .presiding-dashboard .page-heading { flex-direction: column; }
        .presiding-dashboard .dashboard-card-header { padding: 1rem; }
        .presiding-dashboard .table thead th,
        .presiding-dashboard .table tbody td { padding: .85rem 1rem; }
        .presiding-dashboard .btn-review { width: 100%; }
    }

    .presiding-dashboard .pd-stat-card{height:100%;padding:1.25rem;border:1px solid var(--pd-line);border-radius:15px;background:#fff;box-shadow:0 5px 18px rgba(16,24,40,.035)}
    .presiding-dashboard .pd-stat-top{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.85rem}
    .presiding-dashboard .pd-stat-label{color:var(--pd-muted);font-size:.85rem;font-weight:700}
    .presiding-dashboard .pd-stat-icon{display:inline-flex;align-items:center;justify-content:center;width:2.6rem;height:2.6rem;border-radius:12px;background:#eef2ff;color:var(--pd-primary);flex-shrink:0}
    .presiding-dashboard .pd-stat-value{color:var(--pd-ink);font-size:2.25rem;line-height:1.1;font-weight:800;letter-spacing:-.04em;margin-bottom:.55rem}
    .presiding-dashboard .pd-stat-note{color:var(--pd-muted);font-size:.83rem;line-height:1.45;margin:0}
    .presiding-dashboard .pd-vote-card{height:100%;padding:1rem 1.1rem;border:1px solid var(--pd-line);border-radius:12px;background:#fff}
    .presiding-dashboard .pd-vote-card .metric-label{color:var(--pd-muted);font-size:.85rem;font-weight:700;margin-bottom:.5rem}
    .presiding-dashboard .pd-vote-card .metric-value{color:var(--pd-ink);font-size:2rem;font-weight:800;line-height:1.1;margin-bottom:.4rem}
    .presiding-dashboard .pd-vote-card .metric-note{color:var(--pd-muted);font-size:.82rem;margin:0}

</style>

<div class="container py-4 presiding-dashboard">
    <div class="page-heading">
        <div>
            <div class="eyebrow">Legislative Workflow</div>
            <h1 class="page-title">Presiding Officer Dashboard</h1>
            <p class="page-subtitle">Review minutes submitted by the SB Secretary and follow up on returned minutes.</p>
        </div>
        <div class="summary-chip">
            <span class="dot" aria-hidden="true"></span>
            {{ $pendingSessions->total() }} pending {{ \Illuminate\Support\Str::plural('review', $pendingSessions->total()) }}
        </div>
    </div>


    {{-- Dashboard KPI figures --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['label'=>'Minutes Awaiting Review','value'=>$pendingReviewCount,'note'=>'Minutes submitted by the Secretary','icon'=>'fa-file-alt','style'=>''],
            ['label'=>'Returned for Corrections','value'=>$returnedMinutesCount,'note'=>'Minutes returned to the Secretary','icon'=>'fa-undo-alt','style'=>'background:#fff4e5;color:#b54708;'],
            ['label'=>'Previous Minutes Pending','value'=>$previousMinutesPendingCount,'note'=>'Awaiting a recorded decision','icon'=>'fa-history','style'=>'background:#fff4d6;color:#8a5700;'],
            ['label'=>'Decisions Recorded','value'=>$previousMinutesRecordedCount,'note'=>'Previous Minutes decisions recorded','icon'=>'fa-clipboard-check','style'=>'background:#ecfdf3;color:#027a48;'],
        ] as $stat)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="pd-stat-card">
                    <div class="pd-stat-top"><div class="pd-stat-label">{{ $stat['label'] }}</div><span class="pd-stat-icon" style="{{ $stat['style'] }}" aria-hidden="true"><i class="fas {{ $stat['icon'] }}"></i></span></div>
                    <div class="pd-stat-value">{{ $stat['value'] }}</div>
                    <p class="pd-stat-note">{{ $stat['note'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @if(session('success'))
        <div class="notice notice-success" role="status">
            <span class="notice-icon" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="notice notice-error" role="alert">
            <span class="notice-icon" aria-hidden="true"><i class="fas fa-exclamation-circle"></i></span>
            <div>{{ session('error') }}</div>
        </div>
    @endif


    {{-- Voting Overview --}}
    <section class="dashboard-card" aria-labelledby="presiding-voting-heading">
        <div class="dashboard-card-header">
            <div><h2 class="section-title" id="presiding-voting-heading"><span class="section-icon" aria-hidden="true"><i class="fas fa-vote-yea"></i></span>Voting Overview</h2><p class="page-subtitle mt-2">Monitor active motions, participation, and finalized results.</p></div>
            <span class="count-badge">{{ $recentVotes->count() }} recent votes</span>
        </div>
        <div class="p-3 p-md-4">
            <div class="row g-3 mb-4">
                @foreach([['label'=>'Open Votes','key'=>'open','note'=>'Currently accepting votes'],['label'=>'Closed Votes','key'=>'closed','note'=>'Awaiting finalization'],['label'=>'Finalized Votes','key'=>'finalized','note'=>'Voting results recorded']] as $stat)
                    <div class="col-12 col-sm-4"><div class="pd-vote-card"><div class="metric-label">{{ $stat['label'] }}</div><div class="metric-value">{{ $votingStats[$stat['key']] }}</div><p class="metric-note">{{ $stat['note'] }}</p></div></div>
                @endforeach
            </div>
            <h3 class="section-title mb-3">Recent Voting Activity</h3>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Motion</th><th>Method</th><th>Status</th><th>Participation</th><th>Results</th><th>Action</th></tr></thead><tbody>
                @forelse($recentVotes as $vote)
                    <tr><td><div class="session-name">{{ $vote->motion_title }}</div>@if($vote->motion_description)<div class="small text-secondary mt-1">{{ \Illuminate\Support\Str::limit($vote->motion_description,70) }}</div>@endif</td>
                    <td>{{ $vote->voting_method === 'secret' ? 'Secret Ballot' : 'Roll Call' }}</td>
                    <td>@if($vote->status === 'open')<span class="badge text-bg-success">Open</span>@elseif($vote->status === 'closed')<span class="badge text-bg-warning">Closed</span>@else<span class="badge text-bg-primary">Finalized</span>@endif</td>
                    <td>{{ $vote->ballot_count }} / {{ $vote->eligible_count }}<div class="small text-secondary">Members voted</div></td>
                    <td>@if($vote->status === 'finalized')<span class="text-success">Yes: {{ $vote->yes_count }}</span><br><span class="text-danger">No: {{ $vote->no_count }}</span><br><span class="text-secondary">Abstain: {{ $vote->abstain_count }}</span>@else<span class="small text-secondary">Available after finalization</span>@endif</td>
                    <td><a href="{{ route('presiding-officer.sessions.show',$vote->session_id) }}" class="btn btn-sm btn-review btn-review-secondary">View Session <i class="fas fa-chevron-right" aria-hidden="true"></i></a></td></tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><i class="fas fa-vote-yea" aria-hidden="true"></i></div><div class="empty-title">No voting activity yet</div><p class="empty-copy">Voting records will appear here when motions are created.</p></div></td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </section>

    <section class="dashboard-card" aria-labelledby="pending-minutes-heading">
        <div class="dashboard-card-header">
            <h2 class="section-title" id="pending-minutes-heading">
                <span class="section-icon" aria-hidden="true"><i class="fas fa-clipboard-check"></i></span>
                Minutes Awaiting Review
            </h2>
            <span class="count-badge">{{ $pendingSessions->total() }} pending</span>
        </div>

        @if($pendingSessions->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Session</th>
                            <th scope="col">Session Date</th>
                            <th scope="col">Submitted At</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingSessions as $session)
                            <tr>
                                <td><div class="session-name">{{ $session->title }}</div></td>
                                <td>
                                    <span class="date-value"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <span class="date-value"><i class="far fa-clock" aria-hidden="true"></i>{{ $session->minutes_submitted_at ? \Carbon\Carbon::parse($session->minutes_submitted_at)->format('M d, Y h:i A') : '—' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('secretary.minutes.review', $session->id) }}" class="btn btn-review btn-review-primary">
                                        Review Minutes <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $pendingSessions->links() }}</div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-check" aria-hidden="true"></i></div>
                <div class="empty-title">You're all caught up</div>
                <p class="empty-copy">No minutes are awaiting review right now.</p>
            </div>
        @endif
    </section>

    <section class="dashboard-card" aria-labelledby="returned-minutes-heading">
        <div class="dashboard-card-header">
            <h2 class="section-title" id="returned-minutes-heading">
                <span class="section-icon" style="background:#fff4e5;color:#b54708;" aria-hidden="true"><i class="fas fa-undo-alt"></i></span>
                Returned for Corrections
            </h2>
            <span class="count-badge" style="background:#f2f4f7;color:#475467;">{{ $revisionSessions->total() }} returned</span>
        </div>

        @if($revisionSessions->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Session</th>
                            <th scope="col">Session Date</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revisionSessions as $session)
                            <tr>
                                <td><div class="session-name">{{ $session->title }}</div></td>
                                <td>
                                    <span class="date-value"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('secretary.minutes.review', $session->id) }}" class="btn btn-review btn-review-secondary">
                                        View Minutes <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $revisionSessions->links() }}</div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-inbox" aria-hidden="true"></i></div>
                <div class="empty-title">No corrections pending</div>
                <p class="empty-copy">There are no minutes awaiting corrections.</p>
            </div>
        @endif
    </section>

<section class="dashboard-card" aria-labelledby="previous-minutes-heading">
    <div class="dashboard-card-header">
        <h2 class="section-title" id="previous-minutes-heading">
            <span class="section-icon"
                  style="background:#eef2ff;color:#3157d5;"
                  aria-hidden="true">
                <i class="fas fa-history"></i>
            </span>
            Previous Minutes
        </h2>

        <span class="count-badge">
            {{ $previousMinutesPendingCount }} pending
        </span>
    </div>


    <div class="row g-3 mb-3">
        @foreach([['label'=>'Approved','value'=>$previousMinutesApprovedCount,'note'=>'Previous minutes approved'],['label'=>'Approved with Corrections','value'=>$previousMinutesCorrectionsCount,'note'=>'Approved subject to corrections'],['label'=>'Deferred','value'=>$previousMinutesDeferredCount,'note'=>'Deferred for later action']] as $stat)
            <div class="col-12 col-md-4"><div class="pd-vote-card"><div class="metric-label">{{ $stat['label'] }}</div><div class="metric-value">{{ $stat['value'] }}</div><p class="metric-note">{{ $stat['note'] }}</p></div></div>
        @endforeach
    </div>

    @if($previousMinutesItems->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Session</th>
                        <th scope="col">Previous Minutes</th>
                        <th scope="col">Decision</th>
                        <th scope="col">Decision Recorded</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($previousMinutesItems as $item)
                        @php
                            $decisionKey = strtolower(
                                $item->decision ?: $item->status ?: 'pending'
                            );

                            $decisionLabel = match($decisionKey) {
                                'approved' => 'Approved',
                                'approved_with_corrections' => 'Approved with Corrections',
                                'deferred' => 'Deferred',
                                'pending' => 'Pending',
                                default => \Illuminate\Support\Str::headline($decisionKey),
                            };

                            $decisionClass = match($decisionKey) {
                                'approved' => 'text-bg-success',
                                'approved_with_corrections' => 'text-bg-info',
                                'deferred' => 'text-bg-secondary',
                                default => 'text-bg-warning',
                            };
                        @endphp

                        <tr>
                            <td>
                                <div class="session-name">
                                    {{ $item->session->title ?? 'Session #' . $item->session_id }}
                                </div>

                                @if($item->session && $item->session->session_date)
                                    <span class="date-value">
                                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                                        {{ \Carbon\Carbon::parse($item->session->session_date)->format('M d, Y') }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="session-name">
                                    {{ $item->previousSession->title ?? 'Session #' . $item->previous_session_id }}
                                </div>
                                <span class="date-value">
                                    Signed minutes from the previous session
                                </span>
                            </td>

                            <td>
                                <span class="badge rounded-pill {{ $decisionClass }}">
                                    {{ $decisionLabel }}
                                </span>
                            </td>

                            <td>
                                @if($item->decision_recorded_at)
                                    <div class="session-name">Recorded</div>

                                    <span class="date-value">
                                        <i class="far fa-clock" aria-hidden="true"></i>
                                        {{ $item->decision_recorded_at->format('M d, Y h:i A') }}
                                    </span>

                                    @if($item->decisionRecordedBy)
                                        <div class="date-value mt-1">
                                            By: {{ $item->decisionRecordedBy->name }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge rounded-pill text-bg-warning">
                                        Awaiting Decision
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $previousMinutesItems->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-history" aria-hidden="true"></i>
            </div>

            <div class="empty-title">No Previous Minutes yet</div>

            <p class="empty-copy">
                Previous Minutes added to session agendas will appear here.
            </p>
        </div>
    @endif
</section>
</div>

@endsection
