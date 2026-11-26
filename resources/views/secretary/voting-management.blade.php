

@extends('layouts.app')

@section('styles')
<style>
    .voting-management { --vm-border:#e2e8f0; --vm-muted:#64748b; --vm-ink:#0f172a; color:var(--vm-ink); }
    .voting-management .vm-header { padding-bottom:1.25rem; margin-bottom:1.5rem; border-bottom:1px solid var(--vm-border); }
    .voting-management .vm-eyebrow { color:var(--vm-muted); font-size:.72rem; font-weight:750; letter-spacing:.09em; text-transform:uppercase; }
    .voting-management .vm-title { font-size:clamp(1.5rem,2vw,1.95rem); font-weight:750; letter-spacing:-.035em; }
    .voting-management .vm-subtitle { color:var(--vm-muted); }
    .voting-management .vm-card { border:1px solid var(--vm-border)!important; border-radius:.9rem; overflow:hidden; box-shadow:0 5px 20px rgba(15,23,42,.045)!important; }
    .voting-management .vm-card > .card-header { padding:1.1rem 1.25rem; border-bottom:1px solid #edf1f5; }
    .voting-management .vm-card > .card-body { padding:1.25rem; }
    .voting-management .vm-metric { height:100%; padding:1rem; border:1px solid var(--vm-border); border-radius:.75rem; background:#fff; }
    .voting-management .vm-metric-label { color:var(--vm-muted); font-size:.8rem; font-weight:600; }
    .voting-management .vm-metric-value { margin-top:.35rem; font-size:1.35rem; font-weight:750; letter-spacing:-.03em; overflow-wrap:anywhere; }
    .voting-management .form-control,.voting-management .form-select { min-height:42px; border-color:#dbe2ea; border-radius:.55rem; }
    .voting-management textarea.form-control { min-height:unset; }
    .voting-management .btn { border-radius:.55rem; }
    .voting-management .vm-vote-card { border:1px solid var(--vm-border)!important; border-radius:.8rem; padding:1.1rem; }
    .voting-management .vm-count { border:1px solid #e8edf3; border-radius:.65rem; background:#f8fafc; padding:.8rem; height:100%; }
    .voting-management .table thead th { color:#475569; background:#f8fafc; font-size:.72rem; font-weight:750; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; border-bottom-color:var(--vm-border); }
    .voting-management .table td { vertical-align:middle; }
    .voting-management .modal-content { overflow:hidden; border:1px solid var(--vm-border); border-radius:.9rem; box-shadow:0 20px 50px rgba(15,23,42,.16); }
    .voting-management .modal-header,.voting-management .modal-footer { padding:1rem 1.25rem; border-color:#edf1f5; }
    @media(max-width:767.98px) { .voting-management .vm-card > .card-body { padding:1rem; } .voting-management .vm-header .btn { width:100%; } }
</style>
@endsection

@section('content')

<div class="container-fluid py-4 py-lg-5 voting-management">

    <div class="vm-header d-flex flex-wrap justify-content-between align-items-end gap-3">

        <div>

            <div class="vm-eyebrow mb-2">

                Session Management

            </div>

            <h1 class="vm-title mb-2">Voting Management</h1>

            <div class="text-muted">

                {{ $session->title }}

                @if($session->session_date)

                    · {{ $session->session_date->format('F d, Y') }}

                @endif

            </div>

        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('secretary.session-control.show', $session->id) }}"

               class="btn btn-outline-secondary">

                <i class="fa-solid fa-arrow-left me-1"></i>

                Session Control

            </a>

        </div>

    </div>

    <div class="alert {{ $quorumMet === true ? 'alert-success' : 'alert-warning' }}">

        <strong>Attendance and Quorum</strong>

        <div>

            Present or late: {{ $presentCount }}

            /

            Required: {{ $quorumRequired ?: 'Not configured' }}

        </div>

        @if($quorumMet === true)

            <div>Quorum has been met. Voting may proceed.</div>

        @elseif($quorumMet === false)

            <div>Quorum has not been met. Opening a vote is blocked.</div>

        @else

            <div>Set the required quorum before opening a vote.</div>

        @endif

    </div>

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show" role="alert">

            {{ session('success') }}

            <button type="button"

                    class="btn-close"

                    data-bs-dismiss="alert"

                    aria-label="Close"></button>

        </div>

    @endif

    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show" role="alert">

            {{ session('error') }}

            <button type="button"

                    class="btn-close"

                    data-bs-dismiss="alert"

                    aria-label="Close"></button>

        </div>

    @endif

    @if($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    {{-- Session and agenda status --}}

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card vm-card h-100">

                <div class="card-body">

                    <div class="small text-muted mb-1">Session Status</div>

                    <span class="badge text-bg-{{ $session->status === 'ongoing' ? 'success' : 'secondary' }}">

                        {{ ucfirst($session->status) }}

                    </span>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card vm-card h-100">

                <div class="card-body">

                    <div class="small text-muted mb-1">Active Agenda Item</div>

                    <div class="fw-semibold">

                        {{ $activeAgendaItem?->title ?? 'No active agenda item' }}

                    </div>

                    @if($activeAgendaItem)

                        <small class="text-muted">

                            Agenda {{ $activeAgendaItem->agenda_order }}

                            · {{ ucfirst(str_replace('_', ' ', $activeAgendaItem->status)) }}

                        </small>

                    @endif

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card vm-card h-100">

                <div class="card-body">

                    <div class="small text-muted mb-1">Recorded Motions</div>

                    <div class="vm-metric-value">{{ $votes->count() }}</div>

                </div>

            </div>

        </div>

    </div>

    {{-- Open a new vote --}}

    <div class="card vm-card mb-4">

        <div class="card-header bg-white py-3">

            <h2 class="h5 mb-1">Open a Vote</h2>

            <p class="text-muted small mb-0">

                Create a voting motion for the active agenda item.

            </p>

        </div>

        <div class="card-body">

            @if($session->status !== 'ongoing' ||

                !$activeAgendaItem ||

                $activeAgendaItem->status !== 'in_progress')

                <div class="alert alert-warning mb-0">

                    Start the session and activate an agenda item before opening a vote.

                </div>

            @else

                <form method="POST"

                      action="{{ route('secretary.votes.open', $session->id) }}">

                    @csrf

                    <div class="mb-3">

                        <label for="motion_title" class="form-label">

                            Motion Title <span class="text-danger">\*</span>

                        </label>

                        <input type="text"

                               id="motion_title"

                               name="motion_title"

                               class="form-control"

                               maxlength="255"

                               value="{{ old('motion_title') }}"

                               required

                               placeholder="Enter the motion being voted on">

                    </div>

                    <div class="mb-3">

                        <label for="motion_description" class="form-label">

                            Motion Description

                        </label>

                        <textarea id="motion_description"

                                  name="motion_description"

                                  class="form-control"

                                  rows="3"

                                  placeholder="Optional details of the motion">{{ old('motion_description') }}</textarea>

                    </div>

                    <div class="mb-4">

                        <label for="voting_method" class="form-label">

                            Voting Method <span class="text-danger">\*</span>

                        </label>

                        <select id="voting_method"

                                name="voting_method"

                                class="form-select"

                                required>

                            <option value="roll_call"

                                @selected(old('voting_method', 'roll_call') === 'roll_call')>

                                Roll Call

                            </option>

                            <option value="secret"

                                @selected(old('voting_method') === 'secret')>

                                Secret Ballot

                            </option>

                        </select>

                        <div class="form-text">

                            Choose the method authorized for this motion.

                        </div>

                    </div>

                    
<div class="mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <label class="form-label fw-semibold mb-0">
            Eligible Councilors <span class="text-danger">*</span>
        </label>

        <span class="small text-muted">
            {{ $members->count() }} eligible members
        </span>
    </div>

    <div class="border rounded p-3">

        @if($members->isNotEmpty())
            {{-- Select All Toggle --}}
            <div class="form-check border-bottom pb-2 mb-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="selectAllMembers"
                >
                <label class="form-check-label fw-semibold"
                       for="selectAllMembers">
                    Select All
                </label>
            </div>

            {{-- Individual Members --}}
            @foreach($members as $memberAccount)
                <div class="form-check mb-2">
                    <input
                        class="form-check-input eligible-member-checkbox"
                        type="checkbox"
                        name="eligible_member_ids[]"
                        value="{{ $memberAccount->id }}"
                        id="member_{{ $memberAccount->id }}"
                        @checked(in_array(
                            (string) $memberAccount->id,
                            array_map('strval', old('eligible_member_ids', []))
                        ))
                    >

                    <label class="form-check-label"
                           for="member_{{ $memberAccount->id }}">
                        {{ $memberAccount->member?->name ?? $memberAccount->email }}

                        <span class="text-muted small">
                            ({{ $memberAccount->email }})
                        </span>
                    </label>
                </div>
            @endforeach
        @else
            <p class="text-muted mb-0">
                No eligible members present.
            </p>
        @endif
    </div>

    <div class="form-text">
        Only members marked Present or Late are eligible to vote.
    </div>
</div>


                    <button type="button"

                        class="btn btn-primary"

                        @disabled($members->isEmpty())

                        data-bs-toggle="modal"

                        data-bs-target="#openVotingModal">

                    <i class="fa-solid fa-check-to-slot me-1"></i>

                    Open Voting

                </button>

                </form>

                {{-- Open Voting Confirmation Modal --}}

                <div class="modal fade" id="openVotingModal" tabindex="-1"

                    aria-labelledby="openVotingModalLabel" aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title" id="openVotingModalLabel">

                                    <i class="fa-solid fa-check-to-slot text-primary me-1"></i>

                                    Open Voting?

                                </h5>

                                <button type="button" class="btn-close"

                                        data-bs-dismiss="modal"

                                        aria-label="Close"></button>

                            </div>

                            <div class="modal-body">

                                <p>Are you sure you want to open voting for this motion?</p>

                                <div class="border rounded p-3 bg-light">

                                    <div class="small text-muted mb-1">Motion Title</div>

                                    <div class="fw-semibold" id="confirmMotionTitle"></div>

                                    <div class="small text-muted mt-3 mb-1">Voting Method</div>

                                    <div class="fw-semibold" id="confirmVotingMethod"></div>

                                </div>

                                <p class="text-muted small mt-3 mb-0">

                                    Eligible councilors will be able to cast their votes once

                                    voting is opened.

                                </p>

                            </div>

                            <div class="modal-footer">

                                <button type="button" class="btn btn-light"

                                        data-bs-dismiss="modal">

                                    Cancel

                                </button>

                                <button type="button" class="btn btn-primary"

                                        id="confirmOpenVoting">

                                    <i class="fa-solid fa-lock-open me-1"></i>

                                    Confirm and Open Voting

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>

    {{-- Existing and historical votes --}}

    <div class="card vm-card">

        <div class="card-header bg-white py-3">

            <h2 class="h5 mb-1">Voting History and Results</h2>

            <p class="small text-muted mb-0">

                Review open motions, recorded ballots, and finalized tallies.

            </p>

        </div>

        <div class="card-body">

            @forelse($votes as $vote)

                @php

                    $voteColors = [

                        'open' => 'success',

                        'closed' => 'warning',

                        'finalized' => 'secondary',

                    ];

                @endphp

                <div class="vm-vote-card mb-3">

                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">

                        <div>

                            <h3 class="h6 mb-1">{{ $vote->motion_title }}</h3>

                            <div class="small text-muted">

                                Vote #{{ $vote->id }}

                                · {{ $vote->voting_method === 'secret' ? 'Secret Ballot' : 'Roll Call' }}

                            </div>

                        </div>

                        <span class="badge text-bg-{{ $voteColors[$vote->status] ?? 'secondary' }}">

                            {{ ucfirst($vote->status) }}

                        </span>

                    </div>

                    @if($vote->motion_description)

                        <p class="mb-3">{{ $vote->motion_description }}</p>

                    @endif

                    <div class="row g-2 mb-3">

                        <div class="col-6 col-md-3">

                            <div class="vm-count">

                                <div class="small text-muted">Yes</div>
                                <div class="fs-5 fw-semibold" data-yes>{{ $vote->yes_count }}</div>

                            </div>

                        </div>

                        <div class="col-6 col-md-3">

                            <div class="vm-count">

                                <div class="small text-muted">No</div>
                                <div class="fs-5 fw-semibold" data-no>{{ $vote->no_count }}</div>

                            </div>

                        </div>

                        <div class="col-6 col-md-3">

                            <div class="vm-count">

                                <div class="small text-muted">Abstain</div>
                                <div class="fs-5 fw-semibold" data-abstain>{{ $vote->abstain_count }}</div>

                            </div>

                        </div>

                        <div class="col-6 col-md-3">

                            <div class="vm-count">

                                <div class="small text-muted">Ballots</div>

                                <div class="fs-5 fw-semibold">

                                    <span data-ballots>{{ $vote->ballot_count }}</span>
                                    /
                                    <span data-eligible>{{ $vote->eligible_count }}</span>

                                </div>

                            </div>

                        </div>

                        <div class="mt-3"
                            data-vote-progress
                            data-progress-url="{{ route('secretary.votes.progress', $vote->id) }}">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Voting Progress</strong>
                                <span class="small text-muted">
                                    <span data-ballots>{{ $vote->ballot_count }}</span>
                                    /
                                    <span data-eligible>{{ $vote->eligible_count }}</span>
                                    ballots submitted
                                </span>
                            </div>

                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success"
                                    data-progress-bar
                                    role="progressbar"
                                    style="width: {{ $vote->eligible_count > 0
                                        ? round(($vote->ballot_count / $vote->eligible_count) * 100)
                                        : 0 }}%;">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-2 small">
                                <span class="text-success">
                                    <strong data-voted>{{ $vote->ballot_count }}</strong> Voted
                                </span>

                                <span class="text-muted">
                                    <strong data-pending>{{ max(0, $vote->eligible_count - $vote->ballot_count) }}</strong>
                                    Pending
                                </span>

                                <span class="text-muted">
                                    <strong data-percentage>
                                        {{ $vote->eligible_count > 0
                                            ? round(($vote->ballot_count / $vote->eligible_count) * 100)
                                            : 0 }}%
                                    </strong>
                                </span>
                            </div>

                            <div class="small text-muted mt-2" data-last-updated>
                                Waiting for updates...
                            </div>
                        </div>

                    </div>

                    {{-- Individual voting records: Roll Call only --}}

                    @if($vote->voting_method === 'roll_call')

                        <div class="vm-vote-card mb-3">

                            <h4 class="h6 mb-3">

                                <i class="fa-solid fa-list-check me-1"></i>

                                Individual Voting Records

                            </h4>

                            @if($vote->eligible_members->isEmpty())

                                <p class="text-muted mb-0">

                                    No eligible councilors were recorded.

                                </p>

                            @else

                                <div class="table-responsive">

                                    <table class="table table-sm table-hover align-middle mb-0">

                                        <thead>

                                            <tr>

                                                <th>Councilor</th>

                                                <th>Vote</th>

                                                <th>Date and Time</th>

                                            </tr>

                                        </thead>

                                        <tbody data-member-voting-status>

                                            @foreach($vote->eligible_members as $eligibleMember)

                                                @php

                                                    $record = $vote->individual_records

                                                        ->get($eligibleMember->id);

                                                    $choice = $record?->vote;

                                                    $badgeClass = match ($choice) {

                                                        'yes' => 'success',

                                                        'no' => 'danger',

                                                        'abstain' => 'secondary',

                                                        default => 'light',

                                                    };

                                                @endphp

                                                <tr data-member-id="{{ $eligibleMember->id }}">

                                                    <td>

                                                        <div class="fw-semibold">

                                                            {{ $eligibleMember->member?->name

                                                                ?? $eligibleMember->email }}

                                                        </div>

                                                        <small class="text-muted">

                                                            {{ $eligibleMember->email }}

                                                        </small>

                                                    </td>

                                                     <td data-member-vote>
                                                            @if($record)
                                                                <span class="badge text-bg-{{ $record->vote === 'yes' ? 'success' : ($record->vote === 'no' ? 'danger' : 'secondary') }}">
                                                                    {{ ucfirst($record->vote) }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">Not yet voted</span>
                                                            @endif
                                                        </td>

                                                    <td data-member-time>
                                                        {{ $record?->cast_at?->format('M d, Y h:i A') ?? '—' }}
                                                    </td>

                                                </tr>

                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            @endif

                        </div>

                    @endif

                    @if($vote->status === 'open')

                        <button type="button"

                            class="btn btn-warning btn-sm"

                            data-bs-toggle="modal"

                            data-bs-target="#closeVotingModal{{ $vote->id }}">

                        <i class="fa-solid fa-lock me-1"></i>

                        Close Voting

                    </button>

                    @elseif($vote->status === 'closed')

                        <button type="button"

                            class="btn btn-primary btn-sm"

                            data-bs-toggle="modal"

                            data-bs-target="#finalizeVotingModal{{ $vote->id }}">

                        <i class="fa-solid fa-clipboard-check me-1"></i>

                        Finalize Results

                    </button>

                    @elseif($vote->status === 'finalized')

                        <div class="alert alert-light border mb-0">

                            <strong>Finalized result:</strong>

                            @if($vote->result)

                                @php $result = json_decode($vote->result, true) @endphp

                                Yes: {{ $result['yes'] ?? 0 }},

                                No: {{ $result['no'] ?? 0 }},

                                Abstain: {{ $result['abstain'] ?? 0 }},

                                Total: {{ $result['total_votes'] ?? 0 }}

                            @else

                                Tally unavailable.

                            @endif

                             @if($vote->status === 'finalized')

                                <a href="{{ route('secretary.votes.report', $vote->id) }}"
                                target="_blank"
                                class="btn btn-outline-danger">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    Print Official Voting Report
                                </a>

                            @if($vote->signing_request?->status === 'fully_signed')

                                <a href="{{ route('document-signing.download', $vote->signing_request) }}"
                                class="btn btn-success">
                                    <i class="fas fa-download me-1"></i>
                                    Download Signed Results
                                </a>

                            @elseif(in_array($vote->signing_request?->status, [
                                'pending',
                                'partially_signed',
                                'draft'
                            ], true))

                                <a href="{{ route('document-signing.show', $vote->signing_request) }}"
                                class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i>
                                    View Signing Progress
                                </a>

                            @else

                                <form method="POST"
                                    action="{{ route('secretary.votes.submit-for-signing', $vote->id) }}"
                                    class="d-inline">
                                    @csrf

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-file-signature me-1"></i>
                                        Submit for Electronic Signing
                                    </button>
                                </form>

                            @endif

                        @endif

                        </div>

                    @endif

                   

                    {{-- Close Voting Confirmation Modal --}}

                    @if($vote->status === 'open')

                        <div class="modal fade"

                            id="closeVotingModal{{ $vote->id }}"

                            tabindex="-1"

                            aria-labelledby="closeVotingModalLabel{{ $vote->id }}"

                            aria-hidden="true">

                            <div class="modal-dialog modal-dialog-centered">

                                <div class="modal-content">

                                    <form method="POST"

                                        action="{{ route('secretary.votes.close', $vote->id) }}">

                                        @csrf

                                        <div class="modal-header">

                                            <h5 class="modal-title"

                                                id="closeVotingModalLabel{{ $vote->id }}">

                                                <i class="fa-solid fa-lock text-warning me-1"></i>

                                                Close Voting?

                                            </h5>

                                            <button type="button" class="btn-close"

                                                    data-bs-dismiss="modal"

                                                    aria-label="Close"></button>

                                        </div>

                                        <div class="modal-body">

                                            <p>Are you sure you want to close voting for:</p>

                                            <div class="fw-semibold">{{ $vote->motion_title }}</div>

                                            <div class="alert alert-warning mt-3 mb-0">

                                                No further ballots will be accepted after voting closes.

                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-light"

                                                    data-bs-dismiss="modal">

                                                Cancel

                                            </button>

                                            <button type="submit" class="btn btn-warning">

                                                <i class="fa-solid fa-lock me-1"></i>

                                                Confirm Close Voting

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    @endif

                    {{-- Finalize Results Confirmation Modal --}}

                    @if($vote->status === 'closed')

                        <div class="modal fade"

                            id="finalizeVotingModal{{ $vote->id }}"

                            tabindex="-1"

                            aria-labelledby="finalizeVotingModalLabel{{ $vote->id }}"

                            aria-hidden="true">

                            <div class="modal-dialog modal-dialog-centered">

                                <div class="modal-content">

                                    <form method="POST"

                                        action="{{ route('secretary.votes.finalize', $vote->id) }}">

                                        @csrf

                                        <div class="modal-header">

                                            <h5 class="modal-title"

                                                id="finalizeVotingModalLabel{{ $vote->id }}">

                                                <i class="fa-solid fa-clipboard-check text-primary me-1"></i>

                                                Finalize Voting Results?

                                            </h5>

                                            <button type="button" class="btn-close"

                                                    data-bs-dismiss="modal"

                                                    aria-label="Close"></button>

                                        </div>

                                        <div class="modal-body">

                                            <p>Finalize the tally for this motion?</p>

                                            <div class="fw-semibold">{{ $vote->motion_title }}</div>

                                            <div class="alert alert-info mt-3 mb-0">

                                                Please verify the recorded tally before finalizing.

                                                This action will mark the voting results as finalized.

                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-light"

                                                    data-bs-dismiss="modal">

                                                Cancel

                                            </button>

                                            <button type="submit" class="btn btn-primary">

                                                <i class="fa-solid fa-clipboard-check me-1"></i>

                                                Confirm Finalize

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    @endif

                </div>

            @empty

                <div class="text-center py-5">

                    <i class="fa-solid fa-check-to-slot fa-2x text-muted mb-3"></i>

                    <h3 class="h6">No voting motions yet</h3>

                    <p class="text-muted mb-0">

                        Open a motion when the session has an active agenda item.

                    </p>

                </div>

            @endforelse

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const progressPanels = document.querySelectorAll('[data-vote-progress]');

    async function refreshVoteProgress(panel) {
        const url = panel.dataset.progressUrl;
        if (!url) return;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;

            const data = await response.json();

            const card = panel.closest('.vm-card') || panel.parentElement.parentElement;

            const updateAll = (selector, value) => {
                card.querySelectorAll(selector).forEach(element => {
                    element.textContent = value;
                });
            };

            updateAll('[data-yes]', data.yes);
            updateAll('[data-no]', data.no);
            updateAll('[data-abstain]', data.abstain);
            updateAll('[data-ballots]', data.ballots);
            updateAll('[data-eligible]', data.eligible);
            updateAll('[data-voted]', data.ballots);
            updateAll('[data-pending]', data.pending);
            updateAll('[data-percentage]', data.percentage + '%');

            const progressBar = panel.querySelector('[data-progress-bar]');

            if (progressBar) {
                progressBar.style.width = data.percentage + '%';
                progressBar.setAttribute('aria-valuenow', data.percentage);
            }

            const updated = panel.querySelector('[data-last-updated]');
            if (updated) {
                updated.textContent = 'Last updated: ' + new Date().toLocaleTimeString();
            }

            // Update roll-call member rows.
            if (Array.isArray(data.members)) {
                const tbody = card.querySelector('[data-member-voting-status]');

                if (tbody) {
                    data.members.forEach(member => {
                        const row = tbody.querySelector(
                            `[data-member-id="${member.id}"]`
                        );

                        if (!row) return;

                        const voteCell = row.querySelector('[data-member-vote]');
                        const timeCell = row.querySelector('[data-member-time]');

                        if (voteCell) {
                            if (member.has_voted) {
                                const badgeClass = member.vote === 'yes'
                                    ? 'success'
                                    : member.vote === 'no'
                                        ? 'danger'
                                        : 'secondary';

                                voteCell.innerHTML =
                                    `<span class="badge text-bg-${badgeClass}">` +
                                    `${member.vote.charAt(0).toUpperCase() + member.vote.slice(1)}` +
                                    `</span>`;
                            } else {
                                voteCell.innerHTML =
                                    '<span class="text-muted">Not yet voted</span>';
                            }
                        }

                        if (timeCell) {
                            timeCell.textContent = member.cast_at || '—';
                        }
                    });
                }
            }

            // Stop polling once voting is no longer open.
            if (data.status !== 'open') {
                panel.dataset.polling = 'stopped';
            }

        } catch (error) {
            console.error('Unable to refresh voting progress:', error);
        }
    }

    progressPanels.forEach(panel => {
        refreshVoteProgress(panel);

        setInterval(() => {
            if (panel.dataset.polling === 'stopped') return;
            refreshVoteProgress(panel);
        }, 5000);
    });

    const selectAll = document.getElementById('selectAllMembers');

    if (!selectAll) return;

    const memberCheckboxes = document.querySelectorAll(
        '.eligible-member-checkbox'
    );

    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll(
            '.eligible-member-checkbox:checked'
        ).length;

        selectAll.checked =
            memberCheckboxes.length > 0 &&
            checkedCount === memberCheckboxes.length;

        selectAll.indeterminate =
            checkedCount > 0 &&
            checkedCount < memberCheckboxes.length;
    }

    selectAll.addEventListener('change', function () {
        memberCheckboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });

        updateSelectAllState();
    });

    memberCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSelectAllState);
    });

    // Restore the correct state after validation errors.
    updateSelectAllState();

    const modal = document.getElementById('openVotingModal');

    const confirmButton = document.getElementById('confirmOpenVoting');

    const motionTitle = document.getElementById('motion_title');

    const votingMethod = document.getElementById('voting_method');

    const titleDisplay = document.getElementById('confirmMotionTitle');

    const methodDisplay = document.getElementById('confirmVotingMethod');

    const form = modal?.closest('.card')?.querySelector('form')

        ?? document.querySelector(

            'form[action="{{ route('secretary.votes.open', $session->id) }}"]'

        );

    if (!modal || !confirmButton || !form) return;

    modal.addEventListener('show.bs.modal', function () {

        titleDisplay.textContent = motionTitle.value.trim() || 'Not entered';

        methodDisplay.textContent =

            votingMethod.options[votingMethod.selectedIndex].text;

    });

    confirmButton.addEventListener('click', function () {

        if (!form.reportValidity()) return;

        confirmButton.disabled = true;

        confirmButton.innerHTML =

            '<span class="spinner-border spinner-border-sm me-1"></span> Opening Voting...';

        form.requestSubmit();

    });

});

</script>

@endsection
