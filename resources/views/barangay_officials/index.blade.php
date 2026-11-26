@extends('layouts.app')



 <!-- Filter Officials by Barangay -->



@section('styles')
<style>
    .barangay-officials-page { --bo-primary: #3157d5; --bo-ink: #172033; --bo-muted: #758096; color: var(--bo-ink); }
    .barangay-officials-page .page-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; }
    .barangay-officials-page .eyebrow { color:var(--bo-primary); font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.4rem; }
    .barangay-officials-page .page-title { font-size:clamp(1.45rem,2vw,1.9rem); font-weight:750; letter-spacing:-.035em; margin:0; }
    .barangay-officials-page .page-subtitle { color:var(--bo-muted); margin:.35rem 0 0; font-size:.93rem; }
    .barangay-officials-page .btn-add-official { display:inline-flex; align-items:center; justify-content:center; gap:.55rem; padding:.75rem 1.1rem; border:0; border-radius:.8rem; color:#fff; background:var(--bo-primary); font-weight:650; box-shadow:0 7px 18px rgba(49,87,213,.18); transition:transform .18s ease,box-shadow .18s ease; white-space:nowrap; }
    .barangay-officials-page .btn-add-official:hover { color:#fff; transform:translateY(-1px); box-shadow:0 10px 22px rgba(49,87,213,.25); }
    .barangay-officials-page .filter-panel,.barangay-officials-page .table-panel { background:#fff; border:1px solid #e8ecf3; border-radius:1rem; box-shadow:0 8px 28px rgba(24,39,75,.045); }
    .barangay-officials-page .filter-panel { padding:1rem 1.15rem; margin-bottom:1rem; }
    .barangay-officials-page .filter-label { color:#45516a; font-size:.82rem; font-weight:700; margin-bottom:.45rem; }
    .barangay-officials-page .form-select { min-height:44px; border-color:#dfe5ef; border-radius:.7rem; box-shadow:none; }
    .barangay-officials-page .form-select:focus { border-color:#9aafff; box-shadow:0 0 0 .2rem rgba(49,87,213,.1); }
    .barangay-officials-page .table-panel { overflow:hidden; }
    .barangay-officials-page .table-panel-header { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.25rem; border-bottom:1px solid #edf0f5; }
    .barangay-officials-page .table-panel-title { font-weight:750; margin:0; font-size:1rem; }
    .barangay-officials-page .table-panel-subtitle { color:var(--bo-muted); font-size:.82rem; margin:.2rem 0 0; }
    .barangay-officials-page .table { margin:0; }
    .barangay-officials-page .table thead th { padding:1rem 1.15rem; background:#f8faff; border-bottom:1px solid #e8ecf3; color:#65718a; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.065em; white-space:nowrap; }
    .barangay-officials-page .table tbody td { padding:.9rem 1.15rem; border-color:#eef1f6; color:#344057; vertical-align:middle; }
    .barangay-officials-page .table tbody tr:hover { background:#fafbff; }
    .barangay-officials-page .dataTables_wrapper { padding:1rem 1.15rem; }
    .barangay-officials-page .dataTables_wrapper .dataTables_length,.barangay-officials-page .dataTables_wrapper .dataTables_filter,.barangay-officials-page .dataTables_wrapper .dataTables_info { color:var(--bo-muted); font-size:.84rem; margin-bottom:.8rem; }
    .barangay-officials-page .dataTables_wrapper .dataTables_filter input,.barangay-officials-page .dataTables_wrapper .dataTables_length select { border:1px solid #dfe5ef; border-radius:.6rem; padding:.4rem .65rem; outline:none; }
    .barangay-officials-page .dataTables_wrapper .dataTables_filter input:focus { border-color:#9aafff; }
    .barangay-officials-page .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius:.5rem !important; }
    .barangay-officials-page .dataTables_wrapper .dataTables_paginate .paginate_button.current { background:var(--bo-primary) !important; border-color:var(--bo-primary) !important; color:#fff !important; }
    .barangay-officials-page .dataTables_processing { border:1px solid #e8ecf3; border-radius:.7rem; background:#fff; box-shadow:0 8px 25px rgba(24,39,75,.08); }
    /* Fix DataTables dropdown arrow overlap */
    .barangay-officials-page .dataTables_wrapper .dataTables_length select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background: none !important;
        background-image: none !important;
        padding: 0.4rem 0.65rem !important;
        padding-right: 0.65rem !important;
    }
    @media (max-width: 767.98px) { .barangay-officials-page .page-heading { align-items:flex-start; flex-direction:column; } .barangay-officials-page .btn-add-official { width:100%; } .barangay-officials-page .table-panel-header { padding:1rem; } .barangay-officials-page .dataTables_wrapper { padding:.75rem; } .barangay-officials-page .dataTables_wrapper .dataTables_filter { text-align:left; } .barangay-officials-page .dataTables_wrapper .dataTables_filter input { max-width:60%; } }
</style>
@endsection

@section('content')

<div class="container-fluid py-4 px-3 px-lg-4 barangay-officials-page">

    <!-- Header -->

    <div class="page-heading">

        <div>

            <div class="eyebrow">Directory management</div>
            <h2 class="page-title">Barangay Officials</h2>

            <p class="page-subtitle">Manage officials and their barangay assignments.</p>

        </div>



        <button type="button" class="btn btn-add-official" id="create_button">

            <i class="fas fa-plus me-1"></i> Add Official

        </button>

    </div>



    <!-- Include Modal Form -->

    @include('barangay_officials.edit')



    <!-- Barangay Filter -->

    <div class="filter-panel">

        <label for="filter_barangay" class="form-label filter-label">Filter officials by barangay</label>

        <select id="filter_barangay" class="form-select">

            <option value="">All Barangays</option>

            @foreach($barangays as $barangay)

                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>

            @endforeach

        </select>

    </div>



    <!-- Table -->

    <div class="table-panel">

        <div class="table-panel-header">
            <div>
                <h3 class="table-panel-title">Officials directory</h3>
                <p class="table-panel-subtitle">View, edit, reorder, or deactivate listed officials.</p>
            </div>
        </div>

        <div class="table-responsive">

                <table class="table table-hover table-striped align-middle mb-0" id="myTable">

                    <thead>

                        <tr>

                            <th class="text-muted">Image</th>

                            <th class="text-muted">Name</th>

                            <th class="text-muted">Position</th>

                            <!--<th class="text-muted">Barangay</th>-->

                            <th class="text-muted" style="width: 10%">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <!-- AJAX-loaded content -->

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
        
     // ================= DataTable =================
    const DataTable = $('#myTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('barangay-officials.index') }}',
            data: function (d) {
                d.barangay_id = $('#filter_barangay').val();
            }
        },
        columns: [
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'position', name: 'position' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']], // sort by name initially (visual only)
        rowReorder: false, // disable built-in DataTables reorder plugin
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id); // add data-id for sorting
        },
        drawCallback: function() {
            initSortable(); // re-init Sortable after every table draw
        }
    });



    // ✅ Trigger reload when barangay dropdown changes
    $('#filter_barangay').change(function () {
        DataTable.ajax.reload();
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
                    $('#barangay_id').val(b.barangay_id); // <-- dropdown

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

        function initSortable() {
            const tbody = document.querySelector('#myTable tbody');
            if (!tbody) return;

            Sortable.create(tbody, {
                animation: 150,
                handle: 'td', // drag anywhere on the row
                onEnd: function (evt) {
                    const order = [];
                    $('#myTable tbody tr').each(function (index, el) {
                        order.push($(el).data('id'));
                    });

                    axios.post('{{ route("barangay-officials.reorder") }}', { order })
                        .then(res => {
                            toastr.success(res.data.message);
                            DataTable.ajax.reload(null, false); // reload without resetting pagination
                        })
                        .catch(err => {
                            toastr.error('Failed to update orderss');
                            console.error(err);
                        });
                }
            });
        }

            // initialize manually once table loaded
        DataTable.on('draw', initSortable);
    });
</script>
@endsection