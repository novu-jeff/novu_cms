
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2>Session Recording & Transcription</h2>
        <h5 class="text-muted">{{ $session->title }}</h5>
        <p>
            Session date:
            {{ $session->session_date?->format('F d, Y') ?? 'Not set' }}
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Upload Meeting Recording
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('sessions.recordings.store', $session) }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="mb-3">
                    <label for="recording" class="form-label">
                        Audio or video recording
                    </label>

                    <input
                        type="file"
                        name="recording"
                        id="recording"
                        class="form-control"
                        accept=".mp3,.wav,.m4a,.mp4,.webm,.mpeg,.mpga,.ogg,.flac"
                        required
                    >

                    <small class="form-text text-muted">
                        Choose a supported recording file. Maximum size depends
                        on the server and transcription provider limits.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Upload & Start Transcription
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Recordings & Transcription Status
        </div>

        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Uploaded</th>
                        <th>Recording Status</th>
                        <th>Transcript Status</th>
                        <th>Transcript</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recordings as $recording)
                        <tr>
                            <td>{{ $recording->original_filename }}</td>

                            <td>
                                {{ $recording->created_at?->format('M d, Y h:i A') }}
                            </td>

                            <td>
                                {{ ucfirst($recording->status) }}
                            </td>

                            <td>
                                {{ ucfirst($recording->transcript?->status ?? 'pending') }}
                            </td>

                            <td>
                                @if (
                                    $recording->transcript?->status === 'completed'
                                    && filled($recording->transcript?->transcript_text)
                                )
                                    <a
                                        href="{{ route('sessions.recordings.transcript', [$session, $recording]) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View Transcript
                                    </a>
                                @elseif ($recording->status === 'failed')
                                    <span class="text-danger">
                                        {{ $recording->error_message ?? 'Processing failed' }}
                                    </span>
                                @else
                                    <span class="text-muted">Processing…</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No recordings uploaded for this session yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
