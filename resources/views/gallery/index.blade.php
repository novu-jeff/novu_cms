@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary"
                      style="width: 44px; height: 44px;">
                    <i class="fas fa-images fs-5"></i>
                </span>
                <h3 class="fw-bold text-dark mb-0">Photo Gallery</h3>
            </div>
            <p class="text-muted mb-0 ms-md-1">Manage and organize your uploaded photos.</p>
        </div>

        <button type="button" class="btn btn-primary px-4 py-2 shadow-sm" id="upload_button">
            <i class="fas fa-cloud-upload-alt me-2"></i> Upload Photos
        </button>
    </div>

    {{-- Gallery Summary --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 p-md-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center"
                     style="width: 48px; height: 48px;">
                    <i class="fas fa-photo-video text-primary fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-semibold mb-1">Gallery Collection</h6>
                    <p class="text-muted small mb-0">Photos available in your CMS gallery</p>
                </div>
            </div>

            <div class="text-md-end">
                <div class="fs-3 fw-bold text-dark" id="photoCount">—</div>
                <div class="small text-muted">Total Photos</div>
            </div>
        </div>
    </div>

    {{-- Upload Modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1"
         aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold" id="uploadModalLabel">Upload Photos</h5>
                        <p class="text-muted small mb-0">Select one or more images to add to the gallery.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="photoTitle" class="form-label fw-semibold">
                                Title <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input type="text" name="title" id="photoTitle"
                                   class="form-control form-control-lg"
                                   placeholder="Enter a gallery title">
                        </div>

                        <div class="mb-3">
                            <label for="photoImages" class="form-label fw-semibold">Select Images</label>
                            <input type="file" name="images[]" id="photoImages"
                                   class="form-control" multiple accept="image/*" required>
                            <div class="form-text">You can select multiple image files.</div>
                        </div>

                        <div id="preview" class="row g-3"></div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4"
                                    data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4" id="uploadSubmit">
                                <i class="fas fa-upload me-2"></i> Upload Photos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Gallery --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">All Photos</h5>
        <button type="button" class="btn btn-sm btn-light border" id="refreshGallery">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
    </div>

    <div id="gallery" class="row g-3">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mb-0">Loading gallery...</p>
        </div>
    </div>
</div>

{{-- Photo card styles --}}
<style>
    #gallery .photo-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid #edf0f4;
        border-radius: 16px;
        background: #fff;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    #gallery .photo-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08) !important;
    }

    #gallery .photo-image-wrap {
        position: relative;
        height: 210px;
        overflow: hidden;
        background: #f4f6f9;
    }

    #gallery .photo-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    #gallery .photo-card .card-body {
        padding: 1rem;
    }

    #gallery .photo-title {
        overflow-wrap: anywhere;
        min-height: 2.8rem;
    }

    #preview img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    @media (max-width: 575.98px) {
        #gallery .photo-image-wrap {
            height: 190px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = @json(url('/'));
    const gallery = document.getElementById('gallery');
    const uploadForm = document.getElementById('uploadForm');
    const uploadButton = document.getElementById('upload_button');
    const uploadSubmit = document.getElementById('uploadSubmit');
    const preview = document.getElementById('preview');
    const photoInput = document.getElementById('photoImages');
    const photoCount = document.getElementById('photoCount');
    const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));

    // Escape untrusted text before inserting it into HTML.
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function showError(message) {
        if (window.toastr) {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    function showSuccess(message) {
        if (window.toastr) {
            toastr.success(message);
        }
    }

    function renderEmptyState() {
        gallery.innerHTML = `
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-light"
                             style="width: 76px; height: 76px;">
                            <i class="fas fa-images text-secondary fs-2"></i>
                        </div>
                        <h5 class="fw-bold">No Photos Yet</h5>
                        <p class="text-muted mb-3">Upload your first photos to start building the gallery.</p>
                        <button type="button" class="btn btn-primary" id="emptyUploadButton">
                            <i class="fas fa-upload me-2"></i> Upload Photos
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('emptyUploadButton').addEventListener('click', openUploadModal);
    }

    function renderLoadError() {
        gallery.innerHTML = `
            <div class="col-12">
                <div class="alert alert-warning rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="fas fa-exclamation-triangle me-2"></i>Unable to load photos.</span>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="retryGallery">
                        Try Again
                    </button>
                </div>
            </div>
        `;

        document.getElementById('retryGallery').addEventListener('click', loadGallery);
    }

    function openUploadModal() {
        uploadForm.reset();
        preview.innerHTML = '';
        uploadModal.show();
    }

    uploadButton.addEventListener('click', openUploadModal);

    document.getElementById('refreshGallery').addEventListener('click', loadGallery);

    // Preview selected images.
    photoInput.addEventListener('change', function (event) {
        preview.innerHTML = '';

        Array.from(event.target.files || []).forEach(function (file) {
            if (!file.type.startsWith('image/')) {
                return;
            }

            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';

            const image = document.createElement('img');
            image.className = 'img-fluid rounded-3 border';
            image.alt = file.name;
            image.style.height = '120px';
            image.style.width = '100%';
            image.style.objectFit = 'cover';

            const caption = document.createElement('div');
            caption.className = 'small text-muted text-truncate mt-1';
            caption.textContent = file.name;

            col.appendChild(image);
            col.appendChild(caption);
            preview.appendChild(col);

            const reader = new FileReader();
            reader.onload = function (result) {
                image.src = result.target.result;
            };
            reader.readAsDataURL(file);
        });
    });

    // Load photos from the existing API.
    function loadGallery() {
        gallery.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mb-0">Loading gallery...</p>
            </div>
        `;

        axios.get(`${baseUrl}/api/photos`)
            .then(function (response) {
                const photos = Array.isArray(response.data.photos)
                    ? response.data.photos
                    : [];

                photoCount.textContent = photos.length;
                gallery.innerHTML = '';

                if (photos.length === 0) {
                    renderEmptyState();
                    return;
                }

                photos.forEach(function (photo) {
                    const isActive = Number(photo.active) === 1;
                    const col = document.createElement('div');
                    col.className = 'col-12 col-sm-6 col-lg-4 col-xl-3';

                    const title = photo.title || 'Untitled';
                    const statusClass = isActive ? 'btn-success' : 'btn-secondary';
                    const statusIcon = isActive ? 'fa-toggle-on' : 'fa-toggle-off';
                    const statusLabel = isActive ? 'Active' : 'Inactive';

                    col.innerHTML = `
                        <div class="card photo-card shadow-sm">
                            <div class="photo-image-wrap">
                                <img
                                    src="${escapeHtml(photo.image_url)}"
                                    class="photo-image"
                                    alt="${escapeHtml(title)}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.parentElement.classList.add('d-flex','align-items-center','justify-content-center'); this.parentElement.insertAdjacentHTML('beforeend','<span class=&quot;text-muted small&quot;>Image unavailable</span>');"
                                >
                            </div>
                            <div class="card-body">
                                <h6 class="photo-title fw-semibold text-dark mb-3">
                                    ${escapeHtml(title)}
                                </h6>

                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <button type="button"
                                            class="btn btn-sm ${statusClass} toggle-active"
                                            data-id="${escapeHtml(photo.id)}">
                                        <i class="fas ${statusIcon} me-1"></i>${statusLabel}
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger delete-button"
                                            data-id="${escapeHtml(photo.id)}">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    gallery.appendChild(col);
                });
            })
            .catch(function () {
                photoCount.textContent = '—';
                renderLoadError();
                showError('Failed to load photos.');
            });
    }

    // Upload photos.
    uploadForm.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!photoInput.files || photoInput.files.length === 0) {
            showError('Please select at least one image.');
            return;
        }

        const formData = new FormData(uploadForm);
        uploadSubmit.disabled = true;
        uploadSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

        axios.post(`${baseUrl}/upload`, formData)
            .then(function () {
                showSuccess('Photos uploaded successfully!');
                uploadModal.hide();
                uploadForm.reset();
                preview.innerHTML = '';
                loadGallery();
            })
            .catch(function (error) {
                const message = error.response?.data?.message
                    || 'Upload failed. Please check image size or type.';
                showError(message);
            })
            .finally(function () {
                uploadSubmit.disabled = false;
                uploadSubmit.innerHTML = '<i class="fas fa-upload me-2"></i> Upload Photos';
            });
    });

    // Toggle photo status.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.toggle-active');

        if (!button) {
            return;
        }

        const id = button.dataset.id;
        const wasActive = button.classList.contains('btn-success');
        const newStatus = !wasActive;

        button.disabled = true;

        axios.post(`${baseUrl}/api/photo/toggle/${encodeURIComponent(id)}`, {
            active: newStatus
        })
            .then(function (response) {
                const active = Boolean(response.data.active);

                button.classList.toggle('btn-success', active);
                button.classList.toggle('btn-secondary', !active);
                button.innerHTML = active
                    ? '<i class="fas fa-toggle-on me-1"></i>Active'
                    : '<i class="fas fa-toggle-off me-1"></i>Inactive';

                showSuccess('Photo status updated!');
            })
            .catch(function () {
                showError('Failed to update photo status.');
            })
            .finally(function () {
                button.disabled = false;
            });
    });

    // Delete photo.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.delete-button');

        if (!button) {
            return;
        }

        const id = button.dataset.id;

        Swal.fire({
            title: 'Delete this photo?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            button.disabled = true;

            axios.delete(`${baseUrl}/api/photo/${encodeURIComponent(id)}`)
                .then(function () {
                    showSuccess('Photo deleted successfully!');
                    loadGallery();
                })
                .catch(function () {
                    showError('Failed to delete photo.');
                })
                .finally(function () {
                    button.disabled = false;
                });
        });
    });

    // Initial load.
    loadGallery();
});
</script>
@endsection
