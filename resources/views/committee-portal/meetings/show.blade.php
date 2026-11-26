
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Committee Dashboard - Committee Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          rel="stylesheet">

    <style>
        :root { --committee-ink:#172033; --committee-muted:#667085; --committee-line:#e7ebf2; --committee-primary:#3157d5; }
        body { background:#f6f8fb; color:var(--committee-ink); }
        .navbar { background:#248fc5 !important; box-shadow:0 3px 14px rgba(16,24,40,.08); }
        .navbar .nav-link { font-size: 12px; color:rgba(255,255,255,.72); padding:10px 14px; border-radius:9px; transition:all .18s ease; }
        .navbar .nav-link:hover,.navbar .nav-link.active { color:#fff; background:rgba(255,255,255,.12); }
        .page-header { background:#fff; color:var(--committee-ink); border:1px solid var(--committee-line); border-radius:16px; box-shadow:0 5px 20px rgba(16,24,40,.035); }
        .page-header .small { color:var(--committee-primary) !important; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .page-header h3 { font-size:clamp(1.5rem,2.4vw,2rem); font-weight:750; letter-spacing:-.035em; }
        .page-header p { color:var(--committee-muted) !important; }
        .stat-card,.workspace-card { border:1px solid var(--committee-line); border-radius:16px; box-shadow:0 5px 20px rgba(16,24,40,.035); overflow:hidden; }
        .stat-label { color:var(--committee-muted); font-size:.78rem; font-weight:700; }
        .stat-value { font-size:1.8rem; font-weight:750; letter-spacing:-.03em; color:var(--committee-ink); }
        .section-title { font-size:1rem; font-weight:750; margin-bottom:0; color:var(--committee-ink); }
        .committee-card { border:1px solid var(--committee-line); border-radius:14px; background:#fff; transition:all .18s ease; }
        .committee-card:hover { border-color:#b8c7fb; box-shadow:0 6px 20px rgba(16,24,40,.06); transform:translateY(-2px); }
        .committee-icon { width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;color:var(--committee-primary); }
        .meeting-row,.agenda-row { border-bottom:1px solid #eef1f5; padding:17px 0; }
        .meeting-row:last-child,.agenda-row:last-child { border-bottom:0; }
        .meeting-title { color:var(--committee-ink); font-weight:750; }
        .small-meta { color:var(--committee-muted); font-size:.82rem; }
        .status-count-card { min-height:105px; }
        .status-count { font-size:1.7rem;font-weight:750;color:var(--committee-ink); }
        .table thead th { background:#f8fafc;color:#667085;font-size:.72rem;font-weight:750;text-transform:uppercase;letter-spacing:.055em;white-space:nowrap;border-bottom:1px solid var(--committee-line); }
        .table td { font-size:.88rem;color:#475467;border-color:#eef1f5; }
        .empty-state { padding:38px 20px; }
        .empty-icon { font-size:2.25rem;color:#cbd5e1; }
        .pagination { margin-bottom:0; }
        .pagination .page-link { color:var(--committee-primary); }
        .pagination .active .page-link { background:var(--committee-primary);border-color:var(--committee-primary);color:#fff; }
        .dashboard-stat { height:100%; padding:1.15rem 1.25rem; border:1px solid var(--committee-line); border-radius:15px; background:#fff; box-shadow:0 5px 18px rgba(16,24,40,.035); }
        .dashboard-stat .stat-icon { width:2.6rem;height:2.6rem;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;color:var(--committee-primary);font-size:1.05rem; }
        .dashboard-stat .stat-number { font-size:1.8rem;font-weight:750;letter-spacing:-.03em;line-height:1.2;color:var(--committee-ink);margin-top:.9rem; }
        .dashboard-stat .stat-caption { color:var(--committee-muted);font-size:.8rem;margin-top:.25rem; }
        .card-header { border-bottom:1px solid var(--committee-line) !important; }
        .btn-outline-primary { --bs-btn-color:var(--committee-primary);--bs-btn-border-color:#c7d2fe;--bs-btn-hover-bg:var(--committee-primary);--bs-btn-hover-border-color:var(--committee-primary); }
        @media (max-width:767px) { .page-header { padding:1.25rem !important; } .dashboard-action,.meeting-action { width:100%; } }
    </style>
</head>

<body>

@include('committee-portal.partials.navbar')

<div class="container-fluid px-3 px-lg-4 py-4"
     id="live-session"
     data-state-url="{{ route('committee-portal.live-session.state', $meeting->id) }}">

    {{-- Page navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

        <a href="{{ route('committee-portal.live-session.index') }}"
           class="text-decoration-none small">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Session Hall
        </a>

        <span class="small text-muted" id="last-updated">
            <i class="fas fa-circle text-success me-1"></i>
            Live updates enabled
        </span>

    </div>

    {{-- Meeting header --}}
    <div class="page-header p-4 mb-4 shadow-sm">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

            <div>
                <div class="small text-white-50 mb-2">
                    {{ $meeting->committee?->name ?? 'Committee' }}
                </div>

                <h3 class="fw-bold mb-2">
                    {{ $meeting->title }}
                </h3>

                <div class="small text-white-50">
                    <span class="me-3">
                        <i class="far fa-calendar me-1"></i>
                        {{ $meeting->meeting_date?->format('F d, Y') ?? 'Date not set' }}
                    </span>

                    <span class="me-3">
                        <i class="far fa-clock me-1"></i>
                        {{ $meeting->start_time ?? 'Time not set' }}
                    </span>

                    @if($meeting->venue)
                        <span>
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $meeting->venue }}
                        </span>
                    @endif
                </div>
            </div>

            <span id="meeting-status"
                  class="badge fs-6 {{ $meeting->status === 'ongoing'
                    ? 'bg-success'
                    : ($meeting->status === 'completed'
                        ? 'bg-secondary'
                        : 'bg-primary') }}">
                {{ ucwords(str_replace('_', ' ', $meeting->status)) }}
            </span>

        </div>

    </div>

    {{-- Meeting summary --}}
    <div class="row g-3 mb-4">

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Meeting Date</div>
                    <div class="stat-value">
                        {{ $meeting->meeting_date?->format('M d, Y') ?? 'Not set' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Venue</div>
                    <div class="stat-value fs-6 mt-2">
                        {{ $meeting->venue ?: 'Not specified' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Attendance / Quorum</div>

                    <div class="stat-value" id="quorum-count">
                        {{ $presentCount }} / {{ $requiredQuorum }}
                    </div>

                    <small id="quorum-status"
                           class="{{ $quorumReached ? 'text-success' : 'text-warning' }}">
                        {{ $quorumReached ? 'Quorum reached' : 'Waiting for quorum' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Agenda Items</div>
                    <div class="stat-value" id="agenda-count">
                        {{ $meeting->agendaItems->count() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Quorum progress --}}
    <div class="card workspace-card mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h6 class="section-title">Meeting Quorum</h6>
                    <small class="text-muted">
                        Present and late members count toward quorum.
                    </small>
                </div>

                <strong id="quorum-percentage">
                    {{ $requiredQuorum > 0
                        ? min(100, round(($presentCount / $requiredQuorum) * 100))
                        : 0 }}%
                </strong>
            </div>

            <div class="progress">
                <div id="quorum-progress"
                     class="progress-bar {{ $quorumReached ? 'bg-success' : 'bg-warning' }}"
                     style="width: {{ $requiredQuorum > 0
                        ? min(100, ($presentCount / $requiredQuorum) * 100)
                        : 0 }}%">
                </div>
            </div>

        </div>
    </div>

    {{-- Agenda and document viewer --}}
    <div class="row g-3">

        {{-- Agenda navigation --}}
        <div class="col-lg-4 col-xl-3">

            <div class="card workspace-card h-100">

                <div class="card-header bg-white border-0 py-3">
                    <h5 class="section-title">
                        <i class="fas fa-list-ol text-primary me-2"></i>
                        Meeting Agenda
                    </h5>
                </div>

                <div class="list-group list-group-flush" id="agenda-list">

                    @forelse($meeting->agendaItems as $item)

                       @php
                            $itemStatusClass = match ($item->status) {
                                'in_progress' => 'bg-primary',
                                'completed' => 'bg-success',
                                'deferred' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };

                            $isCurrent = $currentAgendaItem
                                && $currentAgendaItem->id === $item->id;

                            $hasAgendaDocument = $item->referral
                                && (
                                    $item->referral->report_file_path
                                    || $item->referral->sessionDocument?->document?->file_path
                                );

                            $agendaTitle = $item->referral?->title
                                ?? $item->referral?->sessionDocument?->document?->title
                                ?? 'Agenda Item';
                        @endphp

                        <div
                            class="agenda-item {{ $isCurrent ? 'agenda-current' : '' }}"
                            role="button"
                            tabindex="0"
                            aria-pressed="{{ $isCurrent ? 'true' : 'false' }}"
                            data-agenda-item
                            data-item-id="{{ $item->id }}"
                            data-agenda-title="{{ $agendaTitle }}"
                            data-agenda-order="{{ $item->agenda_order }}"
                            data-document-url="{{ $hasAgendaDocument
                                ? route('committee-portal.referrals.document', $item->referral->id)
                                : '' }}"
                        >

                            <div class="d-flex justify-content-between align-items-center gap-2">

                                <strong class="small">
                                    Agenda Item #{{ $item->agenda_order }}
                                </strong>

                                <span class="badge {{ $itemStatusClass }}">
                                    {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                </span>

                            </div>

                            <div class="fw-semibold mt-2">
                                {{ $item->referral?->title
                                    ?? $item->referral?->sessionDocument?->document?->title
                                    ?? 'Agenda Item' }}
                            </div>

                            <div class="small text-muted mt-1">
                                Referral #{{ $item->committee_referral_id }}
                            </div>

                            @if($isCurrent)
                                <div class="small text-primary mt-2">
                                    <i class="fas fa-circle me-1"></i>
                                    Current agenda item
                                </div>
                            @endif

                            @if($item->referral)
                                <a href="{{ route(
                                    'committee-portal.referrals.show',
                                    $item->referral->id
                                ) }}"
                                   class="btn btn-sm btn-outline-secondary mt-3">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    View Referral
                                </a>
                            @endif

                        </div>

                    @empty

                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0">No agenda items available.</p>
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Document viewer --}}
        <div class="col-lg-8 col-xl-9">

            <div class="card workspace-card h-100">

                <div class="card-header bg-white border-0 py-3">
                    <h5 class="section-title">
                        <i class="fas fa-file-pdf text-danger me-2"></i>
                        Session Document Viewer
                    </h5>
                </div>

                <div class="card-body">

            {{-- Selected agenda item information --}}
            <div id="selected-agenda-info" class="mb-3"
                style="{{ $currentAgendaItem ? '' : 'display:none;' }}">

                <div class="small-label">
                    Currently selected agenda item
                </div>

                <h6 class="fw-bold mt-1" id="selected-agenda-title">
                    @if($currentAgendaItem)
                        Item {{ $currentAgendaItem->agenda_order }} -
                        {{ $currentAgendaItem->referral?->title
                            ?? $currentAgendaItem->referral?->sessionDocument?->document?->title
                            ?? 'Agenda Item' }}
                    @endif
                </h6>

            </div>

            {{-- PDF Viewer --}}
            <div id="agenda-document-frame"
                class="document-placeholder"
                style="{{ $currentAgendaItem && $currentAgendaItem->referral
                    && ($currentAgendaItem->referral->report_file_path
                        || $currentAgendaItem->referral->sessionDocument?->document?->file_path)
                    ? '' : 'display:none;' }}">

                <iframe
                    id="agenda-pdf-viewer"
                    src="{{ $currentAgendaItem && $currentAgendaItem->referral
                        && ($currentAgendaItem->referral->report_file_path
                            || $currentAgendaItem->referral->sessionDocument?->document?->file_path)
                        ? route('committee-portal.referrals.document', $currentAgendaItem->referral->id)
                        : '' }}"
                    title="Agenda Document PDF Viewer"
                    style="width:100%; height:650px; border:0; border-radius:10px;"
                    loading="lazy">
                </iframe>

            </div>
                    {{-- No document available --}}
                    <div id="agenda-no-document"
                        class="document-placeholder d-flex align-items-center justify-content-center"
                        style="{{ $currentAgendaItem && $currentAgendaItem->referral
                            && ($currentAgendaItem->referral->report_file_path
                                || $currentAgendaItem->referral->sessionDocument?->document?->file_path)
                            ? 'display:none !important;' : 'display:flex !important;' }}">

                        <div class="text-center text-muted p-4">
                            <i class="fas fa-file-pdf fa-4x mb-3"></i>

                            <h6 class="fw-bold" id="agenda-viewer-message">
                                {{ $currentAgendaItem
                                    ? 'No document attached to this agenda item.'
                                    : 'Select an agenda item to view its document.' }}
                            </h6>

                            <p class="small mb-0">
                                Select an item from the Meeting Agenda panel.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Attendance --}}
    <div class="card workspace-card mt-4">

        <div class="card-header bg-white border-0 py-3">
            <h5 class="section-title">
                <i class="fas fa-users text-primary me-2"></i>
                Attendance and Quorum
            </h5>
        </div>

        <div class="card-body">

            <div class="d-flex flex-wrap gap-4 mb-3">

                <div>
                    <div class="small-label">Present / Late</div>
                    <h4 class="fw-bold mb-0" id="attendance-present">
                        {{ $presentCount }}
                    </h4>
                </div>

                <div>
                    <div class="small-label">Required Quorum</div>
                    <h4 class="fw-bold mb-0">
                        {{ $requiredQuorum }}
                    </h4>
                </div>

                <div>
                    <div class="small-label">Quorum Status</div>
                    <span id="quorum-badge"
                          class="badge mt-1 {{ $quorumReached
                            ? 'bg-success'
                            : 'bg-warning text-dark' }}">
                        {{ $quorumReached ? 'Quorum Reached' : 'Quorum Not Reached' }}
                    </span>
                </div>

            </div>

            <div id="attendance-list">

                @forelse($meeting->attendances as $attendance)

                    @php
                        $attendanceClass = match ($attendance->attendance_status) {
                            'present' => 'bg-success',
                            'late' => 'bg-warning text-dark',
                            'absent' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp

                    <div class="attendance-row d-flex justify-content-between align-items-center py-3 gap-2">

                        <div>
                            <div class="fw-semibold">
                                {{ $attendance->attendee_name
                                    ?: $attendance->committeeMember?->member?->name
                                    ?: 'Committee Member' }}
                            </div>

                            <small class="text-muted">
                                {{ $attendance->attendee_role ?: 'Member' }}
                            </small>
                        </div>

                        <span class="badge {{ $attendanceClass }}">
                            {{ ucfirst($attendance->attendance_status) }}
                        </span>

                    </div>

                @empty

                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-clock fa-2x mb-2"></i>
                        <p class="mb-0">Attendance has not been recorded.</p>
                    </div>

                @endforelse

            </div>

            <div class="small text-muted mt-3">
                Only Present and Late attendance counts toward quorum.
            </div>

        </div>

    </div>

    {{-- Official deliberation records --}}
    <div class="card workspace-card mt-4">

        <div class="card-header bg-white border-0 py-3">
            <h5 class="section-title">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                Official Deliberation Records
            </h5>
        </div>

        <div class="card-body" id="deliberation-history">

            @forelse($meeting->agendaItems as $item)

                <div class="mb-4">

                    <h6 class="fw-bold mb-3">
                        Agenda Item {{ $item->agenda_order }}
                    </h6>

                    @forelse($item->actions as $action)

                        <div class="border-start border-3 border-primary ps-3 py-2 mb-3">

                            <div class="fw-semibold">
                                {{ ucwords(str_replace('_', ' ', $action->action)) }}
                            </div>

                            @if($action->reason)
                                <p class="text-muted small mb-1 mt-2">
                                    <strong>Discussion / Reason:</strong>
                                    {{ $action->reason }}
                                </p>
                            @endif

                            @if($action->recommendation)
                                <p class="small mb-1">
                                    <strong>Recommendation:</strong>
                                    {{ $action->recommendation }}
                                </p>
                            @endif

                            <small class="text-muted">
                                {{ $action->actor?->name ?? 'Committee Secretary' }}

                                @if($action->acted_at)
                                    &middot;
                                    {{ $action->acted_at->format('M d, Y h:i A') }}
                                @endif
                            </small>

                        </div>

                    @empty

                        <p class="text-muted small">
                            No deliberation records available for this agenda item.
                        </p>

                    @endforelse

                </div>

            @empty

                <p class="text-muted mb-0">
                    No agenda items available.
                </p>

            @endforelse

        </div>

    </div>

    {{-- Read-only notice --}}
    <div class="alert alert-info mt-4 footer-note">

        <i class="fas fa-info-circle me-2"></i>

        <strong>Read-only Committee Workspace:</strong>

        This portal allows committee members to view meeting proceedings,
        agenda documents, attendance, quorum, and official deliberation records.

        Official attendance and meeting actions are managed by the Committee Secretary.

    </div>

    <div class="text-center text-muted small py-3">
        Committee Portal &copy; {{ date('Y') }}
    </div>

</div>

<script>
(function () {
    const container = document.getElementById('live-session');

        // ==========================================
    // AGENDA SELECTION AND DOCUMENT VIEWER
    // ==========================================

    const agendaItems = document.querySelectorAll('[data-agenda-item]');
    const selectedAgendaInfo = document.getElementById('selected-agenda-info');
    const selectedAgendaTitle = document.getElementById('selected-agenda-title');
    const pdfViewer = document.getElementById('agenda-pdf-viewer');
    const documentFrame = document.getElementById('agenda-document-frame');
    const noDocument = document.getElementById('agenda-no-document');
    const viewerMessage = document.getElementById('agenda-viewer-message');

    function selectAgendaItem(item) {
        if (!item) return;

        agendaItems.forEach(function (agendaItem) {
            const selected = agendaItem === item;

            agendaItem.classList.toggle('agenda-current', selected);
            agendaItem.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });

        const order = item.dataset.agendaOrder;
        const title = item.dataset.agendaTitle;
        const documentUrl = item.dataset.documentUrl;

        selectedAgendaInfo.style.display = 'block';
        selectedAgendaTitle.textContent = 'Item ' + order + ' - ' + title;

        if (documentUrl && documentUrl.trim() !== '') {
            pdfViewer.src = documentUrl;

            documentFrame.style.setProperty('display', 'block', 'important');
            noDocument.style.setProperty('display', 'none', 'important');
        } else {
            pdfViewer.src = 'about:blank';

            documentFrame.style.setProperty('display', 'none', 'important');
            noDocument.style.setProperty('display', 'flex', 'important');

            viewerMessage.textContent =
                'No document attached to this agenda item.';
        }
    }

    // Click and keyboard selection.
    agendaItems.forEach(function (item) {

        item.addEventListener('click', function (event) {

            // Do not interfere with the separate View Referral link.
            if (event.target.closest('a')) {
                return;
            }

            selectAgendaItem(item);
        });

        item.addEventListener('keydown', function (event) {

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectAgendaItem(item);
            }

        });

    });

    // Initially select the current agenda item, if one exists.
    const initialAgendaItem = document.querySelector(
        '[data-agenda-item][aria-pressed="true"]'
    );

    if (initialAgendaItem) {
        selectAgendaItem(initialAgendaItem);
    }

    if (!container) {
        return;
    }

    const stateUrl = container.dataset.stateUrl;

    let previousState = null;
    let isChecking = false;

    async function refreshMeetingState() {
        if (isChecking) {
            return;
        }

        isChecking = true;

        try {
            const response = await fetch(stateUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if (response.status === 401 || response.status === 403) {
                window.location.reload();
                return;
            }

            if (!response.ok) {
                throw new Error('Unable to retrieve meeting state.');
            }

            const data = await response.json();

            const currentState = JSON.stringify({
                meeting: data.meeting,
                quorum: data.quorum,
                attendance: data.attendance,
                agenda_items: data.agenda_items
            });

            document.getElementById('last-updated').textContent =
                'Last checked: ' + new Date().toLocaleTimeString();

            if (previousState === null) {
                previousState = currentState;
                return;
            }

            if (previousState !== currentState) {
                window.location.reload();
                return;
            }

        } catch (error) {
            console.error('Live meeting update:', error);

            document.getElementById('last-updated').textContent =
                'Live update temporarily unavailable';

        } finally {
            isChecking = false;
        }
    }

    refreshMeetingState();

    // Check for meeting updates every 10 seconds.
    setInterval(refreshMeetingState, 10000);

})();
</script>

</body>
</html>
