@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Barangay Officials</h2>
            <p class="text-muted mb-0">Manage barangay members in this module</p>
        </div>

        <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
            <i class="fas fa-plus me-1"></i> Add Official
        </button>
    </div>

    @include('barangay.edit')

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
                            <th class="text-muted">Barangay</th>
                            <th class="text-muted" style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX Data -->
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
                url: '{{ route('barangay-officials.index') }}',
            },
            columns: [
                { data: "image", name: 'image' },
                { data: "name", name: 'name' },
                { data: "position", name: 'position' },
                { data: "barangay", name: 'barangay' },
                { data: "actions", name: 'actions' },
            ],
        });

        const myModal = $('#myModal');

        $('#create_button').click(e => {
            $('.update-button').hide();
            $('.submit-button').show();
            $('.modal-title').html('Add Barangay Official');
            $('#myForm')[0].reset();
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
            $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');
            myModal.modal('show');
        });

        $('#image_path').on('change', function () {
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

        $('.submit-button').click(e => {
            e.preventDefault();
            $('.submit-button').prop('disabled', true);

            const formData = new FormData($('#myForm')[0]);

            axios.post('{{ route('barangay-officials.store') }}', formData)
                .then(res => {
                    myModal.modal('hide');
                    $('#myForm')[0].reset();
                    $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');
                    $('.text-danger').addClass('d-none');
                    $('.form-control').removeClass('is-invalid');
                    DataTable.ajax.reload();

                    toastr.success(res.data.message, 'Success');
                })
                .catch(err => {
                    $('.text-danger').addClass('d-none');
                    $('.form-control').removeClass('is-invalid');

                    if (err.response?.status === 422) {
                        const errors = err.response.data.errors;
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
                    $('.submit-button').prop('disabled', false);
                });
        });

        let id;

        $(document).on('click', '.edit-button', function () {
            id = $(this).data('id');

            $('.modal-title').text('Edit Barangay Official');
            $('.update-button').show();
            $('.submit-button').hide();
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            axios.get(`${basePath}/barangay-officials/${id}/edit`)
                .then(res => {
                    const b = res.data.data;

                    $('#name').val(b.name);
                    $('#position').val(b.position);
                    $('#barangay').val(b.barangay);

                    const imgUrl = b.image_path
                        ? `/storage/${b.image_path}`
                        : '{{ asset('default/profile.png') }}';

                    $('#image_preview').attr('src', imgUrl).removeClass('d-none');

                    myModal.modal('show');
                })
                .catch(() => {
                    toastr.error('Failed to fetch member data.', 'Error');
                });
        });

        $('.update-button').click(e => {
            e.preventDefault();
            $('.update-button').prop('disabled', true);

            const formData = new FormData($('#myForm')[0]);
            formData.append('_method', 'PUT');

            axios.post(`${basePath}/barangay-officials/${id}`, formData)
                .then(res => {
                    myModal.modal('hide');
                    DataTable.ajax.reload();
                    toastr.success(res.data.message, 'Updated');
                })
                .catch(err => {
                    $('.text-danger').addClass('d-none');
                    $('.form-control').removeClass('is-invalid');

                    if (err.response?.status === 422) {
                        const errors = err.response.data.errors;
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
                text: "This will deactivate the barangay official.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${basePath}/barangay-officials/${id}`)
                        .then(res => {
                            toastr.success(res.data.message, 'Deleted');
                            DataTable.ajax.reload();
                        })
                        .catch(() => {
                            toastr.error('Failed to delete official.', 'Error');
                        });
                }
            });
        });
    });
</script>
@endsection
