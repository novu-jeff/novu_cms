@extends('layouts.app')

@section('content')

<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Member Documents</h2>
            <p class="text-muted mb-0">Review forwarded and approved documents</p>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="d-flex flex-wrap gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       name="search"
                       value="{{ old('search', $search ?? request('search')) }}"
                       class="form-control border-start-0"
                       placeholder="Search by title or member name">
            </div>

            <select name="status" class="form-select w-auto">
                <option value="">All Statuses</option>
                <option value="forwarded" {{ (request('status', $status ?? '') === 'forwarded') ? 'selected' : '' }}>
                    Forwarded
                </option>
                <option value="approved" {{ (request('status', $status ?? '') === 'approved') ? 'selected' : '' }}>
                    Approved
                </option>
            </select>

            <button type="submit" class="btn btn-primary">
                Filter
            </button>

            @if(request('search') || request('status'))
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Title</th>
                            <th class="text-nowrap">Member</th>
                            <th class="text-nowrap">Date</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-end text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $doc->title }}</td>
                                <td>{{ optional($doc->member)->name ?? 'Unknown Member' }}</td>
                                <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($doc->status === 'approved')
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            Approved
                                        </span>
                                    @elseif($doc->status === 'forwarded')
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                                            Forwarded
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">

                                    <!-- View -->
                                    <a href="{{ env('LIS_STORAGE_URL').'/'.$doc->file_path }}"
                                       class="btn btn-sm btn-outline-secondary me-1"
                                       target="_blank"
                                       title="View Document">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($doc->status === 'forwarded')

                                        <!-- Approve -->
                                        <button class="btn btn-sm btn-outline-success me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approveModal{{ $doc->id }}"
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>

                                        <!-- Reject -->
                                        <button class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $doc->id }}"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>

                                    @elseif($doc->status === 'approved')

                                        @php
                                            $currentSession = optional(optional($doc->sessionDocument)->session);
                                        @endphp

                                        @if($currentSession)
                                            <span class="me-2 small text-muted">
                                                Session: {{ $currentSession->title }} ({{ $currentSession->session_date }})
                                            </span>
                                        @endif

                                        <!-- Edit Session -->
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editSessionModal{{ $doc->id }}"
                                                title="Change Session Meeting">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                    @endif

                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST"
                                              action="{{ route('secretary.documents.approve',$doc->id) }}">
                                            @csrf

                                            <div class="modal-header border-0">
                                                <h5 class="modal-title">Approve Document</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Select Session</label>
                                                    <select name="session_id" class="form-select" required>
                                                        @foreach(\App\Models\SessionMeeting::where('status','upcoming')->get() as $session)
                                                            <option value="{{ $session->id }}">
                                                                {{ $session->title }} - {{ $session->session_date }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button class="btn btn-primary" type="submit">
                                                    Approve Document
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Session Modal -->
                            <div class="modal fade" id="editSessionModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST"
                                              action="{{ route('secretary.documents.updateSession', $doc->id) }}">
                                            @csrf

                                            <div class="modal-header border-0">
                                                <h5 class="modal-title">Change Session Meeting</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Select Session</label>
                                                    <select name="session_id" class="form-select" required>
                                                        @php
                                                            $upcomingSessions = \App\Models\SessionMeeting::where('status','upcoming')->get();
                                                            $selectedSessionId = optional(optional($doc->sessionDocument)->session)->id;
                                                        @endphp
                                                        @foreach($upcomingSessions as $session)
                                                            <option value="{{ $session->id }}"
                                                                {{ (string)$session->id === (string)$selectedSessionId ? 'selected' : '' }}>
                                                                {{ $session->title }} - {{ $session->session_date }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button class="btn btn-primary" type="submit">
                                                    Update Session
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST"
                                              action="{{ route('secretary.documents.reject',$doc->id) }}">
                                            @csrf

                                            <div class="modal-header border-0">
                                                <h5 class="modal-title">Reject Document</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Remarks</label>
                                                    <textarea name="remarks"
                                                              class="form-control"
                                                              rows="3"
                                                              placeholder="Provide a brief reason for rejection"
                                                              required></textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button class="btn btn-danger" type="submit">
                                                    Reject Document
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No forwarded or approved documents found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection