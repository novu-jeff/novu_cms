@extends('layouts.app')

@section('styles')
    {{-- Remove these CDN imports if DataTables CSS is already loaded by layouts.app. --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        .barangay-page-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
        .barangay-page-icon{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:14px;background:rgba(13,110,253,.1);color:#0d6efd;font-size:1.2rem}
        .barangay-panel{border:1px solid #e9edf2;border-radius:16px;box-shadow:0 4px 18px rgba(16,24,40,.04)}
        .barangay-panel .card-header{background:transparent;border-bottom:1px solid #edf0f4;padding:1.1rem 1.25rem}
        #barangaysTable thead th{white-space:nowrap;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#667085}
        #barangaysTable td{vertical-align:middle}
        .dataTables_wrapper .dataTables_filter input,.dataTables_wrapper .dataTables_length select{border:1px solid #d0d5dd;border-radius:8px;padding:.4rem .65rem}
        .modal-content{border:0;border-radius:16px;box-shadow:0 18px 50px rgba(16,24,40,.18)}
        .form-control{border-radius:9px;padding:.65rem .8rem}
        .barangay-panel .dataTables_wrapper .dataTables_length select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background: none !important;
        background-image: none !important;
        padding: 0.4rem 0.65rem !important;
        padding-right: 0.65rem !important;
    }
        @media(max-width:575.98px){.barangay-page-header .btn{width:100%}}
    </style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="barangay-page-header mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="barangay-page-icon"><i class="fas fa-map-location-dot"></i></span>
            <div>
                <h4 class="mb-1 fw-bold">Barangay Settings</h4>
                <div class="text-muted small">Add, update, and deactivate barangay records.</div>
            </div>
        </div>
        <button type="button" class="btn btn-primary px-3" id="addBarangayButton"><i class="fas fa-plus me-2"></i>Add Barangay</button>
    </div>

    <div id="pageAlert" class="alert d-none" role="alert"></div>

    <div class="card barangay-panel">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-1 fw-bold">Barangay Directory</h6>
                <div class="text-muted small">Active barangay records.</div>
            </div>
            <span class="badge rounded-pill text-bg-light border"><i class="fas fa-database me-1"></i>Directory</span>
        </div>
        <div class="card-body p-3 p-lg-4">
            <div class="table-responsive">
                <table id="barangaysTable" class="table table-hover align-middle w-100">
                    <thead><tr><th style="width:70px">#</th><th>Barangay Name</th><th class="text-center" style="width:130px">Actions</th></tr></thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit modal --}}
<div class="modal fade" id="barangayModal" tabindex="-1" aria-labelledby="barangayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="barangayForm" class="modal-content" novalidate>
            @csrf
            <input type="hidden" id="barangayId" name="barangay_id">
            <div class="modal-header px-4 pt-4 border-0">
                <div><h5 class="modal-title fw-bold" id="barangayModalLabel">Add Barangay</h5><p class="text-muted small mb-0">Enter the official barangay name.</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-3">
                <div id="formAlert" class="alert alert-danger d-none" role="alert"></div>
                <label for="barangayName" class="form-label fw-semibold">Barangay Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="barangayName" name="name" maxlength="255" placeholder="Enter barangay name" required>
                <div class="invalid-feedback" id="nameError"></div>
            </div>
            <div class="modal-footer px-4 pb-4 border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBarangayButton"><i class="fas fa-check me-1"></i>Save Barangay</button>
            </div>
        </form>
    </div>
</div>

{{-- Deactivation confirmation modal --}}
<div class="modal fade" id="deactivateBarangayModal" tabindex="-1" aria-labelledby="deactivateBarangayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-body text-center p-4">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger" style="width:58px;height:58px;font-size:1.4rem"><i class="fas fa-ban"></i></div>
        <h5 class="fw-bold" id="deactivateBarangayModalLabel">Deactivate Barangay?</h5>
        <p class="text-muted small mb-4">Deactivate <strong id="deactivateBarangayName"></strong>? It will no longer appear in the active directory.</p>
        <div class="d-flex gap-2 justify-content-center"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="confirmDeactivateButton">Deactivate</button></div>
    </div></div></div>
</div>
@endsection

@section('scripts')
    {{-- Remove duplicate imports if jQuery and DataTables are already loaded by layouts.app. --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            const indexUrl = @json(route('barangays.index'));
            const storeUrl = @json(route('barangays.store'));
            const editUrlTemplate = @json(route('barangays.edit', ['barangay' => '__ID__']));
            const updateUrlTemplate = @json(route('barangays.update', ['barangay' => '__ID__']));
            const deleteUrlTemplate = @json(route('barangays.destroy', ['barangay' => '__ID__']));
            const csrfToken = @json(csrf_token());
            let selectedDeleteId = null;

            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });

            const table = $('#barangaysTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: { url: indexUrl, type: 'GET' },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', searchable: false, orderable: false },
                    { data: 'name', name: 'name', render: $.fn.dataTable.render.text() },
                    { data: 'actions', name: 'actions', searchable: false, orderable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: { emptyTable: 'No active barangays found.', zeroRecords: 'No matching barangays found.', processing: 'Loading barangays...' }
            });

            const barangayModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('barangayModal'));
            const deactivateModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deactivateBarangayModal'));

            function showPageAlert(type, message) {
                $('#pageAlert').removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).text(message);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            function clearFormErrors() {
                $('#formAlert').addClass('d-none').empty();
                $('#barangayName').removeClass('is-invalid');
                $('#nameError').text('');
            }
            function showValidationErrors(xhr) {
                clearFormErrors();
                const response = xhr.responseJSON || {};
                if (response.errors && response.errors.name) {
                    $('#barangayName').addClass('is-invalid');
                    $('#nameError').text(response.errors.name[0]);
                } else {
                    $('#formAlert').removeClass('d-none').text(response.message || 'Unable to save the barangay. Please try again.');
                }
            }

            $('#addBarangayButton').on('click', function () {
                $('#barangayForm')[0].reset();
                $('#barangayId').val('');
                clearFormErrors();
                $('#barangayModalLabel').text('Add Barangay');
                $('#saveBarangayButton').html('<i class="fas fa-check me-1"></i>Save Barangay').prop('disabled', false);
                barangayModal.show();
            });

            $('#barangaysTable').on('click', '.edit-button', function () {
                const id = $(this).data('id');
                clearFormErrors();
                $.get(editUrlTemplate.replace('__ID__', id)).done(function (response) {
                    $('#barangayForm')[0].reset();
                    $('#barangayId').val(response.data.id);
                    $('#barangayName').val(response.data.name);
                    $('#barangayModalLabel').text('Edit Barangay');
                    $('#saveBarangayButton').html('<i class="fas fa-check me-1"></i>Save Changes').prop('disabled', false);
                    barangayModal.show();
                }).fail(function (xhr) {
                    showPageAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to load this barangay.');
                });
            });

            $('#barangayForm').on('submit', function (event) {
                event.preventDefault();
                clearFormErrors();
                const id = $('#barangayId').val();
                const url = id ? updateUrlTemplate.replace('__ID__', id) : storeUrl;
                const method = id ? 'PUT' : 'POST';
                const $button = $('#saveBarangayButton');
                $button.prop('disabled', true).text('Saving...');
                $.ajax({ url: url, method: method, data: { name: $('#barangayName').val() } })
                    .done(function (response) {
                        barangayModal.hide();
                        table.ajax.reload(null, false);
                        showPageAlert('success', response.message || 'Barangay saved successfully.');
                    }).fail(showValidationErrors)
                    .always(function () {
                        $button.prop('disabled', false).html(id ? '<i class="fas fa-check me-1"></i>Save Changes' : '<i class="fas fa-check me-1"></i>Save Barangay');
                    });
            });

            $('#barangaysTable').on('click', '.delete-button', function () {
                selectedDeleteId = $(this).data('id');
                $('#deactivateBarangayName').text($(this).data('name') || 'this barangay');
                deactivateModal.show();
            });

            $('#confirmDeactivateButton').on('click', function () {
                if (!selectedDeleteId) return;
                const $button = $(this);
                $button.prop('disabled', true).text('Deactivating...');
                $.ajax({ url: deleteUrlTemplate.replace('__ID__', selectedDeleteId), method: 'POST', data: { _method: 'DELETE' } })
                    .done(function (response) {
                        deactivateModal.hide();
                        table.ajax.reload(null, false);
                        showPageAlert('success', response.message || 'Barangay deactivated successfully.');
                    }).fail(function (xhr) {
                        deactivateModal.hide();
                        showPageAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to deactivate this barangay.');
                    }).always(function () {
                        $button.prop('disabled', false).text('Deactivate');
                        selectedDeleteId = null;
                    });
            });
        });
    </script>
@endsection
