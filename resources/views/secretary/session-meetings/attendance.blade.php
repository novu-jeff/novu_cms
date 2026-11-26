@extends('layouts.app')

@section('styles')
<style>
.attendance-page{color:#0f172a}.attendance-card{border:1px solid #e2e8f0;border-radius:.85rem;background:#fff;box-shadow:0 4px 18px rgba(15,23,42,.035);overflow:hidden}
.attendance-header{padding:1.2rem 1.3rem;border-bottom:1px solid #e2e8f0}.attendance-body{padding:1.25rem}
.attendance-page .form-control,.attendance-page .form-select{min-height:40px;border-color:#dbe2ea;border-radius:.5rem}
.attendance-page .table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#475569;background:#f8fafc;white-space:nowrap}
.attendance-page .table td,.attendance-page .table th{vertical-align:middle}.attendance-page .btn{border-radius:.5rem}
.metric{padding:1rem;border:1px solid #e2e8f0;border-radius:.75rem;background:#fff}.metric-label{font-size:.78rem;color:#64748b;font-weight:600}.metric-value{font-size:1.5rem;font-weight:750}
</style>
@endsection

@section('content')
<div class="container-fluid py-4 attendance-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-bold text-secondary mb-1">Legislative Information System</div>
            <h1 class="h3 fw-bold mb-1">Attendance and Quorum</h1>
            <p class="text-secondary mb-0">{{ $session->title }} · {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}</p>
        </div>

        <a href="{{ route('secretary.votes.manage', $session->id) }}"
        class="btn btn-primary">
            <i class="fa-solid fa-check-to-slot me-1"></i>
            Manage Voting
        </a>

        <a href="{{ route('session-meetings.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to sessions</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Please check the form.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4"><div class="metric"><div class="metric-label">Present / Late</div><div class="metric-value">{{ $presentCount }}</div></div></div>
        <div class="col-6 col-lg-4"><div class="metric"><div class="metric-label">Quorum required</div><div class="metric-value">{{ $quorumRequired ?: 'Not set' }}</div></div></div>
        <div class="col-12 col-lg-4"><div class="metric"><div class="metric-label">Quorum status</div><div class="metric-value fs-5 mt-2">
            @if(is_null($quorumMet))<span class="text-secondary">Not determined</span>
            @elseif($quorumMet)<span class="text-success">Quorum met</span>
            @else<span class="text-danger">Quorum not met</span>@endif
        </div></div></div>
    </div>

    <form method="POST"
    action="{{ route('secretary.session-meetings.attendance.save', $session->id) }}">
    @csrf

    <input type="hidden" name="attendees_submitted" value="1">

    <section class="attendance-card mb-4">
        <div class="attendance-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h6 fw-bold mb-1">Council Members Attendance</h2>
                <p class="small text-secondary mb-0">
                    Record the attendance of all council members for this session.
                </p>
            </div>

            <span class="badge bg-primary">
                {{ $memberAccounts->count() }} Council Members
            </span>
        </div>

        <div class="attendance-body">

            <div class="row g-3 mb-3">
                <div class="col-md-5 col-lg-4">
                    <label class="form-label fw-semibold" for="quorum_required">
                        Required number for quorum
                    </label>

                    <input
                        class="form-control"
                        type="number"
                        min="1"
                        max="1000"
                        name="quorum_required"
                        id="quorum_required"
                        value="{{ old('quorum_required', $session->quorum_required) }}"
                        placeholder="e.g. 7">

                    <div class="form-text">
                        Enter the quorum threshold under the applicable council rules.
                    </div>
                </div>
            </div>

            @if($memberAccounts->isNotEmpty())

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 55px;">#</th>
                                <th style="min-width: 220px;">Council Member</th>
                                <th style="min-width: 180px;">Position</th>
                                <th style="min-width: 160px;">Attendance Status</th>
                                <th style="min-width: 200px;">Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($memberAccounts as $index => $account)

                                @php
                                    $existingAttendance = $attendances
                                        ->firstWhere('member_account_id', $account->id);

                                    $oldAttendee = collect(old('attendees', []))
                                        ->firstWhere('member_account_id', $account->id);

                                    $attendee = $oldAttendee
                                        ?? $existingAttendance?->toArray()
                                        ?? [];

                                    $attendanceStatus = $attendee['attendance_status'] ?? 'absent';
                                    $remarks = $attendee['remarks'] ?? '';
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <input
                                            type="hidden"
                                            name="attendees[{{ $index }}][id]"
                                            value="{{ $attendee['id'] ?? '' }}">

                                        <input
                                            type="hidden"
                                            name="attendees[{{ $index }}][member_account_id]"
                                            value="{{ $account->id }}">

                                        <input
                                            type="hidden"
                                            name="attendees[{{ $index }}][attendee_name]"
                                            value="{{ $account->member->name }}">

                                        <span class="fw-semibold">
                                            {{ $account->member->name }}
                                        </span>
                                    </td>

                                    <td>
                                        <input
                                            type="hidden"
                                            name="attendees[{{ $index }}][attendee_role]"
                                            value="{{ $account->member->position }}">

                                        <span class="text-secondary">
                                            {{ $account->member->position }}
                                        </span>
                                    </td>

                                    <td>
                                        <select
                                            class="form-select"
                                            name="attendees[{{ $index }}][attendance_status]"
                                            required>

                                            <option value="present"
                                                @selected($attendanceStatus === 'present')>
                                                Present
                                            </option>

                                            <option value="absent"
                                                @selected($attendanceStatus === 'absent')>
                                                Absent
                                            </option>

                                            <option value="late"
                                                @selected($attendanceStatus === 'late')>
                                                Late
                                            </option>
                                        </select>
                                    </td>

                                    <td>
                                        <input
                                            class="form-control"
                                            type="text"
                                            name="attendees[{{ $index }}][remarks]"
                                            value="{{ $remarks }}"
                                            maxlength="2000"
                                            placeholder="Optional remarks">
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <small class="text-secondary">
                        All listed council members must have an attendance status.
                    </small>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Save Attendance
                    </button>
                </div>

            @else

                <div class="text-center py-5">
                    <i class="fa-solid fa-users fa-3x text-muted mb-3"></i>
                    <h5>No Council Members Found</h5>
                    <p class="text-secondary mb-0">
                        No active member accounts with an assigned position were found.
                    </p>
                </div>

            @endif

        </div>
    </section>
</form>
</div>

<template id="attendeeRowTemplate">
<tr>

    <td>
        <select class="form-select" data-name="member_account_id">
            <option value="">Guest / non-member</option>

            @foreach($memberAccounts as $account)
                <option value="{{ $account->id }}">
                    {{ $account->email }}
                </option>
            @endforeach
        </select>
    </td>

    <td><input class="form-control" data-name="attendee_name" maxlength="150" required placeholder="Full name"></td>
    <td><input class="form-control" data-name="attendee_role" maxlength="100" placeholder="e.g. Councilor"></td>
    <td><select class="form-select" data-name="attendance_status" required>
        <option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option><option value="excused">Excused</option>
    </select></td>
    <td><input class="form-control" data-name="remarks" maxlength="2000" placeholder="Optional"></td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-attendee" aria-label="Remove attendee"><i class="fa-solid fa-trash"></i></button></td>
</tr>
</template>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.getElementById('attendeeRows');
    const template = document.getElementById('attendeeRowTemplate');
    const addButton = document.getElementById('addAttendee');

    function renumberRows() {
        rows.querySelectorAll('tr').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/attendees\[\d+\]/, 'attendees[' + index + ']');
            });
            row.querySelectorAll('[data-name]').forEach((input) => {
                input.name = 'attendees[' + index + '][' + input.dataset.name + ']';
            });
        });
    }

    addButton.addEventListener('click', function () {
        rows.appendChild(template.content.cloneNode(true));
        renumberRows();
        const first = rows.lastElementChild.querySelector('input');
        if (first) first.focus();
    });

    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-attendee');
        if (button) {
            button.closest('tr').remove();
            renumberRows();
        }
    });

    renumberRows();
});
</script>
@endsection
