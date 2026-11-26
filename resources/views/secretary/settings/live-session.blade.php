{{-- resources/views/settings/live-session.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4 py-lg-5 live-session-settings">
    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-8">
            <header class="live-settings-header mb-4">
                <div class="live-settings-eyebrow">
                    <i class="fas fa-sliders-h me-2" aria-hidden="true"></i>Platform Settings
                </div>
                <h1 class="live-settings-title mb-2">Live Session Settings</h1>
                <p class="live-settings-subtitle mb-0">
                    Configure the Facebook or YouTube livestream link displayed to users.
                </p>
            </header>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show live-settings-alert" role="alert">
                    <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss notification"></button>
                </div>
            @endif

            <section class="card live-settings-card">
                <div class="card-body p-4 p-lg-5">
                    <div class="live-settings-card-heading mb-4">
                        <div class="live-settings-icon" aria-hidden="true">
                            <i class="fas fa-video"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-1">Livestream URL</h2>
                            <p class="text-muted small mb-0">Set the public link for the current live session.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.live.update') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="live_session_url" class="form-label live-settings-label">
                                Facebook / YouTube Live URL
                            </label>
                            <div class="live-url-input-wrap">
                                <span class="live-url-prefix" aria-hidden="true">
                                    <i class="fas fa-link"></i>
                                </span>
                                <input
                                    id="live_session_url"
                                    type="url"
                                    name="live_session_url"
                                    class="form-control live-url-input @error('live_session_url') is-invalid @enderror"
                                    placeholder="https://youtube.com/live/…"
                                    value="{{ old('live_session_url', $url) }}"
                                    aria-describedby="live-url-help @error('live_session_url') live-url-error @enderror"
                                    autocomplete="url"
                                >
                            </div>
                            <div id="live-url-help" class="form-text mt-2">
                                Paste the public livestream URL that users should open. Make sure the stream's visibility settings allow your intended audience to access it.
                            </div>
                            @error('live_session_url')
                                <div id="live-url-error" class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="live-settings-guidance mb-4">
                            <div class="live-settings-guidance-icon" aria-hidden="true">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <strong>Before saving</strong>
                                <p class="mb-0 small">Use the share link from Facebook Live or YouTube Live. Avoid links to private streams that your audience cannot access.</p>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 live-settings-actions">
                            <button type="submit" class="btn btn-primary live-settings-save">
                                <i class="fas fa-save me-2" aria-hidden="true"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <p class="live-settings-footnote mt-3 mb-0">
                <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                Changes apply wherever the platform uses the configured live session URL.
            </p>
        </div>
    </div>
</div>
@endsection


<style>
    .live-session-settings {
        --live-ink: #172033;
        --live-muted: #667085;
        --live-line: #e5eaf1;
        color: var(--live-ink);
    }
    .live-settings-header { padding: .25rem .1rem 0; }
    .live-settings-eyebrow {
        color: #5268a5;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .09em;
        text-transform: uppercase;
        margin-bottom: .7rem;
    }
    .live-settings-title {
        color: var(--live-ink);
        font-size: clamp(1.65rem, 3vw, 2.15rem);
        font-weight: 750;
        letter-spacing: -.035em;
    }
    .live-settings-subtitle { color: var(--live-muted); line-height: 1.65; }
    .live-settings-alert {
        border: 1px solid #b9e7cd;
        border-radius: .85rem;
        box-shadow: 0 4px 14px rgba(16, 24, 40, .04);
    }
    .live-settings-card {
        border: 1px solid var(--live-line);
        border-radius: 1.15rem;
        box-shadow: 0 12px 35px rgba(16, 24, 40, .055);
        overflow: hidden;
    }
    .live-settings-card-heading { display: flex; align-items: center; gap: 1rem; }
    .live-settings-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 3.1rem;
        width: 3.1rem;
        height: 3.1rem;
        border-radius: 1rem;
        background: #edf2ff;
        color: #4358a5;
        font-size: 1.2rem;
    }
    .live-settings-label { color: #344054; font-weight: 700; }
    .live-url-input-wrap { position: relative; }
    .live-url-prefix {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        color: #98a2b3;
        pointer-events: none;
    }
    .live-url-input {
        min-height: 3.2rem;
        padding-left: 2.8rem;
        border-color: #d0d5dd;
        border-radius: .75rem;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .live-url-input:focus {
        border-color: #7185ce;
        box-shadow: 0 0 0 .22rem rgba(82, 104, 165, .13);
    }
    .live-settings-guidance {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        padding: 1rem;
        border: 1px solid #e3eafc;
        border-radius: .85rem;
        background: #f7f9ff;
        color: #344054;
    }
    .live-settings-guidance-icon { color: #5268a5; padding-top: .1rem; }
    .live-settings-guidance p { color: #667085; line-height: 1.55; margin-top: .25rem; }
    .live-settings-save {
        min-height: 2.85rem;
        border-radius: .7rem;
        padding: .7rem 1.25rem;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(13, 110, 253, .12);
    }
    .live-settings-footnote { color: var(--live-muted); font-size: .82rem; line-height: 1.5; }
    @media (max-width: 575.98px) {
        .live-session-settings { padding-left: 1rem; padding-right: 1rem; }
        .live-settings-card .card-body { padding: 1.25rem !important; }
        .live-settings-save { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .live-url-input { transition: none; }
    }
</style>

