@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">📸 Photo Gallery</h2>
            <p class="text-muted mb-0">Manage and view uploaded photos</p>
        </div>

        <button type="button" class="btn btn-primary p-3 px-5" id="upload_button">
            <i class="fas fa-upload me-1"></i> Upload Photos
        </button>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Upload Photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Title (optional)</label>
                            <input type="text" name="title" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Images</label>
                            <input type="file" name="images[]" multiple class="form-control" accept="image/*" required>
                        </div>

                        <div id="preview" class="row g-2"></div>

                        <button type="submit" class="btn btn-primary mt-3 w-100">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Section -->
    <div id="gallery" class="row g-3 mt-4">
        <!-- Photos loaded via API -->
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = "{{ url('/') }}";
    const gallery = document.getElementById('gallery');
    const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));

    // Show upload modal
    document.getElementById('upload_button').addEventListener('click', () => {
        document.getElementById('uploadForm').reset();
        document.getElementById('preview').innerHTML = '';
        uploadModal.show();
    });

    // Preview selected images
    document.querySelector('input[name="images[]"]').addEventListener('change', function (e) {
        const preview = document.getElementById('preview');
        preview.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = ev => {
                const col = document.createElement('div');
                col.className = 'col-4';
                col.innerHTML = `<img src="${ev.target.result}" class="img-fluid rounded-3 border">`;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });

    // Load photos via API
    function loadGallery() {
        axios.get(`${baseUrl}/api/photos`)
            .then(res => {
                gallery.innerHTML = '';
                const photos = res.data.photos;

                if (photos.length === 0) {
                    gallery.innerHTML = `<p class="text-center text-muted mt-5">No photos uploaded yet.</p>`;
                    return;
                }

                photos.forEach(photo => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 col-sm-6';

                    // Dynamic color & icon
                    const isActive = photo.active == 1;
                    const activeClass = photo.active ? 'btn-success' : 'btn-danger';
                    const icon = photo.active ? 'fa-toggle-on' : 'fa-toggle-off';
                    const label = photo.active ? 'Active' : 'Inactive';

                    col.innerHTML = `
                        <div class="card shadow-sm border-0">
                            <img src="${photo.image_url}" class="card-img-top" alt="${photo.title}">
                            <div class="card-body text-center">
                                <p class="card-text">${photo.title ?? 'Untitled'}</p>
                                <button class="btn btn-sm ${activeClass} toggle-active" data-id="${photo.id}">
                                    <i class="fas ${icon}"></i> ${label}
                                </button>
                                <button class="btn btn-sm btn-danger delete-button" data-id="${photo.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>`;
                    gallery.appendChild(col);
                });
            })
            .catch(() => {
                toastr.error('Failed to load photos.');
            });
    }

    loadGallery(); // initial load

    // Upload form submit
    document.getElementById('uploadForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        axios.post(`${baseUrl}/upload`, formData)
            .then(res => {
                toastr.success('Photos uploaded successfully!');
                uploadModal.hide();
                loadGallery();
            })
            .catch(err => {
                toastr.error('Upload failed. Please check image size or type.');
            });
    });

    // Toggle Active/Inactive
    document.addEventListener('click', function (e) {
        if (e.target.closest('.toggle-active')) {
            const button = e.target.closest('.toggle-active');
            const id = button.dataset.id;
            const isActive = button.classList.contains('btn-success'); // current status
            const newStatus = !isActive;

            axios.post(`${baseUrl}/api/photo/toggle/${id}`, { active: newStatus })
                .then(res => {
                    if (res.data.active) {
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="fas fa-toggle-on"></i> Active';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-danger');
                        button.innerHTML = '<i class="fas fa-toggle-off"></i> Inactive';
                    }

                    toastr.success('Photo status updated!');
                })
                .catch(() => {
                    toastr.error('Failed to toggle photo status.');
                });
        }
    });


    // Delete photo
    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-button')) {
            const id = e.target.closest('.delete-button').dataset.id;

            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the photo.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33'
            }).then(result => {
                if (result.isConfirmed) {
                    axios.delete(`${baseUrl}/api/photo/${id}`)
                        .then(() => {
                            toastr.success('Photo deleted successfully!');
                            loadGallery();
                        })
                        .catch(() => {
                            toastr.error('Failed to delete photo.');
                        });
                }
            });
        }
    });
    
});
</script>
@endsection
