@extends('layouts.app')
@section('styles')
<style>
.session-control-page{--sc-border:#e2e8f0;--sc-muted:#64748b;--sc-ink:#0f172a;color:var(--sc-ink)}
.session-control-page .page-header{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.4rem 1.5rem;margin-bottom:1.25rem;border:1px solid var(--sc-border);border-radius:1rem;background:linear-gradient(135deg,#fff 0%,#f8fbff 100%);box-shadow:0 4px 18px rgba(15,23,42,.035)}
.session-control-page .eyebrow{font-size:.72rem;font-weight:750;letter-spacing:.09em;text-transform:uppercase;color:var(--sc-muted);margin-bottom:.4rem}
.session-control-page .page-title{font-size:clamp(1.45rem,2vw,1.9rem);font-weight:750;letter-spacing:-.035em;margin:0 0 .45rem;overflow-wrap:anywhere}
.session-control-page .page-meta{color:var(--sc-muted);font-size:.875rem;display:flex;flex-wrap:wrap;gap:.35rem .75rem}
.session-control-page .panel{height:100%;border:1px solid var(--sc-border);border-radius:.9rem;background:#fff;box-shadow:0 4px 18px rgba(15,23,42,.035);overflow:hidden}
.session-control-page .panel-header{padding:1.1rem 1.25rem;border-bottom:1px solid #eef2f7;background:#fff}
.session-control-page .panel-body{padding:1.25rem}
.session-control-page .panel-title{font-size:1rem;font-weight:750;margin:0 0 .25rem}
.session-control-page .panel-subtitle{font-size:.825rem;color:var(--sc-muted);margin:0}
.session-control-page .metric-card{height:100%;padding:1rem;border:1px solid var(--sc-border);border-radius:.75rem;background:#fff}
.session-control-page .metric-label{font-size:.76rem;color:var(--sc-muted);font-weight:600}
.session-control-page .metric-value{font-size:1.7rem;line-height:1.15;font-weight:750;letter-spacing:-.04em;margin-top:.4rem}
.session-control-page .current-agenda{border:1px solid #dbeafe;border-radius:.8rem;background:linear-gradient(135deg,#f8fbff,#f1f7ff);padding:1.1rem 1.2rem;margin-bottom:1.5rem}
.session-control-page .current-label{font-size:.7rem;font-weight:750;letter-spacing:.08em;text-transform:uppercase;color:#475569;margin-bottom:.6rem}
.session-control-page .agenda-table{margin:0}
.session-control-page .agenda-table thead th{font-size:.7rem;letter-spacing:.05em;text-transform:uppercase;color:#475569;background:#f8fafc;border-bottom:1px solid var(--sc-border);white-space:nowrap;padding:.85rem .9rem}
.session-control-page .agenda-table tbody td{padding:.9rem;vertical-align:middle;border-color:#f1f5f9;font-size:.875rem}
.session-control-page .agenda-table tbody tr:last-child td{border-bottom:0}
.session-control-page .agenda-table tbody tr:hover{background:#fafcff}
.session-control-page .action-entry{padding-bottom:1rem;margin-bottom:1rem;border-bottom:1px solid #eef2f7}
.session-control-page .action-entry:last-child{padding-bottom:0;margin-bottom:0;border-bottom:0}
.session-control-page .modal-content{border:1px solid var(--sc-border);border-radius:.9rem;overflow:hidden;box-shadow:0 20px 50px rgba(15,23,42,.16)}
.session-control-page .modal-header,.session-control-page .modal-footer{padding:1rem 1.25rem;border-color:#eef2f7}
.session-control-page .modal-body{padding:1.25rem}
.session-control-page .form-control,.session-control-page .form-select{min-height:42px;border-color:#dbe2ea;border-radius:.55rem}
.session-control-page .btn{border-radius:.55rem}
.session-control-page .status-badge{display:inline-flex;align-items:center;padding:.4rem .7rem;border-radius:999px;font-size:.75rem;font-weight:700}
@media(max-width:767.98px){.session-control-page .page-header{padding:1.1rem}.session-control-page .panel-body{padding:1rem}.session-control-page .panel-header{padding:1rem}.session-control-page .agenda-table thead th,.session-control-page .agenda-table tbody td{padding:.75rem}}
</style>
@endsection
@section('content')
<div class="container-fluid py-4 py-lg-5 session-control-page">
@php
$statusColors = [
    'upcoming' => 'warning',
    'ongoing' => 'success',
    'suspended' => 'warning',
    'completed' => 'secondary',
    'cancelled' => 'danger',
];
$itemColors=['pending'=>'secondary','in_progress'=>'primary','completed'=>'success','postponed'=>'warning','referred'=>'info','tabled'=>'dark'];
@endphp
<header class="page-header">
 <div><div class="eyebrow">Paperless Session Room</div><h1 class="page-title">{{ $session->title }}</h1>
 <div class="page-meta"><span><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>{{ $session->session_date ? $session->session_date->format('F d, Y') : 'Date not set' }}</span> @if($session->venue)<span><i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>{{ $session->venue }}</span>@endif @if($session->session_number)<span><i class="fa-solid fa-hashtag me-1" aria-hidden="true"></i>Session No. {{ $session->session_number }}</span>@endif</div></div>
 <span class="status-badge text-bg-{{ $statusColors[$session->status] ?? 'secondary' }}">{{ ucfirst($session->status ?? 'Unknown') }}</span>
</header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($session->status === 'suspended' && $activeSuspension)

    <div class="alert alert-warning border-warning d-flex align-items-start gap-3 mb-4">

        <i class="fa-solid fa-pause-circle fa-2x mt-1"></i>

        <div>
            <strong>Session Suspended</strong>

            <div class="small mt-1">
                {{ $activeSuspension->reason }}
            </div>

            <div class="small text-muted mt-2">
                Suspended on:
                {{ $activeSuspension->suspended_at?->format('F d, Y h:i A') }}
            </div>

            <div class="small text-muted">
                Suspended by:
                {{ $activeSuspension->suspended_by ?? 'Unknown' }}
            </div>
        </div>

    </div>

@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-4">
<div class="col-12 col-xl-8">
<section class="panel mb-4">
<div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3"><div><h2 class="panel-title">Session Controls</h2><p class="panel-subtitle">Start/end the meeting and manage agenda progression.</p></div>
@if($session->status === 'upcoming')

    <button type="button"
        class="btn btn-success"
        data-bs-toggle="modal"
        data-bs-target="#startSessionModal">
        <i class="fa-solid fa-play me-1"></i>
        Start Session
    </button>

@elseif($session->status === 'ongoing')

    <button type="button"
        class="btn btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#suspendSessionModal">
        <i class="fa-solid fa-pause me-1"></i>
        Suspend Session
    </button>

    <button type="button"
        class="btn btn-danger"
        data-bs-toggle="modal"
        data-bs-target="#endSessionModal">
        <i class="fa-solid fa-stop me-1"></i>
        End Session
    </button>

@elseif($session->status === 'suspended')

    <form method="POST"
        action="{{ route('secretary.session-control.resume', $session->id) }}"
        class="d-inline">
        @csrf

        <button type="submit" class="btn btn-success">
            <i class="fa-solid fa-play me-1"></i>
            Resume Session
        </button>
    </form>

@endif
@if($session->status !== 'suspended')
    <a href="{{ route('secretary.votes.manage', $session->id) }}"
       class="btn btn-outline-primary">
        <i class="fa-solid fa-check-to-slot me-1"></i>
        Voting Management
    </a>
@else
    <span class="btn btn-outline-secondary disabled"
          title="Voting is unavailable while the session is suspended">
        <i class="fa-solid fa-lock me-1"></i>
        Voting Suspended
    </span>
@endif
</div>
<div class="panel-body">
<div class="row g-3 mb-4">
@foreach([['Agenda items',$agendaItems->count()],['Completed',$agendaItems->where('status','completed')->count()],['In progress',$agendaItems->where('status','in_progress')->count()],['Deferred/referred/tabled',$agendaItems->whereIn('status',['postponed','referred','tabled'])->count()]] as $metric)
<div class="col-6 col-md-3"><div class="metric-card"><div class="metric-label">{{ $metric[0] }}</div><div class="metric-value">{{ $metric[1] }}</div></div></div>
@endforeach
</div>
{{-- Add Previous Minutes --}}
@if($session->status === 'ongoing' && $eligiblePreviousSessions->isNotEmpty())
    <div class="card border-primary mb-4">
        <div class="card-header">
            <strong>Add Previous Minutes to Agenda</strong>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('secretary.session-control.previous-minutes.add', $session->id) }}"
                  class="row g-3 align-items-end">
                @csrf

                <div class="col-md-8">
                    <label for="previous_session_id" class="form-label">
                        Select Previous Session
                    </label>

                    <select name="previous_session_id"
                            id="previous_session_id"
                            class="form-select"
                            required>
                        <option value="">Choose finalized session</option>

                        @foreach($eligiblePreviousSessions as $previousSession)
                            <option value="{{ $previousSession->id }}">
                                {{ $previousSession->title }}
                                — {{ \Carbon\Carbon::parse($previousSession->session_date)->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-1"></i>
                        Add to Agenda
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
<div class="current-agenda">
<div class="current-label">Current agenda item</div>
@if($currentAgendaItem)
    <h3 class="h4">{{ $currentAgendaItem->title }}</h3>

    <p class="small text-muted">
        Agenda item {{ $currentAgendaItem->agenda_order ?? '—' }}
        · {{ ucfirst(str_replace('_', ' ', $currentAgendaItem->status)) }}
        · {{ $currentAgendaItem->agenda_type === 'previous_minutes'
    ? 'Previous Minutes'
    : ($currentAgendaItem->agenda_type === 'committee_report' ? 'Committee Report' : 'Regular Document') }}
    </p>

    @if($currentAgendaItem->agenda_type === 'committee_report')
        @if($currentAgendaItem->committee_name)
            <p class="small mb-2">
                Committee: {{ $currentAgendaItem->committee_name }}
            </p>
        @endif

        @if($currentAgendaItem->referral_id && $currentAgendaItem->report_file_path)
            <a class="btn btn-sm btn-outline-primary"
            href="{{ route('secretary.committee-referrals.report', $currentAgendaItem->referral_id) }}">
                <i class="fa-solid fa-file-pdf me-1"></i>Open Committee Report
            </a>
        @endif
    @elseif($currentAgendaItem->file_path)
        <a class="btn btn-sm btn-outline-primary"
        target="_blank"
        rel="noopener"
        href="{{ rtrim(config('app.lis_url'), '/') . '/storage/' . ltrim(str_replace('storage/', '', $currentAgendaItem->file_path), '/') }}">
            <i class="fa-solid fa-file-pdf me-1"></i>Open supporting PDF
        </a>
    @endif

        @if(
        $currentAgendaItem->agenda_type === 'previous_minutes'
        && $session->status === 'ongoing'
        && $currentAgendaItem->status === 'in_progress'
        && empty($currentAgendaItem->decision)
    )
        <div class="card border-warning mt-4">
            <div class="card-header bg-warning-subtle">
                <strong>Previous Minutes Decision</strong>
            </div>

            <div class="card-body">
                <form method="POST"
                      action="{{ route('secretary.session-control.previous-minutes-decision', [$session->id, $currentAgendaItem->id]) }}">
                    @csrf

                    <div class="mb-3">
                        <label for="previous_minutes_decision" class="form-label">
                            Decision
                        </label>

                        <select name="decision"
                                id="previous_minutes_decision"
                                class="form-select"
                                required>
                            <option value="">Select decision</option>
                            <option value="approved">Approved</option>
                            <option value="approved_with_corrections">
                                Approved with Corrections
                            </option>
                            <option value="deferred">Deferred</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="previous_minutes_mover" class="form-label">
                                Motion Moved By
                            </label>
                            <input type="text"
                                   name="motion_moved_by"
                                   id="previous_minutes_mover"
                                   class="form-control"
                                   maxlength="255">
                        </div>

                        <div class="col-md-6">
                            <label for="previous_minutes_seconder" class="form-label">
                                Motion Seconded By
                            </label>
                            <input type="text"
                                   name="motion_seconded_by"
                                   id="previous_minutes_seconder"
                                   class="form-control"
                                   maxlength="255">
                        </div>
                    </div>

                    <div class="mt-3 mb-3">
                        <label for="previous_minutes_remarks" class="form-label">
                            Remarks / Corrections
                        </label>
                        <textarea name="remarks"
                                  id="previous_minutes_remarks"
                                  class="form-control"
                                  rows="3"
                                  maxlength="5000"
                                  placeholder="Enter corrections or remarks, if applicable"></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-check me-1"></i>
                        Record Decision
                    </button>
                </form>
            </div>
        </div>
    @endif

@else
<h3 class="h5 text-muted">No current agenda item selected</h3><p class="small text-muted mb-0">Start the session, then select an approved item below.</p>@endif
@if($session->status === 'ongoing' && $currentAgendaItem)
    <form method="POST"
          action="{{ route('secretary.session-control.next', $session->id) }}"
          class="mt-3">
        @csrf

        <button class="btn btn-primary"
                {{ $agendaItems->where('status', 'pending')->isEmpty() ? 'disabled' : '' }}>
            <i class="fa-solid fa-forward-step me-1"></i>
            Complete Current & Advance
        </button>
    </form>
@endif
</div>
<h3 class="h6 mb-3">Agenda progression</h3>
<div class="table-responsive"><table class="table table-hover align-middle agenda-table"><thead><tr><th>Order</th><th>Agenda item</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody>
@forelse($agendaItems as $item)

@php
    $isCurrent = (int) $session->current_agenda_item_id === (int) $item->id
        && ($session->current_agenda_item_type ?: 'document') === $item->agenda_type;
@endphp

<tr class="{{ $isCurrent ? 'table-primary' : '' }}">
    <td>{{ $item->agenda_order ?? $loop->iteration }}</td>

    <td>
        <div class="fw-semibold">{{ $item->title }}</div>

        @if($item->agenda_type === 'committee_report')
            <span class="badge text-bg-info">Committee Report</span>

            @if($item->committee_name)
                <div class="small text-muted">
                    {{ $item->committee_name }}
                </div>
            @endif

            @if($item->referral_id && $item->report_file_path)
                <a class="small"
                href="{{ route('secretary.committee-referrals.report', $item->referral_id) }}">
                    Open Committee Report PDF
                </a>
            @endif

        @elseif($item->agenda_type === 'previous_minutes')
            <span class="badge text-bg-warning">Previous Minutes</span>

            @if($item->previous_session_date)
                <div class="small text-muted">
                    Session date:
                    {{ \Carbon\Carbon::parse($item->previous_session_date)->format('M d, Y') }}
                </div>
            @endif

            @if($item->decision)
                <div class="small text-muted">
                    Decision:
                    {{ ucfirst(str_replace('_', ' ', $item->decision)) }}
                </div>
            @endif

        @elseif($item->file_path)
            <a class="small"
            target="_blank"
            rel="noopener"
            href="{{ rtrim(config('app.lis_url'), '/') . '/storage/' . ltrim(str_replace('storage/', '', $item->file_path), '/') }}">
                Open supporting document
            </a>
        @endif
    </td>

    <td>
        <span class="badge text-bg-{{ $itemColors[$item->status ?? 'pending'] ?? 'secondary' }}">
            {{ ucfirst(str_replace('_', ' ', $item->status ?? 'pending')) }}
        </span>
    </td>

    <td class="text-end">
        @if($session->status === 'ongoing' && !in_array($item->status, ['postponed', 'referred', 'tabled'], true))

            <form method="POST"
                  action="{{ route('secretary.session-control.activate', [$session->id, $item->id]) }}?type={{ $item->agenda_type }}"
                  class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-primary" title="Make current">
                    <i class="fa-solid fa-bullseye"></i>
                </button>
            </form>

            @if(in_array($item->agenda_type, ['document', 'committee_report'], true))
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#dispositionModal"
                    data-action-url="{{ route('secretary.session-control.disposition', [$session->id, $item->id]) }}?type={{ $item->agenda_type }}"
                    data-item-title="{{ $item->title }}"
                >
                    <i class="fa-solid fa-ellipsis"></i>
                </button>
            @endif

        @endif
    </td>
</tr>

@empty
<tr>
    <td colspan="4" class="text-center text-muted py-4">
        No approved agenda items are assigned to this session.
    </td>
</tr>
@endforelse
</tbody>

</table></div>
</div></section>

{{-- QUORUM MONITORING --}}
<div class="panel mb-4">
    <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="panel-title">Quorum Monitoring</h2>
            <p class="panel-subtitle">
                Live attendance summary and session quorum status.
            </p>
        </div>

        <a href="{{ route('secretary.session-meetings.attendance', $session->id) }}"
           class="btn btn-outline-primary btn-sm">
            <i class="fas fa-users me-1"></i>
            Manage Attendance
        </a>
    </div>

    <div class="panel-body">

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label">Registered Members</div>
                    <div class="metric-value">{{ $totalMembers }}</div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label">Present / Late</div>
                    <div class="metric-value text-success">
                        {{ $presentCount }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label">Absent / Not Recorded</div>
                    <div class="metric-value text-danger">
                        {{ $absentCount }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label">Required Quorum</div>
                    <div class="metric-value">
                        {{ $quorumRequired ?: '—' }}
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-4 p-3 rounded
            {{ $quorumMet ? 'bg-success-subtle border border-success-subtle' : 'bg-warning-subtle border border-warning-subtle' }}">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>
                    <strong>
                        @if (! $quorumConfigured)
                            Quorum Not Configured
                        @elseif ($quorumMet)
                            Quorum Achieved
                        @else
                            Quorum Not Achieved
                        @endif
                    </strong>

                    <div class="small mt-1">
                        @if (! $quorumConfigured)
                            Set the required quorum in Attendance Management.
                        @elseif ($quorumMet)
                            The session has enough members present to proceed.
                        @else
                            {{ max(0, $quorumRequired - $presentCount) }}
                            more member(s) needed to achieve quorum.
                        @endif
                    </div>
                </div>

                @if ($quorumMet)
                    <span class="badge bg-success">QUORUM MET</span>
                @else
                    <span class="badge bg-warning text-dark">
                        {{ $quorumConfigured ? 'QUORUM PENDING' : 'NOT CONFIGURED' }}
                    </span>
                @endif

            </div>

            @if ($quorumConfigured)
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar {{ $quorumMet ? 'bg-success' : 'bg-warning' }}"
                         role="progressbar"
                         style="width: {{ min(100, ($presentCount / $quorumRequired) * 100) }}%">
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>
</div>
<div class="col-12 col-xl-4">
    {{-- SESSION SUSPENSION HISTORY --}}
<section class="panel mb-4">

    <div class="panel-header">
        <h2 class="panel-title">Suspension History</h2>
        <p class="panel-subtitle">
            Recorded recesses, suspensions and resumptions.
        </p>
    </div>

    <div class="panel-body">

        @forelse($suspensions as $suspension)

            <div class="action-entry">

                <div class="d-flex justify-content-between gap-2">
                    <strong>
                        <i class="fa-solid fa-pause-circle text-warning me-1"></i>
                        Session Suspended
                    </strong>

                    <span class="small text-muted">
                        {{ $suspension->suspended_at?->format('M d, Y') }}
                    </span>
                </div>

                <p class="small mt-2 mb-2">
                    {{ $suspension->reason }}
                </p>

                <div class="small text-muted">
                    Suspended:
                    {{ $suspension->suspended_at?->format('g:i A') }}
                </div>
                @if($suspension->suspendedBy)
                    <div class="small text-muted">
                        Suspended by:
                       {{ $suspension->suspendedBy?->name ?? 'Unknown' }}
                    </div>
                @endif

                @if($suspension->resumed_at)

                    <div class="small text-success mt-1">
                        <i class="fa-solid fa-play me-1"></i>
                        Resumed:
                        {{ $suspension->resumed_at->format('M d, Y g:i A') }}
                    </div>

                @else

                    <span class="badge text-bg-warning mt-2">
                        Currently Suspended
                    </span>

                @endif

                @if($suspension->resumedBy)
                    <div class="small text-muted mt-1">
                        Resumed by: {{ $suspension->resumedBy->name }}
                    </div>
                @endif

            </div>

        @empty

            <p class="small text-muted mb-0">
                No suspension history recorded.
            </p>

        @endforelse

    </div>

</section>
<section class="panel"><div class="panel-header"><h2 class="panel-title">Agenda Action History</h2><p class="panel-subtitle">Recent recorded dispositions.</p></div><div class="panel-body">
@forelse($actions as $action)
<div class="action-entry"><div class="d-flex justify-content-between gap-2"><strong>{{ ucfirst($action->action) }}</strong><span class="small text-muted">{{ optional($action->acted_at)->format('M d, Y g:i A') }}</span></div>
<div class="small fw-semibold mt-1">
    {{ $action->committeeReportAgendaItem?->referral?->sessionDocument?->document?->title
        ?? $action->sessionDocument?->document?->title
        ?? 'Agenda item' }}
</div>

@if($action->committeeReportAgendaItem)
    <span class="badge text-bg-info">Committee Report</span>
@endif
@if($action->referred_to)<div class="small text-muted">Referred to: {{ $action->referred_to }}</div>@endif
@if($action->reason)<p class="small mb-0 mt-1">{{ $action->reason }}</p>@endif</div>
@empty<p class="small text-muted mb-0">No agenda actions recorded.</p>@endforelse
</div></section></div>
</div></div>

<div class="modal fade" id="dispositionModal" tabindex="-1"
     aria-labelledby="dispositionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="dispositionForm" action="">
                @csrf

                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="dispositionModalLabel">
                        Record Agenda Action
                    </h2>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="small text-muted" id="dispositionItemTitle"></p>

                    <div class="mb-3">
                        <label for="agendaAction" class="form-label">Action</label>
                        <select name="action" id="agendaAction"
                                class="form-select" required>
                            <option value="postponed">Postpone</option>
                            <option value="referred">Refer</option>
                            <option value="tabled">Table</option>
                            <option value="completed">Mark completed</option>
                        </select>
                    </div>

                    
                    <div class="mb-3 d-none" id="referredToWrap">
                            <label for="referredTo" class="form-label">
                                Refer to Committee
                            </label>

                            <select
                                name="committee_id"
                                id="referredTo"
                                class="form-select"
                            >
                                <option value="">Select a committee</option>

                                @foreach ($committees as $committee)
                                    <option value="{{ $committee->id }}">
                                        {{ $committee->name }}
                                    </option>
                                @endforeach
                            </select>
                        
                    </div>

                    <div class="mb-3">
                        <label for="actionReason" class="form-label">
                            Reason / notes
                        </label>
                        <textarea name="reason" id="actionReason"
                                  class="form-control" rows="3"
                                  maxlength="5000" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="saveAgendaAction"
                            class="btn btn-primary">Save Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SUSPEND SESSION MODAL --}}
@if($session->status === 'ongoing')

    <div class="modal fade" id="suspendSessionModal"
         tabindex="-1"
         aria-labelledby="suspendSessionModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form method="POST"
                    action="{{ route('secretary.session-control.suspend', $session->id) }}">

                    @csrf

                    <div class="modal-header">

                        <h5 class="modal-title" id="suspendSessionModalLabel">
                            <i class="fa-solid fa-pause text-warning me-1"></i>
                            Suspend Session
                        </h5>

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>

                    </div>

                    <div class="modal-body">

                        <div class="alert alert-warning">
                            The session will be temporarily suspended.
                            The current agenda item and its progress will be preserved.
                        </div>

                        <div class="mb-3">

                            <label for="suspensionReason" class="form-label">
                                Reason for Suspension
                            </label>

                            <select id="suspensionReasonType"
                                class="form-select mb-2"
                                onchange="setSuspensionReason(this.value)">

                                <option value="">Select a reason</option>
                                <option value="Recess">Recess</option>
                                <option value="Lack of quorum">Lack of quorum</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Technical interruption">Technical interruption</option>
                                <option value="Other">Other</option>

                            </select>

                            <textarea
                                name="reason"
                                id="suspensionReason"
                                class="form-control"
                                rows="3"
                                maxlength="2000"
                                placeholder="Enter the reason for suspending the session..."
                                required></textarea>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-pause me-1"></i>
                            Confirm Suspension
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endif

@if($session->status === 'ongoing')
    <div class="modal fade" id="endSessionModal" tabindex="-1"
         aria-labelledby="endSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST"
                      action="{{ route('secretary.session-control.end', $session->id) }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="endSessionModalLabel">
                            End Session?
                        </h5>
                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>
                            Are you sure you want to end this session?
                        </p>

                        @php
                            $unresolvedItems = $agendaItems->filter(
                                fn ($item) => in_array(
                                    $item->status,
                                    ['pending', 'in_progress'],
                                    true
                                )
                            );
                        @endphp

                        @if($unresolvedItems->isNotEmpty())
                            <div class="alert alert-warning">
                                <strong>Attention:</strong>
                                {{ $unresolvedItems->count() }}
                                agenda item(s) remain pending or in progress.
                                These items will not automatically be marked
                                completed.
                            </div>
                        @endif

                        <p class="text-muted small mb-0">
                            The session will be marked completed. Its recorded
                            agenda dispositions and action history will be preserved.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Confirm End Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($session->status === 'upcoming')
    <div class="modal fade" id="startSessionModal" tabindex="-1"
         aria-labelledby="startSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST"
                      action="{{ route('secretary.session-control.start', $session->id) }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="startSessionModalLabel">
                            <i class="fa-solid fa-play text-success me-1"></i>
                            Start Session?
                        </h5>
                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to start
                        <strong>{{ $session->title }}</strong>?

                        <p class="text-muted small mt-2 mb-0">
                            Starting the session will change its status to ongoing.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-play me-1"></i>
                            Confirm Start Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

<script>
function setSuspensionReason(value) {
    const textarea = document.getElementById('suspensionReason');

    if (!textarea) return;

    if (value && value !== 'Other') {
        textarea.value = value;
    } else if (value === 'Other') {
        textarea.value = '';
        textarea.focus();
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('dispositionModal');
    const form = document.getElementById('dispositionForm');
    const title = document.getElementById('dispositionItemTitle');
    const action = document.getElementById('agendaAction');
    const wrap = document.getElementById('referredToWrap');
    const to = document.getElementById('referredTo');
    const reason = document.getElementById('actionReason');
    const saveButton = document.getElementById('saveAgendaAction');

    function toggleReferredTo() {
        const isReferred = action.value === 'referred';
        wrap.classList.toggle('d-none', !isReferred);
        to.required = isReferred;
    }

    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const actionUrl = button?.dataset.actionUrl;

        if (!actionUrl) {
            event.preventDefault();
            alert('The agenda action endpoint is missing.');
            return;
        }

        form.action = actionUrl;
        title.textContent = button.dataset.itemTitle || 'Selected agenda item';
        action.value = 'postponed';
        reason.value = '';
        to.value = '';
        toggleReferredTo();
    });

    action.addEventListener('change', toggleReferredTo);

    form.addEventListener('submit', function () {
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
    });
});

</script>



