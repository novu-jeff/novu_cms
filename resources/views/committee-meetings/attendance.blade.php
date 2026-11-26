@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Meeting Attendance</h3>
            <p class="text-muted mb-0">
                {{ $meeting->title }} — {{ $meeting->committee->name }}
            </p>
        </div>

        <a href="{{ route('secretary.committee-meetings.show', $meeting->id) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Meeting
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted">Meeting Date</small>
                    <div class="fw-semibold">
                        {{ $meeting->meeting_date->format('F d, Y') }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Venue</small>
                    <div class="fw-semibold">
                        {{ $meeting->venue ?? 'Not specified' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Required Quorum</small>
                    <div class="fw-semibold">
                        {{ $meeting->quorum_required ?? 'Not specified' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
    $presentCount = $meeting->attendances
        ->whereIn('attendance_status', ['present', 'late'])
        ->count();

    $requiredQuorum = (int) $meeting->quorum_required;

    $quorumReached = $requiredQuorum > 0
        && $presentCount >= $requiredQuorum;

    $progress = $requiredQuorum > 0
        ? min(100, ($presentCount / $requiredQuorum) * 100)
        : 0;
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <small class="text-muted fw-semibold">MEETING QUORUM</small>

                <h5 class="fw-bold mb-1">
                    @if($quorumReached)
                        <span class="text-success">Quorum Reached</span>
                    @else
                        <span class="text-warning">Waiting for Quorum</span>
                    @endif
                </h5>

                <div class="text-muted">
                    {{ $presentCount }} of {{ $requiredQuorum }}
                    required members are present or late.
                </div>
            </div>

            <div style="min-width: 220px; flex: 1; max-width: 450px;">
                <div class="d-flex justify-content-between mb-2">
                    <small>Attendance Progress</small>
                    <strong>{{ $presentCount }} / {{ $requiredQuorum }}</strong>
                </div>

                <div class="progress" style="height: 10px;">
                    <div class="progress-bar {{ $quorumReached ? 'bg-success' : 'bg-warning' }}"
                         style="width: {{ $progress }}%">
                    </div>
                </div>
            </div>

        </div>

        @if(!$quorumReached)
            <div class="alert alert-warning mt-3 mb-0">
                Official deliberation is locked until the required quorum is reached.
                You may continue recording attendance.
            </div>
        @else
            <div class="alert alert-success mt-3 mb-0">
                The required quorum has been reached. Official deliberation may proceed
                once the meeting is ongoing.
            </div>
        @endif

    </div>
</div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Committee Members</h5>
                <small class="text-muted">
                    Record the attendance status of each assigned member.
                </small>
            </div>

            <span class="badge bg-primary">
                {{ $committeeMembers->count() }} Members
            </span>
        </div>

        <div class="card-body">

            @if($committeeMembers->isNotEmpty())

                <form method="POST"
                      action="{{ route('secretary.committee-meetings.attendance.save', $meeting->id) }}">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Member Name</th>
                                    <th>Committee Role</th>
                                    <th style="width: 22%">Attendance Status</th>
                                    <th style="width: 25%">Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($committeeMembers as $index => $assignment)
                                    @php
                                        $existingAttendance = $meeting->attendances
                                            ->firstWhere('committee_member_id', $assignment->id);

                                        $status = old(
                                            "attendance.{$assignment->id}.attendance_status",
                                            $existingAttendance?->attendance_status
                                        );

                                        $remarks = old(
                                            "attendance.{$assignment->id}.remarks",
                                            $existingAttendance?->remarks
                                        );
                                    @endphp

                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        <td class="fw-semibold">
                                            {{ $assignment->member->name }}
                                        </td>

                                        <td>
                                            {{ $assignment->position ?? 'Member' }}
                                        </td>

                                        <td>
                                            <select
                                                name="attendance[{{ $assignment->id }}][attendance_status]"
                                                class="form-select form-select-sm"
                                                required>
                                                <option value="">Select Status</option>

                                                @foreach(['present', 'absent', 'late', 'excused'] as $option)
                                                    <option value="{{ $option }}"
                                                        {{ $status === $option ? 'selected' : '' }}>
                                                        {{ ucfirst($option) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                name="attendance[{{ $assignment->id }}][remarks]"
                                                class="form-control form-control-sm"
                                                value="{{ $remarks }}"
                                                placeholder="Optional remarks">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Save Attendance
                        </button>
                    </div>

                </form>

            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No Active Committee Members</h5>
                    <p class="text-muted mb-0">
                        Assign active members to this committee before recording attendance.
                    </p>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection