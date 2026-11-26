
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-3">Add Committee Report to Future Agenda</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Committee Recommendation</h2>
            <p class="mb-2">{{ $referral->committee->name }}</p>
            <p>{{ $referral->recommendation }}</p>

            <a href="{{ route('secretary.committee-referrals.report', $referral->id) }}"
               class="btn btn-outline-primary">
                Download Committee Report (PDF)
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($sessions->isEmpty())
        <div class="alert alert-warning">
            No upcoming sessions are available. Create a future session first.
        </div>
    @else
        <form method="POST"
              action="{{ route('secretary.committee-referrals.store-in-agenda', $referral->id) }}">
            @csrf

            <div class="mb-3">
                <label for="session_id" class="form-label">Future Session</label>
                <select name="session_id" id="session_id" class="form-select" required>
                    <option value="">Select a session</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}"
                            @selected(old('session_id') == $session->id)>
                            {{ $session->title }} —
                            {{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Add to Agenda
            </button>

            <a href="{{ route('secretary.committee-referrals.show', $referral->id) }}"
               class="btn btn-outline-secondary">
                Cancel
            </a>
        </form>
    @endif
</div>
@endsection
