@extends('layouts.app')

@section('styles')
<style>
    .photo-gallery-page { --gallery-primary: #3157d5; --gallery-ink: #172033; --gallery-muted: #748094; padding: clamp(1rem, 2.4vw, 2rem) 0 2.5rem; }
    .photo-gallery-page .gallery-header { display:flex; align-items:center; justify-content:space-between; gap:1.25rem; flex-wrap:wrap; margin-bottom:1.75rem; padding:0 0 1.5rem; border-bottom:1px solid #e8edf4; }
    .photo-gallery-page .eyebrow { display:inline-flex; align-items:center; gap:.5rem; color:var(--gallery-primary); font-size:.75rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.55rem; }
    .photo-gallery-page .page-title { color:var(--gallery-ink); font-size:clamp(1.55rem, 2.5vw, 2rem); font-weight:800; letter-spacing:-.035em; margin:0 0 .4rem; overflow-wrap:anywhere; }
    .photo-gallery-page .page-subtitle { color:var(--gallery-muted); margin:0; font-size:.95rem; }
    .photo-gallery-page .header-actions { display:flex; gap:.65rem; flex-wrap:wrap; }
    .photo-gallery-page .btn-gallery { display:inline-flex; align-items:center; justify-content:center; gap:.55rem; border:0; border-radius:11px; padding:.8rem 1.1rem; font-weight:700; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease, background .18s ease; }
    .photo-gallery-page .btn-gallery:hover { transform:translateY(-1px); }
    .photo-gallery-page .btn-gallery-primary { background:var(--gallery-primary); color:#fff; box-shadow:0 6px 16px rgba(49,87,213,.2); }
    .photo-gallery-page .btn-gallery-primary:hover { background:#2447bb; color:#fff; box-shadow:0 8px 20px rgba(49,87,213,.25); }
    .photo-gallery-page .btn-gallery-back { color:#46536a; background:#fff; border:1px solid #dce3ed; }
    .photo-gallery-page .btn-gallery-back:hover { background:#f5f7fb; color:#202b3d; }
    .photo-gallery-page .gallery-panel { background:#fff; border:1px solid #e7ecf3; border-radius:18px; box-shadow:0 8px 28px rgba(24,39,75,.045); overflow:hidden; }
    .photo-gallery-page .gallery-panel-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; padding:1.25rem 1.5rem; border-bottom:1px solid #edf0f5; }
    .photo-gallery-page .panel-title { margin:0; color:var(--gallery-ink); font-size:1rem; font-weight:800; }
    .photo-gallery-page .panel-description { margin:.25rem 0 0; color:var(--gallery-muted); font-size:.85rem; }
    .photo-gallery-page .gallery-count { color:#53627a; background:#f1f4f9; border-radius:999px; padding:.35rem .7rem; font-size:.78rem; font-weight:800; white-space:nowrap; }
    .photo-gallery-page .gallery-content { padding:1.5rem; }
    .photo-gallery-page .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:1rem; }
    .photo-gallery-page .gallery-item { min-width:0; border:1px solid #e7ecf3; border-radius:14px; overflow:hidden; background:#f7f9fc; transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .photo-gallery-page .gallery-item:hover { transform:translateY(-3px); border-color:#d3dcf4; box-shadow:0 10px 24px rgba(24,39,75,.1); }
    .photo-gallery-page .gallery-image-link { position:relative; display:block; aspect-ratio:4 / 3; overflow:hidden; background:#e9edf4; }
    .photo-gallery-page .gallery-image { display:block; width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
    .photo-gallery-page .gallery-item:hover .gallery-image { transform:scale(1.04); }
    .photo-gallery-page .gallery-image-overlay { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; background:rgba(17,26,45,.0); opacity:0; transition:opacity .2s ease, background .2s ease; }
    .photo-gallery-page .gallery-item:hover .gallery-image-overlay { opacity:1; background:rgba(17,26,45,.2); }
    .photo-gallery-page .gallery-item-actions { display:flex; justify-content:flex-end; padding:.65rem .75rem; background:#fff; }
    .photo-gallery-page .delete-button { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border:1px solid #f2d6d9; border-radius:9px; background:#fff7f7; color:#c83b49; }
    .photo-gallery-page .delete-button:hover { background:#c83b49; color:#fff; border-color:#c83b49; }
    .photo-gallery-page .gallery-empty, .photo-gallery-page .gallery-loading { text-align:center; padding:3.5rem 1rem; color:var(--gallery-muted); }
    .photo-gallery-page .gallery-empty-icon { display:flex; align-items:center; justify-content:center; width:68px; height:68px; margin:0 auto 1rem; border-radius:20px; color:var(--gallery-primary); background:#edf2ff; font-size:1.65rem; }
    .photo-gallery-page .gallery-empty h3 { color:var(--gallery-ink); font-size:1.05rem; font-weight:800; margin-bottom:.4rem; }
    .photo-gallery-page .gallery-empty p { margin:0; font-size:.9rem; }
    .photo-gallery-page #imageContainer .alert { margin:0; }
    @media (max-width:575.98px) {
        .photo-gallery-page .header-actions { width:100%; }
        .photo-gallery-page .header-actions .btn-gallery { flex:1; padding:.75rem .7rem; }
        .photo-gallery-page .gallery-content { padding:1rem; }
        .photo-gallery-page .gallery-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.7rem; }
        .photo-gallery-page .gallery-panel-head { padding:1rem; }
    }
    @media (max-width:360px) { .photo-gallery-page .gallery-grid { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-lg-4 photo-gallery-page">
    <div class="gallery-header">
        <div>
            <div class="eyebrow"><i class="fas fa-images"></i> Photo Journal</div>
            <h1 class="page-title">{{ $data->name }}</h1>
            <p class="page-subtitle">View and manage photos in this album.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('photo-journals.index') }}" class="btn-gallery btn-gallery-back" id="back_button">
                <i class="fas fa-arrow-left"></i><span>Back to Albums</span>
            </a>
            <button type="button" class="btn-gallery btn-gallery-primary" id="create_button">
                <i class="fas fa-plus"></i><span>Add Images</span>
            </button>
        </div>
    </div>

    @include('photo-album.photo.create')

    <section class="gallery-panel" aria-label="Album photo gallery">
        <div class="gallery-panel-head">
            <div>
                <h2 class="panel-title">Album Gallery</h2>
                <p class="panel-description">Select a photo to view it in full size.</p>
            </div>
            <span class="gallery-count" id="galleryCount">Loading photos…</span>
        </div>
        <div class="gallery-content">
            <div id="imageContainer" aria-live="polite">
                <div class="gallery-loading"><i class="fas fa-spinner fa-spin me-2"></i>Loading photos…</div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    const alias = @json(config('app.alias'));
    const basePath = alias ? `/${alias}` : '';
    const albumId = @json($data->id);

    $(document).ready(function () {
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox="gallery"]', {
                Image: { fit: 'cover' },
                Toolbar: {
                    display: [
                        { id: 'counter', position: 'center' },
                        'zoom', 'fullscreen', 'download', 'thumbs', 'close'
                    ]
                }
            });
        }

        loadGalleryImages();

        const modalElement = document.getElementById('myModal');
        const getModal = () => {
            if (window.bootstrap && bootstrap.Modal && modalElement) {
                return bootstrap.Modal.getOrCreateInstance(modalElement);
            }
            return null;
        };
        const showModal = () => {
            const modal = getModal();
            if (modal) modal.show();
            else if (window.jQuery && typeof $('#myModal').modal === 'function') $('#myModal').modal('show');
        };
        const hideModal = () => {
            const modal = getModal();
            if (modal) modal.hide();
            else if (window.jQuery && typeof $('#myModal').modal === 'function') $('#myModal').modal('hide');
        };

        $('#create_button').on('click', function () {
            $('.modal-title').text('Upload Images');
            const form = document.getElementById('myForm');
            if (form) form.reset();
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
            showModal();
        });

        $('.submit-button').on('click', function (e) {
            e.preventDefault();
            const submitButton = $('.submit-button');
            if (submitButton.prop('disabled')) return;
            submitButton.prop('disabled', true);

            const formElement = document.getElementById('myForm');
            if (!formElement) {
                toastr.error('Upload form could not be found.', 'Error');
                submitButton.prop('disabled', false);
                return;
            }

            const formData = new FormData(formElement);
            // Ensure the current album is submitted even if the hidden field is absent.
            if (!formData.get('album_id')) formData.set('album_id', albumId);

            axios.post(`${basePath}/photo-journals/photos/store`, formData, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => {
                hideModal();
                formElement.reset();
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                toastr.success(response.data.message || 'Images uploaded successfully.', 'Success', { iconClass: 'toast-success' });
                loadGalleryImages();
            })
            .catch(error => {
                $('.text-danger').addClass('d-none');
                $('.form-control').removeClass('is-invalid');
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors || {};
                    $.each(errors, (field, messages) => {
                        const input = $('#' + field);
                        const errorSpan = $('#' + field + '_error');
                        input.addClass('is-invalid');
                        errorSpan.removeClass('d-none').text(messages[0]);
                    });
                } else {
                    toastr.error(error.response?.data?.message || 'Something went wrong while uploading images.', 'Error', { iconClass: 'toast-error' });
                }
            })
            .finally(() => submitButton.prop('disabled', false));
        });

        $(document).on('click', '.delete-button', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Delete this photo?',
                text: 'This photo will be removed from the album.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c83b49',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete photo!',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;
                axios.delete(`${basePath}/photo-journals/photos/${id}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => {
                    toastr.success(response.data.message || 'Image deleted successfully.', 'Success', { iconClass: 'toast-success' });
                    loadGalleryImages();
                })
                .catch(error => {
                    toastr.error(error.response?.data?.message || 'Failed to delete photo.', 'Error', { iconClass: 'toast-error' });
                });
            });
        });
    });

    function loadGalleryImages(endpoint = `${basePath}/api/photo-journals/photos/load/${albumId}`) {
        const container = $('#imageContainer');
        const count = $('#galleryCount');
        container.html('<div class="gallery-loading"><i class="fas fa-spinner fa-spin me-2"></i>Loading photos…</div>');
        count.text('Loading photos…');

        axios.get(endpoint, { headers: { 'Accept': 'application/json' } })
            .then(response => {
                const images = response.data.data ?? [];
                count.text(`${images.length} ${images.length === 1 ? 'photo' : 'photos'}`);

                if (!images.length) {
                    container.html(`
                        <div class="gallery-empty">
                            <div class="gallery-empty-icon"><i class="fas fa-images"></i></div>
                            <h3>No photos yet</h3>
                            <p>Upload the first photos to this album using the Add Images button.</p>
                        </div>
                    `);
                    return;
                }

                const grid = $('<div>', { class: 'gallery-grid' });
                images.forEach(image => {
                    const relativePath = String(image.image_path || '').replace(/^\/+/, '');
                    const imageUrl = `${basePath}/storage/${relativePath}`;
                    const item = $('<article>', { class: 'gallery-item' });
                    const link = $('<a>', {
                        class: 'gallery-image-link',
                        href: imageUrl,
                        'data-fancybox': 'gallery',
                        'aria-label': 'View photo full size'
                    });
                    const img = $('<img>', {
                        class: 'gallery-image',
                        src: imageUrl,
                        alt: 'Album photo',
                        loading: 'lazy'
                    });
                    img.on('error', function () {
                        $(this).attr('alt', 'Photo could not be loaded');
                        $(this).css('object-fit', 'contain');
                    });
                    link.append(img, $('<span>', { class: 'gallery-image-overlay' }).append($('<i>', { class: 'fas fa-expand-alt fa-lg' })));
                    const actions = $('<div>', { class: 'gallery-item-actions' });
                    const deleteButton = $('<button>', {
                        type: 'button',
                        class: 'delete-button',
                        'data-id': image.id,
                        'aria-label': 'Delete photo'
                    }).append($('<i>', { class: 'fas fa-trash-alt' }));
                    actions.append(deleteButton);
                    item.append(link, actions);
                    grid.append(item);
                });
                container.empty().append(grid);
                if (typeof Fancybox !== 'undefined') {
                    Fancybox.bind('[data-fancybox="gallery"]', {
                        Image: { fit: 'cover' },
                        Toolbar: { display: [{ id: 'counter', position: 'center' }, 'zoom', 'fullscreen', 'download', 'thumbs', 'close'] }
                    });
                }
            })
            .catch(error => {
                console.error('Failed to load images:', error);
                count.text('Unavailable');
                container.html('<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Failed to load photos. Please refresh the page and try again.</div>');
            });
    }
</script>
@endsection
