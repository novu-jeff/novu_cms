<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" id="myForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Add Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div id="name_error" class="text-danger small pt-1 d-none"></div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary submit-button">
                <i class="fas fa-plus"></i> Add 
            </button>
            <button type="submit" class="btn btn-secondary update-button">
                <i class="fas fa-save"></i> Update
            </button>
        </div>
      </div>
    </form>
  </div>
</div>
