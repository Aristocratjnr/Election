<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Redirect if not logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
require 'configs/dbconnection.php';

// Initialize variables
$successMessage = '';
$errorMessage = '';
$studentData = [];

// Fetch current student data
try {
    $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
    $stmt->bind_param('i', $_SESSION['login_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $studentData = $result->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Settings error: " . $e->getMessage());
    $errorMessage = "Error loading your profile information.";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Profile Information
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $department = trim($_POST['department']);
        $contactNumber = trim($_POST['contactNumber']);
        $profilePicture = $studentData['profilePicture'] ?? null;

        // Handle file upload
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/img/profile/students/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['profileImage']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $errorMessage = "Only JPG, PNG, or GIF files are allowed.";
            } elseif ($_FILES['profileImage']['size'] > 2 * 1024 * 1024) {
                $errorMessage = "File size must be less than 2MB.";
            } else {
                // Generate unique filename
                $extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
                $profilePicture = $_SESSION['login_id'] . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $profilePicture;
                
                // Delete old picture if it exists
                if (!empty($studentData['profilePicture']) && file_exists($uploadDir . $studentData['profilePicture'])) {
                    unlink($uploadDir . $studentData['profilePicture']);
                }
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadPath)) {
                    $errorMessage = "Failed to upload profile picture.";
                }
            }
        }

        if (empty($errorMessage)) {
            try {
                $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, department = ?, contactNumber = ?, profilePicture = ? WHERE studentID = ?");
                $stmt->bind_param('sssssi', $name, $email, $department, $contactNumber, $profilePicture, $_SESSION['login_id']);
                
                if ($stmt->execute()) {
                    $successMessage = "✅Profile updated successfully!";
                    // Refresh student data
                    $studentData['name'] = $name;
                    $studentData['email'] = $email;
                    $studentData['department'] = $department;
                    $studentData['contactNumber'] = $contactNumber;
                    $studentData['profilePicture'] = $profilePicture;
                } else {
                    $errorMessage = "Failed to update profile. Please try again.";
                }
            } catch (Exception $e) {
                error_log("Profile update error: " . $e->getMessage());
                $errorMessage = "An error occurred while updating your profile.";
            }
        }
    }
    
    // Change Password
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        try {
            $stmt = $conn->prepare("SELECT password FROM students WHERE studentID = ?");
            $stmt->bind_param('i', $_SESSION['login_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                
                if (password_verify($currentPassword, $student['password'])) {
                    if ($newPassword === $confirmPassword) {
                        // Update password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE studentID = ?");
                        $stmt->bind_param('si', $hashedPassword, $_SESSION['login_id']);
                        
                        if ($stmt->execute()) {
                            $successMessage = "Password changed successfully!";
                        } else {
                            $errorMessage = "Failed to update password.";
                        }
                    } else {
                        $errorMessage = "New passwords do not match.";
                    }
                } else {
                    $errorMessage = "Current password is incorrect.";
                }
            }
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $errorMessage = "An error occurred while changing your password.";
        }
    }
    
    // Handle account actions
    if (isset($_POST['account_action'])) {
        $action = $_POST['account_action'];
        $password = $_POST['action_password'] ?? '';
        
        try {
            // Verify password first
            $stmt = $conn->prepare("SELECT password FROM students WHERE studentID = ?");
            $stmt->bind_param('i', $_SESSION['login_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                
                if (password_verify($password, $student['password'])) {
                    if ($action === 'deactivate') {
                        $stmt = $conn->prepare("UPDATE students SET status = 'Inactive' WHERE studentID = ?");
                        $stmt->bind_param('i', $_SESSION['login_id']);
                        if ($stmt->execute()) {
                            session_destroy();
                            header("Location: login.php?deactivated=1");
                            exit;
                        }
                    } elseif ($action === 'delete') {
                        // Delete profile picture if exists
                        if (!empty($studentData['profilePicture'])) {
                            $filePath = 'assets/img/profile/students/' . $studentData['profilePicture'];
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                        
                        // Delete account
                        $stmt = $conn->prepare("DELETE FROM students WHERE studentID = ?");
                        $stmt->bind_param('i', $_SESSION['login_id']);
                        if ($stmt->execute()) {
                            session_destroy();
                            header("Location: register.php?deleted=1");
                            exit;
                        }
                    }
                } else {
                    $errorMessage = "Incorrect password.";
                }
            }
        } catch (Exception $e) {
            error_log("Account action error: " . $e->getMessage());
            $errorMessage = "An error occurred while processing your request.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - SmartVote</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .settings-container {
            max-width: 552px;
            margin: 0 auto;
        }
        .settings-card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .settings-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .profile-picture:hover {
            opacity: 0.8;
            transform: scale(1.02);
        }
        .form-label {
            font-weight: 500;
        }
        .file-input {
            display: none;
        }
        .action-modal .modal-header {
            color: white;
        }
        .deactivate-modal .modal-header {
            background-color: #ffc107;
        }
        .delete-modal .modal-header {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?><br>

    <div class="container py-5">
        <div class="settings-container">
            <h2 class="mb-4"><i class="bi bi-gear me-2"></i> Account Settings</h2>
            
            <!-- Success/Error Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Information Card -->
            <div class="settings-card card mb-2">
                <div class="settings-card-header card-header d-flex justify-content-between align-items-center ">
                    <span><i class="bi bi-person me-2"></i> Profile Information</span>
                </div>
                <div class="card-body">
                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <label for="profileImage" style="cursor: pointer;">
                                    <img id="profilePreview" 
                                         src="<?php echo !empty($studentData['profilePicture']) ? 
                                             'assets/img/profile/students/'.htmlspecialchars($studentData['profilePicture']) : 
                                             'assets/img/student.png'; ?>" 
                                         class="profile-picture mb-3">
                                    <input type="file" id="profileImage" name="profileImage" class="file-input" accept="image/*">
                                </label>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('profileImage').click()">
                                    <i class="bi bi-upload me-1"></i> Change Picture
                                </button>
                                <small class="text-muted d-block mt-2">Max 2MB (JPG, PNG, GIF)</small>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($studentData['name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($studentData['email'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department" 
                                           value="<?php echo htmlspecialchars($studentData['department'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                    <input type="tel" class="form-control" id="contactNumber" name="contactNumber" 
                                           value="<?php echo htmlspecialchars($studentData['contactNumber'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="settings-card card mb-4">
                <div class="settings-card-header card-header">
                    <i class="bi bi-shield-lock me-2"></i> Change Password
                </div>
                <div class="card-body">
                    <form action="settings.php" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Minimum 8 characters with at least one number and one letter</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Actions Card -->
            <div class="settings-card card">
                <div class="settings-card-header card-header">
                    <i class="bi bi-exclamation-triangle me-2"></i> Account Actions
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Deactivate Account</h6>
                            <p class="mb-0 text-muted">Temporarily disable your account (you can reactivate later)</p>
                        </div>
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                            Deactivate
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Delete Account</h6>
                            <p class="mb-0 text-muted">Permanently remove your account and all data</p>
                        </div>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate Account Modal -->
    <div class="modal fade deactivate-modal" id="deactivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i> Confirm Deactivation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings.php" method="POST">
                    <div class="modal-body">
                        <p>Your account will be deactivated. You can reactivate by logging in again.</p>
                        <div class="mb-3">
                            <label for="deactivatePassword" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="deactivatePassword" name="action_password" required>
                        </div>
                        <input type="hidden" name="account_action" value="deactivate">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">Deactivate Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade delete-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> This action cannot be undone!
                        </div>
                        <p>All your data will be permanently deleted from our systems.</p>
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="deletePassword" name="action_password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this action is permanent
                            </label>
                        </div>
                        <input type="hidden" name="account_action" value="delete">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Profile picture preview
    document.getElementById('profileImage').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            
            // Validate file size
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Only JPG, PNG or GIF images are allowed');
                this.value = '';
                return;
            }
            
            // Preview image
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profilePreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Password validation
    document.querySelector('form[name="change_password"]').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
        }
    });

    // Delete account confirmation
    document.querySelector('#deleteModal form').addEventListener('submit', function(e) {
        if (!confirm('Are you absolutely sure you want to permanently delete your account?')) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>