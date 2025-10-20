@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Members</h2>
            <p class="text-muted mb-0">Manage members in this module</p>
        </div>

        <button type="button" class="btn btn-secondary p-3 px-5" id="create_button">
            <i class="fas fa-plus me-1"></i> Add Member
        </button>
    </div>

    @include('member.edit') <!-- Modal -->

    <!-- Table Card -->
    <div class="card shadow border-1 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="myTable">
                    <thead>
                        <tr>
                            <th class="text-muted">Image</th>
                            <th class="text-muted">Name</th>
                            <th class="text-muted">Position</th>
                            <th class="text-muted" style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
    let currentMemberId = null; // null = add, id = edit

    // ================= Dynamic List Helper =================
    function setupDynamicList(listId, addBtnId, hiddenInputId, placeholderText, existingValues = []) {
        const $list = $('#' + listId);
        const $addBtn = $('#' + addBtnId);
        const $hiddenInput = $('#' + hiddenInputId);

        $list.empty();

        function updateHidden() {
            const values = $list.find('input').map(function(){ return $(this).val().trim(); }).get().filter(v => v.length);
            $hiddenInput.val(values.join('\n'));
        }

        function createItem(value = '') {
            const $item = $(`
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2">•</span>
                    <input type="text" class="form-control form-control-sm me-2" value="${value}" placeholder="${placeholderText}">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-minus"></i></button>
                </div>
            `);
            $item.find('input').on('input', updateHidden);
            $item.find('.remove-item').on('click', () => { $item.remove(); updateHidden(); });
            $list.append($item);
            updateHidden();
        }

        if (existingValues.length) {
            existingValues.forEach(v => createItem(v));
        } else {
            createItem();
        }

        $addBtn.off('click').on('click', () => createItem());
    }

   
    // ================= DataTable =================
    const DataTable = $('#myTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("members.index") }}',
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




    

    const myModal = $('#myModal');

    // ================= Add Member =================
    $('#create_button').click(() => {
        currentMemberId = null;
        $('.modal-title').text('Add New Member');
        $('.submit-button').show();
        $('.update-button').hide();

        $('#myForm')[0].reset();
        $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');

        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');

        setupDynamicList('achievements-list','add-achievement','achievements-input','Enter achievement...');
        setupDynamicList('projects-list','add-project','projects-input','Enter project...');

        myModal.modal('show');
    });

    // ================= Image Preview =================
    $('#image_path').on('change', function() {
        const input = this;
        const preview = $('#image_preview');
        if(input.files && input.files[0]){
            const reader = new FileReader();
            reader.onload = e => preview.attr('src', e.target.result).removeClass('d-none');
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.attr('src', '{{ asset('default/profile.png') }}').addClass('d-none');
        }
    });

    // ================= Submit Form (Add/Update) =================
    $(document).on('submit', '#myForm', function(e){
        e.preventDefault();

        console.log('Submitting form...');
        // Update hidden inputs for dynamic lists

        $('#achievements-input').val($('#achievements-list input').map(function(){ return $(this).val(); }).get().join('\n'));
        $('#projects-input').val($('#projects-list input').map(function(){ return $(this).val(); }).get().join('\n'));

        const formData = new FormData(this);
        let url = `${basePath}/members`;
        if(currentMemberId) {
            formData.append('_method','PUT');
            url = `${basePath}/members/${currentMemberId}`;
        }

        axios.post(url, formData, {
            headers: {'Content-Type': 'multipart/form-data'}
        })
        .then(res => {
            toastr.success(res.data.message);
            $('#myModal').modal('hide');
            DataTable.ajax.reload();
            $('#myForm')[0].reset();
            $('#image_preview').attr('src', '{{ asset('default/profile.png') }}');
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
        })
        .catch(err => {
            $('.text-danger').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            if(err.response?.status === 422){
                const errors = err.response.data.errors;
                Object.keys(errors).forEach(key => {
                    $(`#${key}`).addClass('is-invalid');
                    $(`#${key}_error`).removeClass('d-none').text(errors[key][0]);
                });
            } else {
                toastr.error(err.message || 'Operation failed');
            }
        });
    });

    // ================= Edit Member =================
    $(document).on('click', '.edit-button', function(){
        currentMemberId = $(this).data('id');

        $('.modal-title').text('Edit Member');
        $('.update-button').show();
        $('.submit-button').hide();
        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');
console.log(currentMemberId) 
console.log(`${basePath}/members/${currentMemberId}/edit`);  
        axios.get(`${basePath}/members/${currentMemberId}/edit`)
        .then(res => {
            const m = res.data.data;
            $('#name').val(m.name);
            $('#position').val(m.position);
            $('#description').val(m.description || '');
            $('#email').val(m.email || '');
            $('#contact_number').val(m.contact_number || '');
            $('#address').val(m.address || '');
            $('#term_start').val(m.term_start || '');
            $('#term_end').val(m.term_end || '');
            $('#social_facebook').val(m.social_facebook || '');
            $('#social_twitter').val(m.social_twitter || '');
            $('#social_instagram').val(m.social_instagram || '');
            $('#sort_order').val(m.sort_order ?? 0);
            $('#isActive').prop('checked', m.isActive ? true : false);
            $('#image_preview').attr('src', m.image_path ? `/storage/${m.image_path}` : '{{ asset('default/profile.png') }}');

            setupDynamicList('achievements-list','add-achievement','achievements-input','Enter achievement...', m.achievements ? m.achievements.split("\n") : []);
            setupDynamicList('projects-list','add-project','projects-input','Enter project...', m.priority_projects ? m.priority_projects.split("\n") : []);

            myModal.modal('show');
        })
        .catch(err => toastr.error(err.response?.data?.message || 'Failed to load member data'));
    });

    // ================= Delete Member =================
    $(document).on('click', '.delete-button', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will deactivate the standing member.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if(result.isConfirmed){
                axios.delete(`${basePath}/members/${id}`)
                    .then(res => {
                        toastr.success(res.data.message || 'Member deleted successfully.');
                        DataTable.ajax.reload();
                    })
                    .catch(err => {
                        toastr.error(err.response?.data?.message || 'Failed to delete member.');
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

                axios.post('{{ route("members.reorder") }}', { order })
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
