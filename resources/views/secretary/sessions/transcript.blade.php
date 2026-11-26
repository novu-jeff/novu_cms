@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Recording Transcript</h2>
    <p class="text-muted">{{ $session->title }}</p>

    <div class="card">
        <div class="card-header">
            {{ $recording->original_filename }}
        </div>

        <div class="card-body">
            <pre style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $transcript->transcript_text }}</pre>
        </div>
    </div>

    <a
        href="{{ route('sessions.recordings.index', $session) }}"
        class="btn btn-secondary mt-3"
    >
        Back to Recordings
    </a>
</div>
@endsection