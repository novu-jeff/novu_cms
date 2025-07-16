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
          {{-- Image Upload --}}
            <label for="image_path" class="form-label">Profile Picture</label>
            <div id="image_path_error" class="text-danger small pt-1 d-none"></div>
            <div class="image-container mb-3">
                <div class="mt-2 image_preview">
                    <img id="image_preview" src="{{ asset('default/profile.png') }}" alt="Image Preview"/>
                </div>
                <div class="image_path position-relative text-center">
                    <input type="file" id="image_path" name="image_path" accept="image/*" class="d-none">

                    <label for="image_path" class="upload-label text-primary">
                        <i class="fas fa-upload fa-2x"></i><br>
                        <span>Click to Upload</span>
                    </label>
                </div>
            </div>

          {{-- Name --}}
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <div id="name_error" class="text-danger small pt-1 d-none"></div>
          </div>

          {{-- Position --}}
          <div class="mb-3">
            <label for="position" class="form-label">Position</label>
            <input type="text" class="form-control" id="position" name="position" required>
            <div id="position_error" class="text-danger small pt-1 d-none"></div>
          </div>

            {{-- Parent Dropdown --}}
            <div class="mb-3" id="parent-select-container">
                <!-- Content will be injected here -->
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
