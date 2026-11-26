@extends('layouts.app')

@section('content')
<div class="container-fluid presiding-review-page py-4 py-lg-5">
    <div class="review-page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="review-eyebrow"><i class="fas fa-clipboard-check me-2"></i>Legislative Session Management</div>
            <h1 class="review-page-title mb-2">Presiding Officer Review Results</h1>
            <p class="review-page-subtitle mb-0">Select a reviewed session to open its existing minutes details and read all Presiding Officer comments.</p>
        </div>
        <div class="review-summary-chip">
            <i class="fas fa-folder-open me-2"></i>
            {{ $sessions->count() }} {{ \Illuminate\Support\Str::plural('session', $sessions->count()) }}
        </div>
    </div>

    @forelse ($sessions as $session)
        <article class="card review-session-card mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="review-session-label">Session minutes</span>
                            @if ($session->minutes_status === 'reviewed')
                                <span class="badge review-status-badge status-reviewed"><i class="fas fa-check-circle me-1"></i>Reviewed</span>
                            @elseif ($session->minutes_status === 'revision_required')
                                <span class="badge review-status-badge status-revision"><i class="fas fa-rotate-left me-1"></i>Returned for Corrections</span>
                            @elseif ($session->minutes_status === 'finalized')
                                <span class="badge review-status-badge status-finalized"><i class="fas fa-lock me-1"></i>Finalized</span>
                            @else
                                <span class="badge review-status-badge status-other">{{ ucfirst(str_replace('_', ' ', $session->minutes_status ?? 'Unknown')) }}</span>
                            @endif
                        </div>
                        <h2 class="review-session-title mb-2">{{ $session->title }}</h2>
                        <div class="review-session-date text-muted">
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $session->session_date?->format('F d, Y') ?? 'Date not set' }}
                        </div>
                    </div>

                    
                    <div class="review-primary-actions d-flex flex-wrap align-items-start gap-2">

                        @if ($session->minutes_status === 'reviewed')

                            @if (
                                $session->signingRequest &&
                                in_array($session->signingRequest->status, ['pending', 'partially_signed'])
                            )

                                <a href="{{ route('document-signing.show', $session->signingRequest->id) }}"
                                class="btn btn-warning">
                                    <i class="fas fa-file-signature me-2"></i>
                                    View Signing Progress
                                </a>

                            @else

                                <button type="button"
                                        class="btn btn-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#finalizeMinutesModal{{ $session->id }}">
                                    <i class="fas fa-file-signature me-2"></i>
                                    Submit for Signing
                                </button>

                            @endif

                        @elseif ($session->minutes_status === 'finalized')

                            <a href="{{ route('secretary.minutes.generate', $session->id) }}"
                            class="btn btn-primary">
                                <i class="fas fa-file-pdf me-2"></i>
                                Download Signed Official PDF
                            </a>

                        @endif

                        <a href="{{ route('secretary.minutes.review', $session->id) }}"
                        class="btn btn-outline-primary">
                            <i class="fas fa-file-circle-check me-2"></i>
                            View Review Details
                        </a>

                    </div>

                </div>

                <div class="review-session-footer mt-4 pt-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Select “View Review Details” to read the Presiding Officer’s comments and full review history.
                    </div>
                    <div class="review-meta text-muted small">
                        @if ($session->minutes_reviewed_at)
                            <i class="far fa-clock me-1"></i>
                            Last reviewed: {{ \Carbon\Carbon::parse($session->minutes_reviewed_at)->format('M d, Y h:i A') }}
                        @elseif ($session->minuteReviews->isNotEmpty())
                            <i class="far fa-clock me-1"></i>
                            Latest review activity: {{ $session->minuteReviews->first()->created_at?->format('M d, Y h:i A') ?? 'Date unavailable' }}
                        @endif
                    </div>
                </div>
            </div>
        </article>

        @if ($session->minutes_status === 'reviewed')
            <div class="modal fade" id="finalizeMinutesModal{{ $session->id }}" tabindex="-1" aria-labelledby="finalizeMinutesModalLabel{{ $session->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content review-finalize-modal">
                        <div class="modal-header">
                            <div>
                                <div class="review-eyebrow mb-1">Confirmation required</div>
                                <h5 class="modal-title" id="finalizeMinutesModalLabel{{ $session->id }}">
                                    <i class="fas fa-file-signature text-success me-2"></i>
                                    Submit Minutes for Electronic Signing
                                </h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                Submit the reviewed minutes for
                                <strong>{{ $session->title }}</strong>
                                to the electronic signing workflow?
                            </p>

                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                The Secretary will sign first, followed by the Presiding Officer.
                                The minutes will only be finalized after both signers have completed signing.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <form method="POST" action="{{ route('secretary.minutes.finalize', $session->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success"><i class="fas fa-file-signature me-2"></i>
    Submit for Signing</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @empty
        <div class="review-empty-state text-center py-5 px-3">
            <div class="review-empty-icon"><i class="fas fa-folder-open"></i></div>
            <h2 class="h5 mb-2">No review results available</h2>
            <p class="text-muted mb-0">Sessions will appear here after the Presiding Officer has reviewed, returned, or finalized their minutes.</p>
        </div>
    @endforelse
</div>
@endsection


<style>
    .presiding-review-page { --review-ink: #182230; --review-muted: #667085; --review-line: #e7ebf0; color: var(--review-ink); }
    .presiding-review-page .review-page-header { padding: .25rem .15rem .75rem; }
    .presiding-review-page .review-eyebrow { color: #5268a5; font-size: .73rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
    .presiding-review-page .review-page-title { color: var(--review-ink); font-size: clamp(1.5rem, 2vw, 2rem); font-weight: 750; letter-spacing: -.035em; }
    .presiding-review-page .review-page-subtitle { color: var(--review-muted); }
    .presiding-review-page .review-summary-chip { background: #fff; border: 1px solid var(--review-line); border-radius: 999px; color: #475467; font-size: .88rem; font-weight: 650; padding: .65rem 1rem; white-space: nowrap; }
    .presiding-review-page .review-session-card { border: 1px solid var(--review-line); border-radius: 1rem; box-shadow: 0 8px 28px rgba(16, 24, 40, .045); overflow: hidden; }
    .presiding-review-page .review-session-label { color: #667085; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .presiding-review-page .review-session-title { font-size: 1.2rem; font-weight: 750; letter-spacing: -.02em; overflow-wrap: anywhere; }
    .presiding-review-page .review-session-date { font-size: .9rem; }
    .presiding-review-page .review-status-badge { border-radius: 999px; font-size: .75rem; font-weight: 700; padding: .45rem .7rem; }
    .presiding-review-page .status-reviewed, .presiding-review-page .status-finalized { background: #e8f7ee; color: #167647; }
    .presiding-review-page .status-revision { background: #fff2d6; color: #8a5200; }
    .presiding-review-page .status-other { background: #eef1f5; color: #475467; }
    .presiding-review-page .review-primary-actions .btn { border-radius: .65rem; font-weight: 650; padding: .6rem .85rem; }
    .presiding-review-page .review-section-heading { border-bottom: 1px solid var(--review-line); padding-bottom: .85rem; }
    .presiding-review-page .review-section-heading h3 { font-weight: 750; }
    .presiding-review-page .review-count { background: #f2f4f7; border-radius: 999px; color: #475467; font-size: .75rem; font-weight: 700; padding: .35rem .65rem; white-space: nowrap; }
    .presiding-review-page .review-history-item { align-items: flex-start; display: flex; gap: .9rem; padding: 1rem 0; }
    .presiding-review-page .review-history-item + .review-history-item { border-top: 1px solid #f0f2f5; }
    .presiding-review-page .review-history-icon { align-items: center; background: #edf2ff; border-radius: .75rem; color: #4257a5; display: flex; flex: 0 0 2.4rem; height: 2.4rem; justify-content: center; }
    .presiding-review-page .review-history-content { flex: 1; min-width: 0; }
    .presiding-review-page .review-action-title { color: #344054; }
    .presiding-review-page .review-timestamp { color: var(--review-muted); font-size: .78rem; }
    .presiding-review-page .review-comment { color: #475467; line-height: 1.65; overflow-wrap: anywhere; white-space: pre-line; }
    .presiding-review-page .review-empty-history, .presiding-review-page .review-empty-state { background: #fafbfc; border: 1px dashed #d7dde5; border-radius: .85rem; }
    .presiding-review-page .review-empty-icon { align-items: center; background: #eef2f6; border-radius: 1rem; color: #667085; display: inline-flex; font-size: 1.2rem; height: 3.25rem; justify-content: center; margin-bottom: 1rem; width: 3.25rem; }
    .review-finalize-modal { border: 0; border-radius: 1rem; overflow: hidden; }
    .review-finalize-modal .modal-header, .review-finalize-modal .modal-footer { border-color: #eef0f3; padding: 1.1rem 1.25rem; }
    .review-finalize-modal .modal-body { padding: 1.25rem; }
    @media (max-width: 767.98px) {
        .presiding-review-page { padding-left: 1rem; padding-right: 1rem; }
        .presiding-review-page .review-primary-actions { width: 100%; }
        .presiding-review-page .review-primary-actions .btn { flex: 1 1 100%; }
        .presiding-review-page .review-history-item { gap: .65rem; }
        .presiding-review-page .review-timestamp { line-height: 1.5; }
    }
</style>

