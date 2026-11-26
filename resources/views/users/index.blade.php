@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary"
                     style="width:48px;height:48px;">
                    <i class="fas fa-users-cog fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-1">User Management</h3>
                    <p class="text-muted mb-0">Manage CMS accounts, access roles, and credentials.</p>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-primary px-4 py-2 shadow-sm"
                data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-2"></i> Add User
        </button>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="fw-semibold mb-1">
                <i class="fas fa-exclamation-circle me-2"></i>Please check the following:
            </div>
            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Card --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;">
                        <i class="fas fa-users fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small mb-1">Users on This Page</div>
                        <div class="fs-3 fw-bold text-dark">{{ $users->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 px-4 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">CMS Users</h5>
                    <p class="text-muted small mb-0">View and manage registered system accounts.</p>
                </div>

                <div class="input-group" style="max-width:340px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="search" id="userSearch" class="form-control bg-light border-start-0"
                           placeholder="Search this page..." aria-label="Search users">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-uppercase small fw-semibold text-secondary">User</th>
                        <th class="px-4 py-3 text-uppercase small fw-semibold text-secondary">Role</th>
                        <th class="px-4 py-3 text-uppercase small fw-semibold text-secondary">Date Created</th>
                        <th class="px-4 py-3 text-uppercase small fw-semibold text-secondary text-end"
                            style="width:120px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold">
                                        {{ strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark user-name">{{ $user->name }}</div>
                                        <div class="text-muted small user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                @if ($user->role)
                                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        {{ $user->role->display_name }}
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-light text-secondary border px-3 py-2">
                                        No Role Assigned
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-dark small">
                                    {{ $user->created_at?->format('M d, Y') ?? '—' }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ $user->created_at?->format('h:i A') ?? '' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-end">
                                <button type="button"
                                        class="btn btn-light border btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-role-id="{{ $user->role_id }}"
                                        title="Edit user">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-users text-muted" style="font-size:2.5rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No users found</h6>
                                <p class="text-muted small mb-3">Add a user account to get started.</p>
                                <button type="button" class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-user-plus me-1"></i> Add User
                                </button>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="noSearchResults" style="display:none;">
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="fas fa-search me-2"></i>No users match your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer bg-white border-0 border-top px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1"
     aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold" id="addUserModalLabel">Add User</h5>
                    <p class="text-muted small mb-0">Create a new CMS user account.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="add_name" class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" id="add_name" class="form-control"
                               value="{{ old('name') }}" placeholder="Enter full name" required>
                    </div>

                    <div class="mb-3">
                        <label for="add_email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" id="add_email" class="form-control"
                               value="{{ old('email') }}" placeholder="name@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="add_role_id" class="form-label fw-semibold">User Role</label>
                        <select name="role_id" id="add_role_id" class="form-select">
                            <option value="">— No role —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label for="add_password" class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="add_password"
                               class="form-control" required minlength="8"
                               autocomplete="new-password" placeholder="Minimum 8 characters">
                    </div>

                    <div class="mb-0">
                        <label for="add_password_confirmation" class="form-label fw-semibold">
                            Confirm Password
                        </label>
                        <input type="password" name="password_confirmation"
                               id="add_password_confirmation" class="form-control"
                               required minlength="8" autocomplete="new-password"
                               placeholder="Re-enter password">
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1"
     aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit User</h5>
                    <p class="text-muted small mb-0">Update account details and access role.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="editUserForm" action="">
                @csrf
                @method('PUT')

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_role_id" class="form-label fw-semibold">User Role</label>
                        <select name="role_id" id="edit_role_id" class="form-select">
                            <option value="">— No role —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-light border small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Leave the password fields blank if you do not want to change the current password.
                    </div>

                    <div class="mb-3">
                        <label for="edit_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" id="edit_password"
                               class="form-control" minlength="8"
                               autocomplete="new-password" placeholder="Leave blank to keep current">
                    </div>

                    <div class="mb-0">
                        <label for="edit_password_confirmation" class="form-label fw-semibold">
                            Confirm New Password
                        </label>
                        <input type="password" name="password_confirmation"
                               id="edit_password_confirmation" class="form-control"
                               minlength="8" autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .user-avatar {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        font-size: 1rem;
    }

    #usersTable thead th {
        white-space: nowrap;
        letter-spacing: .035em;
    }

    #usersTable tbody tr:last-child td {
        border-bottom: 0;
    }

    .modal-content {
        overflow: hidden;
    }

    .form-control,
    .form-select {
        padding-top: .65rem;
        padding-bottom: .65rem;
        border-radius: .5rem;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editUserModal');
    const editForm = document.getElementById('editUserForm');

    if (editModal && editForm) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) {
                return;
            }

            const id = button.getAttribute('data-user-id');
            const name = button.getAttribute('data-user-name') || '';
            const email = button.getAttribute('data-user-email') || '';
            const roleId = button.getAttribute('data-user-role-id') || '';

            editForm.action = @json(url('/users')) + '/' + encodeURIComponent(id);

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role_id').value = roleId;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
        });
    }

    // Client-side search for users on the current pagination page.
    const searchInput = document.getElementById('userSearch');
    const table = document.getElementById('usersTable');

    if (searchInput && table) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr:not(#noSearchResults)');
            let visibleCount = 0;

            rows.forEach(function (row) {
                // Preserve the original empty-state row.
                if (row.querySelector('td[colspan]')) {
                    return;
                }

                const matches = row.textContent.toLowerCase().includes(query);
                row.style.display = matches ? '' : 'none';

                if (matches) {
                    visibleCount++;
                }
            });

            const noResults = document.getElementById('noSearchResults');
            if (noResults) {
                noResults.style.display = query !== '' && visibleCount === 0 ? '' : 'none';
            }
        });
    }
});
</script>
@endsection