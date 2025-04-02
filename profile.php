<?php
session_start(); // Start the session
// Secure session start and authorization check
include 'configs/dbconnection.php';
include 'configs/session.php';

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

$studentId = (int)$_SESSION['login_id']; // Force integer type for security

// Get student profile data
try {
    $stmt = $conn->prepare("SELECT StudentID, FullName, Username, Email, PhoneNumber, ProfilePicture FROM students WHERE StudentID = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Student not found");
    }
    
    $student = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "Unable to load profile data. Please try again later.";
}

// Get unread notifications count
$unreadCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE StudentID = ? AND IsRead = 0");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadCount = $result->fetch_assoc()['unread'];
    $stmt->close();
} catch (Exception $e) {
    error_log("Notification count error: " . $e->getMessage());
}
?>

<!-- HTML HEAD SECTION (same as before) -->

<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Student Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notifications.php">
                            Notifications
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        <?php if (!empty($student['ProfilePicture']) && file_exists('assets/img/profile/students/' . $student['ProfilePicture'])): ?>
                            <img src="<?php echo htmlspecialchars('assets/img/profile/students/' . $student['ProfilePicture']); ?>" 
                                 alt="Profile Picture" 
                                 class="rounded-circle" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <img src="assets/img/student.png" 
                                 alt="Profile" 
                                 class="rounded-circle" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php endif; ?>
                        <h2 class="text-truncate mt-3"><?php echo htmlspecialchars($student['FullName']); ?></h2>
                        <h3 class="text-muted">Student</h3>
                        
                        <!-- Rest of the profile card content -->
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
                        </ul>
                        
                        <div class="tab-content pt-3">
                            <!-- Overview Tab -->
                            <div class="tab-pane fade show active" id="profile-overview" role="tabpanel">
                                <h5 class="card-title">Student Details</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label">Full Name</div>
                                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($student['FullName']); ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label">Username</div>
                                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($student['Username']); ?></div>
                                </div>
                                
                                <?php if (!empty($student['PhoneNumber'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label">Phone</div>
                                        <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($student['PhoneNumber']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($student['Email'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label">Email</div>
                                        <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($student['Email']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Edit Profile Tab -->
                            <div class="tab-pane fade" id="profile-edit" role="tabpanel">
                                <form id="edit-profile" enctype="multipart/form-data" novalidate>
                                    <input type="hidden" name="StudentID" value="<?php echo $student['StudentID']; ?>">
                                    
                                    <div class="row mb-3">
                                        <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($student['ProfilePicture']) && file_exists('assets/img/profile/students/' . $student['ProfilePicture'])): ?>
                                                    <img id="profile-preview" 
                                                         src="<?php echo htmlspecialchars('assets/img/profile/students/' . $student['ProfilePicture']); ?>" 
                                                         alt="Profile" 
                                                         class="rounded-circle me-3" 
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img id="profile-preview" 
                                                         src="assets/img/student.png" 
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="fullName" type="text" class="form-control" id="fullName" 
                                                   value="<?php echo htmlspecialchars($student['FullName']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <label for="phoneNumber" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="phoneNumber" type="tel" class="form-control" id="phoneNumber" 
                                                   value="<?php echo htmlspecialchars($student['PhoneNumber']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
</body>
</html>