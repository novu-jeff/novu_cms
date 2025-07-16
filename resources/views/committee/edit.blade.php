<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="POST" enctype="multipart/form-data" id="myForm" class="w-100">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Add Standing Committee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          {{-- Committee Name --}}
          <div class="mb-3">
            <label for="committee_name" class="form-label">Standing Committee Name</label>
            <input type="text" class="form-control" id="committee_name" name="name" required>
            <div id="name_error" class="text-danger small pt-1 d-none"></div>
          </div>

          {{-- Members Section --}}
          <div id="members-wrapper">
            <h6 class="mb-3">Committee Members</h6>

            <div class="member-entry border rounded p-3 mb-3 position-relative">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-2">
                            <label class="form-label">Name</label>
                            <input type="text" name="members[0][name]" class="form-control" required>
                            <div class="field-error text-danger small pt-1 d-none"></div>
                        </div>
                        <div>
                            <label class="form-label">Position</label>
                            <input type="text" name="members[0][position]" class="form-control">
                            <div class="field-error text-danger small pt-1 d-none"></div>
                        </div>
                    </div>
                </div>
            </div>

          </div>

          <button type="button" class="btn btn-sm btn-secondary" id="addMemberBtn">
            <i class="fas fa-plus"></i> Add Member
          </button>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary submit-button">
                <i class="fas fa-plus me-1"></i> Add
            </button>
            <button type="submit" class="btn btn-secondary update-button">
                <i class="fas fa-save me-1"></i> Update
            </button>
        </div>
      </div>
    </form>
  </div>
</div>
