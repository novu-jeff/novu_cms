@extends('layouts.app')

@section('styles')
<style>
    .member-documents-page {
        --doc-ink: #17233b;
        --doc-muted: #718096;
        --doc-border: #e5eaf1;
        --doc-surface: #ffffff;
        --doc-canvas: #f5f7fb;
        color: var(--doc-ink);
    }

    .member-documents-page .page-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .member-documents-page .eyebrow {
        color: #3975c6;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .11em;
        text-transform: uppercase;
        margin-bottom: .45rem;
    }

    .member-documents-page .page-title {
        color: var(--doc-ink);
        font-size: clamp(1.55rem, 2.4vw, 2.1rem);
        font-weight: 800;
        letter-spacing: -.045em;
        line-height: 1.15;
        margin: 0;
    }

    .member-documents-page .page-description {
        color: var(--doc-muted);
        font-size: .92rem;
        margin: .45rem 0 0;
    }

    .member-documents-page .summary-strip {
        display: flex;
        align-items: center;
        gap: .65rem;
        color: #64748b;
        font-size: .82rem;
        background: #fff;
        border: 1px solid var(--doc-border);
        border-radius: .75rem;
        padding: .7rem .9rem;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
    }

    .member-documents-page .summary-strip strong { color: var(--doc-ink); font-size: 1rem; }
    .member-documents-page .summary-strip .summary-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.2rem;
        height: 2.2rem;
        color: #2868b2;
        background: #edf5ff;
        border-radius: .65rem;
        font-size: 1rem;
    }

    .member-documents-page .toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: var(--doc-surface);
        border: 1px solid var(--doc-border);
        border-bottom: 0;
        border-radius: .9rem .9rem 0 0;
    }

    .member-documents-page .toolbar-title { font-size: 1rem; font-weight: 750; margin: 0; }
    .member-documents-page .toolbar-subtitle { color: var(--doc-muted); font-size: .8rem; margin: .2rem 0 0; }
    .member-documents-page .filter-form { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
    .member-documents-page .search-box { min-width: min(100%, 290px); }
    .member-documents-page .search-box .input-group-text {
        color: #8290a3;
        background: #fff;
        border-color: #dce3ec;
        border-right: 0;
        border-radius: .6rem 0 0 .6rem;
    }
    .member-documents-page .search-box .form-control {
        min-height: 41px;
        border-color: #dce3ec;
        border-left: 0;
        border-radius: 0 .6rem .6rem 0;
        font-size: .85rem;
        box-shadow: none;
    }
    .member-documents-page .filter-form .form-select,
    .member-documents-page .filter-form .btn { min-height: 41px; border-radius: .6rem; font-size: .84rem; }
    .member-documents-page .filter-form .form-select { min-width: 145px; border-color: #dce3ec; }

    .member-documents-page .document-panel {
        overflow: hidden;
        background: var(--doc-surface);
        border: 1px solid var(--doc-border);
        border-radius: 0 0 .9rem .9rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .035);
    }

    .member-documents-page .table-responsive { min-height: 220px; }
    .member-documents-page .documents-table { margin: 0; }
    .member-documents-page .documents-table thead th {
        padding: .85rem 1rem;
        color: #64748b;
        background: #f8fafc;
        border-top: 1px solid #edf1f6;
        border-bottom: 1px solid var(--doc-border);
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .member-documents-page .documents-table tbody td {
        padding: 1rem;
        color: #475569;
        border-color: #eef2f6;
        vertical-align: middle;
        font-size: .86rem;
    }
    .member-documents-page .documents-table tbody tr { transition: background .15s ease; }
    .member-documents-page .documents-table tbody tr:hover { background: #f9fbfe; }
    .member-documents-page .documents-table tbody tr:last-child td { border-bottom: 0; }

    .member-documents-page .document-cell { display: flex; align-items: center; gap: .8rem; min-width: 250px; }
    .member-documents-page .file-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        color: #3474bd;
        background: #edf5ff;
        border: 1px solid #dcecff;
        border-radius: .7rem;
        font-size: 1.05rem;
    }
    .member-documents-page .document-title { color: var(--doc-ink); font-weight: 700; line-height: 1.35; overflow-wrap: anywhere; }
    .member-documents-page .document-meta { color: #8a97a8; font-size: .74rem; margin-top: .25rem; }
    .member-documents-page .member-name { color: #334155; font-weight: 600; }
    .member-documents-page .date-main { color: #334155; white-space: nowrap; }
    .member-documents-page .date-sub { color: #94a3b8; font-size: .73rem; margin-top: .2rem; }

    .member-documents-page .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        padding: .4rem .68rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 750;
        white-space: nowrap;
    }
    .member-documents-page .status-approved { color: #087653; background: #ecfdf5; border-color: #b7efd8; }
    .member-documents-page .status-forwarded { color: #9a6208; background: #fff8e8; border-color: #f7dfaa; }
    .member-documents-page .status-rejected { color: #b42318; background: #fff1f0; border-color: #ffd0cc; }
    .member-documents-page .status-neutral { color: #526174; background: #f1f5f9; border-color: #e2e8f0; }

    .member-documents-page .session-info { min-width: 175px; max-width: 225px; }
    .member-documents-page .session-name { display: block; color: #475569; font-size: .79rem; font-weight: 650; line-height: 1.35; }
    .member-documents-page .session-date { display: block; color: #94a3b8; font-size: .72rem; margin-top: .25rem; }
    .member-documents-page .session-unassigned { color: #94a3b8; font-size: .78rem; font-style: italic; }

    .member-documents-page .action-buttons { display: inline-flex; align-items: center; justify-content: flex-end; gap: .38rem; white-space: nowrap; }
    .member-documents-page .action-buttons .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: .58rem;
        font-size: .86rem;
    }
    .member-documents-page .action-buttons .btn-view { color: #2868b2; background: #f1f7ff; border-color: #d9e9fb; }
    .member-documents-page .action-buttons .btn-view:hover { color: #fff; background: #2868b2; border-color: #2868b2; }

    .member-documents-page .empty-state { padding: 3.5rem 1rem !important; text-align: center; }
    .member-documents-page .empty-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 3.5rem; height: 3.5rem; margin-bottom: .85rem;
        color: #7890aa; background: #f1f5f9; border-radius: 1rem; font-size: 1.35rem;
    }
    .member-documents-page .empty-title { color: #334155; font-weight: 750; margin-bottom: .25rem; }
    .member-documents-page .empty-description { color: #94a3b8; font-size: .83rem; }
    .member-documents-page .pagination-wrap { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; padding: 1rem 1.15rem; border-top: 1px solid #edf1f6; }
    .member-documents-page .pagination-note { color: #8290a3; font-size: .78rem; }

    .member-documents-page .modal-content { overflow: hidden; border: 1px solid #e5eaf1; border-radius: .9rem; box-shadow: 0 20px 55px rgba(15, 23, 42, .18); }
    .member-documents-page .modal-header, .member-documents-page .modal-footer { padding: 1rem 1.25rem; border-color: #edf1f6; }
    .member-documents-page .modal-body { padding: 1.25rem; }
    .member-documents-page .modal-title { color: var(--doc-ink); font-size: 1rem; font-weight: 750; }
    .member-documents-page .modal-description { color: #8290a3; font-size: .8rem; margin: .25rem 0 0; }
    .member-documents-page .modal .form-control, .member-documents-page .modal .form-select { min-height: 42px; border-color: #dce3ec; border-radius: .6rem; box-shadow: none; }
    .member-documents-page .modal textarea.form-control { min-height: 100px; }

    .member-documents-page .documents-pagination nav {
    display: flex;
    justify-content: center;
}

.member-documents-page .documents-pagination .pagination {
    margin-bottom: 0;
    gap: 4px;
}

.member-documents-page .documents-pagination .page-link {
    border-radius: 8px;
    border-color: #e2e8f0;
    color: #475569;
    font-size: .85rem;
    min-width: 38px;
    text-align: center;
    padding: .5rem .75rem;
}

.member-documents-page .documents-pagination .page-item.active .page-link {
    background: #258fc5;
    border-color: #258fc5;
    color: #fff;
}

.member-documents-page .documents-pagination .page-item.disabled .page-link {
    color: #94a3b8;
    background: #f8fafc;
}

    @media (max-width: 767.98px) {
        .member-documents-page .page-heading { align-items: stretch; }
        .member-documents-page .summary-strip { align-self: flex-start; }
        .member-documents-page .toolbar { align-items: stretch; }
        .member-documents-page .filter-form { width: 100%; }
        .member-documents-page .search-box { flex: 1 1 100%; min-width: 100%; }
        .member-documents-page .filter-form .form-select { flex: 1 1 auto; }
        .member-documents-page .documents-table thead th,
        .member-documents-page .documents-table tbody td { padding: .8rem .75rem; }
        .member-documents-page .document-cell { min-width: 220px; }
    }
</style>
@endsection

@section('content')
@php
    // Load once for the page. Prefer passing $upcomingSessions from the controller.
    $upcomingSessions = $upcomingSessions ?? \App\Models\SessionMeeting::where('status', 'upcoming')
        ->orderBy('session_date')
        ->get();

    $documentCount = method_exists($documents, 'total') ? $documents->total() : $documents->count();
    $storageBaseUrl = rtrim(env('LIS_STORAGE_URL', config('app.url')), '/');
@endphp

<div class="container-fluid px-3 px-lg-4 py-4 member-documents-page">
    <header class="page-heading">
        <div>
            <div class="eyebrow"><i class="bi bi-bank me-1"></i> Legislative Management / Secretary Portal</div>
            <h1 class="page-title">Member Documents</h1>
            <p class="page-description">Review member submissions, manage approvals, and assign approved documents to Council sessions.</p>
        </div>
        <div class="summary-strip">
            <span class="summary-icon"><i class="bi bi-files"></i></span>
            <span><strong>{{ number_format($documentCount) }}</strong><br>documents in this result</span>
        </div>
    </header>

    <section aria-labelledby="documents-list-heading">
        <div class="toolbar">
            <div>
                <h2 class="toolbar-title" id="documents-list-heading">Submitted Documents</h2>
                <p class="toolbar-subtitle">Forwarded and approved legislative submissions from SB members</p>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="filter-form">
                <div class="input-group search-box">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" name="search"
                           value="{{ old('search', $search ?? request('search')) }}"
                           class="form-control" placeholder="Search title or member..."
                           aria-label="Search by document title or member">
                </div>

                <select name="status" class="form-select" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <option value="forwarded" {{ (request('status', $status ?? '') === 'forwarded') ? 'selected' : '' }}>Forwarded</option>
                    <option value="approved" {{ (request('status', $status ?? '') === 'approved') ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ (request('status', $status ?? '') === 'rejected') ? 'selected' : '' }}>Rejected</option>
                </select>

                <button type="submit" class="btn btn-primary px-3">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Apply
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ url()->current() }}" class="btn btn-light border px-3">Clear</a>
                @endif
            </form>
        </div>

        <div class="document-panel">
            <div class="table-responsive">
                <table class="table documents-table align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="ps-lg-4">Document</th>
                            <th scope="col">Submitted by</th>
                            <th scope="col">Date submitted</th>
                            <th scope="col">Status</th>
                            <th scope="col">Assigned session</th>
                            <th scope="col" class="text-end pe-lg-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($documents as $doc)
                        @php
                            $currentSession = optional(optional($doc->sessionDocument)->session);
                            $documentUrl = $storageBaseUrl . '/' . ltrim($doc->file_path ?? '', '/');
                        @endphp
                        <tr>
                            <td class="ps-lg-4">
                                <div class="document-cell">
                                    <span class="file-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                                    <div>
                                        <div class="document-title">{{ $doc->title }}</div>
                                        <div class="document-meta">Submission #{{ $doc->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="member-name">{{ optional($doc->member)->name ?? 'Unknown Member' }}</span></td>
                            <td>
                                <div class="date-main">{{ optional($doc->created_at)->format('M d, Y') ?? '—' }}</div>
                                <div class="date-sub">{{ optional($doc->created_at)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($doc->status === 'approved')
                                    <span class="status-badge status-approved"><i class="bi bi-check-circle-fill"></i> Approved</span>
                                @elseif($doc->status === 'forwarded')
                                    <span class="status-badge status-forwarded"><i class="bi bi-clock-history"></i> Forwarded</span>
                                @elseif($doc->status === 'rejected')
                                    <span class="status-badge status-rejected"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                                @else
                                    <span class="status-badge status-neutral">{{ ucfirst($doc->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($currentSession)
                                    <div class="session-info">
                                        <span class="session-name"><i class="bi bi-calendar-event me-1"></i>{{ $currentSession->title }}</span>
                                        <span class="session-date">{{ \Carbon\Carbon::parse($currentSession->session_date)->format('M d, Y') }}</span>
                                    </div>
                                @else
                                    <span class="session-unassigned">Not assigned</span>
                                @endif
                            </td>
                            <td class="text-end pe-lg-4">
                                <div class="action-buttons">
                                    <a href="{{ $documentUrl }}" class="btn btn-view" target="_blank" rel="noopener noreferrer"
                                       title="View document" aria-label="View {{ $doc->title }}">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>

                                    @if($doc->status === 'forwarded')
                                        <button type="button" class="btn btn-outline-success"
                                                data-bs-toggle="modal" data-bs-target="#approveModal{{ $doc->id }}"
                                                title="Approve document" aria-label="Approve {{ $doc->title }}">
                                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}"
                                                title="Reject document" aria-label="Reject {{ $doc->title }}">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        </button>
                                    @elseif($doc->status === 'approved')
                                        <button type="button" class="btn btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#editSessionModal{{ $doc->id }}"
                                                title="Change session" aria-label="Change session for {{ $doc->title }}">
                                            <i class="bi bi-calendar2-plus" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></div>
                                <div class="empty-title">No documents found</div>
                                <div class="empty-description">Try changing your search or status filter. Forwarded and approved submissions will appear here.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($documents, 'links'))
                <div class="d-flex flex-column flex-md-row
                        justify-content-between align-items-center
                        gap-3 mt-4 pt-3 border-top">

                <div class="text-muted small">
                    Showing {{ $documents->firstItem() ?? 0 }}
                    to {{ $documents->lastItem() ?? 0 }}
                    of {{ $documents->total() }} documents
                </div>

                <div class="documents-pagination">
                    {{ $documents->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>

                </div>
            @endif
        </div>
    </section>

    {{-- Modals are intentionally outside the table to keep table markup valid. --}}
    @foreach($documents as $doc)
        @if($doc->status === 'forwarded')
            <div class="modal fade" id="approveModal{{ $doc->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $doc->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('secretary.documents.approve', $doc->id) }}">
                            @csrf
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="approveModalLabel{{ $doc->id }}">Approve document</h5>
                                    <p class="modal-description">Select the Council session where this document will be included.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">Document</div>
                                    <div class="fw-semibold">{{ $doc->title }}</div>
                                </div>
                                <label class="form-label fw-semibold" for="approve-session-{{ $doc->id }}">Council session <span class="text-danger">*</span></label>
                                <select id="approve-session-{{ $doc->id }}" name="session_id" class="form-select" required>
                                    <option value="">Choose an upcoming session</option>
                                    @foreach($upcomingSessions as $session)
                                        <option value="{{ $session->id }}">{{ $session->title }} — {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}</option>
                                    @endforeach
                                </select>
                                @if($upcomingSessions->isEmpty())
                                    <div class="alert alert-warning small mt-3 mb-0">There are no upcoming sessions available. Create or schedule a session first.</div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit" {{ $upcomingSessions->isEmpty() ? 'disabled' : '' }}><i class="bi bi-check2-circle me-1"></i> Approve and assign</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $doc->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('secretary.documents.reject', $doc->id) }}">
                            @csrf
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="rejectModalLabel{{ $doc->id }}">Reject document</h5>
                                    <p class="modal-description">Provide a clear reason that can be reviewed by the submitting member.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">Document</div>
                                    <div class="fw-semibold">{{ $doc->title }}</div>
                                </div>
                                <label class="form-label fw-semibold" for="reject-remarks-{{ $doc->id }}">Rejection remarks <span class="text-danger">*</span></label>
                                <textarea id="reject-remarks-{{ $doc->id }}" name="remarks" class="form-control" rows="4" placeholder="Explain why this submission is being rejected..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-danger" type="submit"><i class="bi bi-x-circle me-1"></i> Reject document</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($doc->status === 'approved')
            @php $selectedSessionId = optional(optional($doc->sessionDocument)->session)->id; @endphp
            <div class="modal fade" id="editSessionModal{{ $doc->id }}" tabindex="-1" aria-labelledby="editSessionModalLabel{{ $doc->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('secretary.documents.updateSession', $doc->id) }}">
                            @csrf
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="editSessionModalLabel{{ $doc->id }}">Change assigned session</h5>
                                    <p class="modal-description">Move this approved document to another upcoming Council session.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">Document</div>
                                    <div class="fw-semibold">{{ $doc->title }}</div>
                                </div>
                                <label class="form-label fw-semibold" for="edit-session-{{ $doc->id }}">Council session <span class="text-danger">*</span></label>
                                <select id="edit-session-{{ $doc->id }}" name="session_id" class="form-select" required>
                                    <option value="">Choose an upcoming session</option>
                                    @foreach($upcomingSessions as $session)
                                        <option value="{{ $session->id }}" {{ (string) $session->id === (string) $selectedSessionId ? 'selected' : '' }}>
                                            {{ $session->title }} — {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($upcomingSessions->isEmpty())
                                    <div class="alert alert-warning small mt-3 mb-0">There are no upcoming sessions available.</div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit" {{ $upcomingSessions->isEmpty() ? 'disabled' : '' }}><i class="bi bi-arrow-repeat me-1"></i> Update session</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection
