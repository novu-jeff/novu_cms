@extends('layouts.app')

@section('content')
<div class="remarks-page">
    <div class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <div class="remarks-header mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="remarks-eyebrow">
                        <i class="bi bi-chat-square-text me-2"></i>Legislative Records
                    </div>
                    <h1 class="remarks-title">Session Remarks</h1>
                    <p class="remarks-subtitle mb-0">Review members’ remarks and replies for each agenda document.</p>
                </div>
                <div class="session-date-chip">
                    <span class="date-chip-icon"><i class="bi bi-calendar-event"></i></span>
                    <span>
                        <span class="date-chip-label">Session date</span>
                        <strong>{{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}</strong>
                    </span>
                </div>
            </div>

            <div class="session-summary mt-4">
                <div class="session-summary-icon"><i class="bi bi-journal-text"></i></div>
                <div class="min-w-0">
                    <div class="summary-label">Session</div>
                    <h2 class="session-name mb-0">{{ $session->title }}</h2>
                </div>
                <div class="summary-count ms-lg-auto">
                    <span class="summary-count-value">{{ count($documents) }}</span>
                    <span class="summary-count-label">{{ count($documents) === 1 ? 'Agenda item' : 'Agenda items' }}</span>
                </div>
            </div>
        </div>

        @if(($documents ?? collect())->isEmpty())
            <div class="remarks-empty-state">
                <div class="empty-state-icon"><i class="bi bi-folder2-open"></i></div>
                <h3>No agenda documents</h3>
                <p class="mb-0">There are no documents available for this session yet.</p>
            </div>
        @else
            <div class="agenda-list">
                @foreach($documents as $doc)
                    @php
                        $remarks = $doc->document->documentRemarks ?? collect();
                        $remarkCount = is_countable($remarks) ? count($remarks) : 0;
                    @endphp

                    <section class="agenda-card mb-4">
                        <div class="agenda-card-header">
                            <div class="agenda-number">{{ $doc->agenda_order }}</div>
                            <div class="agenda-heading min-w-0">
                                <div class="agenda-kicker">Agenda item {{ $doc->agenda_order }}</div>
                                <h3 class="agenda-title">{{ $doc->document->title }}</h3>
                                <div class="agenda-proponent">
                                    <i class="bi bi-person-circle me-1"></i>
                                    Proponent: <strong>{{ $doc->document->memberss->name ?? 'Member' }}</strong>
                                </div>
                            </div>
                            <div class="remarks-count">
                                <i class="bi bi-chat-left-text"></i>
                                <span>{{ $remarkCount }} {{ $remarkCount === 1 ? 'remark' : 'remarks' }}</span>
                            </div>
                        </div>

                        <div class="agenda-card-body">
                            <div class="section-heading">
                                <div class="section-heading-icon"><i class="bi bi-chat-square-quote"></i></div>
                                <div>
                                    <h4>Member remarks</h4>
                                    <p class="mb-0">Comments and follow-up replies for this document</p>
                                </div>
                            </div>

                            @forelse($remarks as $remark)
                                <article class="remark-item">
                                    <div class="remark-avatar">
                                        {{ mb_strtoupper(mb_substr($remark->member->member->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div class="remark-content">
                                        <div class="remark-meta">
                                            <strong class="remark-author">{{ $remark->member->member->name ?? 'Member' }}</strong>
                                            @if($remark->created_at)
                                                <span class="remark-time">
                                                    <i class="bi bi-clock me-1"></i>{{ $remark->created_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="remark-message">{{ $remark->remark ?? '' }}</div>

                                        @if(($remark->replies ?? collect())->isNotEmpty())
                                            <div class="reply-list">
                                                @foreach($remark->replies as $reply)
                                                    <div class="reply-item">
                                                        <div class="reply-avatar">
                                                            {{ mb_strtoupper(mb_substr($reply->member->member->name ?? 'M', 0, 1)) }}
                                                        </div>
                                                        <div class="reply-content">
                                                            <div class="remark-meta">
                                                                <strong class="remark-author">{{ $reply->member->member->name ?? 'Member' }}</strong>
                                                                @if($reply->created_at)
                                                                    <span class="remark-time">
                                                                        <i class="bi bi-clock me-1"></i>{{ $reply->created_at->diffForHumans() }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="remark-message">{{ $reply->remark ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="no-remarks">
                                    <div class="no-remarks-icon"><i class="bi bi-chat-square"></i></div>
                                    <div>
                                        <strong>No remarks yet</strong>
                                        <p class="mb-0">No member remarks have been added to this agenda document.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

<style>
    .remarks-page {
        --remarks-ink: #172b4d;
        --remarks-muted: #718096;
        --remarks-line: #e7edf4;
        --remarks-primary: #3157d5;
        --remarks-soft: #f4f7fc;
        min-height: calc(100vh - 70px);
        background: #f6f8fc;
        color: var(--remarks-ink);
    }

    .remarks-header { max-width: 1500px; margin: 0 auto; }
    .remarks-eyebrow {
        display: inline-flex; align-items: center; margin-bottom: 8px;
        color: var(--remarks-primary); font-size: .76rem; font-weight: 800;
        letter-spacing: .09em; text-transform: uppercase;
    }
    .remarks-title { margin: 0; font-size: clamp(1.7rem, 3vw, 2.25rem); font-weight: 800; letter-spacing: -.045em; }
    .remarks-subtitle { margin-top: 8px; color: var(--remarks-muted); font-size: .96rem; }
    .session-date-chip {
        display: inline-flex; align-items: center; gap: 12px; padding: 12px 16px;
        background: #fff; border: 1px solid var(--remarks-line); border-radius: 14px;
        box-shadow: 0 4px 14px rgba(23, 43, 77, .035);
    }
    .date-chip-icon, .session-summary-icon, .section-heading-icon {
        display: grid; place-items: center; flex: 0 0 auto;
        width: 42px; height: 42px; border-radius: 12px;
        background: #edf2ff; color: var(--remarks-primary); font-size: 1.15rem;
    }
    .date-chip-label, .summary-label { display: block; color: var(--remarks-muted); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
    .session-date-chip strong { display: block; margin-top: 2px; font-size: .9rem; }
    .session-summary {
        display: flex; align-items: center; gap: 15px; padding: 20px;
        border: 1px solid var(--remarks-line); border-radius: 18px; background: #fff;
        box-shadow: 0 8px 24px rgba(23, 43, 77, .035);
    }
    .session-summary-icon { width: 50px; height: 50px; font-size: 1.35rem; }
    .session-name { margin-top: 4px; font-size: 1.12rem; font-weight: 750; line-height: 1.45; overflow-wrap: anywhere; }
    .summary-count { display: flex; flex-direction: column; align-items: flex-end; padding-left: 20px; border-left: 1px solid var(--remarks-line); }
    .summary-count-value { font-size: 1.35rem; line-height: 1.2; font-weight: 800; }
    .summary-count-label { margin-top: 3px; color: var(--remarks-muted); font-size: .78rem; }
    .agenda-list { max-width: 1500px; margin: 0 auto; }
    .agenda-card {
        overflow: hidden; background: #fff; border: 1px solid var(--remarks-line);
        border-radius: 18px; box-shadow: 0 8px 26px rgba(23, 43, 77, .04);
    }
    .agenda-card-header { display: flex; align-items: flex-start; gap: 16px; padding: 22px 24px; border-bottom: 1px solid var(--remarks-line); }
    .agenda-number {
        display: grid; place-items: center; flex: 0 0 44px; height: 44px;
        border-radius: 13px; background: var(--remarks-ink); color: #fff;
        font-size: 1.05rem; font-weight: 800;
    }
    .agenda-heading { flex: 1; }
    .agenda-kicker { margin-bottom: 4px; color: var(--remarks-primary); font-size: .72rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
    .agenda-title { margin: 0; font-size: 1.08rem; line-height: 1.45; font-weight: 750; overflow-wrap: anywhere; }
    .agenda-proponent { margin-top: 8px; color: var(--remarks-muted); font-size: .84rem; }
    .agenda-proponent strong { color: #475569; font-weight: 700; }
    .remarks-count {
        display: inline-flex; align-items: center; gap: 7px; white-space: nowrap;
        padding: 7px 11px; border: 1px solid #e2e9ff; border-radius: 999px;
        background: #f5f7ff; color: #425bc0; font-size: .78rem; font-weight: 750;
    }
    .agenda-card-body { padding: 24px; }
    .section-heading { display: flex; align-items: center; gap: 12px; margin-bottom: 22px; }
    .section-heading-icon { width: 40px; height: 40px; border-radius: 11px; }
    .section-heading h4 { margin: 0; font-size: .98rem; font-weight: 800; }
    .section-heading p { margin-top: 4px; color: var(--remarks-muted); font-size: .82rem; }
    .remark-item { display: flex; gap: 13px; padding: 0 0 22px; }
    .remark-item + .remark-item { padding-top: 22px; border-top: 1px solid #edf1f6; }
    .remark-avatar, .reply-avatar {
        display: grid; place-items: center; flex: 0 0 38px; height: 38px;
        border-radius: 50%; background: #e8efff; color: #3157d5; font-weight: 800;
    }
    .remark-content, .reply-content { flex: 1; min-width: 0; }
    .remark-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 12px; }
    .remark-author { color: #253858; font-size: .88rem; font-weight: 800; }
    .remark-time { color: #8a97aa; font-size: .76rem; }
    .remark-message { margin-top: 8px; color: #46566f; font-size: .92rem; line-height: 1.7; white-space: pre-line; overflow-wrap: anywhere; }
    .reply-list { margin-top: 18px; margin-left: 2px; padding-left: 18px; border-left: 2px solid #e1e8f5; }
    .reply-item { display: flex; gap: 11px; padding: 13px 15px; background: #f8faff; border: 1px solid #edf1f8; border-radius: 12px; }
    .reply-item + .reply-item { margin-top: 10px; }
    .reply-avatar { flex-basis: 32px; height: 32px; background: #e8f6f2; color: #16816c; font-size: .82rem; }
    .reply-message { color: #46566f; }
    .no-remarks { display: flex; align-items: center; gap: 14px; padding: 20px; border: 1px dashed #d8e0ec; border-radius: 14px; background: #fafcff; }
    .no-remarks-icon, .empty-state-icon { display: grid; place-items: center; flex: 0 0 auto; width: 44px; height: 44px; border-radius: 13px; background: #edf2f8; color: #718096; font-size: 1.2rem; }
    .no-remarks strong { font-size: .9rem; }
    .no-remarks p { margin-top: 4px; color: var(--remarks-muted); font-size: .83rem; }
    .remarks-empty-state { max-width: 1500px; margin: 0 auto; padding: 56px 20px; text-align: center; background: #fff; border: 1px dashed #d7e0ec; border-radius: 18px; }
    .empty-state-icon { width: 58px; height: 58px; margin: 0 auto 16px; font-size: 1.55rem; }
    .remarks-empty-state h3 { font-size: 1.1rem; font-weight: 800; }
    .remarks-empty-state p { color: var(--remarks-muted); font-size: .9rem; }
    @media (max-width: 767.98px) {
        .agenda-card-header { gap: 12px; padding: 18px; flex-wrap: wrap; }
        .agenda-number { flex-basis: 38px; height: 38px; }
        .agenda-heading { flex-basis: calc(100% - 55px); }
        .remarks-count { margin-left: 50px; }
        .agenda-card-body { padding: 18px; }
        .session-summary { align-items: flex-start; flex-wrap: wrap; padding: 16px; }
        .summary-count { align-items: flex-start; width: 100%; margin-left: 65px !important; padding: 12px 0 0; border-left: 0; border-top: 1px solid var(--remarks-line); }
        .session-date-chip { align-self: flex-start; }
        .reply-list { padding-left: 10px; }
        .reply-item { padding: 12px; }
    }
</style>

