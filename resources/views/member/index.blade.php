@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Members</h2>
            <p class="text-muted mb-0">Mange members in this module</p>
        </div>

        <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
            <i class="fas fa-plus me-1"></i> Add Member
        </button>
    </div>

    @include('member.edit')

    <!-- Table Card -->
    <div class="card shadow border-1 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="myTable">
                    <thead>
                        <tr>
                            <th class="text-muted">Image</th>
                            <th class="text-muted">Name</th>
                            <th class="text-muted">Position</th>
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
    $(document).ready(function () {
        let DataTable = $('#myTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('members.index') }}',
            },
            columns: [
                { data: "image", name: 'image' },          
                { data: "name", name: 'name' },
                { data: "position", name: 'position' },         
                { data: "actions", name: 'actions' },         
            ],
        });

        const myModal = $('#myModal');

        // Create
        $('#create_button').click(e => {
            $('.update-button').hide();
            $('.update-section').hide();
            $('.submit-button').show();
            $('.modal-title').html('Add New Member');
            $('#myForm')[0].reset();

            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');

            myModal.modal('show')
        });

        // image on change
        $('#image_path').on('change', function (e) {
            const input = this;
            const preview = $('#image_preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.attr('src', event.target.result).removeClass('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.attr('src', '{{ asset('default/profile.png') }}').addClass('d-none');
            }
        });

        // submit
        $('.submit-button').click(e => {
            e.preventDefault();
            $('.submit-button').prop('disabled', true);

            const formElement = document.getElementById('myForm');
            const formData = new FormData(formElement);

            axios.post('/members', formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((response) => {
                $('#myModal').modal('hide');
                $('#myForm')[0].reset();
                $('#image_preview').attr('src', '{{ asset('default/profile.png') }}').addClass('d-none');
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');

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
                    $.each(errors, (field, messages) => {
                        const input = $('#' + field);
                        const errorSpan = $('#' + field + '_error');
                        input.addClass('is-invalid');
                        errorSpan.removeClass('d-none').text(messages[0]);
                    });
                } else {
                    toastr.error('Something went wrong!', 'Error', {
                        iconClass: 'toast-error'
                    });
                }
            })
            .finally(() => {
                $('.submit-button').prop('disabled', false);
            });
        });

        let id;

        // edit
        $(document).on('click', '.edit-button', function () {
            id = $(this).data('id');   // grab the row id

            // Modal header & buttons
            $('.modal-title').text('Edit Member');
            $('.update-button').show();
            $('.submit-button').hide();

            // Clear previous errors & styles
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            // Get member data
            axios.get(`/members/${id}/edit`)
                .then(response => {
                    const m = response.data.data;

                    // Populate fields
                    $('#name').val(m.name);
                    $('#position').val(m.position);

                    // Image preview (existing or fallback)
                    const imgUrl = m.image_path
                        ? `/storage/${m.image_path}`
                        : '{{ asset('default/profile.png') }}';
                    $('#image_preview')
                        .attr('src', imgUrl)
                        .removeClass('d-none');

                    $('#myModal').modal('show');
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

            const formElement = document.getElementById('myForm');
            const formData = new FormData(formElement);
            formData.append('_method', 'PUT');

            axios.post(`/members/${id}`, formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((response) => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');

                DataTable.ajax.reload();

                toastr.success(response.data.message, 'Updated', {
                    iconClass: 'toast-success'
                });
            })
            .catch(error => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    $.each(errors, (field, messages) => {
                        const input = $('#' + field);
                        const errorSpan = $('#' + field + '_error');
                        input.addClass('is-invalid');
                        errorSpan.removeClass('d-none').text(messages[0]);
                    });
                } else {
                    toastr.error('Something went wrong!', 'Error', {
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
                text: "This will deactivate the standing member.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/members/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        toastr.success(response.data.message || 'Member deleted successfully.', 'Success', {
                            iconClass: 'toast-success'
                        });

                        DataTable.ajax.reload(); // Reload DataTable
                    })
                    .catch(error => {
                        toastr.error(
                            error.response?.data?.message || 'Failed to delete standing member.',
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

