
@extends('layouts.app')

@section('content')
<style>
    .live-workspace {
        color: #172033;
    }

    .live-workspace .panel {
        background: #fff;
        border: 1px solid #e7ebf2;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(16, 24, 40, .035);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .live-workspace .panel-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e7ebf2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .live-workspace .panel-body {
        padding: 1.25rem;
    }

    .live-workspace .panel-title {
        font-size: 1rem;
        font-weight: 750;
        margin: 0;
    }

    .live-workspace .muted {
        color: #667085;
    }

    .live-workspace .metric {
        border: 1px solid #e7ebf2;
        border-radius: 12px;
        padding: 1rem;
        height: 100%;
        background: #fff;
    }

    .live-workspace .metric-label {
        color: #667085;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .live-workspace .metric-value {
        font-size: 1.6rem;
        font-weight: 800;
        margin-top: .35rem;
    }

    .live-workspace .agenda-item {
        display: flex;
        align-items: flex-start;
        gap: .9rem;
        padding: 1rem;
        border-bottom: 1px solid #eef1f5;
    }

    .live-workspace .agenda-item:last-child {
        border-bottom: 0;
    }

    .live-workspace .agenda-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex-shrink: 0;
        border-radius: 9px;
        background: #f2f4f7;
        color: #475467;
        font-weight: 750;
        font-size: .85rem;
    }

    .live-workspace .agenda-current {
        background: #eff6ff;
        border-left: 4px solid #3157d5;
    }

    .live-workspace .status-pill {
        display: inline-block;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 750;
        background: #f2f4f7;
        color: #475467;
        white-space: nowrap;
    }

    .live-workspace .status-ongoing,
    .live-workspace .status-in_progress {
        background: #dcfce7;
        color: #166534;
    }

    .live-workspace .status-suspended {
        background: #fef3c7;
        color: #92400e;
    }

    .live-workspace .status-completed,
    .live-workspace .status-finalized,
    .live-workspace .status-closed {
        background: #e0e7ff;
        color: #3730a3;
    }

    .live-workspace .status-upcoming,
    .live-workspace .status-open {
        background: #dbeafe;
        color: #1e40af;
    }

    .live-workspace .status-pending {
        background: #f2f4f7;
        color: #475467;
    }

    .live-workspace .notice {
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: 1.25rem;
    }

    .live-workspace .notice-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .live-workspace .notice-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .live-workspace .table th {
        background: #f8fafc;
        color: #667085;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }

    .live-workspace .table td,
    .live-workspace .table th {
        padding: .85rem 1rem;
        vertical-align: middle;
    }

    .live-workspace .timeline {
        border-left: 2px solid #e7ebf2;
        margin-left: .5rem;
        padding-left: 1.25rem;
    }

    .live-workspace .timeline-item {
        position: relative;
        padding-bottom: 1.25rem;
    }

    .live-workspace .timeline-item:last-child {
        padding-bottom: 0;
    }

    .live-workspace .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.67rem;
        top: .25rem;
        width: .75rem;
        height: .75rem;
        border-radius: 50%;
        background: #3157d5;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #c7d2fe;
    }

    @media (max-width: 767px) {
        .live-workspace .panel-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid py-4 live-workspace">

    {{-- Page heading --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('presiding-officer.sessions.index') }}"
               class="btn btn-sm btn-outline-secondary mb-3">
                <i class="fa-solid fa-arrow-left me-1"></i>
                Back to Assigned Sessions
            </a>

            <div class="text-uppercase text-primary fw-bold small mb-1">
                Presiding Officer Workspace
            </div>

            <h2 class="fw-bold mb-1">{{ $session->title }}</h2>

            <div class="muted">
                {{ $session->session_date?->format('F d, Y') ?? 'Date not set' }}

                @if($session->start_time)
                    <span class="mx-2">|</span>
                    {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                @endif

                @if($session->venue)
                    <span class="mx-2">|</span>
                    {{ $session->venue }}
                @endif
            </div>
        </div>

        <div class="text-end">
            <div class="small muted">Presiding Officer</div>
            <div class="fw-bold">{{ $member->name }}</div>
            <span class="badge bg-primary">{{ $member->position }}</span>

            <div class="mt-2">
                @php
                    $sessionStatusClass = match($session->status) {
                        'ongoing' => 'status-ongoing',
                        'suspended' => 'status-suspended',
                        'completed' => 'status-completed',
                        'upcoming' => 'status-upcoming',
                        default => '',
                    };
                @endphp

                <span class="status-pill {{ $sessionStatusClass }}">
                    {{ ucfirst($session->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Suspended session alert --}}
    @if($session->status === 'suspended')
        <div class="notice notice-warning">
            <div class="fw-bold mb-1">
                <i class="fa-solid fa-circle-pause me-1"></i>
                Session Currently Suspended
            </div>

            @if($activeSuspension)
                <div>
                    <strong>Reason:</strong>
                    {{ $activeSuspension->reason }}
                </div>

                <div class="small mt-1">
                    Suspended by:
                    {{ $activeSuspension->suspendedBy?->name ?? 'Unknown' }}

                    @if($activeSuspension->suspended_at)
                        | {{ $activeSuspension->suspended_at->format('M d, Y h:i A') }}
                    @endif
                </div>
            @else
                <div class="small">
                    The session is marked as suspended. No active suspension record was found.
                </div>
            @endif
        </div>
    @elseif($session->status === 'ongoing')
        <div class="notice notice-success">
            <i class="fa-solid fa-circle-check me-1"></i>
            The session is currently in progress.
        </div>
    @endif

    {{-- Session summary --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="metric">
                <div class="metric-label">Agenda Items</div>
                <div class="metric-value">{{ $agendaItems->count() }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric">
                <div class="metric-label">Present / Late</div>
                <div class="metric-value">{{ $presentCount }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric">
                <div class="metric-label">Required Quorum</div>
                <div class="metric-value">{{ $quorumRequired }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric">
                <div class="metric-label">Voting Events</div>
                <div class="metric-value">{{ $votes->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Current agenda item --}}
    <div class="panel">
        <div class="panel-header">
            <h5 class="panel-title">
                <i class="fa-solid fa-bullseye text-primary me-2"></i>
                Current Agenda Item
            </h5>

            @if($currentAgendaItem)
                <span class="status-pill status-in_progress">In Progress</span>
            @endif
        </div>

        <div class="panel-body">
            @if($currentAgendaItem)
                <div class="small text-uppercase text-primary fw-bold mb-2">
                    Agenda Item {{ $currentAgendaItem->agenda_order ?? '—' }}
                </div>

                <h4 class="fw-bold mb-2">
                    {{ $currentAgendaItem->title ?? 'Untitled Agenda Item' }}
                </h4>

                @if($currentAgendaItem->agenda_type === 'committee_report')
                    <div class="muted">
                        <i class="fa-solid fa-users me-1"></i>
                        Committee:
                        {{ $currentAgendaItem->committee_name ?? 'Unassigned Committee' }}
                    </div>
                @elseif($currentAgendaItem->agenda_type === 'previous_minutes')
                    <div class="muted">
                        <i class="fa-solid fa-file-signature me-1"></i>
                        Previous Session Minutes
                    </div>

                    @if(!empty($currentAgendaItem->session_date))
                        <div class="small muted mt-1">
                            Session Date: {{ $currentAgendaItem->session_date }}
                        </div>
                    @endif

                    @if(!empty($currentAgendaItem->decision))
                        <div class="small mt-2">
                            <strong>Recorded Decision:</strong>
                            {{ ucfirst(str_replace('_', ' ', $currentAgendaItem->decision)) }}
                        </div>
                    @endif
                @else
                    <div class="muted">
                        <i class="fa-solid fa-file-lines me-1"></i>
                        Regular Session Document
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="fa-regular fa-rectangle-list fa-2x text-muted mb-3"></i>
                    <h6 class="fw-bold">No Active Agenda Item</h6>
                    <p class="muted small mb-0">
                        There is currently no active agenda item for this session.
                    </p>
                </div>
            @endif
        </div>
    </div>


    {{-- Embedded agenda PDF viewer --}}
    @if($currentAgendaItem)
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="panel-title">
                        <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                        {{ $currentAgendaItem->agenda_type === 'previous_minutes'
    ? 'Signed Previous Minutes Viewer'
    : 'Document Viewer' }}
                    </h5>
                    <small class="muted">
                        {{ $currentAgendaItem->title }}
                    </small>
                </div>

                <a href="{{ route('presiding-officer.sessions.agenda.preview', [
                        'sessionId' => $session->id,
                        'agendaType' => $currentAgendaItem->agenda_type,
                        'agendaItemId' => $currentAgendaItem->id,
                    ]) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-up-right-from-square me-1"></i>
                    Open in New Tab
                </a>
            </div>

            <div class="panel-body p-0">
                <iframe
                    src="{{ route('presiding-officer.sessions.agenda.preview', [
                        'sessionId' => $session->id,
                        'agendaType' => $currentAgendaItem->agenda_type,
                        'agendaItemId' => $currentAgendaItem->id,
                    ]) }}"
                    title="Agenda Document PDF Viewer"
                    width="100%"
                    height="750"
                    style="border: 0; background: #f1f3f5;"
                    loading="lazy">
                </iframe>
            </div>
        </div>
    @endif


    {{-- Presiding Officer Proceedings Recording --}}
    <div class="panel">
        <div class="panel-header">
            <div>
                <h5 class="panel-title">
                    <i class="fa-solid fa-clipboard-list text-primary me-2"></i>
                    Proceedings Recording
                </h5>
                <small class="muted">
                    Record discussions, motions, rulings, remarks, and decisions.
                </small>
            </div>

            <span class="badge bg-primary">
                {{ $proceedings->count() }} Entries
            </span>
        </div>

        <div class="panel-body">

            {{-- Recording form --}}
            @if($session->status === 'ongoing' && $currentAgendaItem)

                <div class="mb-4">
                    <div class="fw-bold mb-1">Record New Proceedings</div>
                    <div class="small muted">
                        Current Agenda: {{ $currentAgendaItem->title }}
                    </div>
                </div>

                <form method="POST"
                    action="{{ route('presiding-officer.sessions.agenda.proceedings.store', [
                        'sessionId' => $session->id,
                        'agendaType' => $currentAgendaItem->agenda_type,
                        'agendaItemId' => $currentAgendaItem->id,
                    ]) }}">

                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Entry Type</label>

                        <select name="entry_type"
                            id="proceedingEntryType"
                            class="form-select"
                            required>
                            <option value="">Select entry type</option>
                            <option value="discussion" @selected(old('entry_type') === 'discussion')>
                                Discussion
                            </option>
                            <option value="motion" @selected(old('entry_type') === 'motion')>
                                Motion
                            </option>
                            <option value="ruling" @selected(old('entry_type') === 'ruling')>
                                Presiding Officer's Ruling
                            </option>
                            <option value="remark" @selected(old('entry_type') === 'remark')>
                                Remark
                            </option>
                            <option value="decision" @selected(old('entry_type') === 'decision')>
                                Decision
                            </option>
                        </select>

                        @error('entry_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Motion details --}}
                    <div id="motionFields"
                        class="row g-3 mb-3"
                        style="{{ old('entry_type') === 'motion' ? '' : 'display:none;' }}">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Motion Moved By</label>
                            <input type="text"
                                name="moved_by"
                                class="form-control"
                                value="{{ old('moved_by') }}"
                                maxlength="255"
                                placeholder="Name of council member">

                            @error('moved_by')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Seconded By</label>
                            <input type="text"
                                name="seconded_by"
                                class="form-control"
                                value="{{ old('seconded_by') }}"
                                maxlength="255"
                                placeholder="Name of seconder">

                            @error('seconded_by')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Proceedings Details</label>

                        <textarea name="content"
                            class="form-control"
                            rows="5"
                            maxlength="20000"
                            required
                            placeholder="Enter the proceedings, discussion summary, motion details, ruling, or decision...">{{ old('content') }}</textarea>

                        @error('content')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    @error('proceedings')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Record Proceedings
                        </button>
                    </div>
                </form>

            @elseif($session->status === 'suspended')

                <div class="notice notice-warning mb-4">
                    <i class="fa-solid fa-pause-circle me-1"></i>
                    The session is suspended. New proceedings entries are disabled.
                    Resume the session before recording additional proceedings.
                </div>

            @elseif($session->status === 'completed')

                <div class="alert alert-secondary">
                    This session is completed. Proceedings history is available
                    for review, but no new entries can be recorded.
                </div>

            @else

                <div class="alert alert-secondary">
                    Proceedings recording will become available when the session
                    is ongoing and an agenda item is active.
                </div>

            @endif

            <hr class="my-4">

            {{-- Proceedings history --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Proceedings History</h6>
                <span class="small muted">Chronological Record</span>
            </div>

            @if($proceedings->isNotEmpty())

                <div class="timeline">
                    @foreach($proceedings as $entry)

                        @php
                            $entryAgenda = $agendaItems->first(
                                fn ($item) =>
                                    $item->agenda_type === $entry->agenda_type
                                    && (int) $item->id === (int) $entry->agenda_item_id
                            );
                        @endphp

                        <div class="timeline-item">

                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">

                                <div class="fw-bold">
                                    {{ ucfirst(str_replace('_', ' ', $entry->entry_type)) }}
                                </div>

                                <small class="muted">
                                    {{ $entry->created_at?->format('M d, Y h:i A') }}
                                </small>

                            </div>

                            <div class="small text-primary fw-semibold mt-1">
                                {{ $entryAgenda?->title ?? 'Agenda Item' }}
                            </div>

                            <div class="mt-2" style="white-space: pre-wrap;">{{ $entry->content }}</div>

                            @if($entry->entry_type === 'motion')
                                <div class="small muted mt-2">
                                    <strong>Moved by:</strong>
                                    {{ $entry->moved_by ?: 'Not specified' }}

                                    <span class="mx-2">|</span>

                                    <strong>Seconded by:</strong>
                                    {{ $entry->seconded_by ?: 'Not specified' }}
                                </div>
                            @endif

                            <div class="small muted mt-2">
                                <i class="fa-solid fa-user me-1"></i>
                                Recorded by:
                                {{ $entry->recorder?->name ?? 'Unknown User' }}
                            </div>

                        </div>

                    @endforeach
                </div>

            @else

                <div class="text-center py-4">
                    <i class="fa-regular fa-clipboard fa-2x text-muted mb-3"></i>
                    <h6 class="fw-bold">No Proceedings Recorded</h6>
                    <p class="muted small mb-0">
                        Proceedings entries will appear here as they are recorded.
                    </p>
                </div>

            @endif

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const entryType = document.getElementById('proceedingEntryType');
            const motionFields = document.getElementById('motionFields');

            if (!entryType || !motionFields) {
                return;
            }

            function toggleMotionFields() {
                motionFields.style.display =
                    entryType.value === 'motion' ? '' : 'none';
            }

            entryType.addEventListener('change', toggleMotionFields);
            toggleMotionFields();
        });
    </script>


    <div class="row g-4">

        {{-- Agenda sequence --}}
        <div class="col-xl-7">
            <div class="panel h-100">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="fa-solid fa-list-ol text-primary me-2"></i>
                        Session Agenda
                    </h5>
                    <span class="badge bg-primary">{{ $agendaItems->count() }} Items</span>
                </div>

                <div class="panel-body p-0">
                    @forelse($agendaItems as $item)
                        @php
                            $isCurrent = $currentAgendaItem
                                && $currentAgendaItem->agenda_type === $item->agenda_type
                                && (int) $currentAgendaItem->id === (int) $item->id;

                            $itemStatusClass = match($item->status) {
                                'in_progress' => 'status-in_progress',
                                'completed' => 'status-completed',
                                'pending' => 'status-pending',
                                default => '',
                            };
                        @endphp

                        <div class="agenda-item {{ $isCurrent ? 'agenda-current' : '' }}">
                            <div class="agenda-number">
                                {{ $item->agenda_order ?? '—' }}
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                    <div class="fw-semibold">
                                        {{ $item->title ?? 'Untitled Agenda Item' }}

                                        @if($isCurrent)
                                            <span class="badge bg-primary ms-1">Current</span>
                                        @endif
                                    </div>

                                    <span class="status-pill {{ $itemStatusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->status ?? 'pending')) }}
                                    </span>
                                </div>

                                <div class="small muted mt-1">
                                    @if($item->agenda_type === 'committee_report')
                                        Committee Report
                                        @if(!empty($item->committee_name))
                                            | {{ $item->committee_name }}
                                        @endif
                                    @elseif($item->agenda_type === 'previous_minutes')
                                        <i class="fa-solid fa-file-signature me-1"></i>
                                        Previous Session Minutes
                                    @else
                                        Regular Session Document
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <p class="muted mb-0">No agenda items have been added.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quorum and attendance --}}
        <div class="col-xl-5">
            <div class="panel">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="fa-solid fa-users text-primary me-2"></i>
                        Quorum Monitoring
                    </h5>
                </div>

                <div class="panel-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="muted small">Present / Late</span>
                        <strong>{{ $presentCount }} / {{ $quorumRequired }}</strong>
                    </div>

                    @php
                        $progress = $quorumRequired > 0
                            ? min(100, round(($presentCount / $quorumRequired) * 100))
                            : 0;
                    @endphp

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar {{ $quorumMet ? 'bg-success' : 'bg-warning' }}"
                             role="progressbar"
                             style="width: {{ $progress }}%;"
                             aria-valuenow="{{ $progress }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>

                    @if($quorumRequired <= 0)
                        <div class="alert alert-secondary py-2 small mb-0">
                            Required quorum has not been configured.
                        </div>
                    @elseif($quorumMet)
                        <div class="alert alert-success py-2 small mb-0">
                            <i class="fa-solid fa-check-circle me-1"></i>
                            Quorum achieved.
                        </div>
                    @else
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Quorum not achieved.
                            {{ max(0, $quorumRequired - $presentCount) }} more required.
                        </div>
                    @endif

                    <div class="small muted mt-3">
                        Active registered member accounts: {{ $totalMembers }}
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h5 class="panel-title">Attendance Register</h5>
                    <span class="badge bg-secondary">{{ $attendances->count() }}</span>
                </div>

                <div class="panel-body p-0">
                    @forelse($attendances as $attendance)
                        <div class="px-3 py-3 border-bottom d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold small">
                                    {{ $attendance->attendee_name
                                        ?: ($attendance->memberAccount?->name ?? 'Council Member') }}
                                </div>

                                <div class="small muted">
                                    {{ $attendance->attendee_role ?? 'Council Member' }}
                                </div>
                            </div>

                            @php
                                $attendanceClass = match($attendance->attendance_status) {
                                    'present', 'late' => 'bg-success',
                                    'absent' => 'bg-danger',
                                    'excused' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp

                            <span class="badge {{ $attendanceClass }}">
                                {{ ucfirst($attendance->attendance_status ?? 'Unrecorded') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="muted small mb-0">No attendance records found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Voting activity --}}
    <div class="panel">
        <div class="panel-header">
            <h5 class="panel-title">
                <i class="fa-solid fa-check-to-slot text-primary me-2"></i>
                Voting Activity
            </h5>

            <span class="badge bg-primary">{{ $votes->count() }}</span>
        </div>

        <div class="panel-body p-0">
            @if($votes->isNotEmpty())
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Motion</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Ballots Cast</th>
                                <th>Eligible Members</th>
                                <th>Result</th>
                                <th>Opened</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($votes as $vote)
                                @php
                                    $voteStatusClass = match($vote->status) {
                                        'open' => 'status-open',
                                        'closed' => 'status-closed',
                                        'finalized' => 'status-finalized',
                                        default => '',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $vote->motion_title }}
                                        </div>

                                        @if($vote->motion_description)
                                            <div class="small muted">
                                                {{ \Illuminate\Support\Str::limit($vote->motion_description, 90) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ ucfirst(str_replace('_', ' ', $vote->voting_method ?? '—')) }}
                                    </td>

                                    <td>
                                        <span class="status-pill {{ $voteStatusClass }}">
                                            {{ ucfirst($vote->status) }}
                                        </span>
                                    </td>

                                    <td>{{ $vote->records_count }}</td>
                                    <td>{{ $vote->eligibilities_count }}</td>

                                    <td>
                                        {{ $vote->result ?: 'Pending' }}
                                    </td>

                                    <td class="small muted">
                                        {{ $vote->opened_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa-solid fa-check-to-slot fa-2x text-muted mb-3"></i>
                    <h6 class="fw-bold">No Voting Activity</h6>
                    <p class="muted small mb-0">
                        No voting events have been recorded for this session.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Suspension history --}}
    <div class="panel">
        <div class="panel-header">
            <h5 class="panel-title">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                Suspension History
            </h5>

            <span class="badge bg-secondary">{{ $suspensions->count() }}</span>
        </div>

        <div class="panel-body">
            @if($suspensions->isNotEmpty())
                <div class="timeline">
                    @foreach($suspensions as $suspension)
                        <div class="timeline-item">
                            <div class="fw-bold">
                                Session Suspended
                                @if(!$suspension->resumed_at)
                                    <span class="badge bg-warning text-dark ms-1">Active</span>
                                @else
                                    <span class="badge bg-success ms-1">Resumed</span>
                                @endif
                            </div>

                            <div class="small muted mt-1">
                                {{ $suspension->suspended_at?->format('F d, Y h:i A') ?? 'Time not recorded' }}
                            </div>

                            <div class="mt-2">
                                <strong>Reason:</strong>
                                {{ $suspension->reason }}
                            </div>

                            <div class="small muted mt-1">
                                Suspended by:
                                {{ $suspension->suspendedBy?->name ?? 'Unknown' }}
                            </div>

                            @if($suspension->resumed_at)
                                <div class="small text-success mt-1">
                                    Resumed:
                                    {{ $suspension->resumed_at->format('F d, Y h:i A') }}
                                    by {{ $suspension->resumedBy?->name ?? 'Unknown' }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fa-regular fa-clock fa-2x text-muted mb-3"></i>
                    <p class="muted small mb-0">
                        No suspension records for this session.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div class="text-center muted small py-2">
        <i class="fa-solid fa-eye me-1"></i>
        Presiding Officer Workspace. Proceedings entries are recorded for the official minutes workflow. Session operations remain under the Secretary's control.
    </div>

</div>
@endsection
