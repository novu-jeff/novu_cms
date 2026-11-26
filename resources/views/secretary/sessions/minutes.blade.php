

@extends('layouts.app')

@section('content')

<div class="container-fluid py-4 minutes-page">

    {{-- Page Header --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <div class="text-muted small mb-1">

                Legislative Session / Minutes

            </div>

            <h3 class="fw-bold mb-2">{{ $session->title }}</h3>

            <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">

                <span>

                    <i class="bi bi-calendar3 me-1"></i>

                    {{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}

                </span>

                <span>

                    <i class="bi bi-list-ol me-1"></i>

                    {{ $documents->count() }}

                    {{ \Illuminate\Support\Str::plural('agenda item', $documents->count()) }}

                </span>

            </div>

        </div>

@if($session->minutes_status === 'finalized')

    <span class="badge rounded-pill bg-success-subtle text-success-emphasis border px-3 py-2">

        <i class="bi bi-check-circle-fill me-1"></i>

        Finalized Minutes

    </span>

@elseif($session->minutes_status === 'for_review')

    <span class="badge rounded-pill bg-info-subtle text-info-emphasis border px-3 py-2">

        <i class="bi bi-eye me-1"></i>

        Under Review

    </span>

@elseif($session->minutes_status === 'reviewed')

    <span class="badge rounded-pill bg-success-subtle text-success-emphasis border px-3 py-2">

        <i class="bi bi-check-circle me-1"></i>

        Reviewed

    </span>

@elseif($session->minutes_status === 'revision_required')

    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border px-3 py-2">

        <i class="bi bi-pencil-square me-1"></i>

        Corrections Required

    </span>

@else

    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border px-3 py-2">

        <i class="bi bi-pencil-square me-1"></i>

        Draft Minutes

    </span>

@endif

    </div>

    {{-- Success Message --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">

            <i class="bi bi-check-circle me-2"></i>

            {{ session('success') }}

            <button type="button"

                    class="btn-close"

                    data-bs-dismiss="alert"

                    aria-label="Close"></button>

        </div>

    @endif

    {{-- Validation Errors --}}

    @if($errors->any())

        <div class="alert alert-danger mb-4" role="alert">

            <div class="fw-semibold mb-2">

                Please correct the following errors:

            </div>

            <ul class="mb-0 ps-3">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    {{-- Finalized Minutes Notice --}}

@if($session->minutes_status === 'finalized')

    <div class="alert alert-success mb-4">

        <div class="d-flex align-items-start gap-2">

            <i class="bi bi-check-circle-fill fs-4"></i>

            <div>

                <strong>Minutes Finalized</strong>

                <p class="mb-0 mt-1">

                    These minutes were finalized on

                    {{ $session->minutes_finalized_at

                        ? \Carbon\Carbon::parse($session->minutes_finalized_at)->format('M d, Y h:i A')

                        : 'an unspecified date' }}.

                    Editing is disabled.

                </p>

            </div>

        </div>

    </div>

@else

    {{-- AI-Assisted Minutes Draft --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">AI-Assisted Minutes Draft</h5>

            <p class="text-muted">
                Generate an editable draft from the session recording's
                completed transcript. Review all details before submission.
            </p>

            <form
                method="POST"
                action="{{ route('secretary.minutes.generate-draft', $session->id) }}"
                onsubmit="return confirm('Generate a draft from the latest completed transcript? Existing minutes content may be replaced if confirmed.');"
            >
                @csrf

                <div class="form-check mb-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="replace_existing"
                        value="1"
                        id="replace_existing"
                    >

                    <label class="form-check-label" for="replace_existing">
                        Confirm replacement of existing minutes content
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-magic me-2"></i>
                    Generate AI Draft
                </button>
            </form>
        </div>
    </div>

    <form method="POST"

          action="{{ route('secretary.minutes.store', $session->id) }}"

          id="minutesForm">

        @csrf

        {{-- Session Opening --}}

        <div class="card minutes-card mb-4">

            <div class="card-header bg-white d-flex align-items-center gap-3">

                <div class="section-icon">

                    <i class="bi bi-journal-text"></i>

                </div>

                <div>

                    <h5 class="mb-1 fw-semibold">Session Proceedings</h5>

                    <div class="text-muted small">

                        Record the opening and closing details of the session.

                    </div>

                </div>

            </div>

            <div class="card-body">

                <div class="row g-3">

                {{-- Call to Order --}}

                <div class="col-lg-6">

                    <label for="call_to_order" class="form-label fw-semibold">

                        <i class="bi bi-clock me-2 text-primary"></i>

                        Call to Order

                    </label>

                    <textarea

                        id="call_to_order"

                        name="call_to_order"

                        class="form-control"

                        rows="3"

                        placeholder="Record who called the session to order and the actual time..."

                    \>{{ old('call_to_order', $session->call_to_order ?? '') }}</textarea>

                    <div class="form-text">

                        Record the official proceedings, not merely the system start time.

                    </div>

                </div>

                {{-- Presiding Officer Reference --}}

                <div class="col-lg-6">

                    <label class="form-label fw-semibold">

                        <i class="bi bi-person-badge me-2 text-primary"></i>

                        Presiding Officer

                    </label>

                    <div class="form-control bg-light d-flex align-items-center"

                        style="min-height: 46px;">

                        {{ $session->presiding_officer ?: 'Not yet assigned' }}

                    </div>

                    <div class="form-text">

                        Retrieved from the session record.

                    </div>

                </div>

                    <div class="col-lg-6">

                        <label for="prayer" class="form-label">

                            Invocation / Prayer

                        </label>

                        <input type="text"

                               id="prayer"

                               name="prayer"

                               class="form-control"

                               value="{{ old('prayer', $session->prayer ?? '') }}"

                               placeholder="e.g. Led by Hon. Reyes">

                    </div>

                    {{-- Roll Call / Official Attendance Records --}}

                    <div class="col-12">

                        <div class="border rounded-3 p-3 p-md-4 bg-light">

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                                <div>

                                    <h6 class="fw-bold mb-1">

                                        <i class="bi bi-people me-2 text-primary"></i>

                                        Roll Call and Attendance

                                    </h6>

                                    <p class="text-muted small mb-0">

                                        Attendance records linked to this session.

                                    </p>

                                </div>

                                <span class="badge bg-white text-dark border">

                                    {{ $attendance->count() }} recorded

                                </span>

                            </div>

                            @if($attendance->isNotEmpty())

                                @php

                                    $attendanceGroups = $attendance->groupBy(

                                        fn ($record) => $record->attendance_status ?: 'Not specified'

                                    );

                                @endphp

                                <div class="row g-3 mb-3">

                                    @foreach($attendanceGroups as $status => $records)

                                        <div class="col-6 col-md-3">

                                            <div class="bg-white border rounded-3 p-3 h-100">

                                                <div class="small text-muted">

                                                    {{ \Illuminate\Support\Str::headline($status) }}

                                                </div>

                                                <div class="fs-4 fw-bold">

                                                    {{ $records->count() }}

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                                <div class="table-responsive">

                                    <table class="table table-sm align-middle bg-white mb-0">

                                        <thead>

                                            <tr>

                                                <th>Attendee</th>

                                                <th>Role</th>

                                                <th>Attendance Status</th>

                                                <th>Remarks</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            @foreach($attendance as $record)

                                                <tr>

                                                    <td class="fw-semibold">

                                                        {{ $record->attendee_name ?: 'Unnamed attendee' }}

                                                    </td>

                                                    <td>

                                                        {{ $record->attendee_role ?: '—' }}

                                                    </td>

                                                    <td>

                                                        {{ $record->attendance_status

                                                            ? \Illuminate\Support\Str::headline($record->attendance_status)

                                                            : 'Not specified' }}

                                                    </td>

                                                    <td>

                                                        {{ $record->remarks ?: '—' }}

                                                    </td>

                                                </tr>

                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            @else

                                <div class="text-center py-4">

                                    <i class="bi bi-person-exclamation fs-3 text-muted"></i>

                                    <p class="fw-semibold mt-2 mb-1">No attendance records found</p>

                                    <p class="text-muted small mb-0">

                                        Record attendance in the Attendance module before finalizing the minutes.

                                    </p>

                                </div>

                            @endif

                        </div>

                    </div>

                    <div class="col-lg-4">

                        {{-- Quorum Confirmation --}}

                    <div class="col-lg-6 mb-3">

                        <label for="quorum_confirmation" class="form-label fw-semibold">Quorum Confirmation</label>

                        <select id="quorum_confirmation" name="quorum_confirmation" class="form-select">

                            <option value="">Select confirmation</option>

                            <option value="confirmed" @selected(old('quorum_confirmation', $session->quorum_confirmation ?? '') === 'confirmed')>Quorum Confirmed</option>

                            <option value="not_confirmed" @selected(old('quorum_confirmation', $session->quorum_confirmation ?? '') === 'not_confirmed')>Quorum Not Confirmed</option>

                        </select>

                        <div class="form-text">Confirm after checking attendance and the applicable quorum rules.</div>

                    </div>

                    {{-- Previous Minutes Approval --}}

                    <div class="col-lg-6 mb-3">

                        <label for="previous_minutes_status" class="form-label fw-semibold">Approval of Previous Minutes</label>

                        <select id="previous_minutes_status" name="previous_minutes_status" class="form-select">

                            <option value="">Select status</option>

                            <option value="approved" @selected(old('previous_minutes_status', $session->previous_minutes_status ?? '') === 'approved')>Approved</option>

                            <option value="approved_with_corrections" @selected(old('previous_minutes_status', $session->previous_minutes_status ?? '') === 'approved_with_corrections')>Approved with Corrections</option>

                            <option value="deferred" @selected(old('previous_minutes_status', $session->previous_minutes_status ?? '') === 'deferred')>Deferred</option>

                            <option value="not_applicable" @selected(old('previous_minutes_status', $session->previous_minutes_status ?? '') === 'not_applicable')>Not Applicable</option>

                        </select>

                    </div>

                    <label for="adjournment_time" class="form-label">

                            Adjournment Time

                        </label>

                        <input type="text"

                               id="adjournment_time"

                               name="adjournment_time"

                               class="form-control"

                               value="{{ old('adjournment_time', $session->adjournment_time ?? '') }}"

                               placeholder="e.g. 11:45 AM">

                    </div>

                </div>

            </div>

        </div>

        {{-- Agenda Workspace --}}

        <div class="row g-4">

            {{-- Agenda Navigation --}}

            <div class="col-xl-3 col-lg-4">

                <div class="agenda-sidebar">

                    <div class="card minutes-card agenda-nav-card">

                        <div class="card-header bg-white">

                            <div class="d-flex justify-content-between align-items-center mb-2">

                                <h6 class="fw-bold mb-0">Agenda Items</h6>

                                <span class="badge bg-light text-dark border">

                                    {{ $documents->count() }}

                                </span>

                            </div>

                            <div class="text-muted small">

                                Select an item to navigate to its minutes.

                            </div>

                        </div>

                        <div class="list-group list-group-flush agenda-list">

                            @forelse($documents as $doc)

                                @php

                                    $agendaMinute = $minutes->get($doc->document->id ?? null);

                                    $hasContent = $agendaMinute && (

                                        filled($agendaMinute->discussion) ||

                                        filled($agendaMinute->motion) ||

                                        filled($agendaMinute->decision)

                                    );

                                    $agendaTitle = $doc->document->title

                                        ?? 'Untitled agenda item';

                                @endphp

                                <a href="#agenda{{ $doc->id }}"

                                   class="list-group-item list-group-item-action agenda-link"

                                   data-target="agenda{{ $doc->id }}">

                                    <div class="d-flex align-items-start gap-3">

                                        <span class="agenda-number">

                                            {{ $doc->agenda_order }}

                                        </span>

                                        <div class="flex-grow-1 min-width-0">

                                            <div class="agenda-title">

                                                {{ $agendaTitle }}

                                            </div>

                                            <div class="small text-muted mt-1">

                                                {{ $doc->document->member->name ?? 'Proponent not specified' }}

                                            </div>

                                            <div class="agenda-progress mt-2">

                                                @if($hasContent)

                                                    <span class="text-success small">

                                                        <i class="bi bi-check-circle-fill me-1"></i>

                                                        Has entries

                                                    </span>

                                                @else

                                                    <span class="text-muted small">

                                                        <i class="bi bi-circle me-1"></i>

                                                        Not yet encoded

                                                    </span>

                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                </a>

                            @empty

                                <div class="p-4 text-center">

                                    <i class="bi bi-inbox fs-3 text-muted"></i>

                                    <p class="text-muted small mt-2 mb-0">

                                        No agenda items are attached to this session.

                                    </p>

                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

            </div>

            {{-- Minutes Editor --}}

            <div class="col-xl-9 col-lg-8">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                    <div>

                        <h5 class="fw-bold mb-1">Minutes Editor</h5>

                        <p class="text-muted small mb-0">

                            Record the proceedings and decisions for each agenda item.

                        </p>

                    </div>

                </div>

                @forelse($documents as $doc)

                    @php
                        $minute = $minutes->get($doc->document->id ?? null);
                        $agendaVotes = $votesByAgenda->get($doc->id, collect());
                        $agendaProceedings = $proceedingsByAgenda->get($doc->id, collect());

                        $agendaTitle = $doc->document->title
                            ?? 'Untitled agenda item';
                    @endphp

                    <section id="agenda{{ $doc->id }}"

                             class="card minutes-card agenda-card mb-4"

                             data-agenda-index="{{ $loop->index }}">

                        <div class="card-header bg-white">

                            <div class="d-flex align-items-start gap-3">

                                <div class="agenda-number agenda-number-large">

                                    {{ $doc->agenda_order }}

                                </div>

                                <div class="flex-grow-1 min-width-0">

                                    <div class="small text-muted mb-1">

                                        AGENDA ITEM {{ $doc->agenda_order }}

                                    </div>

                                    <h5 class="fw-bold mb-2 agenda-heading">

                                        {{ $agendaTitle }}

                                    </h5>

                                    <div class="small text-muted">

                                        <i class="bi bi-person me-1"></i>

                                        Proponent:

                                        {{ $doc->document->member->name ?? 'Not specified' }}

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="card-body">


                            {{-- Presiding Officer Proceedings Reference --}}
                            <div class="border rounded-3 p-3 p-md-4 bg-light mb-4">

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            <i class="bi bi-journal-check me-2 text-primary"></i>
                                            Presiding Officer Proceedings
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            Reference entries recorded during the live deliberation.
                                            Review and incorporate relevant details into the official minutes.
                                        </p>
                                    </div>

                                    <span class="badge bg-white text-dark border">
                                        {{ $agendaProceedings->count() }}
                                        {{ \Illuminate\Support\Str::plural('entry', $agendaProceedings->count()) }}
                                    </span>
                                </div>

                                @forelse($agendaProceedings as $entry)

                                    <div class="bg-white border rounded-3 p-3 mb-3">

                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">

                                            <span class="badge bg-primary-subtle text-primary-emphasis border">
                                                {{ \Illuminate\Support\Str::headline($entry->entry_type) }}
                                            </span>

                                            <span class="text-muted small">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $entry->created_at?->format('M d, Y h:i A') }}
                                            </span>

                                        </div>

                                        <div class="mb-2" style="white-space: pre-line;">{{ $entry->content }}</div>

                                        @if($entry->entry_type === 'motion')
                                            <div class="small border-top pt-2 mt-2">

                                                @if($entry->moved_by)
                                                    <div>
                                                        <strong>Moved by:</strong>
                                                        {{ $entry->moved_by }}
                                                    </div>
                                                @endif

                                                @if($entry->seconded_by)
                                                    <div>
                                                        <strong>Seconded by:</strong>
                                                        {{ $entry->seconded_by }}
                                                    </div>
                                                @endif

                                            </div>
                                        @endif

                                        <div class="text-muted small border-top pt-2 mt-2">
                                            <i class="bi bi-person-circle me-1"></i>
                                            Recorded by: {{ $entry->recorder?->name ?? 'Unknown' }}
                                        </div>

                                    </div>

                                @empty

                                    <div class="text-center py-4 bg-white border rounded-3">
                                        <i class="bi bi-journal-text fs-3 text-muted"></i>
                                        <p class="fw-semibold mb-1 mt-2">No proceedings recorded yet</p>
                                        <p class="text-muted small mb-0">
                                            No Presiding Officer entries are available for this agenda item.
                                        </p>
                                    </div>

                                @endforelse

                                <div class="small text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Reference only. These entries are not automatically included in the official minutes.
                                </div>

                            </div>


                            {{-- Discussion --}}

                            <div class="minutes-field mb-4">

                                <label for="discussion_{{ $doc->id }}"

                                       class="form-label fw-semibold">

                                    <i class="bi bi-chat-left-text me-2 text-primary"></i>

                                    Discussion

                                </label>

                                <textarea id="discussion_{{ $doc->id }}"

                                          name="minutes[{{ $doc->document->id }}][discussion]"

                                          class="form-control"

                                          rows="5"

                                          placeholder="Summarize the key points raised, clarifications, questions, and relevant statements during the discussion...">{{ old("minutes.{$doc->document->id}.discussion", $minute->discussion ?? '') }}</textarea>

                                <div class="form-text">

                                    Record a concise, factual summary of the proceedings.

                                </div>

                            </div>

                            {{-- Motion --}}

                            <div class="minutes-field mb-4">

                                <label for="motion_{{ $doc->id }}"

                                       class="form-label fw-semibold">

                                    <i class="bi bi-megaphone me-2 text-primary"></i>

                                    Motion

                                </label>

                                <textarea id="motion_{{ $doc->id }}"

                                          name="minutes[{{ $doc->document->id }}][motion]"

                                          class="form-control"

                                          rows="3"

                                          placeholder="Record the motion as stated, including the mover and seconder when applicable...">{{ old("minutes.{$doc->document->id}.motion", $minute->motion ?? '') }}</textarea>

                            </div>

                            <div class="row g-3 mb-4">

                               <div class="col-md-6">

                                   <label for="motion_moved_by_{{ $doc->id }}" class="form-label fw-semibold">Motion Moved By</label>

                                   <input type="text" id="motion_moved_by_{{ $doc->id }}"

                                          name="minutes[{{ $doc->document->id }}][motion_moved_by]"

                                          class="form-control"

                                          value="{{ old("minutes.{$doc->document->id}.motion_moved_by", $minute->motion_moved_by ?? '') }}"

                                          placeholder="Name of the member who made the motion">

                               </div>

                               <div class="col-md-6">

                                   <label for="motion_seconded_by_{{ $doc->id }}" class="form-label fw-semibold">Motion Seconded By</label>

                                   <input type="text" id="motion_seconded_by_{{ $doc->id }}"

                                          name="minutes[{{ $doc->document->id }}][motion_seconded_by]"

                                          class="form-control"

                                          value="{{ old("minutes.{$doc->document->id}.motion_seconded_by", $minute->motion_seconded_by ?? '') }}"

                                          placeholder="Name of the member who seconded the motion, if applicable">

                               </div>

                           </div>

                           {{-- Finalized Voting Records --}}

                            @if($agendaVotes->isNotEmpty())

                                <div class="voting-section mb-4">

                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                                        <div>

                                            <h6 class="fw-bold mb-1">

                                                <i class="bi bi-check2-square me-2 text-primary"></i>

                                                Recorded Voting Results

                                            </h6>

                                            <p class="text-muted small mb-0">

                                                Finalized results from the session voting module.

                                            </p>

                                        </div>

                                        <span class="badge bg-success-subtle text-success-emphasis border">

                                            <i class="bi bi-lock-fill me-1"></i>

                                            Read-only

                                        </span>

                                    </div>

                                    @foreach($agendaVotes as $vote)

                                        @php

                                            $result = $vote->result_data ?? [];

                                        @endphp

                                        <div class="voting-result mb-3">

                                            <div class="fw-semibold mb-2">

                                                {{ $vote->motion_title ?: 'Recorded Motion' }}

                                            </div>

                                            @if($vote->motion_description)

                                                <p class="text-muted small mb-3">

                                                    {{ $vote->motion_description }}

                                                </p>

                                            @endif

                                            <div class="row g-2 text-center">

                                                <div class="col-4">

                                                    <div class="vote-count vote-yes">

                                                        <div class="small">Yes</div>

                                                        <div class="fs-4 fw-bold">

                                                            {{ $result['yes'] ?? 0 }}

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-4">

                                                    <div class="vote-count vote-no">

                                                        <div class="small">No</div>

                                                        <div class="fs-4 fw-bold">

                                                            {{ $result['no'] ?? 0 }}

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-4">

                                                    <div class="vote-count vote-abstain">

                                                        <div class="small">Abstain</div>

                                                        <div class="fs-4 fw-bold">

                                                            {{ $result['abstain'] ?? 0 }}

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="d-flex flex-wrap justify-content-between gap-2 mt-3 pt-3 border-top">

                                                <span class="small text-muted">

                                                    Total recorded votes:

                                                    <strong>{{ $result['total_votes'] ?? 0 }}</strong>

                                                </span>

                                                <span class="small text-muted">

                                                    Finalized:

                                                    {{ $vote->finalized_at?->format('M d, Y h:i A') ?? 'N/A' }}

                                                </span>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @endif

                            {{-- Decision --}}

                            <div class="minutes-field">

                                <label for="decision_{{ $doc->id }}"

                                       class="form-label fw-semibold">

                                    <i class="bi bi-journal-check me-2 text-primary"></i>

                                    Decision / Disposition

                                </label>

                                <textarea id="decision_{{ $doc->id }}"

                                          name="minutes[{{ $doc->document->id }}][decision]"

                                          class="form-control"

                                          rows="3"

                                          placeholder="Record the decision, action taken, or disposition of the agenda item. Verify the official outcome before recording it...">{{ old("minutes.{$doc->document->id}.decision", $minute->decision ?? '') }}</textarea>

                                <div class="form-text">

                                    Enter the official decision or disposition. Do not infer passage solely from vote totals.

                                </div>

                                <div class="mt-3">

                                    <label for="official_reference_{{ $doc->id }}" class="form-label fw-semibold">Resolution / Ordinance Reference</label>

                                    <input type="text" id="official_reference_{{ $doc->id }}"

                                           name="minutes[{{ $doc->document->id }}][official_reference]"

                                           class="form-control"

                                           value="{{ old("minutes.{$doc->document->id}.official_reference", $minute->official_reference ?? '') }}"

                                           placeholder="e.g. Resolution No. 2026-001, if assigned">

                                    <div class="form-text">Enter the official reference only when one has been assigned.</div>

                                </div>

                            </div>

                        </div>

                    </section>

                @empty

                    <div class="card minutes-card">

                        <div class="card-body text-center py-5">

                            <i class="bi bi-journal-x display-5 text-muted"></i>

                            <h5 class="mt-3">No agenda items available</h5>

                            <p class="text-muted mb-0">

                                Add agenda items to this session before encoding its minutes.

                            </p>

                        </div>

                    </div>

                @endforelse

            </div>

        </div>


        {{-- Committee Report Proceedings Reference --}}
        @if($committeeReports->isNotEmpty())

            <div class="card minutes-card mb-4">

                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-people-fill me-2 text-primary"></i>
                            Committee Report Proceedings
                        </h5>
                        <p class="text-muted small mb-0">
                            Reference entries recorded during deliberation of committee reports.
                        </p>
                    </div>

                    <span class="badge bg-info-subtle text-info-emphasis border">
                        {{ $committeeReports->count() }}
                        {{ \Illuminate\Support\Str::plural('report', $committeeReports->count()) }}
                    </span>
                </div>

                <div class="card-body">

                    @foreach($committeeReports as $reportItem)

                        @php
                            $referral = $reportItem->referral;
                            $committeeProceedings = $committeeProceedingsByAgenda
                                ->get($reportItem->id, collect());
                            
                            $committeeReportMinute = $committeeReportMinutes->get($reportItem->id);    

                            $originalDocument = $referral?->sessionDocument?->document;

                            $committeeName = $referral?->committee?->name
                                ?? 'Committee';

                            $originalTitle = $originalDocument?->title
                                ?? 'Original agenda document unavailable';
                        @endphp

                        <div class="border rounded-3 p-3 p-md-4 mb-3">

                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">

                                <div>
                                    <span class="badge bg-info text-dark mb-2">
                                        Committee Report
                                    </span>

                                    <h6 class="fw-bold mb-1">
                                        {{ $committeeName }}
                                        — Referral #{{ $reportItem->committee_referral_id }}
                                    </h6>

                                    <div class="small text-muted">
                                        Agenda Item {{ $reportItem->agenda_order }}
                                    </div>

                                    <div class="small text-muted mt-1">
                                        Original Agenda:
                                        {{ $originalTitle }}
                                    </div>
                                </div>

                                <span class="badge bg-white text-dark border">
                                    {{ $committeeProceedings->count() }}
                                    {{ \Illuminate\Support\Str::plural('entry', $committeeProceedings->count()) }}
                                </span>

                            </div>

                            @if($referral?->recommendation)
                                <div class="bg-light border rounded-3 p-3 mb-3">
                                    <div class="small fw-bold text-muted mb-1">
                                        COMMITTEE RECOMMENDATION
                                    </div>
                                    <div style="white-space: pre-line;">{{ $referral->recommendation }}</div>
                                </div>
                            @endif

                            <div class="border-top pt-3">

                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-journal-text me-2 text-primary"></i>
                                    Recorded Proceedings
                                </h6>

                                @forelse($committeeProceedings as $entry)

                                    <div class="bg-light border rounded-3 p-3 mb-3">

                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">

                                            <span class="badge bg-primary-subtle text-primary-emphasis border">
                                                {{ \Illuminate\Support\Str::headline($entry->entry_type) }}
                                            </span>

                                            <span class="small text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $entry->created_at?->format('M d, Y h:i A') }}
                                            </span>

                                        </div>

                                        <div style="white-space: pre-line;">{{ $entry->content }}</div>

                                        @if($entry->entry_type === 'motion')
                                            <div class="small border-top pt-2 mt-2">

                                                @if($entry->moved_by)
                                                    <div>
                                                        <strong>Moved by:</strong>
                                                        {{ $entry->moved_by }}
                                                    </div>
                                                @endif

                                                @if($entry->seconded_by)
                                                    <div>
                                                        <strong>Seconded by:</strong>
                                                        {{ $entry->seconded_by }}
                                                    </div>
                                                @endif

                                            </div>
                                        @endif

                                        <div class="small text-muted border-top pt-2 mt-2">
                                            <i class="bi bi-person-circle me-1"></i>
                                            Recorded by:
                                            {{ $entry->recorder?->name ?? 'Unknown' }}
                                        </div>

                                    </div>

                                @empty

                                    <div class="text-center py-4 bg-light rounded-3">
                                        <i class="bi bi-journal-x fs-3 text-muted"></i>
                                        <p class="fw-semibold mb-1 mt-2">
                                            No proceedings recorded yet
                                        </p>
                                        <p class="text-muted small mb-0">
                                            No Presiding Officer entries are available for this committee report.
                                        </p>
                                    </div>

                                @endforelse

                                                        </div>

                            {{-- Secretary's Official Committee Report Minutes --}}
                            <div class="border-top mt-4 pt-4">

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            <i class="bi bi-file-earmark-text me-2 text-success"></i>
                                            Secretary's Official Minutes
                                        </h6>

                                        <p class="small text-muted mb-0">
                                            Prepare the official minutes for this committee report.
                                            These entries will be included in the final session minutes PDF.
                                        </p>
                                    </div>

                                    <span class="badge bg-success-subtle text-success-emphasis border">
                                        Official Record
                                    </span>
                                </div>

                                <div class="row g-3">

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            Official Reference
                                        </label>

                                        <input
                                            type="text"
                                            name="committee_report_minutes[{{ $reportItem->id }}][official_reference]"
                                            class="form-control"
                                            value="{{ old('committee_report_minutes.' . $reportItem->id . '.official_reference', $committeeReportMinute->official_reference ?? '') }}"
                                            placeholder="Enter official reference, if applicable"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            Discussion
                                        </label>

                                        <textarea
                                            name="committee_report_minutes[{{ $reportItem->id }}][discussion]"
                                            class="form-control"
                                            rows="4"
                                            placeholder="Record the official discussion and deliberations..."
                                        >{{ old('committee_report_minutes.' . $reportItem->id . '.discussion', $committeeReportMinute->discussion ?? '') }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            Motion
                                        </label>

                                        <textarea
                                            name="committee_report_minutes[{{ $reportItem->id }}][motion]"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Record the motion, if any..."
                                        >{{ old('committee_report_minutes.' . $reportItem->id . '.motion', $committeeReportMinute->motion ?? '') }}</textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Moved By
                                        </label>

                                        <input
                                            type="text"
                                            name="committee_report_minutes[{{ $reportItem->id }}][motion_moved_by]"
                                            class="form-control"
                                            value="{{ old('committee_report_minutes.' . $reportItem->id . '.motion_moved_by', $committeeReportMinute->motion_moved_by ?? '') }}"
                                            placeholder="Name of member"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Seconded By
                                        </label>

                                        <input
                                            type="text"
                                            name="committee_report_minutes[{{ $reportItem->id }}][motion_seconded_by]"
                                            class="form-control"
                                            value="{{ old('committee_report_minutes.' . $reportItem->id . '.motion_seconded_by', $committeeReportMinute->motion_seconded_by ?? '') }}"
                                            placeholder="Name of member"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            Decision / Disposition
                                        </label>

                                        <textarea
                                            name="committee_report_minutes[{{ $reportItem->id }}][decision]"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Record the Council's decision or disposition..."
                                        >{{ old('committee_report_minutes.' . $reportItem->id . '.decision', $committeeReportMinute->decision ?? '') }}</textarea>
                                    </div>

                                </div>

                                <div class="small text-muted mt-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    These fields are maintained by the Secretary and are separate
                                    from the Presiding Officer's recorded proceedings above.
                                </div>

                            </div>

                        </div>

                    @endforeach

                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        These records are references only and are not automatically included in official minutes.
                        The Secretary retains responsibility for preparing the official minutes.
                    </div>

                </div>
            </div>

        @endif


        {{-- Bottom Actions --}}

        <div class="minutes-action-bar mt-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div class="small text-muted">

                    <i class="bi bi-info-circle me-1"></i>

                    Save your entries before reviewing the draft.

                </div>

                <div class="d-flex flex-wrap gap-2">

                    <button type="submit"

                            class="btn btn-primary px-4"

                            id="saveMinutesButton">

                        <i class="bi bi-save me-2"></i>

                        Save Draft

                    </button>

                    <button type="submit"

                            class="btn btn-outline-primary px-4"

                            formaction="{{ route('secretary.minutes.store', $session->id) }}"

                            name="redirect_to_review"

                            value="1">

                        <i class="bi bi-eye me-2"></i>

                        Save & Review

                    </button>

                <button

                    type="button"

                    class="btn btn-success"

                    data-bs-toggle="modal"

                    data-bs-target="#submitReviewModal"

                >

                    Submit for Review

                </button>

                    @if($minutes->count() > 0)

                      <!--  <a href="{{ route('secretary.minutes.review', $session->id) }}"

                           class="btn btn-outline-secondary px-3">

                            <i class="bi bi-file-earmark-check me-2"></i>

                            Review Saved Minutes

                        </a>-->

                    @endif

                </div>

            </div>

        </div>

    </form>

@endif

@if($session->minutes_status !== 'finalized')

        <div class="modal fade" id="submitReviewModal" tabindex="-1"

            aria-labelledby="submitReviewModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title" id="submitReviewModalLabel">

                            Submit Minutes for Review

                        </h5>

                        <button type="button" class="btn-close"

                                data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>

                    <div class="modal-body">

                        Are you sure you want to submit these minutes to the

                        Presiding Officer for review?

                        <p class="text-muted small mt-2 mb-0">

                            After submission, the Presiding Officer can review

                            the minutes or return them for corrections.

                        </p>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary"

                                data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <form method="POST"

                            action="{{ route('secretary.minutes.submit-review', $session->id) }}">

                            @csrf

                            <button type="submit" class="btn btn-success">

                                <i class="fas fa-paper-plane me-1"></i>

                                Yes, Submit for Review

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

@endif

</div>

@endsection

<style>

.minutes-page {

    color: #1f2937;

    --minutes-border: #e5e7eb;

    --minutes-muted: #6b7280;

    --minutes-primary: #0d6efd;

}

.minutes-page .minutes-card {

    border: 1px solid var(--minutes-border);

    border-radius: 12px;

    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.035);

    overflow: hidden;

}

.minutes-page .card-header {

    padding: 1rem 1.25rem;

    border-bottom: 1px solid var(--minutes-border);

}

.minutes-page .card-body {

    padding: 1.25rem;

}

.minutes-page .section-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 42px;

    height: 42px;

    flex-shrink: 0;

    border-radius: 10px;

    background: #eff6ff;

    color: #1d4ed8;

    font-size: 1.2rem;

}

.minutes-page .form-label {

    display: block;

    color: #374151;

    font-size: 0.9rem;

    margin-bottom: 0.5rem;

}

.minutes-page .form-control {

    border-color: #d1d5db;

    border-radius: 8px;

    padding: 0.7rem 0.85rem;

}

.minutes-page .form-control:focus {

    border-color: #86b7fe;

    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);

}

.minutes-page textarea {

    resize: vertical;

    min-height: 90px;

    line-height: 1.6;

}

.minutes-page .agenda-sidebar {

    position: sticky;

    top: 80px;

}

.minutes-page .agenda-nav-card {

    max-height: calc(100vh - 100px);

    display: flex;

    flex-direction: column;

}

.minutes-page .agenda-list {

    overflow-y: auto;

}

.minutes-page .agenda-link {

    padding: 1rem;

    border-left: 3px solid transparent;

    border-right: 0;

    transition: background-color 0.15s ease, border-color 0.15s ease;

}

.minutes-page .agenda-link:hover {

    background-color: #f8fafc;

}

.minutes-page .agenda-link.active {

    background-color: #eef4ff;

    border-left-color: var(--minutes-primary);

}

.minutes-page .agenda-number {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 34px;

    height: 34px;

    flex-shrink: 0;

    border-radius: 9px;

    background: #f3f4f6;

    color: #374151;

    font-weight: 700;

    font-size: 0.85rem;

}

.minutes-page .agenda-link.active .agenda-number {

    background: #dbeafe;

    color: #1d4ed8;

}

.minutes-page .agenda-number-large {

    width: 42px;

    height: 42px;

    font-size: 1rem;

}

.minutes-page .agenda-title {

    font-weight: 600;

    font-size: 0.9rem;

    line-height: 1.45;

    overflow-wrap: anywhere;

}

.minutes-page .agenda-heading {

    line-height: 1.45;

    overflow-wrap: anywhere;

}

.minutes-page .min-width-0 {

    min-width: 0;

}

.minutes-page .agenda-card {

    scroll-margin-top: 90px;

}

.minutes-page .voting-section {

    padding: 1rem;

    border: 1px solid #dbeafe;

    border-radius: 10px;

    background: #f8fbff;

}

.minutes-page .voting-result {

    background: #fff;

    border: 1px solid var(--minutes-border);

    border-radius: 10px;

    padding: 1rem;

}

.minutes-page .vote-count {

    padding: 0.8rem 0.4rem;

    border-radius: 8px;

}

.minutes-page .vote-yes {

    background: #ecfdf5;

    color: #047857;

}

.minutes-page .vote-no {

    background: #fef2f2;

    color: #b91c1c;

}

.minutes-page .vote-abstain {

    background: #f3f4f6;

    color: #4b5563;

}

.minutes-page .minutes-action-bar {

    position: sticky;

    bottom: 0;

    z-index: 20;

    padding: 1rem 1.25rem;

    background: rgba(255, 255, 255, 0.97);

    border: 1px solid var(--minutes-border);

    border-radius: 12px;

    box-shadow: 0 -4px 18px rgba(15, 23, 42, 0.06);

    backdrop-filter: blur(8px);

}

.minutes-page .btn {

    border-radius: 8px;

    font-weight: 600;

}

 .minutes-page .alert {
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 10px;
}
.minutes-page .form-select {
    border-color: #d1d5db;
    border-radius: 8px;
    padding: 0.7rem 2.25rem 0.7rem 0.85rem;
}
.minutes-page .table thead th {
    color: #64748b;
    background: #f8fafc;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.035em;
    text-transform: uppercase;
    white-space: nowrap;
}
.minutes-page .table td {
    padding: 0.75rem;
}
.minutes-page .agenda-link:focus-visible {
    outline: 2px solid var(--minutes-primary);
    outline-offset: -2px;
}
.minutes-page .minutes-action-bar .btn {
    min-height: 42px;
}

@media (max-width: 991.98px) {

    .minutes-page .agenda-sidebar {

        position: static;

    }

    .minutes-page .agenda-nav-card {

        max-height: 350px;

    }

    .minutes-page .minutes-action-bar {

        position: static;

    }

}

@media (max-width: 575.98px) {

    .minutes-page .card-body {

        padding: 1rem;

    }

    .minutes-page .card-header {

        padding: 1rem;

    }

    .minutes-page .minutes-action-bar {

        padding: 1rem;

    }

    .minutes-page .minutes-action-bar .btn {

        width: 100%;

    }

    .minutes-page .minutes-action-bar > div,

    .minutes-page .minutes-action-bar > div > div:last-child {

        width: 100%;

    }

}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const agendaCards = Array.from(

        document.querySelectorAll('.agenda-card')

    );

    const agendaLinks = Array.from(

        document.querySelectorAll('.agenda-link')

    );

    if (!agendaCards.length || !agendaLinks.length) {

        return;

    }

    const linkByTarget = new Map(

        agendaLinks.map(link => [

            link.dataset.target,

            link

        ])

    );

    function setActiveAgenda(id) {

        agendaLinks.forEach(link => {

            const active = link.dataset.target === id;

            link.classList.toggle('active', active);

            if (active) {

                link.setAttribute('aria-current', 'location');

            } else {

                link.removeAttribute('aria-current');

            }

        });

        const activeLink = linkByTarget.get(id);

        // Keep active agenda highlighting without scrolling the main page.

    }

    agendaLinks.forEach(link => {

        link.addEventListener('click', function () {

            setActiveAgenda(this.dataset.target);

        });

    });

    if ('IntersectionObserver' in window) {

        const observer = new IntersectionObserver(entries => {

            const visible = entries

                .filter(entry => entry.isIntersecting)

                .sort((a, b) =>

                    a.boundingClientRect.top - b.boundingClientRect.top

                );

            if (visible.length) {

                setActiveAgenda(visible[0].target.id);

            }

        }, {

            rootMargin: '-120px 0px -60% 0px',

            threshold: 0

        });

        agendaCards.forEach(card => observer.observe(card));

    }

});

</script>


