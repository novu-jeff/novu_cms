<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="POST" enctype="multipart/form-data" id="myForm" class="w-100">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Add Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Profile Picture -->
          <label for="image_path" class="form-label">Profile Picture</label>
          <div id="image_path_error" class="text-danger small pt-1 d-none"></div>
          <div class="image-container mb-3 text-center">
            <div class="mt-2 image_preview mb-2">
              <img id="image_preview" src="{{ asset('default/profile.png') }}" alt="Image Preview" width="120" height="120" class="rounded-circle shadow-sm" />
            </div>
            <div class="image_path position-relative">
              <input type="file" id="image_path" name="image_path" accept="image/*" class="d-none">
              <label for="image_path" class="upload-label text-primary cursor-pointer">
                <i class="fas fa-upload fa-2x"></i><br>
                <span>Click to Upload</span>
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
            <input type="text" class="form-control" id="position" name="position" placeholder="e.g., Vice Mayor" required>
            <div id="position_error" class="text-danger small pt-1 d-none"></div>
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Short background or message..."></textarea>
          </div>

          <!-- Contact Info -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com">
            </div>
            <div class="col-md-6 mb-3">
              <label for="contact_number" class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="09XXXXXXXXX">
            </div>
          </div>

          <!-- Address -->
          <div class="mb-3">
            <label for="address" class="form-label">Office Address</label>
            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter office address..."></textarea>
          </div>

          <!-- Term Duration -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="term_start" class="form-label">Term Start</label>
              <input type="date" class="form-control" id="term_start" name="term_start">
            </div>
            <div class="col-md-6 mb-3">
              <label for="term_end" class="form-label">Term End</label>
              <input type="date" class="form-control" id="term_end" name="term_end">
            </div>
          </div>

        <!-- Achievements -->
            <div class="mb-3">
            <label class="form-label">Achievements</label>
            <div id="achievements-list" class="dynamic-list mb-2"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-achievement">
                <i class="fas fa-plus"></i> Add Achievement
            </button>
            <input type="hidden" name="achievements" id="achievements-input" value="{{ old('achievements', $member->achievements ?? '') }}">
            </div>

            <!-- Priority Projects -->
            <div class="mb-3">
            <label class="form-label">Priority Projects</label>
            <div id="projects-list" class="dynamic-list mb-2"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-project">
                <i class="fas fa-plus"></i> Add Project
            </button>
            <input type="hidden" name="priority_projects" id="projects-input" value="{{ old('priority_projects', $member->priority_projects ?? '') }}">
            </div>

          <!-- Social Links -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="social_facebook" class="form-label">Facebook</label>
              <input type="url" class="form-control" id="social_facebook" name="social_facebook" placeholder="https://facebook.com/...">
            </div>
            <div class="col-md-4 mb-3">
              <label for="social_twitter" class="form-label">Twitter</label>
              <input type="url" class="form-control" id="social_twitter" name="social_twitter" placeholder="https://twitter.com/...">
            </div>
            <div class="col-md-4 mb-3">
              <label for="social_instagram" class="form-label">Instagram</label>
              <input type="url" class="form-control" id="social_instagram" name="social_instagram" placeholder="https://instagram.com/...">
            </div>
          </div>

          <!-- Active + Sort Order -->
          <div class="row align-items-center">
            <div class="col-md-6 mb-3">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="isActive" name="isActive" value="1" >
                <label class="form-check-label" for="isActive">Active Member</label>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="sort_order" class="form-label">Sort Order</label>
              <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
            </div>
          </div>
        </div>

        <!-- Footer -->
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


