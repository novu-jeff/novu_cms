@extends('layouts.app')

@section('content')

<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Edit Session Meetings</h2>
            <p class="text-muted mb-0">Manage Session Meetings documents in this module</p>
        </div>

        
    </div>



<form method="POST" action="{{ route('session-meetings.update', $session_meeting->id) }}">
@csrf
@method('PUT')

<div class="row">

    <div class="col-md-4">
        <input type="text"
               name="title"
               class="form-control"
               placeholder="Session Title"
               value="{{ old('title', $session_meeting->title) }}"
               required>
    </div>

    <div class="col-md-4">
        <input type="date"
               name="session_date"
               class="form-control"
               value="{{ old('session_date', $session_meeting->session_date) }}"
               required>
    </div>

    <div class="col-md-3">
        <select name="status" class="form-control" required>
            <option value="upcoming" {{ old('status', $session_meeting->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
            <option value="ongoing" {{ old('status', $session_meeting->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
            <option value="completed" {{ old('status', $session_meeting->status) === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ old('status', $session_meeting->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
           
        </select>
    </div>

    <div class="col-md-1">
        <button class="btn btn-primary">
            Update
        </button>
    </div>

</div>

</form>

<hr>

<a href="{{ route('session-meetings.index') }}" class="btn btn-secondary btn-sm">
    Back to Sessions
</a>

</div>

@endsection

