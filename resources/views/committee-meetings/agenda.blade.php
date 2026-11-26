
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Meeting Agenda</h3>
            <p class="text-muted mb-0">
                {{ $meeting->committee->name ?? 'Committee' }}
                &mdash; {{ $meeting->title }}
            </p>
        </div>

        <a href="{{ route('secretary.committee-meetings.show', $meeting->id) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Meeting
        </a>
    </div>

    {{-- Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $isLocked = in_array($meeting->status, ['completed', 'cancelled']);

        $presentCount = $meeting->attendances
            ->whereIn('attendance_status', ['present', 'late'])
            ->count();

        $requiredQuorum = (int) $meeting->quorum_required;

        $quorumReached = $requiredQuorum > 0
            && $presentCount >= $requiredQuorum;

        $canDeliberate = $meeting->status === 'ongoing'
            && $quorumReached
            && !$isLocked;

        $statusColors = [
            'scheduled' => 'primary',
            'ongoing' => 'success',
            'completed' => 'secondary',
            'cancelled' => 'danger',
        ];
    @endphp

    {{-- Meeting Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Meeting Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

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
                            : 'Not specified' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Venue</small>
                    <div class="fw-semibold">
                        {{ $meeting->venue ?? 'Not specified' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Meeting Status</small>
                    <div>
                        <span class="badge bg-{{ $statusColors[$meeting->status] ?? 'secondary' }}">
                            {{ ucfirst($meeting->status) }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Meeting Quorum Status --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <div class="row align-items-center g-3">

            <div class="col-md-6">
                <small class="text-muted fw-semibold">
                    MEETING QUORUM
                </small>

                <h5 class="fw-bold mb-1">
                    @if($quorumReached)
                        <span class="text-success">Quorum Reached</span>
                    @else
                        <span class="text-warning">Waiting for Quorum</span>
                    @endif
                </h5>

                <p class="text-muted mb-0">
                    {{ $presentCount }} of {{ $requiredQuorum }}
                    required members are present or late.
                </p>
            </div>

            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2">
                    <small>Attendance Progress</small>
                    <strong>{{ $presentCount }} / {{ $requiredQuorum }}</strong>
                </div>

                <div class="progress" style="height: 10px;">
                    <div class="progress-bar {{ $quorumReached ? 'bg-success' : 'bg-warning' }}"
                         role="progressbar"
                         style="width: {{ $requiredQuorum > 0 ? min(100, ($presentCount / $requiredQuorum) * 100) : 0 }}%">
                    </div>
                </div>
            </div>

        </div>

        @if($meeting->status !== 'ongoing')
            <div class="alert alert-info mt-3 mb-0">
                The meeting must be started before official deliberation can begin.
            </div>
        @elseif(!$quorumReached)
            <div class="alert alert-warning mt-3 mb-0">
                Quorum has not been reached. Official deliberation is currently blocked.
                Record or update attendance to proceed.
            </div>
        @else
            <div class="alert alert-success mt-3 mb-0">
                Quorum is satisfied. Official deliberation is permitted.
            </div>
        @endif

    </div>
</div>

    @if($isLocked)
        <div class="alert alert-warning">
            <i class="fas fa-lock me-1"></i>
            This meeting is {{ $meeting->status }}.
            Agenda modifications are disabled.
        </div>
    @endif

    {{-- Current Meeting Agenda --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Current Meeting Agenda</h5>
                <small class="text-muted">
                    Ordered list of documents scheduled for deliberation
                </small>
            </div>

            <span class="badge bg-primary">
                {{ $meeting->agendaItems->count() }} Items
            </span>
        </div>

        <div class="card-body">

            @forelse($meeting->agendaItems as $index => $item)

                @php
                    $referral = $item->referral;
                    $document = $referral?->sessionDocument?->document;

                    $itemStatusColors = [
                        'pending' => 'secondary',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'deferred' => 'warning',
                        'returned_for_revision' => 'danger',
                        'for_further_study' => 'info',
                    ];

                    $currentIds = $meeting->agendaItems
                        ->pluck('id')
                        ->values()
                        ->all();
                @endphp

                <div class="border rounded p-3 mb-3">

                    <div class="d-flex justify-content-between align-items-start">

                        <div class="d-flex gap-3">

                            <div class="bg-light rounded text-center p-3"
                                 style="min-width:65px;">
                                <small class="text-muted d-block">ITEM</small>
                                <h4 class="mb-0">
                                    {{ str_pad($item->agenda_order, 2, '0', STR_PAD_LEFT) }}
                                </h4>
                            </div>

                            <div>
                                <h6 class="fw-bold mb-1">
                                    {{ $document?->title ?? 'Document unavailable' }}
                                </h6>

                                <div class="small text-muted mb-2">
                                    Referral ID: #{{ $referral?->id ?? $item->committee_referral_id }}
                                </div>

                                @if($referral?->referral_remarks)
                                    <p class="small mb-2">
                                        {{ $referral->referral_remarks }}
                                    </p>
                                @endif

                                @if($document)
                                    <a href="{{ route('secretary.committee-referrals.show', $referral->id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-alt me-1"></i>
                                        View Document Details
                                    </a>
                                @endif
                            </div>

                        </div>

                        <span class="badge bg-{{ $itemStatusColors[$item->status] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                        </span>

                    </div>


                    {{-- Deliberation History --}}
                    <div class="mt-3">

                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-clipboard-list me-1"></i>
                            Deliberation History
                            <span class="badge bg-secondary">
                                {{ $item->actions->count() }}
                            </span>
                        </h6>

                        @forelse($item->actions->sortBy('acted_at') as $action)

                            <div class="border-start border-3 border-primary ps-3 mb-3">

                                <div class="d-flex justify-content-between">
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
                                        <small class="text-muted fw-semibold">Reason</small>
                                        <p class="mb-1">{{ $action->reason }}</p>
                                    </div>
                                @endif

                                @if($action->recommendation)
                                    <div class="mt-2">
                                        <small class="text-muted fw-semibold">Recommendation</small>
                                        <p class="mb-1">{{ $action->recommendation }}</p>
                                    </div>
                                @endif

                            </div>

                        @empty

                            <p class="text-muted small mb-3">
                                No deliberation records yet.
                            </p>

                        @endforelse

                    </div>

                   
                    
                    {{-- Deliberation Controls --}}

                    @if(!$isLocked)

                        <div class="border rounded p-3 mt-3 bg-light">

                            @if($item->status === 'pending')

                                <h6 class="fw-bold mb-2">
                                    <i class="fas fa-play-circle me-1"></i>
                                    Ready for Deliberation
                                </h6>

                                <p class="text-muted small">
                                    Start discussing this agenda item with the committee.
                                </p>

                                @if($canDeliberate)

                                    <form method="POST"
                                        action="{{ route(
                                            'secretary.committee-meetings.deliberations.start',
                                            [$meeting->id, $item->id]
                                        ) }}">
                                        @csrf

                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-play me-1"></i>
                                            Start Deliberation
                                        </button>
                                    </form>

                                @else

                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-lock me-1"></i>

                                        @if($meeting->status !== 'ongoing')
                                            Start the meeting before beginning deliberation.
                                        @else
                                            Waiting for the required quorum before deliberation can begin.
                                        @endif
                                    </div>

                                @endif

                            @elseif($item->status === 'in_progress')

                                @if($canDeliberate)

                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-edit me-1"></i>
                                    Record Deliberation
                                </h6>

                                <form method="POST"
                                    action="{{ route(
                                        'secretary.committee-meetings.deliberations.store',
                                        [$meeting->id, $item->id]
                                    ) }}">

                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Committee Action <span class="text-danger">*</span>
                                        </label>

                                        <select name="action" class="form-select" required>
                                            <option value="">Select committee action</option>
                                            <option value="approved">Approved</option>
                                            <option value="approved_with_amendments">Approved with Amendments</option>
                                            <option value="returned_for_revision">Returned for Revision</option>
                                            <option value="deferred">Deferred</option>
                                            <option value="not_recommended">Not Recommended</option>
                                            <option value="for_further_study">For Further Study</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Reason / Discussion Notes
                                        </label>

                                        <textarea name="reason"
                                                class="form-control"
                                                rows="3"
                                                maxlength="10000"
                                                placeholder="Record the committee discussion and reasons..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Committee Recommendation
                                        </label>

                                        <textarea name="recommendation"
                                                class="form-control"
                                                rows="3"
                                                maxlength="10000"
                                                placeholder="Enter the committee recommendation..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-save me-1"></i>
                                        Save Discussion Record
                                    </button>

                                </form>

                                <hr>

                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-flag-checkered me-1"></i>
                                    Finalize Agenda Item
                                </h6>

                                <form method="POST"
                                    action="{{ route(
                                        'secretary.committee-meetings.deliberations.finalize',
                                        [$meeting->id, $item->id]
                                    ) }}"
                                    onsubmit="return confirm('Finalize this agenda item? This will record its final disposition.');">

                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Final Disposition <span class="text-danger">*</span>
                                        </label>

                                        <select name="action" class="form-select" required>
                                            <option value="">Select final disposition</option>
                                            <option value="approved">Approved</option>
                                            <option value="approved_with_amendments">Approved with Amendments</option>
                                            <option value="returned_for_revision">Returned for Revision</option>
                                            <option value="deferred">Deferred</option>
                                            <option value="not_recommended">Not Recommended</option>
                                            <option value="for_further_study">For Further Study</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Final Reason / Remarks
                                        </label>

                                        <textarea name="reason"
                                                class="form-control"
                                                rows="3"
                                                maxlength="10000"
                                                placeholder="Enter the final decision and justification..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Final Recommendation
                                        </label>

                                        <textarea name="recommendation"
                                                class="form-control"
                                                rows="3"
                                                maxlength="10000"
                                                placeholder="Enter the final committee recommendation..."></textarea>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Confirm Final Disposition
                                        </button>
                                    </div>

                                </form>

                                @else

                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-pause-circle me-1"></i>

                                        <strong>Deliberation Suspended</strong>

                                        <p class="mb-0 mt-1">
                                            Quorum is no longer satisfied or the meeting is not ongoing.
                                            Previously recorded deliberation history is preserved.
                                            Restore quorum before recording or finalizing further actions.
                                        </p>
                                    </div>

                                @endif

                            @else

                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-lock me-1"></i>
                                    This agenda item has a final disposition:
                                    <strong>
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </strong>
                                </div>

                            @endif

                        </div>

                    @endif



                    @unless($isLocked)
                        <hr>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">

                            {{-- Move Up --}}
                            @if($index > 0)
                                @php
                                    $upIds = $currentIds;
                                    [$upIds[$index - 1], $upIds[$index]] =
                                        [$upIds[$index], $upIds[$index - 1]];
                                @endphp

                                <form method="POST"
                                      action="{{ route('secretary.committee-meetings.agenda.reorder', $meeting->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    @foreach($upIds as $agendaId)
                                        <input type="hidden"
                                               name="agenda_ids[]"
                                               value="{{ $agendaId }}">
                                    @endforeach

                                    <button type="submit"
                                            class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-arrow-up me-1"></i>
                                        Move Up
                                    </button>
                                </form>
                            @endif

                            {{-- Move Down --}}
                            @if($index < count($currentIds) - 1)
                                @php
                                    $downIds = $currentIds;
                                    [$downIds[$index], $downIds[$index + 1]] =
                                        [$downIds[$index + 1], $downIds[$index]];
                                @endphp

                                <form method="POST"
                                      action="{{ route('secretary.committee-meetings.agenda.reorder', $meeting->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    @foreach($downIds as $agendaId)
                                        <input type="hidden"
                                               name="agenda_ids[]"
                                               value="{{ $agendaId }}">
                                    @endforeach

                                    <button type="submit"
                                            class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-arrow-down me-1"></i>
                                        Move Down
                                    </button>
                                </form>
                            @endif

                            {{-- Remove --}}
                            @if($item->status === 'pending')
                                <form method="POST"
                                      action="{{ route('secretary.committee-meetings.agenda.remove', [$meeting->id, $item->id]) }}"
                                      onsubmit="return confirm('Remove this referral from the meeting agenda?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>
                                        Remove
                                    </button>
                                </form>
                            @endif

                        </div>
                    @endunless

                </div>

            @empty

                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5>No Agenda Items Yet</h5>
                    <p class="text-muted mb-0">
                        Select available committee referrals below to create the meeting agenda.
                    </p>
                </div>

            @endforelse

        </div>
    </div>

    {{-- Available Referrals --}}
    @unless($isLocked)

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Available Referrals</h5>
                    <small class="text-muted">
                        Documents referred to this committee
                    </small>
                </div>

                <span class="badge bg-secondary">
                    {{ $availableReferrals->count() }} Available
                </span>
            </div>

            <div class="card-body">

                @forelse($availableReferrals as $referral)

                    @php
                        $document = $referral->sessionDocument?->document;
                    @endphp

                    <div class="border rounded p-3 mb-3">

                        <div class="row align-items-center g-3">

                            <div class="col-md-8">

                                <h6 class="fw-bold mb-1">
                                    {{ $document?->title ?? 'Document unavailable' }}
                                </h6>

                                <div class="small text-muted mb-2">
                                    Referral #{{ $referral->id }}
                                </div>

                                @if($referral->referral_remarks)
                                    <p class="small text-muted mb-2">
                                        {{ $referral->referral_remarks }}
                                    </p>
                                @endif

                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                                    </span>

                                    @if($referral->referred_at)
                                        <small class="text-muted">
                                            Referred:
                                            {{ $referral->referred_at->format('M d, Y') }}
                                        </small>
                                    @endif
                                </div>

                            </div>

                            <div class="col-md-4 text-md-end">

                                <a href="{{ route('secretary.committee-referrals.show', $referral->id) }}"
                                   class="btn btn-outline-primary btn-sm mb-2">
                                    <i class="fas fa-eye me-1"></i>
                                    View Details
                                </a>

                                <form method="POST"
                                      action="{{ route('secretary.committee-meetings.agenda.add', $meeting->id) }}">
                                    @csrf

                                    <input type="hidden"
                                           name="committee_referral_id"
                                           value="{{ $referral->id }}">

                                    <button type="submit"
                                            class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>
                                        Add to Agenda
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h6>No Available Referrals</h6>
                        <p class="text-muted mb-0">
                            There are currently no eligible referrals
                            available for this committee meeting.
                        </p>
                    </div>

                @endforelse

            </div>
        </div>

    @endunless

</div>
@endsection
