@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2 border-bottom pb-4">
        <div>
            <h2 class="fw-semibold text-dark mb-1">{{ $data->name }}</h2>
            <p class="text-muted mb-0 fs-6">
                Manage <span class="fw-semibold">{{ $data->name }}</span> album in this module
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('photo-journals.index') }}" class="btn btn-danger p-3 px-5" id="back_button">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
                <i class="fas fa-plus me-1"></i> Add Images
            </button>
        </div>
    </div>

    @include('photo-album.photo.create')

    <div class="container">
        <div class="row" id="imageContainer">

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const alias = @json(config('app.alias'));
    const basePath = alias ? `/${alias}` : '';
    const albumId = @json($data->id);

    $(document).ready(function () {
       Fancybox.bind("[data-fancybox]", {
            Image: {
                fit: "cover"
            },
            Toolbar: {
                display: [
                    { id: "counter", position: "center" },
                    "zoom",
                    "fullscreen",
                    "download",
                    "thumbs",
                    "close",
                ],
            },
        });

        loadGalleryImages();

        const myModal = $('#myModal');

        // Create
        $('#create_button').click(e => {
            $('.modal-title').html('Upload Images');
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

            const albumId = formData.get('album_id');

            axios.post(`${basePath}/photo-journals/photos/store`, formData, {
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

                loadGalleryImages();
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
                    axios.delete(`${basePath}/photo-journals/photos/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        toastr.success(response.data.message || 'Image deleted successfully.', 'Success', {
                            iconClass: 'toast-success'
                        });
                        loadGalleryImages();
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

    function loadGalleryImages(endpoint = `${basePath}/api/photo-journals/photos/load/${albumId}`) {
        const container = $('#imageContainer');
        
        container.empty();

        axios.get(endpoint)
            .then(function (response) {
                const images = response.data.data ?? [];
                const container = $('#imageContainer');

                container.empty();

                if (images.length === 0) {
                    container.append(`
                        <div class="col-12 text-center text-muted py-5">
                            <i class="fas fa-image fa-3x mb-2"></i><br>
                            <span>No images yet.</span>
                        </div>
                    `);
                    return;
                }

                images.forEach(image => {
                    const imagePath = image.image_path; 
                    const imageUrl = `/storage/${imagePath}`;

                    const imageCard = `
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 mb-4">
                            <div class="image position-relative">
                                <a data-fancybox="gallery" href="${basePath}${imageUrl}">
                                    <img src="${basePath}${imageUrl}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;" />
                                </a>
                                <div class="btns">
                                    <button type="button" data-id="${image.id}" class="btn-close delete-button text-light" aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                    `;

                    container.append(imageCard);
                });

                Fancybox.bind("[data-fancybox='gallery']");
            })

            .catch(function (error) {
                console.error('Failed to load images:', error);
                container.html('<p class="text-danger">Failed to load images.</p>');
            });
    }
</script>
@endsection

