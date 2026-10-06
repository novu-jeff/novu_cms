@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-semibold text-dark">Users</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-1"></i> Add User
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-0 mb-0" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-0 mb-0">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 py-3 text-uppercase small fw-semibold text-secondary">Name</th>
                                    <th class="border-0 py-3 text-uppercase small fw-semibold text-secondary">Email</th>
                                    <th class="border-0 py-3 text-uppercase small fw-semibold text-secondary">Role</th>
                                    <th class="border-0 py-3 text-uppercase small fw-semibold text-secondary">Created</th>
                                    <th class="border-0 py-3 text-uppercase small fw-semibold text-secondary text-end" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="py-3">{{ $user->name }}</td>
                                        <td class="py-3">{{ $user->email }}</td>
                                        <td class="py-3">
                                            @if($user->role)
                                                <span class="badge bg-secondary">{{ $user->role->display_name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-muted small">{{ $user->created_at?->format('M d, Y') }}</td>
                                        <td class="py-3 text-end">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" data-user-role-id="{{ $user->role_id }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="card-footer bg-white border-0 border-top py-2">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="addUserModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Name</label>
                        <input type="text" name="name" id="add_name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_email" class="form-label">Email</label>
                        <input type="email" name="email" id="add_email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_role_id" class="form-label">Role</label>
                        <select name="role_id" id="add_role_id" class="form-select">
                            <option value="">— No role —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_password" class="form-label">Password</label>
                        <input type="password" name="password" id="add_password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-0">
                        <label for="add_password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="add_password_confirmation" class="form-control" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editUserForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role_id" class="form-label">Role</label>
                        <select name="role_id" id="edit_role_id" class="form-select">
                            <option value="">— No role —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password <span class="text-muted small">(leave blank to keep current)</span></label>
                        <input type="password" name="password" id="edit_password" class="form-control" minlength="8">
                    </div>
                    <div class="mb-0">
                        <label for="edit_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control" minlength="8">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editUserModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            var id = btn.getAttribute('data-user-id');
            var name = btn.getAttribute('data-user-name');
            var email = btn.getAttribute('data-user-email');
            var roleId = btn.getAttribute('data-user-role-id') || '';
            var form = document.getElementById('editUserForm');
            form.action = '{{ url("users") }}/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role_id').value = roleId;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
        });
    }
});
</script>
@endsection
