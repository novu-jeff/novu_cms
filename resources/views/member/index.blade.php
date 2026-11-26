
@extends('layouts.app')

@section('styles')
<style>
    .members-page {
        padding: 32px;
        max-width: 1800px;
        margin: 0 auto;
        color: #1e293b;
    }

    .members-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .members-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .members-title {
        margin: 0 0 8px;
        font-size: clamp(25px, 3vw, 32px);
        line-height: 1.2;
        font-weight: 750;
        letter-spacing: -0.8px;
        color: #172338;
    }

    .members-subtitle {
        margin: 0;
        font-size: 14px;
        color: #718096;
        line-height: 1.6;
    }

    .members-add-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 46px;
        padding: 0 20px;
        border: 0;
        border-radius: 10px;
        background: #315efb;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 5px 12px rgba(49, 94, 251, .18);
        transition: background .2s ease, transform .2s ease;
    }

    .members-add-btn:hover {
        background: #244bd6;
        color: #fff;
        transform: translateY(-1px);
    }

    .members-card {
        background: #fff;
        border: 1px solid #e6ebf2;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
        overflow: hidden;
    }

    .members-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 22px 24px;
        border-bottom: 1px solid #edf0f5;
    }

    .members-card-title {
        margin: 0 0 5px;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }

    .members-card-description {
        margin: 0;
        font-size: 13px;
        color: #8491a5;
    }

    .members-table-container {
        padding: 8px 24px 20px;
    }

    #myTable {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0 !important;
    }

    #myTable thead th {
        padding: 16px 14px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e9eef5;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #myTable tbody td {
        padding: 15px 14px;
        border-bottom: 1px solid #eef2f7;
        color: #334155;
        font-size: 13px;
        vertical-align: middle;
    }

    #myTable tbody tr:last-child td {
        border-bottom: 0;
    }

    #myTable tbody tr:hover td {
        background: #f8faff;
    }

    #myTable tbody td img {
        width: 46px;
        height: 46px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e8edf4;
        background: #f8fafc;
    }

    .members-table-container .dataTables_wrapper {
        padding-top: 14px;
    }

    .members-table-container .dataTables_wrapper .dataTables_length,
    .members-table-container .dataTables_wrapper .dataTables_filter {
        margin-bottom: 18px;
        color: #64748b;
        font-size: 13px;
    }

    .members-table-container .dataTables_wrapper .dataTables_length select,
    .members-table-container .dataTables_wrapper .dataTables_filter input {
        min-height: 38px;
        padding: 7px 12px;
        border: 1px solid #dce3ed;
        border-radius: 8px;
        background-color: #fff;
        color: #334155;
        outline: none;
        box-shadow: none;
    }

    .members-table-container .dataTables_wrapper .dataTables_filter input {
        min-width: 220px;
        margin-left: 8px;
    }

    .members-table-container .dataTables_wrapper .dataTables_length select:focus,
    .members-table-container .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #8da8ff;
        box-shadow: 0 0 0 3px rgba(49, 94, 251, .09);
    }

    .members-table-container .dataTables_wrapper .dataTables_info {
        padding-top: 18px;
        color: #8491a5;
        font-size: 12px;
    }

    .members-table-container .dataTables_wrapper .dataTables_paginate {
        padding-top: 12px;
    }

    .members-table-container .dataTables_wrapper .paginate_button {
        border-radius: 7px !important;
        font-size: 12px;
    }

    .members-table-container .dataTables_wrapper .paginate_button.current {
        border: 1px solid #315efb !important;
        background: #315efb !important;
        color: #fff !important;
    }

    .members-table-container .dataTables_processing {
        background: rgba(255, 255, 255, .96);
        border: 1px solid #e6ebf2;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        color: #334155;
    }

    /* Existing row action buttons */
    #myTable .btn {
        border-radius: 7px;
    }

    /* Modal polish */
    #myModal .modal-content,
    #accountModal .modal-content {
        border: 1px solid #e6ebf2;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .16);
        overflow: hidden;
    }

    #myModal .modal-header,
    #accountModal .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #edf0f5;
    }

    #myModal .modal-title,
    #accountModal .modal-title {
        color: #1e293b;
        font-size: 18px;
        font-weight: 700;
    }

    #myModal .modal-body,
    #accountModal .modal-body {
        padding: 24px;
    }

    #myModal .modal-footer,
    #accountModal .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #edf0f5;
        background: #fafbfd;
    }

    #myModal .form-control,
    #myModal .form-select,
    #accountModal .form-control {
        min-height: 42px;
        border-radius: 8px;
        border-color: #dce3ed;
        font-size: 13px;
    }

    #myModal .form-control:focus,
    #myModal .form-select:focus,
    #accountModal .form-control:focus {
        border-color: #8da8ff;
        box-shadow: 0 0 0 3px rgba(49, 94, 251, .09);
    }

    @media (max-width: 768px) {
        .members-page {
            padding: 22px 15px;
        }

        .members-header {
            align-items: stretch;
            margin-bottom: 22px;
        }

        .members-add-btn {
            width: 100%;
        }

        .members-card-header {
            padding: 18px;
        }

        .members-table-container {
            padding: 8px 14px 16px;
        }

        .members-table-container .dataTables_wrapper .dataTables_filter,
        .members-table-container .dataTables_wrapper .dataTables_length {
            float: none;
            text-align: left;
            width: 100%;
        }

        .members-table-container .dataTables_wrapper .dataTables_filter input {
            display: block;
            width: 100%;
            margin: 8px 0 0;
        }

        .members-table-container .dataTables_wrapper .dataTables_length select {
            margin: 0 5px;
        }

        #myModal .modal-body,
        #accountModal .modal-body {
            padding: 18px;
        }
    }
</style>
@endsection

@section('content')
<div class="members-page">

    <div class="members-header">
        <div>
            <div class="members-eyebrow">
                <i class="fa-solid fa-users"></i>
                Administration
            </div>

            <h1 class="members-title">Members</h1>

            <p class="members-subtitle">
                Manage member profiles, positions, and account access.
            </p>
        </div>

        <button type="button" class="members-add-btn" id="create_button">
            <i class="fa-solid fa-plus"></i>
            <span>Add Member</span>
        </button>
    </div>

    {{-- Existing Add/Edit Member Modal --}}
    @include('member.edit')

    {{-- Member Account Modal --}}
    <div class="modal fade" id="accountModal" tabindex="-1"
         aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="accountForm" class="w-100">
                @csrf

                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="accountModalLabel">
                                Member Account
                            </h5>
                            <p class="text-muted small mb-0 mt-1">
                                Configure login credentials and account status.
                            </p>
                        </div>

                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="account_member_id" name="member_id">

                        <div class="mb-3">
                            <label for="account_email" class="form-label fw-semibold">
                                Email address
                            </label>
                            <input type="email" class="form-control"
                                   id="account_email" name="email" required
                                   autocomplete="email">
                            <div id="account_email_error"
                                 class="text-danger small pt-1 d-none"></div>
                        </div>

                        <div class="mb-3">
                            <label for="account_password" class="form-label fw-semibold">
                                Password
                            </label>
                            <input type="password" class="form-control"
                                   id="account_password" name="password"
                                   autocomplete="new-password">
                            <div id="account_password_error"
                                 class="text-danger small pt-1 d-none"></div>
                            <small class="text-muted">
                                Leave blank to keep the current password.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="account_password_confirmation"
                                   class="form-label fw-semibold">
                                Confirm password
                            </label>
                            <input type="password" class="form-control"
                                   id="account_password_confirmation"
                                   name="password_confirmation"
                                   autocomplete="new-password">
                        </div>

                        <div class="p-3 rounded-3 border bg-light">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                       value="1" id="account_is_active"
                                       name="is_active" checked>
                                <label class="form-check-label fw-semibold"
                                       for="account_is_active">
                                    Active account
                                </label>
                                <div class="small text-muted mt-1">
                                    Enable or disable this member's login access.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-check me-1"></i>
                            Save Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Members Table --}}
    <div class="members-card">
        <div class="members-card-header">
            <div>
                <h2 class="members-card-title">Member Directory</h2>
                <p class="members-card-description">
                    View and manage registered members.
                </p>
            </div>

            <span class="badge rounded-pill text-bg-light border px-3 py-2">
                <i class="fa-solid fa-list me-1"></i>
                Member Records
            </span>
        </div>

        <div class="members-table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="myTable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection


@section('scripts')
<script>
const alias = @json(config('app.alias'));
const basePath = alias ? `/${alias}` : '';

$(document).ready(function () {
    let currentMemberId = null; // null = add, id = edit
        let currentAccountMemberId = null;

    // ================= Dynamic List Helper =================
    function setupDynamicList(listId, addBtnId, hiddenInputId, placeholderText, existingValues = []) {
        const $list = $('#' + listId);
        const $addBtn = $('#' + addBtnId);
        const $hiddenInput = $('#' + hiddenInputId);

        $list.empty();

        function updateHidden() {
            const values = $list.find('input').map(function(){ return $(this).val().trim(); }).get().filter(v => v.length);
            $hiddenInput.val(values.join('\n'));
        }

        function createItem(value = '') {
            const $item = $(`
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2">•</span>
                    <input type="text" class="form-control form-control-sm me-2" value="${value}" placeholder="${placeholderText}">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-minus"></i></button>
                </div>
            `);
            $item.find('input').on('input', updateHidden);
            $item.find('.remove-item').on('click', () => { $item.remove(); updateHidden(); });
            $list.append($item);
            updateHidden();
        }

        if (existingValues.length) {
            existingValues.forEach(v => createItem(v));
        } else {
            createItem();
        }

        $addBtn.off('click').on('click', () => createItem());
    }

   
    // ================= DataTable =================
    const DataTable = $('#myTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("members.index") }}',
        columns: [
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'position', name: 'position' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']], // sort by name initially (visual only)
        rowReorder: false, // disable built-in DataTables reorder plugin
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id); // add data-id for sorting
        },
        drawCallback: function() {
            initSortable(); // re-init Sortable after every table draw
        }
    });




    

    const myModal = $('#myModal');

    // ================= Add Member =================
    $('#create_button').click(() => {
        currentMemberId = null;
        $('.modal-title').text('Add New Member');
        $('.submit-button').show();
        $('.update-button').hide();

        $('#myForm')[0].reset();
        $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');

        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');

        setupDynamicList('achievements-list','add-achievement','achievements-input','Enter achievement...');
        setupDynamicList('projects-list','add-project','projects-input','Enter project...');

        myModal.modal('show');
    });

    // ================= Image Preview =================
    $('#image_path').on('change', function() {
        const input = this;
        const preview = $('#image_preview');
        if(input.files && input.files[0]){
            const reader = new FileReader();
            reader.onload = e => preview.attr('src', e.target.result).removeClass('d-none');
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.attr('src', '{{ asset('default/profile.png') }}').addClass('d-none');
        }
    });

    // ================= Submit Form (Add/Update) =================
    $(document).on('submit', '#myForm', function(e){
        e.preventDefault();

        console.log('Submitting form...');
        // Update hidden inputs for dynamic lists

        $('#achievements-input').val($('#achievements-list input').map(function(){ return $(this).val(); }).get().join('\n'));
        $('#projects-input').val($('#projects-list input').map(function(){ return $(this).val(); }).get().join('\n'));

        const formData = new FormData(this);
        let url = `${basePath}/members`;
        if(currentMemberId) {
            formData.append('_method','PUT');
            url = `${basePath}/members/${currentMemberId}`;
        }

        axios.post(url, formData, {
            headers: {'Content-Type': 'multipart/form-data'}
        })
        .then(res => {
            toastr.success(res.data.message);
            $('#myModal').modal('hide');
            DataTable.ajax.reload();
            $('#myForm')[0].reset();
            $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
        })
        .catch(err => {
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            if(err.response?.status === 422){
                const errors = err.response.data.errors;
                Object.keys(errors).forEach(key => {
                    $(`#${key}`).addClass('is-invalid');
                    $(`#${key}_error`).removeClass('d-none').text(errors[key][0]);
                });
            } else {
                toastr.error(err.message || 'Operation failed');
            }
        });
    });

    // ================= Edit Member =================
    $(document).on('click', '.edit-button', function(){
        currentMemberId = $(this).data('id');

        $('.modal-title').text('Edit Member');
        $('.update-button').show();
        $('.submit-button').hide();
        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');
console.log(currentMemberId) 
console.log(`${basePath}/members/${currentMemberId}/edit`);  
        axios.get(`${basePath}/members/${currentMemberId}/edit`)
        .then(res => {
            const m = res.data.data;
            $('#name').val(m.name);
            $('#position').val(m.position);
            $('#description').val(m.description || '');
            $('#email').val(m.email || '');
            $('#contact_number').val(m.contact_number || '');
            $('#address').val(m.address || '');
            $('#term_start').val(m.term_start || '');
            $('#term_end').val(m.term_end || '');
            $('#social_facebook').val(m.social_facebook || '');
            $('#social_twitter').val(m.social_twitter || '');
            $('#social_instagram').val(m.social_instagram || '');
            $('#sort_order').val(m.sort_order ?? 0);
            $('#isActive').prop('checked', m.isActive ? true : false);
            $('#image_preview').attr('src', m.image_path ? `/storage/${m.image_path}` : '{{ asset('default/profile.png') }}');

            setupDynamicList('achievements-list','add-achievement','achievements-input','Enter achievement...', m.achievements ? m.achievements.split("\n") : []);
            setupDynamicList('projects-list','add-project','projects-input','Enter project...', m.priority_projects ? m.priority_projects.split("\n") : []);

            myModal.modal('show');
        })
        .catch(err => toastr.error(err.response?.data?.message || 'Failed to load member data'));
    });

    // ================= Delete Member =================
    $(document).on('click', '.delete-button', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will deactivate the standing member.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if(result.isConfirmed){
                axios.delete(`${basePath}/members/${id}`)
                    .then(res => {
                        toastr.success(res.data.message || 'Member deleted successfully.');
                        DataTable.ajax.reload();
                    })
                    .catch(err => {
                        toastr.error(err.response?.data?.message || 'Failed to delete member.');
                    });
            }
        });
    });

     function initSortable() {
        const tbody = document.querySelector('#myTable tbody');
        if (!tbody) return;

        Sortable.create(tbody, {
            animation: 150,
            handle: 'td', // drag anywhere on the row
            onEnd: function (evt) {
                const order = [];
                $('#myTable tbody tr').each(function (index, el) {
                    order.push($(el).data('id'));
                });

                axios.post('{{ route("members.reorder") }}', { order })
                    .then(res => {
                        toastr.success(res.data.message);
                        DataTable.ajax.reload(null, false); // reload without resetting pagination
                    })
                    .catch(err => {
                        toastr.error('Failed to update orderss');
                        console.error(err);
                    });
            }
        });
    }

         // initialize manually once table loaded
    DataTable.on('draw', initSortable);

        // ================ Member Account (Add / Edit) =================
        const accountModal = $('#accountModal');

        $(document).on('click', '.account-button', function () {
            currentAccountMemberId = $(this).data('id');

            // Get row data from DataTable for name/email fallback
            const row = $(this).closest('tr');
            const rowData = DataTable.row(row).data() || {};
            const memberName = rowData.name || 'Member';
            const memberEmail = rowData.email || '';

            $('#accountForm')[0].reset();
            $('#account_member_id').val(currentAccountMemberId);
            $('#accountModalLabel').text(`Account for ${memberName}`);
            $('#account_email_error').addClass('d-none').text('');
            $('#account_password_error').addClass('d-none').text('');
            $('#account_email').removeClass('is-invalid');
            $('#account_password').removeClass('is-invalid');

            axios.get(`${basePath}/members/${currentAccountMemberId}/account`)
                .then(res => {
                    const acc = res.data.data;
                    $('#account_email').val(acc.email);
                    $('#account_is_active').prop('checked', acc.is_active ? true : false);
                })
                .catch(err => {
                    if (err.response && err.response.status !== 404) {
                        toastr.error(err.response?.data?.message || 'Failed to load account.');
                    }

                    $('#account_is_active').prop('checked', true);
                    $('#account_password').val('');
                    $('#account_password_confirmation').val('');

                    // If no existing account (404) or any error, prefill email from members table if available
                    if (memberEmail) {
                        $('#account_email').val(memberEmail);
                    }
                })
                .finally(() => {
                    accountModal.modal('show');
                });
        });

        $('#accountForm').on('submit', function (e) {
            e.preventDefault();

            if (!currentAccountMemberId) {
                toastr.error('No member selected.');
                return;
            }

            const formData = new FormData(this);

            axios.post(`${basePath}/members/${currentAccountMemberId}/account`, formData)
                .then(res => {
                    toastr.success(res.data.message || 'Account saved successfully.');
                    accountModal.modal('hide');
                    DataTable.ajax.reload(null, false);
                })
                .catch(err => {
                    $('#account_email_error').addClass('d-none').text('');
                    $('#account_password_error').addClass('d-none').text('');
                    $('#account_email').removeClass('is-invalid');
                    $('#account_password').removeClass('is-invalid');

                    if (err.response && err.response.status === 422) {
                        const errors = err.response.data.errors;
                        if (errors.email) {
                            $('#account_email').addClass('is-invalid');
                            $('#account_email_error').removeClass('d-none').text(errors.email[0]);
                        }
                        if (errors.password) {
                            $('#account_password').addClass('is-invalid');
                            $('#account_password_error').removeClass('d-none').text(errors.password[0]);
                        }
                    } else {
                        toastr.error(err.response?.data?.message || 'Failed to save account.');
                    }
                });
        });

});
</script>
@endsection
