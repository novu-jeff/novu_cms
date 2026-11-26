@extends('layouts.app')

@section('styles')
<style>
    .calendar-events-page{--event-primary:#3157d5;--event-ink:#172033;--event-muted:#718096;padding:clamp(1rem,2.5vw,2rem) 0 3rem}
    .calendar-events-page .page-heading{display:flex;align-items:center;justify-content:space-between;gap:1.25rem;flex-wrap:wrap;margin-bottom:1.75rem}
    .calendar-events-page .eyebrow{color:var(--event-primary);font-size:.75rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:.45rem}
    .calendar-events-page .page-title{color:var(--event-ink);font-size:clamp(1.65rem,2.5vw,2.15rem);font-weight:750;letter-spacing:-.04em;margin:0}
    .calendar-events-page .page-subtitle{color:var(--event-muted);margin:.45rem 0 0}
    .calendar-events-page .btn-add-event{display:inline-flex;align-items:center;justify-content:center;gap:.6rem;border:0;border-radius:.8rem;padding:.8rem 1.15rem;font-weight:700;color:#fff;background:var(--event-primary);box-shadow:0 8px 18px rgba(49,87,213,.18);transition:transform .18s ease,box-shadow .18s ease}
    .calendar-events-page .btn-add-event:hover{color:#fff;transform:translateY(-1px);box-shadow:0 10px 22px rgba(49,87,213,.25)}
    .calendar-events-page .events-panel{background:#fff;border:1px solid #e7ebf2;border-radius:1.1rem;box-shadow:0 8px 28px rgba(22,34,55,.055);overflow:hidden}
    .calendar-events-page .panel-heading{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;padding:1.25rem 1.5rem;border-bottom:1px solid #edf0f5}
    .calendar-events-page .panel-heading h3{margin:0;color:var(--event-ink);font-size:1rem;font-weight:750}
    .calendar-events-page .panel-heading p{margin:.25rem 0 0;color:var(--event-muted);font-size:.875rem}
    .calendar-events-page .panel-icon{width:2.5rem;height:2.5rem;display:grid;place-items:center;border-radius:.8rem;color:var(--event-primary);background:#eef2ff;font-size:1rem}
    .calendar-events-page .table-responsive{padding:0 1.25rem 1rem}
    .calendar-events-page #myTable{width:100%!important;border-collapse:separate;border-spacing:0}
    .calendar-events-page #myTable thead th{color:#667085;background:#f8faff;border-bottom:1px solid #e8edf5;padding:1rem .85rem;font-size:.72rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;white-space:nowrap}
    .calendar-events-page #myTable tbody td{padding:1rem .85rem;color:#344054;border-bottom:1px solid #edf0f5;vertical-align:middle}
    .calendar-events-page #myTable tbody tr:last-child td{border-bottom:0}
    .calendar-events-page #myTable tbody tr:hover{background:#fafbff}
    .calendar-events-page .dataTables_wrapper{padding-top:1rem}
    .calendar-events-page .dataTables_wrapper .dataTables_filter input,.calendar-events-page .dataTables_wrapper .dataTables_length select{border:1px solid #dce2ec;border-radius:.55rem;padding:.45rem .65rem;background:#fff;outline:none}
    .calendar-events-page .dataTables_wrapper .dataTables_filter input:focus{border-color:#8299ee;box-shadow:0 0 0 3px rgba(49,87,213,.1)}
    .calendar-events-page .dataTables_wrapper .dataTables_paginate .paginate_button.current{border:1px solid #dbe4ff!important;border-radius:.5rem;background:#eef2ff!important;color:#3157d5!important}
    @media(max-width:767.98px){.calendar-events-page .page-heading{align-items:stretch}.calendar-events-page .btn-add-event{width:100%}.calendar-events-page .panel-heading{padding:1rem}.calendar-events-page .table-responsive{padding:0 .5rem .75rem}.calendar-events-page .dataTables_wrapper .dataTables_filter,.calendar-events-page .dataTables_wrapper .dataTables_length{float:none;text-align:left;margin-bottom:.75rem}}
</style>
@endsection

@section('content')
<div class="container calendar-events-page">
    <header class="page-heading">
        <div>
            <div class="eyebrow"><i class="fas fa-calendar-days me-1"></i> Event Management</div>
            <h1 class="page-title">Calendar Events</h1>
            <p class="page-subtitle">Create, organize, and maintain events on your calendar.</p>
        </div>
        <button type="button" class="btn-add-event" id="create_button">
            <i class="fas fa-plus"></i> Add Event
        </button>
    </header>

    @include('calendar-event.edit')

    <section class="events-panel">
        <div class="panel-heading">
            <div class="d-flex align-items-center gap-3">
                <div class="panel-icon"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <h3>Event Directory</h3>
                    <p>View and manage your scheduled events.</p>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="myTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Date</th>
                        <th style="width:10%">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    const alias = @json(config('app.alias'));
    const basePath = alias ? `/${alias}` : '';

    $(document).ready(function () {
        let id;
        const table = $('#myTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: '{{ route('calendar-event.index') }}' },
            columns: [
                { data: 'title', name: 'title' },
                { data: 'description', name: 'description' },
                { data: 'color', name: 'color' },
                { data: 'date', name: 'date' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [],
            autoWidth: false,
            responsive: true,
            language: {
                processing: '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Loading events...'
            }
        });

        const modalElement = document.getElementById('myModal');
        const modalInstance = modalElement && window.bootstrap
            ? bootstrap.Modal.getOrCreateInstance(modalElement)
            : null;
        const showModal = () => modalInstance ? modalInstance.show() : $('#myModal').modal('show');
        const hideModal = () => modalInstance ? modalInstance.hide() : $('#myModal').modal('hide');

        $('#create_button').on('click', function () {
            $('.update-button').hide();
            $('.submit-button').show().prop('disabled', false);
            $('.modal-title').text('Add New Event');
            $('#myForm')[0].reset();
            clearEventErrors();
            showModal();
        });

        $('.submit-button').on('click', function (e) {
            e.preventDefault();
            const button = $(this);
            button.prop('disabled', true);
            const formData = new FormData(document.getElementById('myForm'));

            axios.post(`${basePath}/calendar-event`, formData, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => {
                hideModal();
                $('#myForm')[0].reset();
                clearEventErrors();
                table.ajax.reload(null, false);
                toastr.success(response.data.message || 'Event created successfully.', 'Success');
            })
            .catch(error => showEventValidationErrors(error))
            .finally(() => button.prop('disabled', false));
        });

        $(document).on('click', '.edit-button', function () {
            id = $(this).data('id');
            $('.modal-title').text('Edit Event');
            $('.submit-button').hide();
            $('.update-button').show().prop('disabled', false);
            clearEventErrors();

            axios.get(`${basePath}/calendar-event/${id}/edit`)
                .then(response => {
                    const event = response.data.data;
                    $('#title').val(event.title);
                    $('#description').val(event.description);
                    $('#start').val(event.start);
                    $('#end').val(event.end);
                    $('#all_day').prop('checked', Boolean(event.all_day));
                    $('#color').val(event.color);
                    showModal();
                })
                .catch(error => {
                    toastr.error(error.response?.data?.message || 'Failed to load event.', 'Error');
                });
        });

        $('.update-button').on('click', function (e) {
            e.preventDefault();
            const button = $(this);
            button.prop('disabled', true);
            const formData = new FormData(document.getElementById('myForm'));
            formData.append('_method', 'PUT');

            axios.post(`${basePath}/calendar-event/${id}`, formData, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => {
                hideModal();
                clearEventErrors();
                table.ajax.reload(null, false);
                toastr.success(response.data.message || 'Event updated successfully.', 'Updated');
            })
            .catch(error => showEventValidationErrors(error))
            .finally(() => button.prop('disabled', false));
        });

        $(document).on('click', '.delete-button', function () {
            const eventId = $(this).data('id');
            Swal.fire({
                title: 'Delete this event?',
                text: 'This action will deactivate the event.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (!result.isConfirmed) return;
                axios.delete(`${basePath}/calendar-event/${eventId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => {
                    toastr.success(response.data.message || 'Event deleted successfully.', 'Success');
                    table.ajax.reload(null, false);
                })
                .catch(error => {
                    toastr.error(error.response?.data?.message || 'Failed to delete event.', 'Error');
                });
            });
        });

        function clearEventErrors() {
            $('.text-danger').addClass('d-none').text('');
            $('.form-control, .form-check-input, .form-select').removeClass('is-invalid');
        }

        function showEventValidationErrors(error) {
            clearEventErrors();
            if (error.response?.status === 422) {
                const errors = error.response.data.errors || {};
                $.each(errors, (field, messages) => {
                    const input = $('#' + field);
                    const errorSpan = $('#' + field + '_error');
                    input.addClass('is-invalid');
                    errorSpan.removeClass('d-none').text(messages[0]);
                });
            } else {
                toastr.error(error.response?.data?.message || 'Something went wrong. Please try again.', 'Error');
            }
        }
    });
</script>
@endsection
