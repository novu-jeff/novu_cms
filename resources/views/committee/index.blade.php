@extends('layouts.app')

@section('styles')
<style>
    .committee-page { max-width: 1800px; margin: 0 auto; padding: 32px; color: #1e293b; }
    .committee-header { display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; margin-bottom:28px; }
    .committee-eyebrow { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:12px; font-weight:700; letter-spacing:1.1px; text-transform:uppercase; margin-bottom:8px; }
    .committee-title { margin:0 0 8px; color:#172338; font-size:clamp(25px,3vw,32px); font-weight:750; letter-spacing:-.7px; line-height:1.2; }
    .committee-subtitle { margin:0; color:#718096; font-size:14px; line-height:1.6; }
    .committee-add-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:46px; padding:0 20px; border:0; border-radius:10px; background:#315efb; color:#fff; font-size:14px; font-weight:600; box-shadow:0 5px 12px rgba(49,94,251,.18); transition:background .2s,transform .2s; }
    .committee-add-btn:hover { background:#244bd6; color:#fff; transform:translateY(-1px); }
    .committee-card { overflow:hidden; border:1px solid #e6ebf2; border-radius:16px; background:#fff; box-shadow:0 4px 18px rgba(15,23,42,.035); }
    .committee-card-header { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; padding:22px 24px; border-bottom:1px solid #edf0f5; }
    .committee-card-title { margin:0 0 5px; color:#1e293b; font-size:16px; font-weight:700; }
    .committee-card-description { margin:0; color:#8491a5; font-size:13px; }
    .committee-table-wrap { padding:8px 24px 20px; }
    #myTable { width:100% !important; margin-bottom:0 !important; border-collapse:separate; border-spacing:0; }
    #myTable thead th { padding:16px 14px; background:#f8fafc; color:#64748b; border-bottom:1px solid #e9eef5; font-size:11px; font-weight:700; letter-spacing:.7px; text-transform:uppercase; white-space:nowrap; }
    #myTable tbody td { padding:16px 14px; border-bottom:1px solid #eef2f7; color:#334155; font-size:13px; vertical-align:middle; }
    #myTable tbody tr:last-child td { border-bottom:0; }
    #myTable tbody tr:hover td { background:#f8faff; }
    .committee-table-wrap .dataTables_wrapper { padding-top:14px; }
    .committee-table-wrap .dataTables_wrapper .dataTables_length, .committee-table-wrap .dataTables_wrapper .dataTables_filter { margin-bottom:18px; color:#64748b; font-size:13px; }
    .committee-table-wrap .dataTables_wrapper .dataTables_length select, .committee-table-wrap .dataTables_wrapper .dataTables_filter input { min-height:38px; padding:7px 12px; border:1px solid #dce3ed; border-radius:8px; background:#fff; color:#334155; outline:none; box-shadow:none; }
    .committee-table-wrap .dataTables_wrapper .dataTables_filter input { min-width:220px; margin-left:8px; }
    .committee-table-wrap .dataTables_wrapper .dataTables_info { padding-top:18px; color:#8491a5; font-size:12px; }
    .committee-table-wrap .dataTables_wrapper .dataTables_paginate { padding-top:12px; }
    .committee-table-wrap .dataTables_wrapper .paginate_button { border-radius:7px !important; font-size:12px; }
    .committee-table-wrap .dataTables_wrapper .paginate_button.current { border:1px solid #315efb !important; background:#315efb !important; color:#fff !important; }
    #myModal .modal-content { overflow:hidden; border:1px solid #e6ebf2; border-radius:16px; box-shadow:0 20px 60px rgba(15,23,42,.16); }
    #myModal .modal-header { padding:20px 24px; border-bottom:1px solid #edf0f5; }
    #myModal .modal-title { color:#1e293b; font-size:18px; font-weight:700; }
    #myModal .modal-body { padding:24px; }
    #myModal .modal-footer { padding:16px 24px; border-top:1px solid #edf0f5; background:#fafbfd; }
    #myModal .form-control, #myModal .form-select { min-height:42px; border-color:#dce3ed; border-radius:8px; font-size:13px; }
    #myModal .form-control:focus, #myModal .form-select:focus { border-color:#8da8ff; box-shadow:0 0 0 3px rgba(49,94,251,.09); }
    @media(max-width:768px) { .committee-page{padding:22px 15px;} .committee-header{align-items:stretch;margin-bottom:22px;} .committee-add-btn{width:100%;} .committee-card-header{padding:18px;} .committee-table-wrap{padding:8px 14px 16px;} .committee-table-wrap .dataTables_wrapper .dataTables_filter,.committee-table-wrap .dataTables_wrapper .dataTables_length{float:none;text-align:left;width:100%;} .committee-table-wrap .dataTables_wrapper .dataTables_filter input{display:block;width:100%;margin:8px 0 0;} #myModal .modal-body{padding:18px;} }
</style>
@endsection

@section('content')
<div class="committee-page">
    <header class="committee-header">
        <div>
            <div class="committee-eyebrow"><i class="fa-solid fa-people-group"></i> Administration</div>
            <h1 class="committee-title">Standing Committee</h1>
            <p class="committee-subtitle">Manage committee names, members, and assigned positions.</p>
        </div>
        <button type="button" class="committee-add-btn" id="create_button">
            <i class="fa-solid fa-plus"></i><span>Add Committee</span>
        </button>
    </header>

    @include('committee.edit')

    <section class="committee-card">
        <div class="committee-card-header">
            <div>
                <h2 class="committee-card-title">Committee Directory</h2>
                <p class="committee-card-description">View and manage registered standing committees.</p>
            </div>
            <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="fa-solid fa-list me-1"></i> Committee Records</span>
        </div>
        <div class="committee-table-wrap">
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
                url: '{{ route('standing-committee.index') }}',
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
                    <button type="button"
                            class="btn-close position-absolute top-0 end-0 m-2 remove-member"
                            aria-label="Close"
                            title="Remove Member"></button>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">SB Member</label>

                                <select name="members[${index}][member_id]"
                                        class="form-select member-select"
                                        required>
                                    <option value="">Select SB Member</option>
                                    @foreach(\App\Models\Member::where('isActive', true)->orderBy('name')->get() as $member)
                                        <option value="{{ $member->id }}">
                                            {{ $member->name }} — {{ $member->position }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="field-error text-danger small pt-1 d-none"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Committee Role</label>

                                <select name="members[${index}][position]"
                                        class="form-select"
                                        required>
                                    <option value="">Select Role</option>
                                    <option value="Chairperson">Chairperson</option>
                                    <option value="Vice-Chairperson">Vice-Chairperson</option>
                                    <option value="Member">Member</option>
                                </select>

                                <div class="field-error text-danger small pt-1 d-none"></div>
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
                        const newName = name.replace(/members\[\d+\]/, `members[${index}]`);
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
            $('.modal-title').html('Add New Committee');
            $('#myForm')[0].reset();
            $('#members-wrapper .member-entry').not(':first').remove();
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            myModal.modal('show')
        });

        // submit
        $('.submit-button').click(e => {
            e.preventDefault();
            $('.submit-button').prop('disabled', true);

            // Build the payload manually
            const payload = {
                name: $('#committee_name').val(),
                committee: []
            };

            // Gather committee members
            $('#members-wrapper .member-entry').each((index, el) => {
                const member_id = $(el).find('[name$="[member_id]"]').val();
                const position = $(el).find('[name$="[position]"]').val();

                payload.committee.push({ member_id, position });
            });

            axios.post(`${basePath}/standing-committee`, payload, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                myModal.modal('hide')
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

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;

                    Object.keys(errors).forEach(key => {
                        const messages = errors[key];
                        if (key === 'name') {
                            // Handle committee name
                            $('#committee_name').addClass('is-invalid');
                            $('#name_error').removeClass('d-none').text(messages[0]);
                        } else if (key.startsWith('committee')) {
                            // Handle nested member errors like committee.0.name
                            const match = key.match(/committee\.(\d+)\.(\w+)/);
                            if (match) {
                                const index = match[1];
                                const field = match[2];
                                const input = $(`#members-wrapper .member-entry:eq(${index}) [name$="[${field}]"]`);

                                input.addClass('is-invalid');

                                // Optional: dynamically show error message under each input
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
                $('.submit-button').prop('disabled', false);
            });
        });

        let id;

        // Edit
        $(document).on('click', '.edit-button', function () {
            id = $(this).data('id');

            // Update modal header and toggle buttons
            $('.modal-title').text('Edit Standing Committee');
            $('.update-button').show();
            $('.submit-button').hide();

            // Clear previous errors & reset styles
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            axios.get(`${basePath}/standing-committee/${id}/edit`)
                .then(response => {
                    const committee = response.data.data;

                    // Set committee name
                    $('#committee_name').val(committee.name);

                    // Clear all but the first member
                    $('#members-wrapper').empty();

                    // Sort members by committee role
                    const roleOrder = {
                        'Chairperson': 1,
                        'Vice-Chairperson': 2,
                        'Member': 3,
                        'Members': 3
                    };

                    const sortedMembers = [...committee.members].sort((a, b) => {
                        return (roleOrder[a.position] ?? 99) - (roleOrder[b.position] ?? 99);
                    });

                    // Loop through members
                    sortedMembers.forEach((member, index) => {
                        const memberHtml = `
                            <div class="member-entry border rounded p-3 mb-3 position-relative">
                                <button type="button"
                                        class="btn-close position-absolute top-0 end-0 m-2 remove-member"
                                        aria-label="Close"
                                        title="Remove Member"></button>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label">SB Member</label>

                                            <select name="members[${index}][member_id]"
                                                    class="form-select member-select"
                                                    required>
                                                <option value="">Select SB Member</option>
                                                @foreach(\App\Models\Member::where('isActive', true)->orderBy('name')->get() as $member)
                                                    <option value="{{ $member->id }}"
                                                        ${String(member.member_id) === '{{ $member->id }}' ? 'selected' : ''}>
                                                        {{ $member->name }} — {{ $member->position }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <div class="field-error text-danger small pt-1 d-none"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label">Committee Role</label>

                                            <select name="members[${index}][position]"
                                                    class="form-select"
                                                    required>
                                                <option value="">Select Role</option>
                                                <option value="Chairperson" ${member.position === 'Chairperson' ? 'selected' : ''}>Chairperson</option>
                                                <option value="Vice-Chairperson" ${member.position === 'Vice-Chairperson' ? 'selected' : ''}>Vice-Chairperson</option>
                                                <option value="Member" ${['Member', 'Members'].includes(member.position) ? 'selected' : ''}>Member</option>
                                            </select>

                                            <div class="field-error text-danger small pt-1 d-none"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        $('#members-wrapper').append(memberHtml);
                    });

                    myModal.modal('show')
                })
                .catch(error => {
                    if (error.response?.status === 403) {
                        toastr.error('You do not have permission to perform this action.', 'Forbidden');
                    } else {
                        toastr.error(error.message || 'Something went wrong', 'Error');
                    }
                });
        });

        // update
        $('.update-button').click(e => {
            e.preventDefault();
            $('.update-button').prop('disabled', true);

            // Ensure `id` is globally tracked (set when Edit is clicked)
            const payload = {
                name: $('#committee_name').val(),
                committee: []
            };

            // Gather committee members
            $('#members-wrapper .member-entry').each((index, el) => {
                const member_id = $(el).find('[name$="[member_id]"]').val();
                const position = $(el).find('[name$="[position]"]').val();

                payload.committee.push({ member_id, position });
            });

            axios.put(`${basePath}/standing-committee/${id}`, payload, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                DataTable.ajax.reload();
                toastr.success(response.data.message, 'Updated Successfully', {
                    iconClass: 'toast-success'
                });
            })
            .catch(error => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                $('.field-error').remove(); // clear previous field error messages

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;

                    Object.keys(errors).forEach(key => {
                        const messages = errors[key];
                        if (key === 'name') {
                            $('#committee_name').addClass('is-invalid');
                            $('#name_error').removeClass('d-none').text(messages[0]);
                        } else if (key.startsWith('committee')) {
                            const match = key.match(/committee\.(\d+)\.(\w+)/);
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
                text: "This will deactivate the standing committee.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${basePath}/standing-committee/${id}`, {
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

