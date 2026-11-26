@extends('layouts.app')

@section('content')
<div class="session-agenda-page container-fluid py-4 py-lg-5">
    <div class="agenda-shell mx-auto">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show agenda-alert" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="agenda-page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="agenda-eyebrow"><i class="fa-solid fa-landmark me-2"></i>Legislative Management</div>
                <h1 class="agenda-title mb-1">Session Agenda</h1>
                <p class="agenda-subtitle mb-0">Review agenda documents and arrange the order of business.</p>
            </div>
            <a href="{{ route('secretary.minutes.index', $session->id) }}" class="btn btn-primary agenda-primary-action">
                <i class="fa-solid fa-file-pen me-2"></i>Encode Minutes
            </a>
        </div>

        <section class="agenda-session-card mb-4" aria-label="Session details">
            <div class="agenda-session-icon"><i class="fa-regular fa-calendar-days"></i></div>
            <div class="agenda-session-info">
                <span class="agenda-label">Current Session</span>
                <h2 class="agenda-session-title mb-1">{{ $session->title }}</h2>
                <div class="agenda-session-date">
                    <i class="fa-regular fa-calendar me-2"></i>
                    {{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
                </div>
            </div>
            <div class="agenda-session-count">
                <span class="agenda-label">Agenda Items</span>
                <strong>{{ $documents->count() + $committeeReports->count() }}</strong>
            </div>
        </section>

        <section class="agenda-list-card">
            <div class="agenda-list-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h2 class="agenda-section-title mb-1">Agenda Items</h2>
                    <p class="text-muted mb-0 small">Drag an item using the handle to change its order.</p>
                </div>
                <span class="agenda-reorder-hint"><i class="fa-solid fa-up-down-left-right me-2"></i>Reorder enabled</span>
            </div>

            @if($documents->isNotEmpty() || $committeeReports->isNotEmpty())
                <ul id="agendaLists" class="agenda-list list-unstyled mb-0">
                    @foreach($documents as $doc)
                        <li class="agenda-item" data-id="{{ $doc->id }}">
                            <div class="agenda-item-number">{{ $doc->agenda_order }}</div>
                            <button type="button" class="agenda-drag-handle" aria-label="Drag to reorder agenda item" title="Drag to reorder">
                                <i class="fa-solid fa-grip-vertical"></i>
                            </button>
                            <div class="agenda-item-content">
                                <h3 class="agenda-document-title">{{ $doc->document->title }}</h3>
                                <div class="agenda-proponent">
                                    <i class="fa-regular fa-user me-1"></i>
                                    Proponent: {{ $doc->document->member->name ?? 'Not specified' }}
                                </div>
                            </div>
                            <a href="{{ config('app.lis_storage_url').'/'.$doc->document->file_path }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn btn-outline-primary agenda-view-button">
                                <i class="fa-regular fa-file-lines me-2"></i><span>View Document</span>
                                <i class="fa-solid fa-arrow-up-right-from-square ms-2 small"></i>
                            </a>
                        </li>
                    @endforeach

                                        @foreach($committeeReports as $reportItem)
                        @php
                            $referral = $reportItem->referral;
                        @endphp

                        @if($referral)
                            <li class="agenda-item" data-type="committee-report">
                                <div class="agenda-item-number">
                                    {{ $reportItem->agenda_order }}
                                </div>

                                <div class="agenda-item-content">
                                    <div class="mb-1">
                                        <span class="badge bg-info text-dark">
                                            Committee Report
                                        </span>
                                    </div>

                                    <h3 class="agenda-document-title">
                                        {{ $referral->committee?->name ?? 'Committee' }}
                                        — Report for Referral #{{ $referral->id }}
                                    </h3>

                                    <div class="agenda-proponent mb-2">
                                        <i class="fa-solid fa-file-signature me-1"></i>
                                        Status:
                                        {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                                    </div>

                                    @if($referral->recommendation)
                                        <p class="mb-2" style="white-space: pre-wrap;">
                                            {{ $referral->recommendation }}
                                        </p>
                                    @endif

                                    @if($referral->report_file_path)
                                        <a
                                            href="{{ route('secretary.committee-referrals.report', $referral->id) }}"
                                            class="btn btn-outline-primary agenda-view-button"
                                        >
                                            <i class="fa-regular fa-file-pdf me-2"></i>
                                            Download Committee Report
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @else
                <div class="agenda-empty-state">
                    <div class="agenda-empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h3>No agenda documents yet</h3>
                    <p class="text-muted mb-0">Documents added to this session will appear here.</p>
                </div>
            @endif
        </section>

        <p class="agenda-footer-note mt-3 mb-0">
            <i class="fa-solid fa-circle-info me-2"></i>Changes to the agenda order are saved automatically after dragging an item.
        </p>
    </div>
</div>
@endsection


<style>
    .session-agenda-page {
        --agenda-ink: #172b4d;
        --agenda-muted: #6b778c;
        --agenda-line: #e8edf4;
        --agenda-primary: #3157d5;
        color: var(--agenda-ink);
    }
    .agenda-shell { max-width: 1120px; }
    .agenda-eyebrow {
        color: var(--agenda-primary); font-size: .76rem; font-weight: 800;
        letter-spacing: .1em; text-transform: uppercase; margin-bottom: .55rem;
    }
    .agenda-title { color: var(--agenda-ink); font-size: clamp(1.65rem, 3vw, 2.15rem); font-weight: 800; letter-spacing: -.035em; }
    .agenda-subtitle { color: var(--agenda-muted); }
    .agenda-primary-action { border-radius: 10px; padding: .78rem 1.1rem; font-weight: 700; box-shadow: 0 5px 14px rgba(49,87,213,.16); }
    .agenda-alert { border: 0; border-radius: 12px; box-shadow: 0 5px 18px rgba(18,38,63,.05); }
    .agenda-session-card {
        display: flex; align-items: center; gap: 1rem; padding: 1.35rem 1.5rem;
        border: 1px solid var(--agenda-line); border-radius: 16px; background: #fff;
        box-shadow: 0 8px 28px rgba(18,38,63,.045);
    }
    .agenda-session-icon {
        display: grid; place-items: center; flex: 0 0 52px; height: 52px; border-radius: 14px;
        color: var(--agenda-primary); background: #edf2ff; font-size: 1.3rem;
    }
    .agenda-session-info { min-width: 0; flex: 1; }
    .agenda-label { display: block; color: var(--agenda-muted); font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; }
    .agenda-session-title { color: var(--agenda-ink); font-size: 1.12rem; font-weight: 750; overflow-wrap: anywhere; }
    .agenda-session-date { color: var(--agenda-muted); font-size: .88rem; }
    .agenda-session-count { text-align: right; padding-left: 1.5rem; border-left: 1px solid var(--agenda-line); }
    .agenda-session-count strong { display: block; color: var(--agenda-ink); font-size: 1.65rem; line-height: 1.25; }
    .agenda-list-card { overflow: hidden; border: 1px solid var(--agenda-line); border-radius: 16px; background: #fff; box-shadow: 0 8px 28px rgba(18,38,63,.045); }
    .agenda-list-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--agenda-line); background: #fbfcfe; }
    .agenda-section-title { font-size: 1rem; font-weight: 800; color: var(--agenda-ink); }
    .agenda-reorder-hint { color: var(--agenda-muted); font-size: .78rem; font-weight: 700; }
    .agenda-item { display: flex; align-items: center; gap: 1rem; padding: 1.15rem 1.5rem; border-bottom: 1px solid #eef1f6; transition: background .18s ease; }
    .agenda-item:last-child { border-bottom: 0; }
    .agenda-item:hover { background: #fafbff; }
    .agenda-item-number { display: grid; place-items: center; flex: 0 0 38px; height: 38px; border-radius: 11px; background: #f0f3fa; color: #445675; font-size: .88rem; font-weight: 800; }
    .agenda-drag-handle { display: grid; place-items: center; flex: 0 0 30px; width: 30px; height: 36px; border: 0; border-radius: 8px; background: transparent; color: #8a96a8; cursor: grab; }
    .agenda-drag-handle:hover { background: #edf2ff; color: var(--agenda-primary); }
    .agenda-drag-handle:active { cursor: grabbing; }
    .agenda-item-content { flex: 1; min-width: 0; }
    .agenda-document-title { color: var(--agenda-ink); font-size: .98rem; font-weight: 750; line-height: 1.45; margin: 0 0 .35rem; overflow-wrap: anywhere; }
    .agenda-proponent { color: var(--agenda-muted); font-size: .82rem; }
    .agenda-view-button { white-space: nowrap; border-radius: 9px; padding: .55rem .8rem; font-size: .8rem; font-weight: 700; }
    .agenda-empty-state { padding: 3.25rem 1.5rem; text-align: center; }
    .agenda-empty-icon { display: grid; place-items: center; width: 58px; height: 58px; margin: 0 auto 1rem; border-radius: 16px; background: #f0f3fa; color: #8290a5; font-size: 1.45rem; }
    .agenda-empty-state h3 { font-size: 1rem; font-weight: 800; }
    .agenda-footer-note { color: var(--agenda-muted); font-size: .8rem; }
    .agenda-item.sortable-ghost { opacity: .35; background: #edf2ff; }
    .agenda-item.sortable-chosen { box-shadow: 0 8px 22px rgba(18,38,63,.1); background: #fff; }
    @media (max-width: 767.98px) {
        .agenda-session-card { align-items: flex-start; padding: 1rem; gap: .8rem; }
        .agenda-session-icon { flex-basis: 44px; height: 44px; }
        .agenda-session-count { margin-left: auto; padding-left: .8rem; }
        .agenda-item { flex-wrap: wrap; gap: .7rem; padding: 1rem; }
        .agenda-item-content { flex-basis: calc(100% - 105px); }
        .agenda-view-button { margin-left: auto; }
        .agenda-list-header { padding: 1rem; }
    }
    @media (max-width: 420px) {
        .agenda-session-card { flex-wrap: wrap; }
        .agenda-session-count { width: 100%; text-align: left; border-left: 0; border-top: 1px solid var(--agenda-line); padding: .75rem 0 0; }
        .agenda-view-button { width: 100%; margin-left: 0; }
        .agenda-view-button span { display: inline; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('agendaLists');
    if (!el || typeof Sortable === 'undefined') return;

    Sortable.create(el, {
        animation: 180,
        handle: '.agenda-drag-handle',
        forceFallback: true,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        onEnd: function () {
            const order = Array.from(el.querySelectorAll('li[data-id]')).map(item => item.dataset.id);

            fetch("{{ route('secretary.session.agenda.reorder') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: order })
            }).then(response => {
                if (!response.ok) throw new Error('Failed to save agenda order.');
            }).catch(error => {
                console.error('Agenda reorder failed:', error);
                window.location.reload();
            });
        }
    });
});
</script>

