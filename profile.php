<?php
// Secure session start and authorization check
include 'configs/dbconnection.php';
include 'configs/session.php';

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['login_id']; // Force integer type for security

// Prepared statement with error handling
try {
    $stmt = $conn->prepare("SELECT id, name, username, email, phone, profile_picture FROM students WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $row = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    // Handle error appropriately (e.g., show error message to user)
    $error = "Unable to load profile data. Please try again later.";
}
?>

<div class="pagetitle">
  <h1>Profile</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item">Users</li>
      <li class="breadcrumb-item active">Profile</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<section class="section profile">
  <div class="row">
    <div class="col-xl-4">
      <div class="card">
        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
          <?php if (!empty($row['profile_picture']) && file_exists('assets/img/profile/users/' . $row['profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars('assets/img/profile/users/' . $row['profile_picture']); ?>" 
                 alt="Profile Picture" 
                 class="rounded-circle" 
                 style="width: 120px; height: 120px; object-fit: cover;">
          <?php else: ?>
            <img src="assets/img/user.png" 
                 alt="Profile" 
                 class="rounded-circle" 
                 style="width: 120px; height: 120px; object-fit: cover;">
          <?php endif; ?>
          <h2 class="text-truncate mt-3"><?php echo htmlspecialchars($row['name']); ?></h2>
          <h3 class="text-muted">Student</h3>
          
          <!-- Quick Stats Section -->
          <div class="mt-3 text-center w-100">
            <div class="d-flex justify-content-around">
              <div>
                <h5 class="mb-0">24</h5>
                <small class="text-muted">Courses</small>
              </div>
              <div>
                <h5 class="mb-0">85%</h5>
                <small class="text-muted">Progress</small>
              </div>
              <div>
                <h5 class="mb-0">12</h5>
                <small class="text-muted">Certificates</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Additional Profile Card -->
      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title">Quick Links</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="courses.php">My Courses</a>
              <span class="badge bg-primary rounded-pill">5</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="messages.php">Messages</a>
              <span class="badge bg-primary rounded-pill">3</span>
            </li>
            <li class="list-group-item">
              <a href="settings.php">Account Settings</a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-xl-8">
      <div class="card">
        <div class="card-body pt-3">
          <!-- Bordered Tabs -->
          <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" type="button" role="tab">Overview</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" type="button" role="tab">Edit Profile</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" type="button" role="tab">Security</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-privacy" type="button" role="tab">Privacy</button>
            </li>
          </ul>
          
          <div class="tab-content pt-3">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="profile-overview" role="tabpanel">
              <h5 class="card-title">Profile Details</h5>
              
              <div class="row mb-3">
                <div class="col-lg-3 col-md-4 label">Full Name</div>
                <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($row['name']); ?></div>
              </div>
              
              <div class="row mb-3">
                <div class="col-lg-3 col-md-4 label">Username</div>
                <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($row['username']); ?></div>
              </div>
              
              <div class="row mb-3">
                <div class="col-lg-3 col-md-4 label">College</div>
                <div class="col-lg-9 col-md-8">CoICT</div>
              </div>
              
              <?php if (!empty($row['phone'])): ?>
                <div class="row mb-3">
                  <div class="col-lg-3 col-md-4 label">Phone</div>
                  <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($row['phone']); ?></div>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($row['email'])): ?>
                <div class="row mb-3">
                  <div class="col-lg-3 col-md-4 label">Email</div>
                  <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($row['email']); ?></div>
                </div>
              <?php endif; ?>
              
              <!-- Additional Info Section -->
              <h5 class="card-title mt-4">About</h5>
              <p class="small text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris.</p>
              
              <h5 class="card-title mt-4">Recent Activity</h5>
              <div class="activity">
                <div class="activity-item d-flex">
                  <div class="activite-label">32 min</div>
                  <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                  <div class="activity-content">
                    Completed "Advanced PHP" course
                  </div>
                </div>
                <div class="activity-item d-flex">
                  <div class="activite-label">2 hrs</div>
                  <i class='bi bi-circle-fill activity-badge text-primary align-self-start'></i>
                  <div class="activity-content">
                    Started "Database Design" course
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Edit Profile Tab -->
            <div class="tab-pane fade" id="profile-edit" role="tabpanel">
              <form id="edit-profile" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                
                <div class="row mb-3">
                  <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                  <div class="col-md-8 col-lg-9">
                    <div class="d-flex align-items-center">
                      <?php if (!empty($row['profile_picture']) && file_exists('assets/img/profile/users/' . $row['profile_picture'])): ?>
                        <img id="profile-preview" 
                             src="<?php echo htmlspecialchars('assets/img/profile/users/' . $row['profile_picture']); ?>" 
                             alt="Profile" 
                             class="rounded-circle me-3" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                      <?php else: ?>
                        <img id="profile-preview" 
                             src="assets/img/user.png" 
                             alt="Profile" 
                             class="rounded-circle me-3" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                      <?php endif; ?>
                      <div>
                        <label for="profileImage" class="btn btn-primary btn-sm mb-1">
                          <i class="bi bi-upload me-1"></i> Change Photo
                          <input type="file" name="profileImage" id="profileImage" class="visually-hidden" accept="image/*">
                        </label>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="remove-photo">
                          <i class="bi bi-trash me-1"></i> Remove
                        </button>
                        <div class="form-text">JPG, GIF or PNG. Max size 2MB</div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name <span class="text-danger">*</span></label>
                  <div class="col-md-8 col-lg-9">
                    <input name="fullName" type="text" class="form-control" id="fullName" 
                           value="<?php echo htmlspecialchars($row['name']); ?>" required>
                    <div class="invalid-feedback">Please provide your full name.</div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="username" class="col-md-4 col-lg-3 col-form-label">Username <span class="text-danger">*</span></label>
                  <div class="col-md-8 col-lg-9">
                    <input name="username" type="text" class="form-control" id="username" 
                           value="<?php echo htmlspecialchars($row['username']); ?>" required>
                    <div class="invalid-feedback">Please choose a username.</div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="phone" type="tel" class="form-control" id="phone" 
                           value="<?php echo htmlspecialchars($row['phone']); ?>">
                    <div class="form-text">Optional</div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="email" type="email" class="form-control" id="email" 
                           value="<?php echo htmlspecialchars($row['email']); ?>">
                    <div class="form-text">Optional</div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="bio" class="col-md-4 col-lg-3 col-form-label">Bio</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="bio" class="form-control" id="bio" style="height: 100px">Tell us about yourself</textarea>
                  </div>
                </div>
                
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
              </form>
            </div>
            
            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="profile-change-password" role="tabpanel">
              <form id="change-password-form" novalidate>
                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                
                <div class="row mb-3">
                  <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password <span class="text-danger">*</span></label>
                  <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                      <input name="currentPassword" type="password" class="form-control" id="currentPassword" required>
                      <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">Please enter your current password.</div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password <span class="text-danger">*</span></label>
                  <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                      <input name="newPassword" type="password" class="form-control" id="newPassword" required 
                             pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                      <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">
                      Password must contain at least 8 characters, including uppercase, lowercase and numbers.
                    </div>
                    <div class="progress mt-2" style="height: 5px;">
                      <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Password strength: <span id="password-strength-text">Weak</span></small>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="confirmNewPassword" class="col-md-4 col-lg-3 col-form-label">Confirm Password <span class="text-danger">*</span></label>
                  <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                      <input name="confirmNewPassword" type="password" class="form-control" id="confirmNewPassword" required>
                      <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">Passwords must match.</div>
                  </div>
                </div>
                
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
              </form>
              
              <div class="mt-4">
                <h5 class="card-title">Security Tips</h5>
                <ul class="list-unstyled">
                  <li><i class="bi bi-check-circle text-success me-2"></i> Use a unique password for this account</li>
                  <li><i class="bi bi-check-circle text-success me-2"></i> Change your password regularly</li>
                  <li><i class="bi bi-check-circle text-success me-2"></i> Never share your password with anyone</li>
                </ul>
              </div>
            </div>
            
            <!-- Privacy Tab -->
            <div class="tab-pane fade" id="profile-privacy" role="tabpanel">
              <h5 class="card-title">Privacy Settings</h5>
              
              <form id="privacy-settings">
                <div class="row mb-3">
                  <div class="col-md-8 offset-md-4 col-lg-9 offset-lg-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="profileVisibility" checked>
                      <label class="form-check-label" for="profileVisibility">Make my profile visible to others</label>
                    </div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-8 offset-md-4 col-lg-9 offset-lg-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="activityVisibility" checked>
                      <label class="form-check-label" for="activityVisibility">Show my activity to others</label>
                    </div>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-8 offset-md-4 col-lg-9 offset-lg-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="emailVisibility">
                      <label class="form-check-label" for="emailVisibility">Show my email address to others</label>
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                </div>
              </form>
              
              <div class="mt-4">
                <h5 class="card-title">Data Privacy</h5>
                <p>We respect your privacy and are committed to protecting your personal data. Read our <a href="privacy.php">Privacy Policy</a> to understand how we collect, use and protect your information.</p>
                <button class="btn btn-outline-danger" id="request-data">
                  <i class="bi bi-download me-1"></i> Request My Data
                </button>
                <button class="btn btn-outline-danger ms-2" id="delete-account">
                  <i class="bi bi-trash me-1"></i> Delete Account
                </button>
              </div>
            </div>
          </div><!-- End Tab Content -->
        </div>
      </div>
    </div>
  </div>
</section>

<!-- JavaScript for enhanced functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Profile image preview
  const profileImageInput = document.getElementById('profileImage');
  const profilePreview = document.getElementById('profile-preview');
  const removePhotoBtn = document.getElementById('remove-photo');
  
  if (profileImageInput && profilePreview) {
    profileImageInput.addEventListener('change', function(e) {
      if (e.target.files.length > 0) {
        const file = e.target.files[0];
        if (file.size > 2 * 1024 * 1024) {
          alert('File size must be less than 2MB');
          return;
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
          profilePreview.src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }
  
  if (removePhotoBtn) {
    removePhotoBtn.addEventListener('click', function() {
      profilePreview.src = 'assets/img/user.png';
      profileImageInput.value = '';
    });
  }
  
  // Toggle password visibility
  const togglePasswordBtns = document.querySelectorAll('.toggle-password');
  togglePasswordBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const input = this.previousElementSibling;
      const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
      input.setAttribute('type', type);
      this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
  });
  
  // Password strength meter
  const newPasswordInput = document.getElementById('newPassword');
  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
      const strengthBar = document.getElementById('password-strength-bar');
      const strengthText = document.getElementById('password-strength-text');
      
      // Simple strength calculation
      const password = this.value;
      let strength = 0;
      
      if (password.length >= 8) strength += 1;
      if (password.match(/[a-z]/)) strength += 1;
      if (password.match(/[A-Z]/)) strength += 1;
      if (password.match(/[0-9]/)) strength += 1;
      if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
      
      let width = 0;
      let text = 'Weak';
      let color = 'bg-danger';
      
      if (strength <= 2) {
        width = 33;
        text = 'Weak';
        color = 'bg-danger';
      } else if (strength <= 3) {
        width = 66;
        text = 'Medium';
        color = 'bg-warning';
      } else {
        width = 100;
        text = 'Strong';
        color = 'bg-success';
      }
      
      strengthBar.style.width = width + '%';
      strengthBar.className = 'progress-bar ' + color;
      strengthText.textContent = text;
    });
  }
  
  // Form validation
  const forms = document.querySelectorAll('form[novalidate]');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      
      this.classList.add('was-validated');
    });
  });
  
  // Confirm password match
  const confirmPasswordInput = document.getElementById('confirmNewPassword');
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function() {
      const newPassword = document.getElementById('newPassword').value;
      if (this.value !== newPassword) {
        this.setCustomValidity('Passwords must match');
      } else {
        this.setCustomValidity('');
      }
    });
  }
  
  // Delete account confirmation
  const deleteAccountBtn = document.getElementById('delete-account');
  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener('click', function(e) {
      if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        e.preventDefault();
      }
    });
  }
});
</script>