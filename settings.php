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
                    $successMessage = "Profile updated successfully!";
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
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --danger-color: #ef476f;
            --warning-color: #fca311;
            --success-color: #06d6a0;
            --light-bg: #f8f9fa;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        body {
            background-color: #f9fafb;
            color: #333;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }
        
        .settings-container {
            max-width: 680px;
            margin: 0 auto;
        }
        
        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eaedf2;
        }
        
        .settings-card {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 24px;
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .settings-card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .settings-card-header {
            background-color: white;
            border-bottom: 1px solid #eaedf2;
            padding: 18px 24px;
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
        }
        
        .card-body {
            padding: 24px;
        }
        
        .profile-picture-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto;
        }
        
        .profile-picture {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .profile-upload-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.2s ease;
        }
        
        .profile-upload-overlay:hover {
            transform: scale(1.1);
            background-color: var(--primary-hover);
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 10px 16px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .file-input {
            display: none;
        }
        
        .action-card-item {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .action-card-item:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .action-card-item:last-child {
            margin-bottom: 0;
        }
        
        .action-text h6 {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .action-text p {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
        }
        
        .modal-header {
            padding: 20px 24px;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #eaedf2;
        }
        
        .deactivate-modal .modal-header {
            background-color: var(--warning-color);
            color: white;
        }
        
        .delete-modal .modal-header {
            background-color: var(--danger-color);
            color: white;
        }
        
        .alert {
            border-radius: 8px;
            padding: 16px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .settings-card {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .settings-card:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .settings-card:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 8px;
            transition: all 0.3s;
        }
        
        .strength-weak {
            width: 30%;
            background-color: #ff4d4f;
        }
        
        .strength-medium {
            width: 60%;
            background-color: #faad14;
        }
        
        .strength-strong {
            width: 100%;
            background-color: #52c41a;
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="settings-container">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="bi bi-gear-fill me-2 text-primary"></i> Account Settings</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"> <i class="bi bi-box-arrow-in-left action-icon icon"></i><a href="student.php">dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">settings</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Information Card -->
            <div class="settings-card card mb-4">
                <div class="settings-card-header card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-circle me-2 text-primary"></i> Profile Information</span>
                    <span class="badge bg-primary">Student</span>
                </div>
                <div class="card-body">
                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <div class="row align-items-center mb-4">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="profile-picture-container">
                                    <img id="profilePreview" 
                                         src="<?php echo !empty($studentData['profilePicture']) ? 
                                             'assets/img/profile/students/'.htmlspecialchars($studentData['profilePicture']) : 
                                             'assets/img/student.png'; ?>" 
                                         class="profile-picture" alt="Profile Picture">
                                    <label for="profileImage" class="profile-upload-overlay">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" id="profileImage" name="profileImage" class="file-input" accept="image/*">
                                </div>
                                <small class="text-muted d-block mt-3">Max 2MB (JPG, PNG, GIF)</small>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($studentData['name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($studentData['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-buildings department-icon icon"></i></span>
                                            <input type="text" class="form-control" id="department" name="department" 
                                                   value="<?php echo htmlspecialchars($studentData['department'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contactNumber" class="form-label">Contact Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" class="form-control" id="contactNumber" name="contactNumber" 
                                                   value="<?php echo htmlspecialchars($studentData['contactNumber'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="settings-card card mb-4">
                <div class="settings-card-header card-header">
                    <i class="bi bi-shield-lock-fill me-2 text-primary"></i> Security Settings
                </div>
                <div class="card-body">
                    <form action="settings.php" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2" id="passwordStrength"></div>
                            <small class="text-muted">Minimum 8 characters with at least one number and one letter</small>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-shield-check me-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Actions Card -->
            <div class="settings-card card">
                <div class="settings-card-header card-header">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-primary"></i> Account Actions
                </div>
                <div class="card-body">
                    <div class="action-card-item d-flex justify-content-between align-items-center">
                        <div class="action-text">
                            <h6>Deactivate Account</h6>
                            <p>Temporarily disable your account (you can reactivate later)</p>
                        </div>
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                            <i class="bi bi-pause-circle me-1"></i> Deactivate
                        </button>
                    </div>
                    <hr>
                    <div class="action-card-item d-flex justify-content-between align-items-center">
                        <div class="action-text">
                            <h6>Delete Account</h6>
                            <p>Permanently remove your account and all data</p>
                        </div>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash3 me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate Account Modal -->
    <div class="modal fade deactivate-modal" id="deactivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i> Confirm Deactivation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings.php" method="POST">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-pause-circle text-warning" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">Account Deactivation</h4>
                            <p class="text-muted">Your account will be temporarily deactivated</p>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i> You can reactivate your account by logging in again.
                        </div>
                        <div class="mb-3">
                            <label for="deactivatePassword" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="deactivatePassword" name="action_password" required>
                        </div>
                        <input type="hidden" name="account_action" value="deactivate">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bi bi-pause-circle me-2"></i> Deactivate Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade delete-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="settings.php" method="POST">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-trash3 text-danger" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">Delete Your Account?</h4>
                            <p class="text-muted">This action cannot be undone</p>
                        </div>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> All your data will be permanently erased from our systems.
                        </div>
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="deletePassword" name="action_password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this action is permanent and cannot be reversed
                            </label>
                        </div>
                        <input type="hidden" name="account_action" value="delete">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash3 me-2"></i> Permanently Delete Account
                        </button>
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

    // Password strength checker
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        const strengthIndicator = document.getElementById('passwordStrength');
        
        // Reset the strength indicator
        strengthIndicator.className = 'password-strength';
        
        if (password.length === 0) {
            strengthIndicator.style.width = '0';
            return;
        }
        
        // Check password strength
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Character variety checks
        if (/[0-9]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Update the strength indicator
        if (strength < 3) {
            strengthIndicator.classList.add('strength-weak');
        } else if (strength < 5) {
            strengthIndicator.classList.add('strength-medium');
        } else {
            strengthIndicator.classList.add('strength-strong');
        }
    });

    // Password visibility toggle
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // Password match validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    confirmPassword.addEventListener('input', function() {
        if (newPassword.value !== this.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
    
    newPassword.addEventListener('input', function() {
        if (confirmPassword.value !== '') {
            if (confirmPassword.value !== this.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    });

    // Delete account confirmation
    document.querySelector('#deleteModal form').addEventListener('submit', function(e) {
        const checkbox = document.getElementById('confirmDelete');
        const password = document.getElementById('deletePassword');
        
        if (!checkbox.checked || password.value === '') {
            e.preventDefault();
            alert('Please confirm your understanding and enter your password');
        }
    });
    </script>
</body>
</html>