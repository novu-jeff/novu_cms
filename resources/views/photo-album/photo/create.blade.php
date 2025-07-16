<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" id="myForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Upload Images</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <input type="hidden" name="album_id" id="album_id" value="{{ $data->id }}">
            <!-- Multiple Image Upload -->
            <div class="mb-3">
                <label for="images" class="form-label">Select Images</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" required>
                <div id="images_error" class="text-danger small pt-1 d-none"></div>
            </div>

            <!-- Optional preview -->
            <div id="preview" class="d-flex flex-wrap gap-2"></div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary submit-button">
                <i class="fas fa-upload"></i> Upload
            </button>
        </div>
      </div>
    </form>
  </div>
</div>
