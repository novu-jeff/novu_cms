@extends('layouts.app')

@section('content')
<div class="container-fluid minutes-returned-page py-4 py-lg-5">
    <header class="returned-page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="returned-eyebrow"><i class="fas fa-clipboard-list me-2"></i>Legislative Session Management</div>
            <h1 class="returned-page-title mb-2">Minutes Returned for Corrections</h1>
            <p class="returned-page-subtitle mb-0">Review the presiding officer's feedback and update the session minutes.</p>
        </div>
        <div class="returned-summary">
            <span class="returned-summary-icon"><i class="fas fa-rotate-left"></i></span>
            <span>
                <strong>{{ $sessions->count() }}</strong>
                <small>{{ \Illuminate\Support\Str::plural('session', $sessions->count()) }} requiring corrections</small>
            </span>
        </div>
    </header>

    @forelse ($sessions as $session)
        @php
            $latestReview = $session->minuteReviews->first();
        @endphp

        <article class="card returned-session-card mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <div class="returned-session-main">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="returned-session-label">Session minutes</span>
                            <span class="badge returned-status-badge">
                                <i class="fas fa-rotate-left me-1"></i>Corrections required
                            </span>
                        </div>
                        <h2 class="returned-session-title mb-2">{{ $session->title }}</h2>
                        <div class="returned-session-date">
                            <i class="far fa-calendar-alt me-2"></i>
                            <span>Session date:</span>
                            <strong>{{ $session->session_date?->format('F d, Y') ?? 'Date not set' }}</strong>
                        </div>
                    </div>

                    <a href="{{ route('secretary.minutes.index', $session->id) }}"
                       class="btn btn-primary returned-review-btn">
                        <i class="fas fa-pen-to-square me-2"></i>
                        Review and Correct Minutes
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>

                <div class="returned-feedback mt-4">
                    <div class="returned-feedback-heading">
                        <span class="returned-feedback-icon"><i class="fas fa-comment-dots"></i></span>
                        <div>
                            <h3 class="h6 mb-1">Presiding Officer's Comments</h3>
                            <p class="small mb-0">Feedback to address before resubmitting the minutes</p>
                        </div>
                    </div>

                    @if ($latestReview)
                        <div class="returned-feedback-body">
                            {{ $latestReview->comments ?: 'No comments provided.' }}
                        </div>
                        @if ($latestReview->created_at)
                            <div class="returned-feedback-meta">
                                <i class="far fa-clock me-1"></i>
                                Returned {{ $latestReview->created_at->format('M d, Y \a\t h:i A') }}
                            </div>
                        @endif
                    @else
                        <div class="returned-feedback-body text-muted">
                            No review comments are available for this session.
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="returned-empty-state text-center py-5 px-3">
            <div class="returned-empty-icon"><i class="fas fa-circle-check"></i></div>
            <h2 class="h5 mb-2">No corrections pending</h2>
            <p class="text-muted mb-0">No minutes have been returned for corrections.</p>
        </div>
    @endforelse
</div>
@endsection


<style>
    .minutes-returned-page {
        --returned-ink: #182230;
        --returned-muted: #667085;
        --returned-line: #e6eaf0;
        color: var(--returned-ink);
    }
    .minutes-returned-page .returned-page-header { padding: .25rem .15rem .75rem; }
    .minutes-returned-page .returned-eyebrow {
        color: #5268a5; font-size: .73rem; font-weight: 800;
        letter-spacing: .09em; text-transform: uppercase; margin-bottom: .6rem;
    }
    .minutes-returned-page .returned-page-title {
        color: var(--returned-ink); font-size: clamp(1.5rem, 2.2vw, 2rem);
        font-weight: 750; letter-spacing: -.035em;
    }
    .minutes-returned-page .returned-page-subtitle { color: var(--returned-muted); max-width: 46rem; }
    .minutes-returned-page .returned-summary {
        display: flex; align-items: center; gap: .75rem; background: #fff;
        border: 1px solid var(--returned-line); border-radius: 1rem;
        padding: .8rem 1rem; min-width: 13rem;
    }
    .minutes-returned-page .returned-summary-icon {
        align-items: center; justify-content: center; display: inline-flex;
        width: 2.6rem; height: 2.6rem; border-radius: .8rem;
        color: #975a00; background: #fff2d6; font-size: 1rem;
    }
    .minutes-returned-page .returned-summary strong { display: block; font-size: 1.15rem; line-height: 1.2; }
    .minutes-returned-page .returned-summary small { display: block; color: var(--returned-muted); font-size: .75rem; margin-top: .2rem; }
    .minutes-returned-page .returned-session-card {
        border: 1px solid var(--returned-line); border-radius: 1rem;
        box-shadow: 0 8px 28px rgba(16, 24, 40, .045); overflow: hidden;
        transition: box-shadow .18s ease, transform .18s ease;
    }
    .minutes-returned-page .returned-session-card:hover {
        box-shadow: 0 12px 32px rgba(16, 24, 40, .08); transform: translateY(-1px);
    }
    .minutes-returned-page .returned-session-label {
        color: var(--returned-muted); font-size: .72rem; font-weight: 800;
        letter-spacing: .08em; text-transform: uppercase;
    }
    .minutes-returned-page .returned-status-badge {
        border-radius: 999px; padding: .45rem .7rem; background: #fff2d6;
        color: #8a5200; font-size: .75rem; font-weight: 750;
    }
    .minutes-returned-page .returned-session-title {
        font-size: 1.2rem; font-weight: 750; letter-spacing: -.02em; overflow-wrap: anywhere;
    }
    .minutes-returned-page .returned-session-date { color: var(--returned-muted); font-size: .88rem; }
    .minutes-returned-page .returned-session-date strong { color: #344054; font-weight: 650; }
    .minutes-returned-page .returned-review-btn {
        border-radius: .7rem; padding: .7rem 1rem; font-weight: 650;
        box-shadow: 0 3px 8px rgba(13, 110, 253, .12);
    }
    .minutes-returned-page .returned-feedback {
        border: 1px solid #f0d9a5; border-radius: .9rem; background: #fffbf2; overflow: hidden;
    }
    .minutes-returned-page .returned-feedback-heading {
        display: flex; align-items: center; gap: .75rem; padding: 1rem 1.1rem;
        border-bottom: 1px solid #f5e7c7;
    }
    .minutes-returned-page .returned-feedback-icon {
        display: inline-flex; align-items: center; justify-content: center; flex: 0 0 2.5rem;
        width: 2.5rem; height: 2.5rem; border-radius: .75rem;
        background: #fff0cc; color: #8a5200;
    }
    .minutes-returned-page .returned-feedback-heading h3 { color: #593d08; font-weight: 750; }
    .minutes-returned-page .returned-feedback-heading p { color: #8a6b35; }
    .minutes-returned-page .returned-feedback-body {
        padding: 1rem 1.1rem; color: #593d08; line-height: 1.7;
        white-space: pre-line; overflow-wrap: anywhere;
    }
    .minutes-returned-page .returned-feedback-meta {
        border-top: 1px solid #f5e7c7; padding: .65rem 1.1rem;
        color: #8a6b35; font-size: .78rem;
    }
    .minutes-returned-page .returned-empty-state {
        background: #fafbfc; border: 1px dashed #d7dde5; border-radius: 1rem;
    }
    .minutes-returned-page .returned-empty-icon {
        align-items: center; background: #e8f7ee; border-radius: 1rem;
        color: #167647; display: inline-flex; font-size: 1.25rem;
        height: 3.5rem; justify-content: center; margin-bottom: 1rem; width: 3.5rem;
    }
    @media (max-width: 767.98px) {
        .minutes-returned-page { padding-left: 1rem; padding-right: 1rem; }
        .minutes-returned-page .returned-review-btn { width: 100%; }
        .minutes-returned-page .returned-summary { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .minutes-returned-page .returned-session-card { transition: none; }
        .minutes-returned-page .returned-session-card:hover { transform: none; }
    }
</style>

