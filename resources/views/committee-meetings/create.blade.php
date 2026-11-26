@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Schedule Committee Meeting</h3>
            <p class="text-muted mb-0">
                Create a formal meeting for a standing committee.
            </p>
        </div>

        <a href="{{ route('secretary.committee-meetings.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form action="{{ route('secretary.committee-meetings.store') }}"
                  method="POST">
                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Standing Committee <span class="text-danger">*</span>
                        </label>

                        <select name="committee_id"
                                class="form-select @error('committee_id') is-invalid @enderror"
                                required>
                            <option value="">Select Committee</option>

                            @foreach($committees as $committee)
                                <option value="{{ $committee->id }}"
                                    {{ old('committee_id') == $committee->id ? 'selected' : '' }}>
                                    {{ $committee->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('committee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Meeting Title <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}"
                               placeholder="e.g. Committee Deliberation on Referred Ordinance"
                               required>

                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Meeting Date <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="meeting_date"
                               class="form-control @error('meeting_date') is-invalid @enderror"
                               value="{{ old('meeting_date') }}"
                               required>

                        @error('meeting_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>

                        <input type="time"
                               name="start_time"
                               class="form-control @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time') }}">

                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Time</label>

                        <input type="time"
                               name="end_time"
                               class="form-control @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time') }}">

                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Venue</label>

                        <input type="text"
                               name="venue"
                               class="form-control @error('venue') is-invalid @enderror"
                               value="{{ old('venue') }}"
                               placeholder="Meeting location">

                        @error('venue')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Presiding Officer</label>

                        <input type="text"
                               name="presiding_officer"
                               class="form-control @error('presiding_officer') is-invalid @enderror"
                               value="{{ old('presiding_officer') }}"
                               placeholder="Committee Chairperson">

                        @error('presiding_officer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Required Quorum</label>

                        <input type="number"
                               name="quorum_required"
                               min="1"
                               class="form-control @error('quorum_required') is-invalid @enderror"
                               value="{{ old('quorum_required') }}"
                               placeholder="Number of members">

                        @error('quorum_required')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted">
                            Minimum number of members required to proceed.
                        </small>
                    </div>

                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('secretary.committee-meetings.index') }}"
                       class="btn btn-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check me-1"></i>
                        Schedule Meeting
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection