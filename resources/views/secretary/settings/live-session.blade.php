<!-- resources/views/settings/live-session.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="row justify-content-center">
        <div class="col-lg-6">

            <!-- Page Header -->
            <div class="mb-3">
                <h4 class="mb-1">Live Session Settings</h4>
                <p class="text-muted mb-0">
                    Update the Facebook or YouTube live stream link used in the platform.
                </p>
            </div>

            <!-- Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.live.update') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Facebook / YouTube Live URL
                            </label>

                            <input type="text"
                                   name="live_session_url"
                                   class="form-control @error('live_session_url') is-invalid @enderror"
                                   placeholder="https://facebook.com/live or https://youtube.com/live"
                                   value="{{ old('live_session_url', $url) }}">

                            <small class="text-muted">
                                Paste the public live stream URL that users will access.
                            </small>

                            @error('live_session_url')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary px-4">
                                Save Settings
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection