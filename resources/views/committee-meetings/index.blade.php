@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Committee Meetings</h3>
            <p class="text-muted mb-0">
                Schedule and manage standing committee meetings.
            </p>
        </div>

        <a href="{{ route('secretary.committee-meetings.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Schedule Meeting
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            @if($meetings->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Meeting</th>
                                <th>Committee</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($meetings as $meeting)
                                <tr>
                                    <td>
                                        <strong>{{ $meeting->title }}</strong>
                                    </td>

                                    <td>
                                        {{ $meeting->committee->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $meeting->meeting_date?->format('M d, Y') }}
                                    </td>

                                    <td>
                                        {{ $meeting->start_time
                                            ? \Carbon\Carbon::parse($meeting->start_time)->format('h:i A')
                                            : 'Not set' }}
                                        @if($meeting->end_time)
                                            – {{ \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }}
                                        @endif
                                    </td>

                                    <td>{{ $meeting->venue ?? 'Not specified' }}</td>

                                    <td>
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
                                    </td>
                                    <td>
                                        <a href="{{ route('secretary.committee-meetings.show', $meeting->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View Meeting
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $meetings->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>No Committee Meetings Yet</h5>
                    <p class="text-muted">
                        Schedule a meeting to begin managing committee proceedings.
                    </p>

                    <a href="{{ route('secretary.committee-meetings.create') }}"
                       class="btn btn-primary">
                        Schedule First Meeting
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection