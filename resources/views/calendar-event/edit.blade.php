<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" enctype="multipart/form-data" id="myForm" class="w-100">
      @csrf
      <div class="modal-content rounded-4 border-0 shadow-sm">
        <div class="modal-header border-0 pb-2 pt-3 px-4">
          <h5 class="modal-title fw-semibold" id="myModalLabel">Add Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body pt-0 pb-2 px-4">
          <div class="row g-3">
            <div class="col-md-12">
              <label for="title" class="form-label mb-1">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control rounded-3" id="title" name="title">
              <small id="title_error" class="text-danger d-none"></small>
            </div>

            <div class="col-md-12">
              <label for="description" class="form-label mb-1">Description</label>
              <textarea class="form-control rounded-3" id="description" name="description" rows="2"></textarea>
              <small id="description_error" class="text-danger d-none"></small>
            </div>

            <div class="col-md-6">
              <label for="start" class="form-label mb-1">Start <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control rounded-3" id="start" name="start">
              <small id="start_error" class="text-danger d-none"></small>
            </div>

            <div class="col-md-6">
              <label for="end" class="form-label mb-1">End</label>
              <input type="datetime-local" class="form-control rounded-3" id="end" name="end">
              <small id="end_error" class="text-danger d-none"></small>
            </div>

            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch">
                    <input type="hidden" name="all_day" value="0">
                    <input class="form-check-input" type="checkbox" id="all_day" name="all_day" value="1">
                    <label class="form-check-label small" for="all_day">All Day Event</label>
                </div>
            </div>

            <div class="col-md-6">
              <label for="color" class="form-label mb-1">Event Color</label>
              <input type="color" class="form-control form-control-color rounded-3" id="color" name="color" value="#3788d8" title="Choose event color">
              <small id="color_error" class="text-danger d-none"></small>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0 px-4 pb-4 pt-2">
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
