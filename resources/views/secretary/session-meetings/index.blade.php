@extends('layouts.app')

@section('styles')

<style>

.session-management{--sm-border:#e2e8f0;--sm-muted:#64748b;--sm-ink:#0f172a;color:var(--sm-ink)}

.session-management .page-header{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1rem;padding-bottom:1.25rem;margin-bottom:1.5rem;border-bottom:1px solid var(--sm-border)}

.session-management .eyebrow{margin-bottom:.35rem;color:var(--sm-muted);font-size:.72rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase}

.session-management .page-title{margin:0 0 .35rem;font-size:clamp(1.5rem,2vw,1.95rem);font-weight:750;letter-spacing:-.035em;color:#0f172a}

.session-management .page-description{margin:0;color:var(--sm-muted);font-size:.925rem}

.session-management .summary-card,.session-management .table-card{height:100%;overflow:hidden;border:1px solid var(--sm-border);border-radius:.85rem;background:#fff;box-shadow:0 4px 18px rgba(15,23,42,.035)}

.session-management .summary-card{padding:1.1rem 1.2rem;transition:transform .18s ease,box-shadow .18s ease}
.session-management .summary-card:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(15,23,42,.07)}

.session-management .summary-label{color:var(--sm-muted);font-size:.8rem;font-weight:600}

.session-management .summary-value{margin-top:.45rem;font-size:1.75rem;font-weight:750;letter-spacing:-.04em;line-height:1.1}

.session-management .summary-icon{display:inline-flex;align-items:center;justify-content:center;width:2.4rem;height:2.4rem;border-radius:.7rem;background:#f1f5f9;color:#334155;font-size:1rem}

.session-management .table-card-header{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;padding:1.1rem 1.25rem;border-bottom:1px solid var(--sm-border);background:linear-gradient(180deg,#fff 0%,#fbfcfe 100%)}

.session-management .section-title{margin:0;font-size:1rem;font-weight:700}

.session-management .section-subtitle{margin:.25rem 0 0;color:var(--sm-muted);font-size:.825rem}

.session-management .session-table{margin:0}

.session-management .session-table thead th{padding:.85rem 1rem;color:#475569;background:#f8fafc;border-bottom:1px solid var(--sm-border);font-size:.7rem;font-weight:700;letter-spacing:.055em;text-transform:uppercase;white-space:nowrap}

.session-management .session-table tbody td{padding:1rem;border-color:#f1f5f9;vertical-align:middle;font-size:.875rem}

.session-management .session-table tbody tr:last-child td{border-bottom:0}

.session-management .session-table tbody tr:hover{background:#fafcff}

.session-management .session-title{font-weight:650;color:#0f172a;overflow-wrap:anywhere}

.session-management .status-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.38rem .65rem;border:1px solid transparent;border-radius:999px;font-size:.72rem;font-weight:650;white-space:nowrap}

.session-management .status-upcoming{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}

.session-management .status-ongoing{color:#92400e;background:#fffbeb;border-color:#fde68a}

.session-management .status-completed{color:#047857;background:#ecfdf5;border-color:#a7f3d0}

.session-management .action-group{display:flex;justify-content:flex-end;align-items:center;flex-wrap:wrap;gap:.35rem}

.session-management .action-group .btn{min-width:36px;min-height:35px;border-radius:.55rem;transition:all .15s ease}
.session-management .action-group .btn:hover{transform:translateY(-1px)}

.session-management .empty-state{padding:3rem 1rem!important;text-align:center;color:var(--sm-muted)}

.session-management .empty-icon{display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;margin-bottom:.85rem;border-radius:.85rem;background:#f1f5f9;color:#64748b;font-size:1.2rem}

.session-management .modal-content{overflow:hidden;border:1px solid rgba(226,232,240,.9);border-radius:.9rem;box-shadow:0 20px 50px rgba(15,23,42,.16)}

.session-management .modal-header,.session-management .modal-footer{padding:1rem 1.25rem;border-color:#eef2f7}

.session-management .modal-body{padding:1.25rem}

.session-management .modal-title{font-size:1.05rem;font-weight:700}

.session-management .form-control,.session-management .form-select{min-height:42px;border-color:#dbe2ea;border-radius:.55rem}

.session-management .btn{border-radius:.55rem;font-weight:600}
.session-management .form-control:focus,.session-management .form-select:focus{border-color:#93b4ff;box-shadow:0 0 0 .22rem rgba(37,99,235,.12)}
.session-management .modal-header{background:#fbfcfe}
.session-management .summary-value{font-variant-numeric:tabular-nums}

@media(max-width:767.98px){.session-management .page-header{align-items:stretch}.session-management .table-card-header{padding:1rem}.session-management .session-table thead th,.session-management .session-table tbody td{padding:.8rem}}

</style>

@endsection

@section('content')

@php

    $sessionCollection = collect($sessions ?? []);

    $totalSessions = $sessionCollection->count();

    $upcomingCount = $sessionCollection->where('status', 'upcoming')->count();

    $ongoingCount = $sessionCollection->where('status', 'ongoing')->count();

    $completedCount = $sessionCollection->where('status', 'completed')->count();

@endphp

<div class="container-fluid py-4 py-lg-5 session-management">

    <header class="page-header">

        <div>

            <div class="eyebrow">Paperless Session Management System</div>

            <h1 class="page-title">Session Management</h1>

            <p class="page-description">Schedule council sessions, manage session details, and prepare agendas and official records.</p>

        </div>

        <button type="button" class="btn btn-primary px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal">

            <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>New Session

        </button>

    </header>

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3"><div class="summary-card">

            <div class="d-flex align-items-center justify-content-between gap-2"><div class="summary-label">Total sessions</div><span class="summary-icon"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span></div>

            <div class="summary-value">{{ $totalSessions }}</div>

        </div></div>

        <div class="col-6 col-xl-3"><div class="summary-card">

            <div class="d-flex align-items-center justify-content-between gap-2"><div class="summary-label">Upcoming</div><span class="summary-icon"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i></span></div>

            <div class="summary-value">{{ $upcomingCount }}</div>

        </div></div>

        <div class="col-6 col-xl-3"><div class="summary-card">

            <div class="d-flex align-items-center justify-content-between gap-2"><div class="summary-label">Ongoing</div><span class="summary-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span></div>

            <div class="summary-value">{{ $ongoingCount }}</div>

        </div></div>

        <div class="col-6 col-xl-3"><div class="summary-card">

            <div class="d-flex align-items-center justify-content-between gap-2"><div class="summary-label">Completed</div><span class="summary-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span></div>

            <div class="summary-value">{{ $completedCount }}</div>

        </div></div>

    </div>

    <section class="table-card">

        <div class="table-card-header">

            <div><h2 class="section-title">Session Schedule</h2><p class="section-subtitle">Manage sessions and access their agendas, packets, minutes, and remarks.</p></div>

            <span class="badge text-bg-light border">{{ $totalSessions }} {{ \Illuminate\Support\Str::plural('session', $totalSessions) }}</span>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle session-table">

                <thead><tr>

                    <th scope="col" class="ps-lg-4">Session</th>

                    <th scope="col">Type / Number</th>

                    <th scope="col">Date &amp; Time</th>

                    <th scope="col">Venue</th>

                    <th scope="col">Status</th>

                    <th scope="col">Agenda</th>

                    <th scope="col" class="text-end pe-lg-4">Actions</th>

                </tr></thead>

                <tbody>

                @forelse($sessions as $session)

                    @php

                        $statusClass = match($session->status) {

                            'upcoming' => 'status-upcoming',

                            'ongoing' => 'status-ongoing',

                            'completed' => 'status-completed',

                            default => 'status-upcoming',

                        };

                       
                        $agendaCount = $session->agenda_count ?? $session->documents_count ?? 0;

                        $sessionTypeLabel = ucfirst($session->session_type ?? 'regular');

                    @endphp

                    <tr>

                        <td class="ps-lg-4">

                            <div class="session-title">{{ $session->title }}</div>

                            @if($session->legislative_term)<div class="small text-secondary mt-1">Legislative term: {{ $session->legislative_term }}</div>@endif

                            @if($session->presiding_officer)<div class="small text-secondary">Presiding: {{ $session->presiding_officer }}</div>@endif

                        </td>

                        <td>

                            <div class="fw-semibold">{{ $sessionTypeLabel }}</div>

                            <div class="small text-secondary">{{ $session->session_number ? 'No. '.$session->session_number : 'Number not set' }}</div>

                        </td>

                        <td class="text-nowrap">

                            <div>{{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('M d, Y') : 'Date not set' }}</div>

                            <div class="small text-secondary">

                                @if($session->start_time)

                                    {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}@if($session->end_time) – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}@endif

                                @else

                                    Time not set

                                @endif

                            </div>

                        </td>

                        <td>{{ $session->venue ?: 'Not set' }}</td>

                        <td><span class="status-badge {{ $statusClass }}">{{ ucfirst($session->status ?? 'Unknown') }}</span></td>

                        <td><span class="badge text-bg-light border">{{ $agendaCount }}</span></td>

                        <td class="text-end pe-lg-4"><div class="action-group">
                            @if($session->status !== 'completed')
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editSessionModal"

                                data-update-url="{{ route('session-meetings.update', $session->id) }}"

                                data-title="{{ $session->title }}"

                                data-session-type="{{ $session->session_type ?? 'regular' }}"

                                data-session-date="{{ $session->session_date }}"

                                data-start-time="{{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '' }}"

                                data-end-time="{{ $session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '' }}"

                                data-venue="{{ $session->venue }}"

                                data-session-number="{{ $session->session_number }}"

                                data-legislative-term="{{ $session->legislative_term }}"

                                data-presiding-officer="{{ $session->presiding_officer }}"

                                data-status="{{ $session->status }}"

                                title="Edit session" aria-label="Edit {{ $session->title }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                            

                            <a href="{{ route('secretary.session-meetings.attendance', $session->id) }}" class="btn btn-sm btn-outline-dark" title="Attendance and quorum" aria-label="Attendance and quorum for {{ $session->title }}"><i class="fa-solid fa-user-check" aria-hidden="true"></i></a>
                            @endif

                            @if($agendaCount > 0 && $session->status !== 'completed')

                            <a href="{{ route('sessions.recordings.index', $session->id) }}"
                                class="btn btn-sm btn-outline-danger"
                                title="Session Recordings"
                                aria-label="Session Recordings for {{ $session->title }}">
                                    <i class="fa-solid fa-video" aria-hidden="true"></i>
                                </a>
                              @endif

                            <a href="{{ route('secretary.session-control.show', $session->id) }}"

                            class="btn btn-sm btn-outline-primary"

                            title="Session Control"

                            aria-label="Session Control for {{ $session->title }}">

                                <i class="fa-solid fa-sliders" aria-hidden="true"></i>

                            </a>

                            @if($agendaCount > 0 && $session->status !== 'completed')

                                <a href="{{ route('secretary.session.agenda', $session->id) }}" class="btn btn-sm btn-outline-primary" title="Manage agenda" aria-label="Manage agenda for {{ $session->title }}"><i class="fa-solid fa-list-check" aria-hidden="true"></i></a>

                            @endif

                            @if($session->packet_file)

                                <a href="{{ asset('storage/'.$session->packet_file) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success" title="View session packet" aria-label="View packet for {{ $session->title }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i></a>

                            @endif

                            @if($agendaCount === 0 && $session->status !== 'completed')

                                <form class="d-inline" method="POST" action="{{ route('session-meetings.destroy', $session->id) }}" data-session-title="{{ $session->title }}" onsubmit="return queueSessionDelete(this, event)">

                                    @csrf @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete session" aria-label="Delete {{ $session->title }}"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>

                                </form>

                            @endif

                                <a href="{{ route('secretary.minutes.index', $session->id) }}" class="btn btn-sm btn-outline-secondary" title="Manage minutes" aria-label="Manage minutes for {{ $session->title }}"><i class="fa-solid fa-file-pen" aria-hidden="true"></i></a>

                            <a href="{{ route('secretary.remarks.index', $session->id) }}" class="btn btn-sm btn-outline-info" title="Review remarks" aria-label="Review remarks for {{ $session->title }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>

                        </div></td>

                    </tr>

                @empty

                    <tr><td colspan="7" class="empty-state">

                        <div class="empty-icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></div>

                        <div class="fw-semibold text-dark mb-1">No sessions created yet</div>

                        <div class="small mb-3">Create a session to start organizing documents and preparing its agenda.</div>

                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Create first session</button>

                    </td></tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </section>

</div>

<div class="modal fade session-management" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">

        <div class="modal-header"><h2 class="modal-title" id="createSessionModalLabel">Create Session</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>

        <form method="POST" action="{{ route('session-meetings.store') }}">

            @csrf

            <div class="modal-body">

                @if ($errors->any() && old('title') !== null)
                    <div class="alert alert-danger" role="alert">
                        <div class="fw-semibold mb-1">Please correct the following before creating the session:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3"><label for="create_title" class="form-label fw-semibold">Session title</label><input type="text" name="title" id="create_title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Regular Council Session" required></div>

                <div class="row g-3 mb-3">

                    <div class="col-md-6"><label for="create_session_type" class="form-label fw-semibold">Session type</label><select name="session_type" id="create_session_type" class="form-select" required><option value="regular" @selected(old('session_type', 'regular') === 'regular')>Regular</option><option value="special" @selected(old('session_type') === 'special')>Special</option><option value="committee" @selected(old('session_type') === 'committee')>Committee</option></select></div>

                    <div class="col-md-6"><label for="create_session_number" class="form-label fw-semibold">Session number</label><input type="number" min="1" max="65535" name="session_number" id="create_session_number" class="form-control" value="{{ old('session_number') }}" placeholder="Optional"></div>

                </div>

                <div class="row g-3 mb-3">

                    <div class="col-md-6"><label for="create_session_date" class="form-label fw-semibold">Session date</label><input type="date" name="session_date" id="create_session_date" class="form-control" value="{{ old('session_date') }}" required></div>

                    <div class="col-md-3"><label for="create_start_time" class="form-label fw-semibold">Start time</label><input type="time" name="start_time" id="create_start_time" class="form-control" value="{{ old('start_time') }}"></div>

                    <div class="col-md-3"><label for="create_end_time" class="form-label fw-semibold">End time</label><input type="time" name="end_time" id="create_end_time" class="form-control" value="{{ old('end_time') }}"></div>

                </div>

                <div class="mb-3"><label for="create_venue" class="form-label fw-semibold">Venue</label><input type="text" name="venue" id="create_venue" class="form-control" value="{{ old('venue') }}" maxlength="255" placeholder="e.g. Sangguniang Panlungsod Session Hall"></div>

                <div class="row g-3">

                    <div class="col-md-6"><label for="create_legislative_term" class="form-label fw-semibold">Legislative term</label><input type="text" name="legislative_term" id="create_legislative_term" class="form-control" value="{{ old('legislative_term') }}" maxlength="50" placeholder="e.g. 2025–2028"></div>

                    <div class="col-md-6"><label for="create_presiding_officer" class="form-label fw-semibold">Presiding officer</label><input type="text" name="presiding_officer" id="create_presiding_officer" class="form-control" value="{{ old('presiding_officer') }}" maxlength="150" placeholder="Name or official title"></div>

                </div>

            </div>

            <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-calendar-plus me-1" aria-hidden="true"></i>Create Session</button></div>

        </form>

    </div></div>

</div>

<div class="modal fade session-management" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">

        <div class="modal-header"><h2 class="modal-title" id="editSessionModalLabel">Edit Session</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>

        <form id="editSessionForm" method="POST" action="">

            @csrf @method('PUT')

            <div class="modal-body">

                <div class="mb-3"><label for="edit_title" class="form-label fw-semibold">Session title</label><input type="text" name="title" id="edit_title" class="form-control" required></div>

                <div class="row g-3 mb-3">

                    <div class="col-md-6"><label for="edit_session_type" class="form-label fw-semibold">Session type</label><select name="session_type" id="edit_session_type" class="form-select" required><option value="regular">Regular</option><option value="special">Special</option><option value="committee">Committee</option></select></div>

                    <div class="col-md-6"><label for="edit_session_number" class="form-label fw-semibold">Session number</label><input type="number" min="1" max="65535" name="session_number" id="edit_session_number" class="form-control"></div>

                </div>

                <div class="row g-3 mb-3">

                    <div class="col-md-6"><label for="edit_session_date" class="form-label fw-semibold">Session date</label><input type="date" name="session_date" id="edit_session_date" class="form-control" required></div>

                    <div class="col-md-3"><label for="edit_start_time" class="form-label fw-semibold">Start time</label><input type="time" name="start_time" id="edit_start_time" class="form-control"></div>

                    <div class="col-md-3"><label for="edit_end_time" class="form-label fw-semibold">End time</label><input type="time" name="end_time" id="edit_end_time" class="form-control"></div>

                </div>

                <div class="mb-3"><label for="edit_venue" class="form-label fw-semibold">Venue</label><input type="text" name="venue" id="edit_venue" class="form-control" maxlength="255"></div>

                <div class="row g-3 mb-3">

                    <div class="col-md-6"><label for="edit_legislative_term" class="form-label fw-semibold">Legislative term</label><input type="text" name="legislative_term" id="edit_legislative_term" class="form-control" maxlength="50" placeholder="e.g. 2025–2028"></div>

                    <div class="col-md-6"><label for="edit_presiding_officer" class="form-label fw-semibold">Presiding officer</label><input type="text" name="presiding_officer" id="edit_presiding_officer" class="form-control" maxlength="150"></div>

                </div>

                <div><label for="edit_status" class="form-label fw-semibold">Status</label><select name="status" id="edit_status" class="form-select" required>

                    <option value="upcoming">Upcoming</option><option value="ongoing">Ongoing</option><option value="completed">Completed</option>

                </select></div>

            </div>

            <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>

        </form>

    </div></div>

</div>

<div class="modal fade session-management" id="confirmDeleteSessionModal" tabindex="-1" aria-labelledby="confirmDeleteSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="confirmDeleteSessionModalLabel">Delete session?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="empty-icon mx-auto mb-3"><i class="fa-solid fa-trash-can text-danger" aria-hidden="true"></i></div>
                <p class="mb-1">You are about to delete:</p>
                <p class="fw-semibold text-dark mb-2" id="deleteSessionName"></p>
                <p class="small text-secondary mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSessionButton"><i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i>Delete session</button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))

<script>document.addEventListener('DOMContentLoaded',function(){if(typeof toastr!=='undefined')toastr.success(@json(session('success')));});</script>

@endif

@if(session('error'))

<script>document.addEventListener('DOMContentLoaded',function(){if(typeof toastr!=='undefined')toastr.error(@json(session('error')));});</script>

@endif

<script>

let pendingSessionDeleteForm = null;

function queueSessionDelete(form, event) {
    if (event) event.preventDefault();
    pendingSessionDeleteForm = form;

    const title = form.getAttribute('data-session-title') || 'this session';
    const titleTarget = document.getElementById('deleteSessionName');
    if (titleTarget) titleTarget.textContent = title;

    const modalElement = document.getElementById('confirmDeleteSessionModal');
    if (modalElement && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    } else if (window.confirm('Delete session "' + title + '"?')) {
        form.submit();
    }
    return false;
}

document.addEventListener('DOMContentLoaded',function(){
    // Reopen the create modal after a validation redirect so errors are visible.
    @if ($errors->any() && old('title') !== null)
        const createModalElement = document.getElementById('createSessionModal');
        if (createModalElement && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(createModalElement).show();
        }
    @endif

    const deleteConfirmButton = document.getElementById('confirmDeleteSessionButton');
    const deleteModalElement = document.getElementById('confirmDeleteSessionModal');

    if (deleteConfirmButton) {
        deleteConfirmButton.addEventListener('click', function () {
            if (!pendingSessionDeleteForm) return;
            deleteConfirmButton.disabled = true;
            deleteConfirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Deleting...';
            pendingSessionDeleteForm.submit();
        });
    }

    if (deleteModalElement) {
        deleteModalElement.addEventListener('hidden.bs.modal', function () {
            pendingSessionDeleteForm = null;
            if (deleteConfirmButton) {
                deleteConfirmButton.disabled = false;
                deleteConfirmButton.innerHTML = '<i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i>Delete session';
            }
        });
    }

    const modal=document.getElementById('editSessionModal');

    if(!modal)return;

    modal.addEventListener('show.bs.modal',function(event){

        const button=event.relatedTarget;

        if(!button)return;

        const form=document.getElementById('editSessionForm');

        if(!form)return;

        form.action=button.getAttribute('data-update-url')||'';

        const fields = {

            edit_title: 'data-title',

            edit_session_type: 'data-session-type',

            edit_session_date: 'data-session-date',

            edit_start_time: 'data-start-time',

            edit_end_time: 'data-end-time',

            edit_venue: 'data-venue',

            edit_session_number: 'data-session-number',

            edit_legislative_term: 'data-legislative-term',

            edit_presiding_officer: 'data-presiding-officer',

            edit_status: 'data-status'

        };

        Object.entries(fields).forEach(([id, attribute]) => {

            const field = document.getElementById(id);

            if (field) field.value = button.getAttribute(attribute) || (id === 'edit_session_type' ? 'regular' : '');

        });

    });

});

</script>

@endsection
