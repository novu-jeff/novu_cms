@extends('layouts.app')

@section('styles')
<style>
    .assignment-page { max-width: 1800px; margin: 0 auto; padding: 32px; color: #1e293b; }
    .assignment-header { display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; margin-bottom:28px; }
    .assignment-eyebrow { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:12px; font-weight:700; letter-spacing:1.1px; text-transform:uppercase; margin-bottom:8px; }
    .assignment-title { margin:0 0 8px; color:#172338; font-size:clamp(25px,3vw,32px); font-weight:750; letter-spacing:-.7px; line-height:1.2; }
    .assignment-subtitle { margin:0; color:#718096; font-size:14px; line-height:1.6; }
    .assignment-add-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:46px; padding:0 20px; border:0; border-radius:10px; background:#315efb; color:#fff; font-size:14px; font-weight:600; box-shadow:0 5px 12px rgba(49,94,251,.18); transition:background .2s,transform .2s; }
    .assignment-add-btn:hover { background:#244bd6; color:#fff; transform:translateY(-1px); }
    .assignment-card { overflow:hidden; border:1px solid #e6ebf2; border-radius:16px; background:#fff; box-shadow:0 4px 18px rgba(15,23,42,.035); }
    .assignment-card-header { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; padding:22px 24px; border-bottom:1px solid #edf0f5; }
    .assignment-card-title { margin:0 0 5px; color:#1e293b; font-size:16px; font-weight:700; }
    .assignment-card-description { margin:0; color:#8491a5; font-size:13px; }
    .assignment-table-wrap { padding:8px 24px 20px; }
    #myTable { width:100% !important; margin-bottom:0 !important; border-collapse:separate; border-spacing:0; }
    #myTable thead th { padding:16px 14px; background:#f8fafc; color:#64748b; border-bottom:1px solid #e9eef5; font-size:11px; font-weight:700; letter-spacing:.7px; text-transform:uppercase; white-space:nowrap; }
    #myTable tbody td { padding:16px 14px; border-bottom:1px solid #eef2f7; color:#334155; font-size:13px; vertical-align:middle; }
    #myTable tbody tr:last-child td { border-bottom:0; }
    #myTable tbody tr:hover td { background:#f8faff; }
    .assignment-table-wrap .dataTables_wrapper { padding-top:14px; }
    .assignment-table-wrap .dataTables_wrapper .dataTables_length, .assignment-table-wrap .dataTables_wrapper .dataTables_filter { margin-bottom:18px; color:#64748b; font-size:13px; }
    .assignment-table-wrap .dataTables_wrapper .dataTables_length select, .assignment-table-wrap .dataTables_wrapper .dataTables_filter input { min-height:38px; padding:7px 12px; border:1px solid #dce3ed; border-radius:8px; background:#fff; color:#334155; outline:none; box-shadow:none; }
    .assignment-table-wrap .dataTables_wrapper .dataTables_filter input { min-width:220px; margin-left:8px; }
    .assignment-table-wrap .dataTables_wrapper .dataTables_info { padding-top:18px; color:#8491a5; font-size:12px; }
    .assignment-table-wrap .dataTables_wrapper .dataTables_paginate { padding-top:12px; }
    .assignment-table-wrap .dataTables_wrapper .paginate_button { border-radius:7px !important; font-size:12px; }
    #myModal .modal-content { overflow:hidden; border:1px solid #e6ebf2; border-radius:16px; box-shadow:0 20px 60px rgba(15,23,42,.16); }
    #myModal .modal-header { padding:20px 24px; border-bottom:1px solid #edf0f5; }
    #myModal .modal-title { color:#1e293b; font-size:18px; font-weight:700; }
    #myModal .modal-body { padding:24px; }
    #myModal .modal-footer { padding:16px 24px; border-top:1px solid #edf0f5; background:#fafbfd; }
    #myModal .form-control, #myModal .form-select { min-height:42px; border-color:#dce3ed; border-radius:8px; font-size:13px; }
    #myModal .form-control:focus, #myModal .form-select:focus { border-color:#8da8ff; box-shadow:0 0 0 3px rgba(49,94,251,.09); }
    #myModal .member-entry { border-color:#e2e8f0 !important; border-radius:12px !important; background:#f8fafc; }
    @media(max-width:768px) { .assignment-page{padding:22px 15px;} .assignment-header{align-items:stretch;margin-bottom:22px;} .assignment-add-btn{width:100%;} .assignment-card-header{padding:18px;} .assignment-table-wrap{padding:8px 14px 16px;} .assignment-table-wrap .dataTables_wrapper .dataTables_filter,.assignment-table-wrap .dataTables_wrapper .dataTables_length{float:none;text-align:left;width:100%;} .assignment-table-wrap .dataTables_wrapper .dataTables_filter input{display:block;width:100%;margin:8px 0 0;} #myModal .modal-body{padding:18px;} }
</style>
@endsection

@section('content')
<div class="assignment-page">
    <header class="assignment-header">
        <div>
            <div class="assignment-eyebrow"><i class="fa-solid fa-map-location-dot"></i> Administration</div>
            <h1 class="assignment-title">District Assignments</h1>
            <p class="assignment-subtitle">Manage district assignments, assigned members, and positions.</p>
        </div>
        <button type="button" class="assignment-add-btn" id="create_button">
            <i class="fa-solid fa-plus"></i><span>Add Assignment</span>
        </button>
    </header>

    @include('assignments.edit')

    <section class="assignment-card">
        <div class="assignment-card-header">
            <div>
                <h2 class="assignment-card-title">District Assignment Directory</h2>
                <p class="assignment-card-description">View and manage registered district assignments.</p>
            </div>
            <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="fa-solid fa-list me-1"></i> Assignment Records</span>
        </div>
        <div class="assignment-table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="myTable">
                    <thead><tr><th>Name</th><th style="width:150px">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')

<script>

    const alias = @json(config('app.alias'));

    const basePath = alias ? `/${alias}` : '';

    $(document).ready(function () {

        let DataTable = $('#myTable').DataTable({

            processing: true,

            serverSide: true,

            ajax: {

                url: '{{ route('district-assignments.index') }}',

            },

            columns: [           

                { data: "name", name: 'name' },       

                { data: "actions", name: 'actions' },         

            ],

        });

        $('#addMemberBtn').click(() => {

            const index = $('#members-wrapper .member-entry').length;

            const newMember = `

            <div class="member-entry border rounded p-3 mb-3 position-relative">

                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-member" aria-label="Close" title="Remove Member"></button>

                <div class="row">

                    <div class="col-md-12">

                        <div class="mb-2">

                            <label class="form-label">Name</label>

                            <input type="text" name="members[${index}][name]" class="form-control" required>

                        </div>

                        <div>

                            <label class="form-label">Position</label>

                            <input type="text" name="members[${index}][position]" class="form-control">

                        </div>

                    </div>

                </div>

            </div>`;

            $('#members-wrapper').append(newMember);

        });

        // Remove member and reindex

        $(document).on('click', '.remove-member', function () {

            $(this).closest('.member-entry').remove();

            // Reindex all remaining members

            $('#members-wrapper .member-entry').each((index, el) => {

                $(el).find('input, select, textarea').each(function () {

                    const name = $(this).attr('name');

                    if (name) {

                        const newName = name.replace(/members\\[\d+\\]/, `members[${index}]`);

                        $(this).attr('name', newName);

                    }

                });

            });

        });

        const myModal = $('#myModal');

        // Create

        $('#create_button').click(e => {

            $('.update-button').hide();

            $('.update-section').hide();

            $('.submit-button').show();

            $('.modal-title').html('Add New Assignment');

            $('#myForm')[0].reset();

            $('.text-danger').addClass('d-none');

            $('.form-control').removeClass('is-invalid');

            myModal.modal('show')

        });

        // submit

        $('.submit-button').click(e => {

            e.preventDefault();

            $('.submit-button').prop('disabled', true);

            const formData = new FormData();

            // Committee name

            formData.append('name', $('#member_name').val());

            // Loop through members and append each as JSON

            const members = [];

            $('#members-wrapper .member-entry').each((index, el) => {

                const name = $(el).find('[name$="[name]"]').val();

                const position = $(el).find('[name$="[position]"]').val();

                members.push({ name, position });

            });

            members.forEach((member, index) => {

                formData.append(`members[${index}][name]`, member.name);

                formData.append(`members[${index}][position]`, member.position);

            });

            axios.post(`${basePath}/district-assignments`, formData, {

                headers: {

                    'Accept': 'application/json',

                    'Content-Type': 'multipart/form-data'

                }

            })

            .then(response => {

                myModal.modal('hide');

                $('#myForm')[0].reset();

                $('#members-wrapper .member-entry').not(':first').remove();

                $('#members-wrapper .member-entry:first input').val('');

                DataTable.ajax.reload();

                toastr.success(response.data.message, 'Success', {

                    iconClass: 'toast-success'

                });

            })

            .catch(error => {

                $('.text-danger').addClass('d-none');

                $('.form-control').removeClass('is-invalid');

                $('.field-error').remove();

                $('#image_preview').removeClass('border border-danger');

                if (error.response && error.response.status === 422) {

                    const errors = error.response.data.errors;

                    $('.text-danger').addClass('d-none').text('');

                    $('.form-control').removeClass('is-invalid');

                    $('.field-error').remove();

                    Object.keys(errors).forEach(key => {

                        const messages = errors[key];

                        console.log(messages);

                        if (key === 'name') {

                            $('#member_name').addClass('is-invalid');

                            $('#name_error').removeClass('d-none').text(messages[0]);

                        } else if (key === 'image_path') {

                            $('#image_path_error').removeClass('d-none').text(messages[0]);

                            $('#image_preview').addClass('border border-danger');

                        } else if (key.startsWith('members.')) {

                            const match = key.match(/members\\.(\d+)\\.(\w+)/);

                            console.log(match);

                            if (match) {

                                const index = match[1];

                                const field = match[2];

                                const input = $(`#members-wrapper .member-entry:eq(${index}) [name$="[${field}]"]`);

                                input.addClass('is-invalid');

                                let errorElement = input.next('.field-error');

                                if (!errorElement.length) {

                                    input.after(`<div class="field-error text-danger small pt-1">${messages[0]}</div>`);

                                } else {

                                    errorElement.text(messages[0]).removeClass('d-none');

                                }

                            }

                        }

                    });

                }

                else {

                    toastr.error('Something went wrong.', 'Error', {

                        iconClass: 'toast-error'

                    });

                }

            })

            .finally(() => {

                $('.submit-button').prop('disabled', false);

            });

        });

        let id;

        // Edit

        $(document).on('click', '.edit-button', function () {

            id = $(this).data('id');

            // Update modal header and toggle buttons

            $('.modal-title').text('Edit District Assignment');

            $('.update-button').show();

            $('.submit-button').hide();

            // Clear previous errors & reset styles

            $('.text-danger').addClass('d-none');

            $('.form-control').removeClass('is-invalid');

            $('.field-error').remove();

            axios.get(`${basePath}/district-assignments/${id}/edit`)

                .then(response => {

                    const assignment = response.data.data;

                    // Set name

                    $('#member_name').val(assignment.name);

                    // Clear members wrapper

                    $('#members-wrapper').empty();

                    // Render each member

                    assignment.members.forEach((member, index) => {

                        const memberHtml = `

                        <div class="member-entry border rounded p-3 mb-3 position-relative">

                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-member" aria-label="Close" title="Remove Member"></button>

                            <div class="row">

                                <div class="col-md-12">

                                    <div class="mb-2">

                                        <label class="form-label">Name</label>

                                        <input type="text" name="members[${index}][name]" class="form-control" value="${member.name}" required>

                                    </div>

                                    <div>

                                        <label class="form-label">Position</label>

                                        <input type="text" name="members[${index}][position]" class="form-control" value="${member.position ?? ''}">

                                    </div>

                                </div>

                            </div>

                        </div>`;

                        $('#members-wrapper').append(memberHtml);

                    });

                    myModal.modal('show');

                })

                .catch(error => {

                    if (error.response?.status === 403) {

                        toastr.error('You do not have permission to perform this action.', 'Forbidden');

                    } else {

                        toastr.error(error.message || 'Something went wrong', 'Error');

                    }

                });

        });

        // Update

        $('.update-button').click(e => {

            e.preventDefault();

            $('.update-button').prop('disabled', true);

            const formData = new FormData();

            // Append name

            formData.append('name', $('#member_name').val());

            // Append members

            const members = [];

            $('#members-wrapper .member-entry').each((index, el) => {

                const name = $(el).find('[name$="[name]"]').val();

                const position = $(el).find('[name$="[position]"]').val();

                members.push({ name, position });

            });

            // Flatten members to formData format

            members.forEach((member, index) => {

                formData.append(`members[${index}][name]`, member.name);

                formData.append(`members[${index}][position]`, member.position);

            });

            axios.post(`${basePath}/district-assignments/${id}?\_method=PUT`, formData, {

                headers: {

                    'Accept': 'application/json',

                    'Content-Type': 'multipart/form-data'

                }

            })

            .then(response => {

                myModal.modal('hide');

                $('#myForm')[0].reset();

                $('#members-wrapper .member-entry').not(':first').remove();

                $('#members-wrapper .member-entry:first input').val('');

                $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');

                DataTable.ajax.reload();

                toastr.success(response.data.message, 'Updated Successfully', {

                    iconClass: 'toast-success'

                });

            })

            .catch(error => {

                $('.text-danger').addClass('d-none');

                $('.form-control').removeClass('is-invalid');

                $('.field-error').remove();

                if (error.response && error.response.status === 422) {

                    const errors = error.response.data.errors;

                    Object.keys(errors).forEach(key => {

                        const messages = errors[key];

                        if (key === 'name') {

                            $('#member_name').addClass('is-invalid');

                            $('#name_error').removeClass('d-none').text(messages[0]);

                        } else if (key === 'image_path') {

                            $('#image_path_error').removeClass('d-none').text(messages[0]);

                        } else if (key.startsWith('members.')) {

                            const match = key.match(/members\\.(\d+)\\.(\w+)/);

                            if (match) {

                                const index = match[1];

                                const field = match[2];

                                const input = $(`#members-wrapper .member-entry:eq(${index}) [name$="[${field}]"]`);

                                input.addClass('is-invalid');

                                let errorElement = input.next('.field-error');

                                if (!errorElement.length) {

                                    input.after(`<div class="field-error text-danger small pt-1">${messages[0]}</div>`);

                                } else {

                                    errorElement.text(messages[0]).removeClass('d-none');

                                }

                            }

                        }

                    });

                } else {

                    toastr.error('Something went wrong.', 'Error', {

                        iconClass: 'toast-error'

                    });

                }

            })

            .finally(() => {

                $('.update-button').prop('disabled', false);

            });

        });

        // delete

        $(document).on('click', '.delete-button', function () {

            const id = $(this).data('id');

            Swal.fire({

                title: 'Are you sure?',

                text: "This will deactivate the Assignment.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Yes, delete it!'

            }).then((result) => {

                if (result.isConfirmed) {

                    axios.delete(`${basePath}/district-assignments/${id}`, {

                        headers: {

                            'Accept': 'application/json'

                        }

                    })

                    .then(response => {

                        toastr.success(response.data.message || 'Committee deleted successfully.', 'Success', {

                            iconClass: 'toast-success'

                        });

                        DataTable.ajax.reload(); // Reload DataTable

                    })

                    .catch(error => {

                        toastr.error(

                            error.response?.data?.message || 'Failed to delete standing committee.',

                            'Error',

                            { iconClass: 'toast-error' }

                        );

                    });

                }

            });

        });

    });

</script>

@endsection

