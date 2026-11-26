
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary fw-bold small mb-1">
                Presiding Officer Workspace
            </div>
            <h2 class="fw-bold mb-1">Live Deliberation</h2>
            <p class="text-muted mb-0">
                Sessions assigned to the Office of the Vice Mayor.
            </p>
        </div>

        <div class="text-end">
            <div class="small text-muted">Presiding Officer</div>
            <div class="fw-semibold">{{ $member->name }}</div>
            <span class="badge bg-primary">{{ $member->position }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Active and Upcoming Sessions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">Assigned Sessions</h5>
                <small class="text-muted">Upcoming and active session schedules</small>
            </div>
            <span class="badge bg-primary rounded-pill">
                {{ $activeSessions->count() }}
            </span>
        </div>

        <div class="card-body p-0">
            @forelse($activeSessions as $session)
                <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-bold text-dark">
                            {{ $session->title }}
                        </div>

                        <div class="small text-muted mt-1">
                            <i class="fa-regular fa-calendar me-1"></i>
                            {{ $session->session_date?->format('F d, Y') ?? 'Date not set' }}

                            @if($session->start_time)
                                <span class="mx-2">|</span>
                                <i class="fa-regular fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                            @endif

                            @if($session->venue)
                                <span class="mx-2">|</span>
                                <i class="fa-solid fa-location-dot me-1"></i>
                                {{ $session->venue }}
                            @endif
                        </div>

                        <div class="mt-2">
                            @php
                                $statusClass = match($session->status) {
                                    'ongoing' => 'bg-success',
                                    'suspended' => 'bg-warning text-dark',
                                    'upcoming' => 'bg-secondary',
                                    default => 'bg-light text-dark',
                                };
                            @endphp

                            <span class="badge {{ $statusClass }}">
                                {{ ucfirst($session->status) }}
                            </span>

                            @if($session->session_type)
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst($session->session_type) }} Session
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('presiding-officer.sessions.show', $session->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-display me-1"></i>
                            Open Workspace
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fa-regular fa-calendar-xmark fa-2x text-muted mb-3"></i>
                    <h6 class="fw-bold">No Assigned Sessions</h6>
                    <p class="text-muted small mb-0">
                        There are currently no upcoming or active sessions assigned to your office.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Completed Sessions --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">Completed Sessions</h5>
                <small class="text-muted">Previously assigned sessions</small>
            </div>
            <span class="badge bg-secondary rounded-pill">
                {{ $completedSessions->count() }}
            </span>
        </div>

        <div class="card-body p-0">
            @forelse($completedSessions as $session)
                <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold text-dark">
                            {{ $session->title }}
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="fa-regular fa-calendar me-1"></i>
                            {{ $session->session_date?->format('F d, Y') ?? 'Date not set' }}
                        </div>
                        <span class="badge bg-secondary mt-2">Completed</span>
                    </div>

                    <a href="{{ route('presiding-officer.sessions.show', $session->id) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fa-regular fa-eye me-1"></i>
                        View Session
                    </a>
                </div>
            @empty
                <div class="text-center py-4">
                    <p class="text-muted small mb-0">
                        No completed sessions found.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
