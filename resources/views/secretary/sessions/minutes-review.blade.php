@extends('layouts.app')


<style>
    .presiding-minutes-page {
        --minutes-ink: #182230;
        --minutes-muted: #667085;
        --minutes-line: #e7ebf0;
        color: var(--minutes-ink);
    }
    .presiding-minutes-page > .minutes-page-header {
        padding: .25rem .15rem 1.15rem;
        border-bottom: 1px solid var(--minutes-line);
    }
    .presiding-minutes-page .minutes-eyebrow {
        color: #5268a5;
        font-size: .73rem;
        font-weight: 800;
        letter-spacing: .09em;
        text-transform: uppercase;
        margin-bottom: .55rem;
    }
    .presiding-minutes-page .minutes-page-title {
        font-size: clamp(1.45rem, 2.4vw, 2rem);
        font-weight: 780;
        letter-spacing: -.035em;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }
    .presiding-minutes-page .minutes-review-chip {
        display: inline-flex;
        align-items: center;
        background: #edf2ff;
        border: 1px solid #dbe4ff;
        border-radius: 999px;
        color: #344b9a;
        font-size: .84rem;
        font-weight: 700;
        padding: .65rem .9rem;
        white-space: nowrap;
    }
    .presiding-minutes-page .minutes-section-title {
        color: var(--minutes-ink);
        font-size: 1.12rem;
        font-weight: 750;
        letter-spacing: -.02em;
        margin-top: 1.5rem;
    }
    .presiding-minutes-page .minutes-section-title i { color: #5268a5; }
    .presiding-minutes-page .minutes-card,
    .presiding-minutes-page .minutes-agenda-card,
    .presiding-minutes-page .minutes-review-card {
        border: 1px solid var(--minutes-line);
        border-radius: 1rem;
        box-shadow: 0 8px 26px rgba(16, 24, 40, .045);
        overflow: hidden;
    }
    .presiding-minutes-page .minutes-agenda-header {
        background: linear-gradient(110deg, #f8faff, #f3f6fb);
        border-bottom: 1px solid var(--minutes-line);
        color: var(--minutes-ink);
        line-height: 1.55;
        padding: 1rem 1.2rem;
        overflow-wrap: anywhere;
    }
    .presiding-minutes-page .card-body > p { line-height: 1.65; overflow-wrap: anywhere; }
    .presiding-minutes-page .card-body > p > strong {
        display: inline-block;
        color: #667085;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .045em;
        margin-bottom: .2rem;
        text-transform: uppercase;
    }
    .presiding-minutes-page .minutes-table-wrap {
        border: 1px solid var(--minutes-line);
        border-radius: .75rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .presiding-minutes-page .minutes-attendance-table thead th {
        background: #f5f7fa;
        border-bottom: 1px solid var(--minutes-line);
        color: #475467;
        font-size: .74rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .presiding-minutes-page .minutes-attendance-table td,
    .presiding-minutes-page .minutes-attendance-table th { padding: .75rem .85rem; }
    .presiding-minutes-page .minutes-vote-card {
        background: #fff;
        border: 1px solid var(--minutes-line);
        border-radius: .8rem;
        padding: 1rem;
    }
    .presiding-minutes-page .minutes-vote-card p { overflow-wrap: anywhere; }
    .presiding-minutes-page .minutes-review-header {
        background: #f8faff;
        border-bottom: 1px solid var(--minutes-line);
        padding: 1rem 1.2rem;
    }
    .presiding-minutes-page textarea.form-control {
        border-color: #d0d5dd;
        border-radius: .75rem;
        padding: .85rem 1rem;
        resize: vertical;
    }
    .presiding-minutes-page textarea.form-control:focus {
        border-color: #8094e8;
        box-shadow: 0 0 0 .2rem rgba(66, 87, 165, .12);
    }
    .presiding-minutes-page .minutes-action-btn,
    .presiding-minutes-page .btn {
        border-radius: .7rem;
        font-weight: 650;
    }
    .presiding-minutes-page .minutes-confirm-modal {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
    }
    .presiding-minutes-page .minutes-confirm-modal .modal-header,
    .presiding-minutes-page .minutes-confirm-modal .modal-footer {
        border-color: #eef0f3;
        padding: 1rem 1.25rem;
    }
    .presiding-minutes-page .minutes-confirm-modal .modal-body {
        color: #475467;
        line-height: 1.65;
        padding: 1.25rem;
    }
    @media (max-width: 767.98px) {
        .presiding-minutes-page { padding-left: 1rem; padding-right: 1rem; }
        .presiding-minutes-page .minutes-page-title { font-size: 1.45rem; }
        .presiding-minutes-page > .d-flex.justify-content-between { gap: .75rem; }
        .presiding-minutes-page > .d-flex.justify-content-between .btn { width: 100%; }
    }
</style>



@section('content')

<div class="container-fluid presiding-minutes-page py-4 py-lg-5">

    <div class="minutes-page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="minutes-eyebrow"><i class="fas fa-landmark me-2"></i>Legislative Session Management</div>
            <h1 class="minutes-page-title mb-2">{{ $session->title }}</h1>
            <p class="text-muted mb-0">
                {{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
            </p>
        </div>

        <span class="minutes-review-chip"><i class="fas fa-clipboard-check me-2"></i>Minutes Review</span>
    </div>

    <hr>


    @if (in_array($session->minutes_status, ['reviewed', 'revision_required', 'finalized']) || $session->minuteReviews->isNotEmpty())
        <section class="card minutes-review-card mb-4" aria-labelledby="presidingReviewHistoryHeading">
            <div class="minutes-review-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h2 class="h5 mb-1" id="presidingReviewHistoryHeading">
                        <i class="fas fa-clipboard-check me-2"></i>Presiding Officer Review Results
                    </h2>
                    <p class="text-muted small mb-0">Decision, review comments, and recorded review activity for this session.</p>
                </div>
                @php
                    $reviewStatus = $session->minutes_status ?? 'unknown';
                @endphp
                <span class="badge {{ $reviewStatus === 'reviewed' ? 'bg-success' : ($reviewStatus === 'revision_required' ? 'bg-warning text-dark' : ($reviewStatus === 'finalized' ? 'bg-primary' : 'bg-secondary')) }} px-3 py-2">
                    {{ ucfirst(str_replace('_', ' ', $reviewStatus)) }}
                </span>
            </div>
            <div class="card-body">
                @forelse ($session->minuteReviews->sortByDesc('created_at') as $review)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                            <strong>{{ ucfirst(str_replace('_', ' ', $review->action ?? 'Review action')) }}</strong>
                            <span class="text-muted small">
                                <i class="far fa-clock me-1"></i>
                                {{ $review->created_at?->format('F d, Y h:i A') ?? 'Date unavailable' }}
                            </span>
                        </div>
                        <div class="text-muted small mb-1">Presiding Officer's comments</div>
                        <p class="mb-0" style="white-space: pre-line; overflow-wrap: anywhere;">{{ $review->comments ?: 'No comments were recorded for this review action.' }}</p>
                    </div>
                @empty
                    <div class="alert alert-light border mb-0">
                        No review comments or review history are recorded for this session.
                    </div>
                @endforelse
            </div>
        </section>
    @endif

<h2 class="minutes-section-title mb-3"><i class="fas fa-list-check me-2"></i>Session Proceedings</h2>

<div class="card minutes-card mb-4">
    <div class="card-body">

        <p>
            <strong>Presiding Officer:</strong><br>
            {{ $session->presiding_officer ?: '—' }}
        </p>

        <p>
            <strong>Call to Order:</strong><br>
            {{ $session->call_to_order ?: '—' }}
        </p>

        <p>
            <strong>Prayer:</strong><br>
            {{ $session->prayer ?: '—' }}
        </p>

        <hr>

        <h6>Roll Call and Attendance</h6>

        @if(isset($attendance) && $attendance->isNotEmpty())
            <div class="table-responsive minutes-table-wrap">
                <table class="table table-hover align-middle minutes-attendance-table mb-0">
                    <thead>
                        <tr>
                            <th>Member / Attendee</th>
                            <th>Role</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendance as $attendee)
                            <tr>
                                <td>{{ $attendee->attendee_name }}</td>
                                <td>{{ $attendee->attendee_role ?: '—' }}</td>
                                <td>{{ ucfirst($attendee->status ?? '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p>No attendance records available.</p>
        @endif

        <p>
            <strong>Roll Call Remarks:</strong><br>
            {{ $session->roll_call ?: '—' }}
        </p>

        <p>
            <strong>Quorum Confirmation:</strong><br>
            @switch($session->quorum_confirmation ?? '')
                @case('confirmed')
                    Quorum confirmed
                    @break
                @case('not_confirmed')
                    Quorum not confirmed
                    @break
                @default
                    —
            @endswitch
        </p>

        <p>
            <strong>Approval of Previous Minutes:</strong><br>
            @switch($session->previous_minutes_status ?? '')
                @case('approved')
                    Approved
                    @break
                @case('approved_with_corrections')
                    Approved with corrections
                    @break
                @case('deferred')
                    Deferred
                    @break
                @case('not_applicable')
                    Not applicable
                    @break
                @default
                    —
            @endswitch
        </p>

        <p class="mb-0">
            <strong>Adjournment Time:</strong><br>
            {{ $session->adjournment_time ?: '—' }}
        </p>

    </div>
</div>


    <h2 class="minutes-section-title mb-3"><i class="fas fa-file-lines me-2"></i>Agenda Proceedings</h2>

    @forelse($documents as $doc)

        @php
            $minute = $minutes[$doc->document->id] ?? null;
            $votes = $votesByAgenda->get($doc->id, collect());
        @endphp

        <div class="card minutes-agenda-card mb-4">

            <div class="card-header minutes-agenda-header">
                <strong>
                    {{ $doc->agenda_order }}.
                    {{ $doc->document->title ?? 'Untitled Document' }}
                </strong>
            </div>

            <div class="card-body">

                <p>
                    <strong>Proponent:</strong><br>
                    {{ $doc->document->member->name ?? 'Member' }}
                </p>

                <p>
                    <strong>Official Reference:</strong><br>
                    {{ $minute->official_reference ?? '—' }}
                </p>

                <p>
                    <strong>Discussion:</strong><br>
                    {!! nl2br(e($minute->discussion ?? '—')) !!}
                </p>

                <p>
                    <strong>Motion:</strong><br>
                    {!! nl2br(e($minute->motion ?? '—')) !!}
                </p>

                <p>
                    <strong>Moved By:</strong><br>
                    {{ $minute->motion_moved_by ?? '—' }}
                </p>

                <p>
                    <strong>Seconded By:</strong><br>
                    {{ $minute->motion_seconded_by ?? '—' }}
                </p>

                <div class="mb-3">
                    <strong>Voting Results:</strong>

                    @if($votes->isNotEmpty())
                        @foreach($votes as $vote)
                            @php
                                $resultData = $vote->result_data ?? [];
                            @endphp

                            <div class="minutes-vote-card mt-2">
                                <p class="mb-2">
                                    <strong>Status:</strong>
                                    {{ ucfirst($vote->status) }}
                                </p>

                                @if(is_array($resultData) && count($resultData))
                                    @foreach($resultData as $key => $value)
                                        <p class="mb-1">
                                            <strong>
                                                {{ ucwords(str_replace('_', ' ', $key)) }}:
                                            </strong>

                                            @if(is_array($value))
                                                {{ json_encode($value) }}
                                            @elseif(is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @else
                                                {{ $value ?? '—' }}
                                            @endif
                                        </p>
                                    @endforeach
                                @else
                                    <p class="mb-0">No voting result details available.</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="mt-1">No finalized voting results recorded.</p>
                    @endif
                </div>

                <p class="mb-0">
                    <strong>Decision / Disposition:</strong><br>
                    {!! nl2br(e($minute->decision ?? '—')) !!}
                </p>

            </div>
        </div>

    @empty
        <div class="alert alert-info">
            No agenda items are recorded for this session.
        </div>
    @endforelse

    {{-- Committee Report Official Minutes --}}
@if($committeeReports->isNotEmpty())

    <div class="card minutes-agenda-card mb-4">

        <div class="card-header">
            <strong>
                <i class="fas fa-users me-2"></i>
                Committee Report Official Minutes
            </strong>
        </div>

        <div class="card-body">

            @foreach($committeeReports as $reportItem)

                @php
                    $referral = $reportItem->referral;
                    $committeeMinute = $committeeReportMinutes->get($reportItem->id);

                    $committeeName = $referral?->committee?->name ?? 'Committee';

                    $originalTitle = $referral?->sessionDocument?->document?->title
                        ?? 'Original agenda document unavailable';
                @endphp

                <div class="border rounded-3 p-3 mb-4">

                    <h5 class="fw-bold">
                        {{ $reportItem->agenda_order }}.
                        {{ $committeeName }} — Referral #{{ $reportItem->committee_referral_id }}
                    </h5>

                    <p class="text-muted small">
                        Original Agenda: {{ $originalTitle }}
                    </p>

                    @if($referral?->recommendation)
                        <div class="bg-light border rounded p-3 mb-3">
                            <strong>Committee Recommendation</strong>
                            <p class="mb-0 mt-2" style="white-space: pre-line;">{{ $referral->recommendation }}</p>
                        </div>
                    @endif

                    <h6 class="fw-bold text-success mt-3">
                        Secretary's Official Record
                    </h6>

                    <p>
                        <strong>Official Reference:</strong><br>
                        {{ $committeeMinute->official_reference ?? '—' }}
                    </p>

                    <p>
                        <strong>Discussion:</strong><br>
                        {!! nl2br(e($committeeMinute->discussion ?? '—')) !!}
                    </p>

                    <p>
                        <strong>Motion:</strong><br>
                        {!! nl2br(e($committeeMinute->motion ?? '—')) !!}
                    </p>

                    <p>
                        <strong>Moved By:</strong><br>
                        {{ $committeeMinute->motion_moved_by ?? '—' }}
                    </p>

                    <p>
                        <strong>Seconded By:</strong><br>
                        {{ $committeeMinute->motion_seconded_by ?? '—' }}
                    </p>

                    <p class="mb-0">
                        <strong>Decision / Disposition:</strong><br>
                        {!! nl2br(e($committeeMinute->decision ?? '—')) !!}
                    </p>

                    @if(!$committeeMinute)
                        <div class="alert alert-warning mt-3 mb-0">
                            Official minutes have not yet been saved for this committee report.
                        </div>
                    @endif

                </div>

            @endforeach

        </div>
    </div>

@endif


@if(($session->minutes_status ?? 'draft') === 'for_review')
    <div class="card minutes-review-card mt-4">
        <div class="card-header minutes-review-header">
            <strong><i class="fas fa-clipboard-check me-2"></i>Presiding Officer Review</strong>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('presiding-officer.minutes.return-for-corrections', $session->id) }}">
                @csrf

                <label for="comments" class="form-label">
                    Review Comments
                </label>

                <textarea
                    id="comments"
                    name="comments"
                    class="form-control mb-3"
                    rows="4"
                    placeholder="Enter corrections or observations..."
                >{{ old('comments') }}</textarea>

                <button type="submit" class="btn btn-warning minutes-action-btn">
                    Return for Corrections
                </button>
            </form>

            <hr>

            <form method="POST"
                    id="markReviewedForm"
                    action="{{ route('presiding-officer.minutes.mark-reviewed', $session->id) }}">
                    @csrf

                <label for="review_comments" class="form-label">
                    General Review Remarks (Optional)
                </label>

                <textarea
                    id="review_comments"
                    name="comments"
                    class="form-control mb-3"
                    rows="3"
                    placeholder="Enter general review remarks..."
                >{{ old('comments') }}</textarea>

                <button
                    type="button"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmMarkReviewedModal"
                >
                    Mark as Reviewed
                </button>
            </form>

            {{-- Confirm Mark as Reviewed Modal --}}
            <div class="modal fade"
                id="confirmMarkReviewedModal"
                tabindex="-1"
                aria-labelledby="confirmMarkReviewedLabel"
                aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content minutes-confirm-modal">

                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmMarkReviewedLabel">
                                Confirm Review
                            </h5>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close">
                            </button>
                        </div>

                        <div class="modal-body">
                            Are you sure you want to mark these minutes as reviewed?
                            Please ensure that you have completed your review before proceeding.
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="button"
                                    class="btn btn-success minutes-action-btn"
                                    id="confirmMarkReviewedBtn">
                                Yes, Mark as Reviewed
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


    <div class="d-flex justify-content-start mt-4">

        @if(in_array($session->minutes_status, ['draft', 'revision_required']))
            <a href="{{ route('secretary.minutes.index', $session->id) }}"
            class="btn btn-outline-secondary minutes-action-btn">
                Edit Minutes
            </a>
        @endif

    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const confirmButton = document.getElementById('confirmMarkReviewedBtn');
        const reviewForm = document.getElementById('markReviewedForm');

        if (confirmButton && reviewForm) {
            confirmButton.addEventListener('click', function () {
                reviewForm.requestSubmit();
            });
        }
    });
</script>

