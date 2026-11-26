@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">


    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

        <div>
            <h3 class="mb-1">{{ $meeting->title }}</h3>
            <p class="text-muted mb-0">
                {{ $meeting->committee->name ?? 'Committee' }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">

            @if($meeting->status === 'scheduled')
                <form method="POST"
                    action="{{ route('secretary.committee-meetings.start', $meeting->id) }}"
                    onsubmit="return confirm('Are you sure you want to start this committee meeting?');">
                    @csrf

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play me-1"></i>
                        Start Meeting
                    </button>
                </form>

            @elseif($meeting->status === 'ongoing')
                <span class="badge bg-success d-flex align-items-center px-3">
                    <i class="fas fa-circle me-2"></i>
                    Meeting In Progress
                </span>

            @elseif($meeting->status === 'completed')
                <span class="badge bg-secondary d-flex align-items-center px-3">
                    <i class="fas fa-check-circle me-2"></i>
                    Meeting Completed
                </span>
            @endif

            <a href="{{ route('secretary.committee-meetings.index') }}"
            class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Meetings
            </a>

        </div>
    </div>

    {{-- Meeting Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Meeting Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <small class="text-muted">Meeting Date</small>
                    <div class="fw-semibold">
                        {{ $meeting->meeting_date?->format('F d, Y') }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Schedule</small>
                    <div class="fw-semibold">
                        {{ $meeting->start_time
                            ? \Carbon\Carbon::parse($meeting->start_time)->format('h:i A')
                            : 'Not set' }}

                        @if($meeting->end_time)
                            – {{ \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }}
                        @endif
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Venue</small>
                    <div class="fw-semibold">
                        {{ $meeting->venue ?? 'Not specified' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Status</small>
                    <div>
                        @php
                            $statusColors = [
                                'scheduled' => 'primary',
                                'ongoing' => 'success',
                                'completed' => 'secondary',
                                'cancelled' => 'danger',
                            ];
                        @endphp

                        <span class="badge bg-{{ $statusColors[$meeting->status] ?? 'secondary' }}">
                            {{ ucfirst($meeting->status) }}
                        </span>
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Presiding Officer</small>
                    <div class="fw-semibold">
                        {{ $meeting->presiding_officer ?? 'Not assigned' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Required Quorum</small>
                    <div class="fw-semibold">
                        {{ $meeting->quorum_required ?? 'Not specified' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Committee Members</small>
                    <div class="fw-semibold">
                        {{ $meeting->committee->activeMembers()->count() }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Actual Start</small>
                    <div class="fw-semibold">
                        {{ $meeting->started_at?->format('M d, Y h:i A') ?? 'Not started' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Actual End</small>
                    <div class="fw-semibold">
                        {{ $meeting->ended_at?->format('M d, Y h:i A') ?? 'Not ended' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Meeting Attendance</h5>
            <span class="badge bg-secondary">
                {{ $meeting->attendances->count() }} Recorded
            </span>
        </div>

        
        <div class="card-body">

            {{-- Quorum Status --}}
            <div class="alert {{ $quorumMet ? 'alert-success' : 'alert-warning' }} d-flex justify-content-between align-items-center">
                <div>
                    <strong>
                        <i class="fas {{ $quorumMet ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-1"></i>
                        @if($quorumMet)
                            Quorum Reached
                        @elseif($meeting->status === 'ongoing' && $meeting->current_agenda_item_id)
                            Deliberation Suspended — Quorum Lost
                        @else
                            Waiting for Quorum
                        @endif
                    </strong>
                    <div class="small mt-1">
                        {{ $attendanceCounts['counted_for_quorum'] }}
                        of {{ $requiredQuorum ?? 'Not specified' }} required members
                        (Present and Late)
                    </div>
                </div>
                <span class="badge {{ $quorumMet ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $quorumMet ? 'Qualified' : 'Insufficient' }}
                </span>
            </div>

            {{-- Attendance Summary --}}
            <div class="row g-3 mb-4">

                @php
                    $summaryCards = [
                        ['label' => 'Present', 'count' => $attendanceCounts['present'], 'color' => 'success'],
                        ['label' => 'Late', 'count' => $attendanceCounts['late'], 'color' => 'info'],
                        ['label' => 'Absent', 'count' => $attendanceCounts['absent'], 'color' => 'danger'],
                        ['label' => 'Excused', 'count' => $attendanceCounts['excused'], 'color' => 'secondary'],
                    ];
                @endphp

                @foreach($summaryCards as $card)
                    <div class="col-md-3 col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">{{ $card['label'] }}</small>
                            <h3 class="mb-0 text-{{ $card['color'] }}">
                                {{ $card['count'] }}
                            </h3>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- Attendance Details --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1">Attendance Records</h6>
                    <small class="text-muted">
                        {{ $attendanceCounts['total'] }} recorded attendance entries
                    </small>
                </div>

                @if(in_array($meeting->status, ['scheduled', 'ongoing']))

                    <a href="{{ route('secretary.committee-meetings.attendance', $meeting->id) }}"
                    class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>
                        Manage Attendance
                    </a>

                @elseif($meeting->status === 'completed')

                    <span class="badge bg-success p-2">
                        <i class="fas fa-lock me-1"></i>
                        Attendance Finalized
                    </span>

                @else

                    <span class="badge bg-secondary p-2">
                        <i class="fas fa-ban me-1"></i>
                        Attendance Locked
                    </span>

                @endif
            </div>

            @if($meeting->attendances->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Member</th>
                                <th>Committee Role</th>
                                <th>Attendance Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meeting->attendances as $attendance)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $attendance->attendee_name }}
                                    </td>
                                    <td>
                                        {{ $attendance->attendee_role ?? 'Member' }}
                                    </td>
                                    <td>
                                        @php
                                            $attendanceColors = [
                                                'present' => 'success',
                                                'late' => 'info',
                                                'absent' => 'danger',
                                                'excused' => 'secondary',
                                            ];
                                        @endphp

                                        <span class="badge bg-{{ $attendanceColors[$attendance->attendance_status] ?? 'secondary' }}">
                                            {{ ucfirst($attendance->attendance_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $attendance->remarks ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-3"></i>
                    <h6>No Attendance Recorded</h6>
                    <p class="text-muted mb-0">
                        Record committee member attendance to determine quorum.
                    </p>
                </div>
            @endif

        </div>

    </div>

    {{-- Agenda --}}
    <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Meeting Agenda</h5>

        <span class="badge bg-secondary">
            {{ $meeting->agendaItems->count() }} Items
        </span>
    </div>

    <div class="card-body">

        @forelse($meeting->agendaItems as $item)

            @php
                $document = $item->referral?->sessionDocument?->document;
            @endphp

            <div class="d-flex justify-content-between align-items-center border-bottom py-3">

                <div>
                    <span class="badge bg-primary me-2">
                        {{ str_pad($item->agenda_order, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <strong>
                        {{ $document?->title ?? 'Document unavailable' }}
                    </strong>
                </div>

                <span class="badge bg-secondary">
                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                </span>

            </div>

        @empty

            <div class="text-center py-4">
                <i class="fas fa-list-alt fa-2x text-muted mb-3"></i>
                <h6>No Agenda Items</h6>
                <p class="text-muted mb-0">
                    No referrals have been added to this meeting agenda.
                </p>
            </div>

        @endforelse

        <div class="mt-3 text-end">
            <a href="{{ route('secretary.committee-meetings.agenda', $meeting->id) }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-tasks me-1"></i>
                Manage Agenda
            </a>
        </div>

    </div>
</div>


{{-- Meeting Closure --}}
@php
    $agendaItems = $meeting->agendaItems;

    $dispositionCounts = [
        'completed' => $agendaItems->where('status', 'completed')->count(),
        'deferred' => $agendaItems->where('status', 'deferred')->count(),
        'returned' => $agendaItems->where('status', 'returned_for_revision')->count(),
        'further_study' => $agendaItems->where('status', 'for_further_study')->count(),
    ];

    $finalizedCount = array_sum($dispositionCounts);
    $totalAgendaItems = $agendaItems->count();

    $allAgendaFinalized = $totalAgendaItems > 0
        && $finalizedCount === $totalAgendaItems;
@endphp

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-flag-checkered me-1"></i>
            Meeting Closure
        </h5>

        <span class="badge bg-{{ $meeting->status === 'completed' ? 'success' : 'secondary' }}">
            {{ ucfirst($meeting->status) }}
        </span>
    </div>

    <div class="card-body">

        <div class="row g-3 mb-4">

            <div class="col-md-3 col-6">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Total Agenda</small>
                    <h3 class="mb-0">{{ $totalAgendaItems }}</h3>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Completed</small>
                    <h3 class="mb-0 text-success">
                        {{ $dispositionCounts['completed'] }}
                    </h3>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Deferred</small>
                    <h3 class="mb-0 text-warning">
                        {{ $dispositionCounts['deferred'] }}
                    </h3>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Other Dispositions</small>
                    <h3 class="mb-0 text-info">
                        {{ $dispositionCounts['returned'] + $dispositionCounts['further_study'] }}
                    </h3>
                </div>
            </div>

        </div>

        {{-- Closure Requirements --}}
        <h6 class="fw-bold mb-3">Closure Requirements</h6>

        <div class="d-flex justify-content-between border-bottom py-2">
            <span>Meeting Started</span>
            @if($meeting->started_at)
                <span class="text-success">
                    <i class="fas fa-check-circle"></i> Yes
                </span>
            @else
                <span class="text-danger">No</span>
            @endif
        </div>

        <div class="d-flex justify-content-between border-bottom py-2">
            <span>Required Quorum</span>
            <span class="{{ $quorumMet ? 'text-success' : 'text-danger' }}">
                {{ $quorumMet ? 'Achieved' : 'Not Achieved' }}
            </span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-2">
            <span>Agenda Dispositions</span>
            <span class="{{ $allAgendaFinalized ? 'text-success' : 'text-danger' }}">
                {{ $finalizedCount }} / {{ $totalAgendaItems }} Finalized
            </span>
        </div>

        <div class="d-flex justify-content-between border-bottom py-2">
            <span>Unfinished Agenda Items</span>
            <span class="{{ $allAgendaFinalized ? 'text-success' : 'text-danger' }}">
                {{ $totalAgendaItems - $finalizedCount }}
            </span>
        </div>

        @if($meeting->status === 'completed')

            <div class="alert alert-success mt-4 mb-0">
                <i class="fas fa-check-circle me-1"></i>
                This committee meeting was closed on
                <strong>
                    {{ $meeting->ended_at?->format('F d, Y h:i A') }}
                </strong>.
            </div>

        @elseif($meeting->status === 'ongoing')

            <div class="mt-4 text-end">

                <form method="POST"
                      action="{{ route('secretary.committee-meetings.close', $meeting->id) }}"
                      onsubmit="return confirm('Are you sure you want to close this committee meeting? This action cannot be undone through the current interface.');">

                    @csrf

                    <button type="submit"
                            class="btn btn-danger"
                            @disabled(!$quorumMet || !$allAgendaFinalized || !$meeting->started_at)>

                        <i class="fas fa-lock me-1"></i>
                        Close Committee Meeting

                    </button>

                </form>

                @if(!$meeting->started_at)
                    <small class="text-danger d-block mt-2">
                        Start the meeting first.
                    </small>
                @elseif(!$quorumMet)
                    <small class="text-danger d-block mt-2">
                        Quorum must be reached before closing the meeting.
                    </small>
                @elseif(!$allAgendaFinalized)
                    <small class="text-danger d-block mt-2">
                        Finalize all agenda items before closing the meeting.
                    </small>
                @endif

            </div>

        @else

            <div class="alert alert-secondary mt-4 mb-0">
                Meeting closure will be available once the meeting starts.
            </div>

        @endif

        @if($meeting->status === 'completed')
            <div class="card border mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-file-pdf text-danger me-2"></i>
                        Consolidated Committee Report
                    </h6>
                </div>

                <div class="card-body">

                    @if($meeting->report)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Consolidated report uploaded and linked to the meeting referrals.
                        </div>

                        <div class="mb-3">
                            <strong>Uploaded File:</strong>
                            {{ $meeting->report->original_filename }}

                            <br>

                            <strong>Uploaded By:</strong>
                            {{ $meeting->report->uploader?->name ?? 'Unknown user' }}

                            <br>

                            <strong>Upload Date:</strong>
                            {{ $meeting->report->uploaded_at?->format('F d, Y h:i A') ?? 'Not recorded' }}

                            <br>

                            <strong>Linked Referrals:</strong>
                            {{ $meeting->report->referrals->count() }}
                        </div>

                        <a href="{{ route('secretary.committee-meetings.report-file', $meeting->id) }}"
                        class="btn btn-success btn-sm mb-3">
                            <i class="fas fa-download me-1"></i>
                            Download Consolidated Report
                        </a>

                        <hr>

                        <p class="small text-muted mb-2">
                            You may upload a replacement PDF if the consolidated report needs to be updated.
                        </p>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            No consolidated committee report has been uploaded yet.
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ route('secretary.committee-meetings.report.upload', $meeting->id) }}"
                        enctype="multipart/form-data">

                        @csrf

                        <div class="mb-3">
                            <label for="consolidated_report" class="form-label fw-semibold">
                                {{ $meeting->report ? 'Replace Consolidated PDF' : 'Upload Consolidated PDF' }}
                            </label>

                            <input type="file"
                                name="report"
                                id="consolidated_report"
                                class="form-control @error('report') is-invalid @enderror"
                                accept="application/pdf,.pdf"
                                required>

                            @error('report')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                PDF format only. Maximum file size: 20 MB.
                            </small>
                        </div>

                        <button type="submit"
                                class="btn btn-primary btn-sm"
                                onclick="return confirm('Upload this consolidated committee report and link it to all referrals in this meeting?');">
                            <i class="fas fa-upload me-1"></i>
                            {{ $meeting->report ? 'Replace Consolidated Report' : 'Upload Consolidated Report' }}
                        </button>
                    </form>

                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('secretary.committee-meetings.report', $meeting->id) }}"
                class="btn btn-outline-danger btn-sm"
                target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>
                    Generate Deliberation Report
                </a>
            </div>
        @endif

    </div>
</div>


    {{-- Deliberation Records --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Deliberation Records
            </h5>

            <span class="badge bg-primary">
                {{ $meeting->agendaItems->sum(fn($item) => $item->actions->count()) }}
                Records
            </span>

        </div>

        <div class="card-body">

            @forelse($meeting->agendaItems->sortBy('agenda_order') as $item)

                @php
                    $document = $item->referral?->sessionDocument?->document;
                    $actions = $item->actions->sortBy('acted_at');
                @endphp

                <div class="border rounded p-3 mb-3">

                    <div class="d-flex justify-content-between align-items-start mb-3">

                        <div>
                            <span class="badge bg-secondary me-1">
                                Agenda {{ $item->agenda_order }}
                            </span>

                            <strong>
                                {{ $document?->title ?? 'Document unavailable' }}
                            </strong>
                        </div>

                        <span class="badge bg-success">
                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                        </span>

                    </div>

                    @forelse($actions as $action)

                        <div class="border-start border-3 border-primary ps-3 mb-3">

                            <div class="d-flex justify-content-between flex-wrap">

                                <strong>
                                    {{ ucwords(str_replace('_', ' ', $action->action)) }}
                                </strong>

                                <small class="text-muted">
                                    {{ $action->acted_at?->format('M d, Y h:i A') }}
                                </small>

                            </div>

                            <small class="text-muted">
                                Recorded by:
                                {{ $action->actor?->name ?? 'Unknown user' }}
                            </small>

                            @if($action->reason)
                                <div class="mt-2">
                                    <strong class="small">Discussion / Reason</strong>
                                    <p class="mb-1">{{ $action->reason }}</p>
                                </div>
                            @endif

                            @if($action->recommendation)
                                <div class="mt-2">
                                    <strong class="small">Recommendation</strong>
                                    <p class="mb-1">{{ $action->recommendation }}</p>
                                </div>
                            @endif

                        </div>

                    @empty

                        <p class="text-muted small mb-0">
                            No deliberation records for this agenda item.
                        </p>

                    @endforelse

                </div>

            @empty

                <div class="text-center py-4 text-muted">
                    No agenda items recorded.
                </div>

            @endforelse

        </div>

    </div>

</div>  
@endsection