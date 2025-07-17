@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Standing Committee</h2>
            <p class="text-muted mb-0">Mange standing Committee in this module</p>
        </div>

        <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
            <i class="fas fa-plus me-1"></i> Add Committee
        </button>
    </div>

    @include('committee.edit')

    <!-- Table Card -->
    <div class="card shadow border-1 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="myTable">
                    <thead>
                        <tr>
                            <th class="text-muted">Name</th>
                            <th class="text-muted" style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
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

            // Loop through all member entries
            $('#members-wrapper .member-entry').each((index, el) => {
                const name = $(el).find('[name$="[name]"]').val();
                const position = $(el).find('[name$="[position]"]').val();

                payload.committee.push({ name, position });
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

                    // Loop through members
                    committee.members.forEach((member, index) => {
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
                const name = $(el).find('[name$="[name]"]').val();
                const position = $(el).find('[name$="[position]"]').val();

                payload.committee.push({ name, position });
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

