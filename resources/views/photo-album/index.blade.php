@extends('layouts.app')

@section('styles')
<style>
    .photo-journal-page { --pj-primary:#315ee8; --pj-ink:#172033; --pj-muted:#748096; padding:clamp(1.25rem, 3vw, 2.5rem); }
    .pj-header { display:flex; align-items:center; justify-content:space-between; gap:1.25rem; margin-bottom:1.75rem; }
    .pj-eyebrow { display:inline-flex; align-items:center; gap:.5rem; margin-bottom:.45rem; color:var(--pj-primary); font-size:.75rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .pj-title { color:var(--pj-ink); font-size:clamp(1.65rem, 2.4vw, 2.15rem); font-weight:800; letter-spacing:-.04em; margin:0; }
    .pj-subtitle { color:var(--pj-muted); margin:.45rem 0 0; }
    .pj-add-btn { display:inline-flex; align-items:center; justify-content:center; gap:.55rem; padding:.8rem 1.15rem; border:0; border-radius:12px; background:var(--pj-primary); color:#fff; font-weight:700; box-shadow:0 8px 18px rgba(49,94,232,.18); transition:transform .2s, box-shadow .2s, background .2s; white-space:nowrap; }
    .pj-add-btn:hover { color:#fff; background:#244bd0; transform:translateY(-1px); box-shadow:0 11px 22px rgba(49,94,232,.24); }
    .pj-library { background:#fff; border:1px solid #e8edf5; border-radius:20px; padding:1.35rem; box-shadow:0 8px 28px rgba(22,34,58,.045); }
    .pj-library-heading { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.25rem; }
    .pj-library-heading h3 { margin:0; color:var(--pj-ink); font-size:1rem; font-weight:800; }
    .pj-library-heading p { margin:.3rem 0 0; color:var(--pj-muted); font-size:.875rem; }
    .pj-album-count { display:inline-flex; align-items:center; gap:.4rem; border-radius:999px; background:#f1f5ff; color:#3155c9; padding:.4rem .7rem; font-size:.78rem; font-weight:800; white-space:nowrap; }
    .pj-album-card { position:relative; height:100%; min-width:0; overflow:hidden; border:1px solid #e8edf5; border-radius:16px; background:#fff; transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .pj-album-card:hover { transform:translateY(-4px); border-color:#d6e0ff; box-shadow:0 14px 30px rgba(27,44,82,.10); }
    .pj-album-cover-link { position:relative; display:block; overflow:hidden; aspect-ratio:4 / 3; background:linear-gradient(135deg,#eef3ff,#f7f9fc); }
    .pj-album-cover-link::after { position:absolute; inset:0; content:""; background:linear-gradient(180deg,transparent 55%,rgba(15,23,42,.15)); pointer-events:none; }
    .pj-album-image { display:block; width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
    .pj-album-card:hover .pj-album-image { transform:scale(1.045); }
    .pj-album-placeholder { display:flex; height:100%; min-height:140px; align-items:center; justify-content:center; color:#8292b5; font-size:2.5rem; }
    .pj-album-open { position:absolute; z-index:1; right:.75rem; bottom:.75rem; display:grid; width:36px; height:36px; place-items:center; border-radius:11px; background:rgba(255,255,255,.94); color:var(--pj-primary); box-shadow:0 3px 10px rgba(0,0,0,.1); }
    .pj-album-info { display:flex; align-items:center; justify-content:space-between; gap:.5rem; padding:.85rem .9rem; }
    .pj-album-name { min-width:0; margin:0; color:var(--pj-ink); font-size:.92rem; font-weight:750; line-height:1.4; overflow-wrap:anywhere; }
    .pj-album-actions .pj-action-toggle { display:grid; width:34px; height:34px; place-items:center; border:1px solid transparent; border-radius:10px; background:transparent; color:#68758b; }
    .pj-album-actions .pj-action-toggle:hover, .pj-album-actions .pj-action-toggle[aria-expanded="true"] { border-color:#e3e9f4; background:#f6f8fc; color:var(--pj-ink); }
    .pj-album-actions .dropdown-menu { min-width:145px; padding:.35rem; border:1px solid #e8edf5; border-radius:12px; box-shadow:0 12px 30px rgba(23,32,51,.12); }
    .pj-album-actions .dropdown-item { display:flex; align-items:center; gap:.6rem; padding:.6rem .7rem; border-radius:8px; font-size:.875rem; }
    .pj-album-actions .dropdown-item.edit-button { color:#334155; }
    .pj-album-actions .dropdown-item.delete-button { color:#c03946; }
    .pj-empty-state { padding:3.25rem 1rem; text-align:center; border:1px dashed #d8e0ed; border-radius:16px; background:#fafcff; }
    .pj-empty-icon { display:grid; width:64px; height:64px; place-items:center; margin:0 auto 1rem; border-radius:20px; background:#edf2ff; color:var(--pj-primary); font-size:1.55rem; }
    .pj-empty-state h4 { margin:0 0 .45rem; color:var(--pj-ink); font-size:1.05rem; font-weight:800; }
    .pj-empty-state p { max-width:400px; margin:0 auto 1.15rem; color:var(--pj-muted); font-size:.9rem; }
    .pj-pagination { display:flex; justify-content:flex-end; padding-top:1.25rem; }
    #myModal .modal-content { overflow:hidden; border:0; border-radius:18px; box-shadow:0 24px 70px rgba(15,23,42,.2); }
    #myModal .modal-header { padding:1.25rem 1.4rem; border-bottom:1px solid #edf0f6; }
    #myModal .modal-title { color:var(--pj-ink); font-weight:800; }
    #myModal .modal-body { padding:1.4rem; }
    #myModal .form-control, #myModal .form-select { min-height:44px; border-color:#dfe5ef; border-radius:10px; box-shadow:none; }
    #myModal .form-control:focus, #myModal .form-select:focus { border-color:#8ba5ff; box-shadow:0 0 0 .2rem rgba(49,94,232,.12); }
    @media (max-width:767.98px) { .photo-journal-page { padding:1rem; } .pj-header { align-items:flex-start; flex-direction:column; } .pj-add-btn { width:100%; } .pj-library { padding:.9rem; border-radius:15px; } .pj-library-heading { align-items:flex-start; } .pj-album-info { padding:.7rem; } .pj-album-name { font-size:.85rem; } }
</style>
@endsection

@section('content')
<div class="container-fluid photo-journal-page">
    <div class="pj-header">
        <div>
            <div class="pj-eyebrow"><i class="fa-solid fa-images"></i> Media Library</div>
            <h1 class="pj-title">Photo Journal</h1>
            <p class="pj-subtitle">Create and organize photo albums for your journal.</p>
        </div>
        <button type="button" class="pj-add-btn" id="create_button">
            <i class="fas fa-plus"></i> Add Album
        </button>
    </div>

    @include('photo-album.edit')

    <section class="pj-library">
        <div class="pj-library-heading">
            <div>
                <h3>Albums</h3>
                <p>Choose an album to view and manage its photos.</p>
            </div>
            <span class="pj-album-count"><i class="fa-regular fa-folder-open"></i> {{ $data->total() }} {{ \Illuminate\Support\Str::plural('album', $data->total()) }}</span>
        </div>

        <div class="row g-3 g-lg-4">
            @forelse($data as $album)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <article class="pj-album-card">
                        <a class="pj-album-cover-link" href="{{ route('photo.index', $album->id) }}" aria-label="Open album {{ $album->name }}">
                            @if($album->image_path)
                                <img src="{{ asset('storage/' . $album->image_path) }}" alt="{{ $album->name }} album cover" class="pj-album-image">
                            @else
                                <div class="pj-album-placeholder"><i class="fa-regular fa-images"></i></div>
                            @endif
                            <span class="pj-album-open"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                        </a>
                        <div class="pj-album-info">
                            <h4 class="pj-album-name" title="{{ $album->name }}">{{ $album->name }}</h4>
                            <div class="dropdown pj-album-actions">
                                <button type="button" class="pj-action-toggle" id="albumMenu{{ $album->id }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for {{ $album->name }}">
                                    <i class="fas fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="albumMenu{{ $album->id }}">
                                    <li><a href="#" class="dropdown-item edit-button" data-id="{{ $album->id }}"><i class="fa-regular fa-pen-to-square"></i> Edit album</a></li>
                                    <li><a href="#" class="dropdown-item delete-button"><i class="fa-regular fa-trash-can"></i> Delete album</a></li>
                                    <span class="d-none album-action-id">{{ $album->id }}</span>
                                </ul>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="pj-empty-state">
                        <div class="pj-empty-icon"><i class="fa-regular fa-images"></i></div>
                        <h4>No albums yet</h4>
                        <p>Create your first photo album to start organizing photos in the Photo Journal.</p>
                        <button type="button" class="pj-add-btn" onclick="document.getElementById('create_button').click()"><i class="fas fa-plus"></i> Create first album</button>
                    </div>
                </div>
            @endforelse
        </div>

        @if($data->hasPages())
            <div class="pj-pagination">{{ $data->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
</div>
@endsection

@section('scripts')



<script>

    const alias = @json(config('app.alias'));

    const basePath = alias ? `/${alias}` : '';



    $(document).ready(function () {

        const myModal = $('#myModal');



        // Create

        $('#create_button').click(e => {

            $('.update-button').hide();

            $('.update-section').hide();

            $('.submit-button').show();

            $('.modal-title').html('Add New Album');

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



            axios.post(`${basePath}/photo-journals`, formData, {

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



            axios.get(`${basePath}/photo-journals/${id}/edit`)

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



            axios.post(`${basePath}/photo-journals/${id}`, formData, {

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

            const id = $(this).closest('.dropdown-menu').find('.album-action-id').text().trim();



            Swal.fire({

                title: 'Are you sure?',

                text: "This will delete this photo album.",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Yes, delete it!'

            }).then((result) => {

                if (result.isConfirmed) {

                    axios.delete(`${basePath}/photo-journals/${id}`, {

                        headers: {

                            'Accept': 'application/json'

                        }

                    })

                    .then(response => {

                        toastr.success(response.data.message || 'Album deleted successfully.', 'Success', {

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

                            error.response?.data?.message || 'Failed to delete album.',

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


