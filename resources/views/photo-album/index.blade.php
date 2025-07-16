@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2 border-bottom pb-4">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Photo Journal</h2>
            <p class="text-muted mb-0">Manage albums in this module</p>
        </div>

        <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
            <i class="fas fa-plus me-1"></i> Add Album
        </button>
    </div>

    @include('photo-album.edit')

    <div class="row">
        @forelse($data as $album)
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 mb-4 d-flex justify-content-center">
                <div class="album-card">
                    <div class="album-title">{{ $album->name }}</div>
                    <a href="{{ route('photo.index', $album->id) }}">
                        <i class="folder fa-solid fa-folder-open"></i>
                        @if($album->image_path)
                            <img src="{{ asset('storage/' . $album->image_path) }}" alt="Album Photo" class="album-image">
                        @else
                            <img src="{{ asset('default/no-image.png') }}" alt="No Image" class="album-image">
                        @endif
                    </a>

                    <div class="dropdown text-end">
                        <button type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item edit-button" data-id="{{ $album->id }}">Edit</a></li>
                            <li><a class="dropdown-item delete-button" data-id="{{ $album->id }}">Delete</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No albums found.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-end">
        {{ $data->links('pagination::bootstrap-5') }}
    </div>

</div>
@endsection

@section('scripts')

<script>
    $(document).ready(function () {
       
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

            myModal.modal('show')
        });

        // submit
        $('.submit-button').click(e => {
            e.preventDefault();
            $('.submit-button').prop('disabled', true);

            const formElement = document.getElementById('myForm');
            const formData = new FormData(formElement);

            axios.post('/photo-journals', formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((response) => {
                $('#myModal').modal('hide');
                $('#myForm')[0].reset();
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                toastr.success(response.data.message, 'Success', {
                    iconClass: 'toast-success'
                });
                
                toastr.warning('<i class="fas fa-spinner fa-spin"></i> Redirecting...', '', {
                    timeOut: 2000,
                    extendedTimeOut: 1000,
                    closeButton: false,
                    tapToDismiss: false,
                    iconClass: 'toast-info'
                });

                setTimeout(() => {
                    window.location.href = response.data.redirect_url;
                }, 2000);
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
            id = $(this).data('id');

            $('.modal-title').text('Edit Album');
            $('.update-button').show();
            $('.submit-button').hide();

            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            axios.get(`/photo-journals/${id}/edit`)
                .then(response => {
                    const m = response.data.data;

                    $('#name').val(m.name);
                    $('#myModal').modal('show');
                })
                .catch(error => {
                    toastr.error(error.message || 'Something went wrong', 'Error');
                });
        });

        // update
        $('.update-button').click(e => {
            e.preventDefault();
            $('.update-button').prop('disabled', true);

            const formElement = document.getElementById('myForm');
            const formData = new FormData(formElement);
            formData.append('_method', 'PUT');

            axios.post(`/photo-journals/${id}`, formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((response) => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');


                toastr.success(response.data.message, 'Updated', {
                    iconClass: 'toast-success'
                });

                toastr.warning('<i class="fas fa-spinner fa-spin"></i> reloading...', '', {
                    timeOut: 2000,
                    extendedTimeOut: 1000,
                    closeButton: false,
                    tapToDismiss: false,
                    iconClass: 'toast-info'
                });

                setTimeout(() => {
                    location.reload();
                }, 2000);
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
                    axios.delete(`/photo-journals/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        toastr.success(response.data.message || 'Member deleted successfully.', 'Success', {
                            iconClass: 'toast-success'
                        });
                        
                        toastr.warning('<i class="fas fa-spinner fa-spin"></i> reloading...', '', {
                            timeOut: 2000,
                            extendedTimeOut: 1000,
                            closeButton: false,
                            tapToDismiss: false,
                            iconClass: 'toast-info'
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
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

