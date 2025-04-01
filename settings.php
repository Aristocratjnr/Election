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
    $stmt = $conn->prepare("SELECT * FROM Students WHERE studentID = ?");
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
        
        try {
            $stmt = $conn->prepare("UPDATE Students SET name = ?, email = ?, department = ?, contactNumber = ? WHERE studentID = ?");
            $stmt->bind_param('ssssi', $name, $email, $department, $contactNumber, $_SESSION['login_id']);
            
            if ($stmt->execute()) {
                $successMessage = "Profile updated successfully!";
                // Refresh student data
                $studentData['name'] = $name;
                $studentData['email'] = $email;
                $studentData['department'] = $department;
                $studentData['contactNumber'] = $contactNumber;
            } else {
                $errorMessage = "Failed to update profile. Please try again.";
            }
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $errorMessage = "An error occurred while updating your profile.";
        }
    }
    
    // Change Password
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        try {
            $stmt = $conn->prepare("SELECT password FROM Students WHERE studentID = ?");
            $stmt->bind_param('i', $_SESSION['login_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                
                if (password_verify($currentPassword, $student['password'])) {
                    if ($newPassword === $confirmPassword) {
                        // Update password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE Students SET password = ? WHERE studentID = ?");
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
            max-width: 800px;
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
        }
        .form-label {
            font-weight: 500;
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
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <!-- Profile Information Card -->
            <div class="settings-card card mb-4">
                <div class="settings-card-header card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person me-2"></i> Profile Information</span>
                </div>
                <div class="card-body">
                    <form action="settings.php" method="POST">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <img src="assets/img/aristo.png" class="profile-picture mb-3" alt="Profile Picture">
                                <button class="btn btn-outline-primary btn-sm">Change Picture</button>
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
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
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
                            <p class="mb-0 text-muted">Temporarily disable your account</p>
                        </div>
                        <button class="btn btn-outline-danger">Deactivate</button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Delete Account</h6>
                            <p class="mb-0 text-muted">Permanently remove your account and all data</p>
                        </div>
                        <button class="btn btn-outline-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Password validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
            return false;
        }
        
        // Add more password validation if needed
        return true;
    });
    </script>
</body>
</html>