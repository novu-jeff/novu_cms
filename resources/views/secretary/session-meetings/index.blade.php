@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 fw-semibold text-dark mb-1">Session Meetings</h1>
            <p class="text-muted small mb-0">Manage session meetings and build agendas</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal">
            <i class="fas fa-plus me-2"></i>New Session
        </button>
    </div>

    {{-- Table card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 fw-semibold text-uppercase small text-secondary ps-4">Title</th>
                            <th class="border-0 fw-semibold text-uppercase small text-secondary">Date</th>
                            <th class="border-0 fw-semibold text-uppercase small text-secondary">Status</th>
                            <th class="border-0 fw-semibold text-uppercase small text-secondary text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td class="ps-4">{{ $session->title }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $statusClass = match($session->status) {
                                        'completed' => 'success',
                                        'cancelled' => 'secondary',
                                        default => 'info',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($session->status) }}</span>
                            </td>
                            <td class="text-end pe-4">
                                @if($session->status != 'completed')
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSessionModal"
                                        data-update-url="{{ route('session-meetings.update', $session->id) }}"
                                        data-title="{{ e($session->title) }}"
                                        data-session-date="{{ $session->session_date }}"
                                        data-status="{{ $session->status }}"
                                        placeholder="Edit">
                                    <i class="fas fa-pen me-1"></i>
                                </button>
                                @endif
                                @if($session->documents_count > 0 && $session->status != 'completed')
                                    <a href="{{ route('secretary.session.agenda', $session->id) }}"
                                       class="btn btn-sm btn-primary"
                                       placeholder="Agenda Builder">
                                        <i class="fas fa-list me-1"></i>
                                    </a>
                                @endif
                                @if($session->packet_file)

                                    <a href="{{ asset('storage/'.$session->packet_file) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-success"
                                    placeholder="View Packet">
                                    <i class="fas fa-file-pdf me-1"></i>

                                    </a>

                                @endif
                                @if($session->documents_count === 0)
                                    <form class="d-inline" method="POST" action="{{ route('session-meetings.destroy', $session->id) }}" data-session-title="{{ e($session->title) }}" onsubmit="return confirmDeleteSession(this, event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" placeholder="Delete">
                                            <i class="fas fa-trash me-1"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($session->documents_count > 0 && $session->status != 'completed')
                                    <a href="{{ route('secretary.minutes.index', $session->id) }}"
                                       class="btn btn-sm btn-outline-primary" placeholder="Encode Minutes">
                                        <i class="fa-solid fa-file-pen"></i>
                                    </a>
                                    @endif

                                <a href="{{ route('secretary.remarks.index',$session->id) }}"
                                    class="btn btn-sm btn-info"
                                    placeholder="Review Remarks">
                                    <i class="fas fa-eye me-1"></i>
                                    </a>   
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-day fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0">No session meetings yet.</p>
                                <button type="button" class="btn btn-link btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#createSessionModal">
                                    Create your first session
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Session Modal --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="createSessionModalLabel">New Session Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('session-meetings.store') }}">
                @csrf
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="create_title" class="form-label small fw-medium text-secondary">Title</label>
                        <input type="text" name="title" id="create_title" class="form-control" placeholder="e.g. Regular Council Meeting" required>
                    </div>
                    <div class="mb-0">
                        <label for="create_session_date" class="form-label small fw-medium text-secondary">Session Date</label>
                        <input type="date" name="session_date" id="create_session_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Session Modal --}}
<div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="editSessionModalLabel">Edit Session Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSessionForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label small fw-medium text-secondary">Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_session_date" class="form-label small fw-medium text-secondary">Session Date</label>
                        <input type="date" name="session_date" id="edit_session_date" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label for="edit_status" class="form-label small fw-medium text-secondary">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toastr.success("{{ session('success') }}");
        });
    </script>
@endif
@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toastr.error("{{ session('error') }}");
        });
    </script>
@endif

<script>
    function confirmDeleteSession(form, e) {
        var title = form.getAttribute('data-session-title') || 'this session';
        if (typeof Swal !== 'undefined') {
            if (e) e.preventDefault();
            Swal.fire({
                title: 'Delete session?',
                text: 'Session "' + title + '" will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete'
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
        return confirm('Delete session "' + title + '"?');
    }
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = document.getElementById('editSessionModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                if (!button) return;

                var title = button.getAttribute('data-title') || '';
                var sessionDate = button.getAttribute('data-session-date') || '';
                var status = (button.getAttribute('data-status') || '').trim();

                var form = document.getElementById('editSessionForm');
                if (!form) return;

                form.action = button.getAttribute('data-update-url') || '';

                document.getElementById('edit_title').value = title;
                document.getElementById('edit_session_date').value = sessionDate;

                var statusSelect = document.getElementById('edit_status');
                if (statusSelect) {
                    // Try direct value match first
                    statusSelect.value = status;

                    // If no option matched (e.g. whitespace or unexpected value), fall back
                    if (statusSelect.value !== status) {
                        Array.prototype.forEach.call(statusSelect.options, function(option) {
                            option.selected = option.value === status;
                        });
                    }
                }
            });
        }
    });
</script>
@endsection
