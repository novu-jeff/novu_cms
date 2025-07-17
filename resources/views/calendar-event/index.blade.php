@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Calendar Events</h2>
            <p class="text-muted mb-0">Manage Calendar Events in this module</p>
        </div>

        <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
            <i class="fas fa-plus me-1"></i> Add Event
        </button>
    </div>

    @include('calendar-event.edit')

    <!-- Table Card -->
    <div class="card shadow border-1 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="myTable">
                    <thead>
                        <tr>
                            <th class="text-muted">Title</th>
                            <th class="text-muted">Description</th>
                            <th class="text-muted">Color</th>
                            <th class="text-muted">Date</th>
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
        let id;
        let DataTable = $('#myTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('calendar-event.index') }}',
            },
            columns: [
                { data: "title", name: 'title' },
                { data: "description", name: 'description' },
                { data: "color", name: 'color' },
                { data: "date", name: 'date' },
                { data: "actions", name: 'actions', orderable: false, searchable: false },
            ],
        });

        const myModal = $('#myModal');

        $('#create_button').click(e => {
            $('.update-button').hide();
            $('.submit-button').show();
            $('.modal-title').text('Add New Event');
            $('#myForm')[0].reset();
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            myModal.modal('show');
        });

        $('.submit-button').click(e => {
            e.preventDefault();
            $('.submit-button').prop('disabled', true);

            const formElement = document.getElementById('myForm');
            const formData = new FormData(formElement);

            axios.post(`${basePath}/calendar-event`, formData, {
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then((response) => {
                $('#myModal').modal('hide');
                $('#myForm')[0].reset();
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                $('.form-check-input').removeClass('is-invalid');

                DataTable.ajax.reload();

                toastr.success(response.data.message, 'Success');
            })
            .catch(error => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                $('.form-check-input').removeClass('is-invalid');

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    $.each(errors, (field, messages) => {
                        const input = $('#' + field);
                        const errorSpan = $('#' + field + '_error');
                        input.addClass('is-invalid');
                        console.log(input);
                        errorSpan.removeClass('d-none').text(messages[0]);
                    });
                } else {
                    toastr.error('Something went wrong!', 'Error');
                }
            })
            .finally(() => {
                $('.submit-button').prop('disabled', false);
            });
        });

        $(document).on('click', '.edit-button', function () {
            id = $(this).data('id');

            $('.modal-title').text('Edit Event');
            $('.submit-button').hide();
            $('.update-button').show();

            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
            $('.form-check-input').removeClass('is-invalid');

            axios.get(`${basePath}/calendar-event/${id}/edit`)
                .then(response => {
                    const e = response.data.data;
                    $('#title').val(e.title);
                    $('#description').val(e.description);
                    $('#start').val(e.start);
                    $('#end').val(e.end);
                    $('#all_day').prop('checked', e.all_day);
                    $('#color').val(e.color);

                    myModal.modal('show');
                })
                .catch(error => {
                    toastr.error('Failed to load event.', 'Error');
                });
        });

        $('.update-button').click(e => {
            e.preventDefault();
            $('.update-button').prop('disabled', true);

            const formElement = document.getElementById('myForm');
            const formData = new FormData(formElement);
            formData.append('_method', 'PUT');

            axios.post(`${basePath}/calendar-event/${id}`, formData, {
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then((response) => {
                $('#myModal').modal('hide');
                $('#myForm')[0].reset();
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                $('.form-check-input').removeClass('is-invalid');

                DataTable.ajax.reload();

                toastr.success(response.data.message, 'Updated');
            })
            .catch(error => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                $('.form-check-input').removeClass('is-invalid');

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    $.each(errors, (field, messages) => {
                        const input = $('#' + field);
                        const errorSpan = $('#' + field + '_error');
                        input.addClass('is-invalid');
                        errorSpan.removeClass('d-none').text(messages[0]);
                    });
                } else {
                    toastr.error('Something went wrong!', 'Error');
                }
            })
            .finally(() => {
                $('.update-button').prop('disabled', false);
            });
        });

        $(document).on('click', '.delete-button', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This will deactivate the event.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${basePath}/calendar-event/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        toastr.success(response.data.message || 'Event deleted successfully.', 'Success');
                        DataTable.ajax.reload();
                    })
                    .catch(error => {
                        toastr.error(error.response?.data?.message || 'Failed to delete event.', 'Error');
                    });
                }
            });
        });
    });
</script>
@endsection
