@extends('layouts.app')

@section('styles')
<style>
    .org-chart-page { --org-ink:#172033; --org-muted:#718096; --org-line:#e8edf4; padding-top:2rem; padding-bottom:2.5rem; }
    .org-chart-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; }
    .org-chart-eyebrow { color:#64748b; font-size:.75rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.45rem; }
    .org-chart-title { color:var(--org-ink); font-size:clamp(1.5rem,2.2vw,2rem); font-weight:750; letter-spacing:-.035em; margin:0; }
    .org-chart-subtitle { color:var(--org-muted); margin:.45rem 0 0; }
    .org-chart-add { display:inline-flex; align-items:center; gap:.55rem; border:0; border-radius:12px; padding:.8rem 1.15rem; font-weight:650; color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); box-shadow:0 8px 18px rgba(37,99,235,.18); white-space:nowrap; }
    .org-chart-add:hover { color:#fff; filter:brightness(1.04); transform:translateY(-1px); }
    .org-chart-panel { background:#fff; border:1px solid var(--org-line); border-radius:18px; box-shadow:0 12px 34px rgba(15,23,42,.055); overflow:hidden; }
    .org-chart-panel-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.25rem 1.5rem; border-bottom:1px solid var(--org-line); }
    .org-chart-panel-title { color:var(--org-ink); font-weight:700; margin:0; }
    .org-chart-panel-caption { color:var(--org-muted); font-size:.875rem; margin:.25rem 0 0; }
    .org-chart-panel-body { padding:1.25rem 1.5rem 1.5rem; }
    #myTable { margin-bottom:0!important; border-color:var(--org-line); }
    #myTable thead th { color:#64748b; background:#f8fafc; border-bottom:1px solid var(--org-line); font-size:.73rem; font-weight:750; letter-spacing:.06em; text-transform:uppercase; padding:1rem .9rem; white-space:nowrap; }
    #myTable tbody td { color:#334155; padding:.9rem; vertical-align:middle; }
    #myTable tbody tr:hover { background:#f8fbff; }
    #myTable .btn, #myTable button { border-radius:9px; }
    .org-chart-panel .dataTables_wrapper .dataTables_filter input, .org-chart-panel .dataTables_wrapper .dataTables_length select { border:1px solid #dce3ed; border-radius:9px; padding:.4rem .65rem; outline:none; }
    .org-chart-panel .dataTables_wrapper .dataTables_filter input:focus { border-color:#93b4ff; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
    .org-chart-panel .dataTables_wrapper .dataTables_length, .org-chart-panel .dataTables_wrapper .dataTables_filter { margin-bottom:1rem; color:#64748b; }
    .org-chart-panel .dataTables_wrapper .dataTables_info, .org-chart-panel .dataTables_wrapper .dataTables_paginate { padding-top:1rem; color:#64748b; }
    #myModal .modal-content { border:0; border-radius:18px; overflow:hidden; box-shadow:0 24px 70px rgba(15,23,42,.2); }
    #myModal .modal-header { padding:1.25rem 1.5rem; border-bottom:1px solid var(--org-line); }
    #myModal .modal-title { color:var(--org-ink); font-weight:750; }
    #myModal .modal-body { padding:1.5rem; }
    #myModal .form-control, #myModal .form-select { min-height:44px; border-radius:10px; border-color:#dce3ed; }
    #myModal .form-control:focus, #myModal .form-select:focus { border-color:#93b4ff; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
    #image_preview { max-width:100px; max-height:100px; object-fit:cover; border-radius:14px; }
    .org-chart-panel .dataTables_wrapper .dataTables_length select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background: none !important;
        background-image: none !important;
        padding: 0.4rem 0.65rem !important;
        padding-right: 0.65rem !important;
    }
    @media(max-width:767.98px) { .org-chart-page { padding-top:1.25rem; } .org-chart-header { align-items:flex-start; flex-direction:column; } .org-chart-add { width:100%; justify-content:center; } .org-chart-panel-head,.org-chart-panel-body { padding:1rem; } }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-lg-4 org-chart-page">
    <div class="org-chart-header">
        <div>
            <div class="org-chart-eyebrow">Organization Management</div>
            <h1 class="org-chart-title">Organizational Chart</h1>
            <p class="org-chart-subtitle">Manage members, positions, and reporting relationships.</p>
        </div>
        <button type="button" class="org-chart-add" id="create_button">
            <i class="fas fa-plus" aria-hidden="true"></i> Add Node
        </button>
    </div>

    @include('organization.edit')

    <section class="org-chart-panel">
        <div class="org-chart-panel-head">
            <div>
                <h2 class="org-chart-panel-title">Organization Directory</h2>
                <p class="org-chart-panel-caption">View and maintain the organizational structure.</p>
            </div>
            <span class="text-muted small"><i class="fas fa-sitemap me-1" aria-hidden="true"></i> Organization</span>
        </div>
        <div class="org-chart-panel-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="myTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Parent</th>
                            <th style="width:10%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
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

                url: '{{ route('organization.index') }}',

            },

            columns: [

                { data: "image", name: 'image' },          

                { data: "name", name: 'name' },

                { data: "position", name: 'position' },         

                { data: "parent", name: 'parent' },         

                { data: "actions", name: 'actions' },         

            ],

        });

        loadParentDropdown()

        const modalElement = document.getElementById('myModal');
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

        // Create

        $('#create_button').click(e => {

            $('.update-button').hide();

            $('.update-section').hide();

            $('.submit-button').show();

            $('.modal-title').html('Add New Node');

            $('#myForm')[0].reset();

            $('.text-danger').addClass('d-none');

            $('.form-control').removeClass('is-invalid');

            $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');

            modalInstance.show()

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

            axios.post(`${basePath}/organization`, formData, {

                headers: {

                    'Accept': 'application/json',

                    'Content-Type': 'multipart/form-data'

                }

            })

            .then((response) => {

                modalInstance.hide();

                $('#myForm')[0].reset();

                $('#image_preview').attr('src', '{{ asset('default/profile.png') }}').addClass('d-none');

                $('.text-danger').addClass('d-none');

                $('.form-control').removeClass('is-invalid');

                $('.form-select').removeClass('is-invalid');

                DataTable.ajax.reload();

                loadParentDropdown();

                toastr.success(response.data.message, 'Success', {

                    iconClass: 'toast-success'

                });

            })

            .catch(error => {

                $('.text-danger').addClass('d-none');

                $('.form-control').removeClass('is-invalid');

                $('.form-select').removeClass('is-invalid');

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

            $('.modal-title').text('Edit Node');

            $('.update-button').show();

            $('.submit-button').hide();

            // Clear previous errors & styles

            $('.text-danger').addClass('d-none');

            $('.form-control').removeClass('is-invalid');

            // Get member data

            axios.get(`${basePath}/organization/${id}/edit`)

                .then(response => {

                    const m = response.data.data;

                    // Populate fields

                    $('#name').val(m.name);

                    $('#position').val(m.position);

                    $('#parent_id').val(m.parent_id);

                    // Image preview (existing or fallback)

                    const imgUrl = m.image_path

                        ? `/storage/${m.image_path}`

                        : '{{ asset('default/profile.png') }}';

                    $('#image_preview')

                        .attr('src', imgUrl)

                        .removeClass('d-none');

                    modalInstance.show();

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

            axios.post(`${basePath}/organization/${id}`, formData, {

                headers: {

                    'Accept': 'application/json',

                    'Content-Type': 'multipart/form-data'

                }

            })

            .then((response) => {

                $('.text-danger').addClass('d-none');

                $('.form-control').removeClass('is-invalid');

                DataTable.ajax.reload();

                loadParentDropdown();

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

                    axios.delete(`${basePath}/organization/${id}`, {

                        headers: {

                            'Accept': 'application/json'

                        }

                    })

                    .then(response => {

                        toastr.success(response.data.message || 'Organization deleted successfully.', 'Success', {

                            iconClass: 'toast-success'

                        });

                        DataTable.ajax.reload();

                        loadParentDropdown();

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

    function loadParentDropdown(selectedId = null) {

        axios.get(`${basePath}/api/org-chart/members`)

            .then(response => {

                const members = response.data.data;

                let selectHtml = `

                    <label for="parent_id" class="form-label">Reports To (Parent)</label>

                    <select class="form-select" name="parent_id" id="parent_id">

                        <option value="">None (Top-level)</option>

                `;

                members.forEach(member => {

                    const selected = selectedId == member.id ? 'selected' : '';

                    selectHtml += `<option value="${member.id}" ${selected}>${member.name} - ${member.position}</option>`;

                });

                selectHtml += `</select>

                    <div id="parent_id_error" class="text-danger small pt-1 d-none"></div>`;

                $('#parent-select-container').html(selectHtml);

            })

            .catch(error => {

                console.error('Failed to load parent list:', error);

                $('#parent-select-container').html('<p class="text-danger">Failed to load parent options.</p>');

            });

    }

</script>

@endsection

