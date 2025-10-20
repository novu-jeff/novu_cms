<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" enctype="multipart/form-data" id="myForm" class="w-100">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="myModalLabel">Add Barangay Official</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Image Upload -->
          <label for="image_path" class="form-label">Profile Picture</label>
          <div id="image_path_error" class="text-danger small pt-1 d-none"></div>
          <div class="image-container mb-3 text-center">
            <img id="image_preview" 
                 src="{{ asset('default/profile.png') }}" 
                 alt="Image Preview"
                 class="rounded-circle shadow-sm mb-2"
                 style="width:120px;height:120px;object-fit:cover;">
            
            <div class="mt-2">
              <input type="file" id="image_path" name="image_path" accept="image/*" class="d-none">
              <label for="image_path" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-upload me-1"></i> Upload Photo
              </label>
            </div>
          </div>

          <!-- Name -->
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <div id="name_error" class="text-danger small pt-1 d-none"></div>
          </div>

          <!-- Position -->
          <div class="mb-3">
            <label for="position" class="form-label">Position</label>
            <input type="text" class="form-control" id="position" name="position" required>
            <div id="position_error" class="text-danger small pt-1 d-none"></div>
          </div>

          <!-- Barangay Dropdown -->
          <div class="mb-3">
            <label for="barangay_id" class="form-label">Barangay</label>
            <select class="form-select" id="barangay_id" name="barangay_id" required>
              <option value="">Select Barangay</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
            <div id="barangay_id_error" class="text-danger small pt-1 d-none"></div>
          </div>
        </div>

        <!-- Footer Buttons -->
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



