<?php
// Secure session initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Check if admin is logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'configs/dbconnection.php';

// Initial variables
$admin_id = $_SESSION['login_id'];
$success_message = "";
$error_message = "";

// First, determine where to look for the admin data based on the authentication system
$admin_found = false;
$admin_data = [];

// First try to find the admin in the admins table
$stmt = $conn->prepare("SELECT * FROM admins WHERE adminID = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin_data = $result->fetch_assoc();
    $admin_found = true;
} else {
    // If not found in admins table, try the students table where role = 'admin'
    $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ? AND role = 'admin'");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
        $admin_found = true;
        
        // Map student table fields to expected admin fields if needed
        if (!isset($admin_data['title'])) $admin_data['title'] = '';
        if (!isset($admin_data['bio'])) $admin_data['bio'] = '';
        if (!isset($admin_data['profile_pic'])) $admin_data['profile_pic'] = '';
    } else {
        $error_message = "Admin profile not found. Your account may not have admin privileges.";
    }
}

// If admin was found in either table, proceed
if (!$admin_found) {
    $error_message = "Admin profile not found. Please contact system administrator.";
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Validate inputs
    if (empty($name) || empty($email)) {
        $error_message = "Name and email are required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Process profile picture upload
        $profile_pic = $admin_data['profile_pic'] ?? ($admin_data['profilePicture'] ?? ''); // Keep existing by default
        
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($_FILES['profile_pic']['type'], $allowed_types)) {
                $error_message = "Only JPG, PNG, and GIF images are allowed.";
            } elseif ($_FILES['profile_pic']['size'] > $max_size) {
                $error_message = "Image size should be less than 2MB.";
            } else {
                $upload_dir = "assets/img/profile/admins/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $file_name = 'admin_' . $admin_id . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                    // Delete old profile pic if it exists
                    if (!empty($admin_data['profile_pic']) && file_exists($upload_dir . $admin_data['profile_pic'])) {
                        unlink($upload_dir . $admin_data['profile_pic']);
                    }
                    if (!empty($admin_data['profilePicture']) && file_exists('assets/img/profile/' . $admin_data['profilePicture'])) {
                        unlink('assets/img/profile/' . $admin_data['profilePicture']);
                    }
                    
                    $profile_pic = $file_name;
                } else {
                    $error_message = "Failed to upload image. Please try again.";
                }
            }
        }
        
       
if (empty($error_message)) {
    // Determine which table to update based on where admin was found
    $update_successful = false;
    
    if (isset($admin_data['adminID'])) {
        // Update in admins table
        $update_stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ?, title = ?, bio = ?, profile_pic = ? WHERE adminID = ?");
        $update_stmt->bind_param("ssssssi", $name, $email, $phone, $title, $bio, $profile_pic, $admin_id);
        
        if ($update_stmt->execute()) {
            $update_successful = true;
        } else {
            $error_message = "Failed to update profile in admins table: " . $conn->error;
        }
    } else {
        // Update in students table - dynamically build query based on available columns
        $columns_to_update = [];
        $params = [];
        $types = "";
        
        // Get the columns in the students table
        $result = $conn->query("SHOW COLUMNS FROM students");
        $available_columns = [];
        while ($row = $result->fetch_assoc()) {
            $available_columns[] = $row['Field'];
        }
        
        // Always update name and email
        $columns_to_update[] = "name = ?";
        $columns_to_update[] = "email = ?";
        $params[] = $name;
        $params[] = $email;
        $types .= "ss";
        
        // Check if phone/contactNumber exists
        if (in_array('phone', $available_columns) || in_array('contactNumber', $available_columns)) {
            $phone_column = in_array('phone', $available_columns) ? 'phone' : 'contactNumber';
            $columns_to_update[] = "$phone_column = ?";
            $params[] = $phone;
            $types .= "s";
        }
        
        // Check if profilePicture exists
        if (in_array('profilePicture', $available_columns)) {
            $columns_to_update[] = "profilePicture = ?";
            $params[] = $profile_pic;
            $types .= "s";
        }
        
        // Check if bio exists (only include if column exists)
        if (in_array('bio', $available_columns)) {
            $columns_to_update[] = "bio = ?";
            $params[] = $bio;
            $types .= "s";
        }
        
        // Add the studentID for WHERE clause
        $params[] = $admin_id;
        $types .= "i";
        
        // Build the final query
        $update_query = "UPDATE students SET " . implode(", ", $columns_to_update) . " WHERE studentID = ?";
        
        // Prepare and execute the statement
        $update_stmt = $conn->prepare($update_query);
        
        // Debug output
        error_log("Update query: " . $update_query);
        error_log("Types: " . $types);
        error_log("Params: " . print_r($params, true));
        
        // Bind parameters dynamically
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        
        call_user_func_array(array($update_stmt, 'bind_param'), $bind_names);
        
        if ($update_stmt->execute()) {
            $update_successful = true;
        } else {
            $error_message = "Failed to update profile in students table: " . $conn->error;
        }
    }
    
    if ($update_successful) {
        $success_message = "Profile updated successfully.";
        $_SESSION['login_name'] = $name; // Update session data
        
        // Refresh admin data
        if (isset($admin_data['adminID'])) {
            $stmt = $conn->prepare("SELECT * FROM admins WHERE adminID = ?");
            $stmt->bind_param("i", $admin_id);
        } else {
            $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ? AND role = 'admin'");
            $stmt->bind_param("i", $admin_id);
        }
        
        $stmt->execute();
        $admin_data = $stmt->get_result()->fetch_assoc();
        
        // Remap fields if needed
        if (!isset($admin_data['title'])) $admin_data['title'] = '';
        if (!isset($admin_data['bio'])) $admin_data['bio'] = '';
        if (!isset($admin_data['profile_pic']) && isset($admin_data['profilePicture'])) {
            $admin_data['profile_pic'] = $admin_data['profilePicture'];
        }
    }
}
    }
}

// Define profile picture path based on database structure
$profile_pic_path = '';
if (isset($admin_data['profile_pic']) && !empty($admin_data['profile_pic'])) {
    $profile_pic_path = 'assets/img/profile/admins/' . $admin_data['profile_pic'];
} elseif (isset($admin_data['profilePicture']) && !empty($admin_data['profilePicture'])) {
    $profile_pic_path = 'assets/img/profile/' . $admin_data['profilePicture'];
} else {
    $profile_pic_path = 'https://ui-avatars.com/api/?name=' . urlencode($admin_data['name'] ?? 'Admin') . '&background=random';
}

// Page title
$page_title = "My Profile";
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
    :root {
        --primary-color: #0d6efd;
        --primary-light: #e0e8ff;
        --secondary-color: #6c757d;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --border-radius: 12px;
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    body {
        background-color: #f5f7fb;
    }

    .profile-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 50%;
        margin: 0 auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
    }

    .profile-image-container:hover {
        transform: scale(1.03);
    }

    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.5);
        overflow: hidden;
        height: 0;
        transition: height 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .profile-image-container:hover .image-overlay {
        height: 100%;
        background-color: rgba(0, 0, 0, 0.3);
    }

    .edit-icon {
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .edit-icon:hover {
        transform: scale(1.2);
    }

    .icon-bg {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .hover-shadow {
        transition: var(--transition);
        border: 1px solid rgba(0, 0, 0, 0.05);
        background-color: #fff;
        border-radius: var(--border-radius);
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: var(--box-shadow);
        border-color: var(--primary-light);
        background-color: var(--primary-light);
    }

    .bg-light-hover:hover {
        background-color: var(--light-color) !important;
    }

    .card {
        border-radius: var(--border-radius);
        transition: var(--transition);
        border: none;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .card:hover {
        box-shadow: var(--box-shadow);
    }

    .nav-tabs .nav-link {
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        transition: var(--transition);
        font-weight: 500;
        border: none;
        padding: 0.75rem 1.25rem;
        color: var(--secondary-color);
    }

    .nav-tabs .nav-link:not(.active):hover {
        background-color: var(--light-color);
        color: var(--primary-color);
    }

    .nav-tabs .nav-link.active {
        background-color: transparent;
        color: var(--primary-color);
        border-bottom: 3px solid var(--primary-color);
        font-weight: 600;
    }

    .input-group-text {
        border-right: 0;
        transition: var(--transition);
        background-color: var(--light-color);
    }

    .form-control {
        transition: var(--transition);
        border-left: 0;
    }

    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .form-control:focus + .input-group-text {
        border-color: #86b7fe;
        background-color: var(--primary-light);
    }

    .badge {
        transition: var(--transition);
    }

    .badge:hover {
        transform: translateY(-2px);
    }

    .btn {
        transition: var(--transition);
        border-radius: var(--border-radius);
    }

    .btn-primary {
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.15);
        background-color: var(--primary-color);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.2);
        background-color: #0b5ed7;
    }

    .btn-light:hover {
        background-color: #f0f0f0;
    }

    .account-info {
        transition: var(--transition);
    }

    .contact-info .d-flex:hover .icon-bg {
        background-color: var(--primary-light) !important;
        transform: scale(1.1);
    }

    .text-gradient {
        background: linear-gradient(45deg, var(--primary-color), #0dcaf0);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
    }

    .pulse-effect {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
        }
    }

    .profile-badge {
        width: 40px;
        height: 40px;
        background-color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 1;
        border: 2px solid var(--primary-light);
    }
    </style>
</head>
<body>
<div class="container py-4 animate__animated animate__fadeIn">
    <!-- Header with Navigation Links -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient text-primary">
            <i class="fas fa-user-circle me-2"></i>
            My Profile
        </h1>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-tachometer-alt me-1"></i>
                Dashboard
            </a>
            <a href="security.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-shield-alt me-1"></i>
                Security Settings
            </a>
            <button type="button" class="btn btn-light btn-sm" id="profileHelp">
                <i class="fas fa-question-circle me-1"></i>
                Help
            </button>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Summary Card - Left Column -->
        <div class="col-lg-4 animate__animated animate__fadeInLeft">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4 position-relative">
                        <div class="profile-badge position-absolute top-0 end-0 translate-middle">
                            <i class="fas fa-check-circle text-primary fs-4"></i>
                        </div>
                        <div class="profile-image-container mx-auto" style="width: 160px; height: 160px;">
                            <img 
                                src="<?php echo $profile_pic_path; ?>" 
                                class="rounded-circle img-thumbnail border-3 shadow" 
                                alt="Admin Profile" 
                                style="width: 160px; height: 160px; object-fit: cover;"
                            >
                            <div class="image-overlay">
                                <label for="profile_pic" class="edit-icon" title="Change picture">
                                    <i class="fas fa-camera"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="fw-bold"><?php echo htmlspecialchars($admin_data['name'] ?? 'Administrator'); ?></h4>
                    
                    <p class="text-muted mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-shield-alt me-1"></i>
                            <?php echo ucfirst($admin_data['role'] ?? 'admin'); ?>
                        </span>
                    </p>
                    
                    <?php if (!empty($admin_data['title'])): ?>
                        <p class="mb-3">
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="fas fa-briefcase me-1"></i>
                                <?php echo htmlspecialchars($admin_data['title']); ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    
                    <div class="contact-info mt-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-bg bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div class="text-start">
                                <small class="text-muted d-block">Email</small>
                                <span><?php echo htmlspecialchars($admin_data['email'] ?? 'No email set'); ?></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-bg bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-phone text-primary"></i>
                            </div>
                            <div class="text-start">
                                <small class="text-muted d-block">Phone</small>
                                <span><?php echo htmlspecialchars($admin_data['phone'] ?? ($admin_data['contactNumber'] ?? 'No phone set')); ?></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="icon-bg bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div class="text-start">
                                <small class="text-muted d-block">Location</small>
                                <span>Election Admin Office</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="account-info mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between text-muted">
                            <small>
                                <i class="fas fa-calendar-alt me-1"></i>
                                Joined: <?php echo date('M d, Y', strtotime($admin_data['created_at'] ?? 'now')); ?>
                            </small>
                            <small>
                                <i class="fas fa-history me-1"></i>
                                Last login: <?php echo date('M d, H:i', strtotime($admin_data['last_login'] ?? 'now')); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Edit Form - Right Column -->
        <div class="col-lg-8 animate__animated animate__fadeInRight">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" 
                                    id="info-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#info" 
                                    type="button" 
                                    role="tab" 
                                    aria-selected="true">
                                <i class="fas fa-id-card me-2"></i>
                                Personal Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    id="bio-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#bio" 
                                    type="button" 
                                    role="tab" 
                                    aria-selected="false">
                                <i class="fas fa-file-alt me-2"></i>
                                Bio & Details
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="tab-content">
                            <!-- Personal Info Tab -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                                <!-- Hidden file input for profile image -->
                                <input type="file" name="profile_pic" id="profile_pic" class="d-none" accept="image/*">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user-tag me-1 text-primary"></i>
                                            Full Name
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($admin_data['name'] ?? ''); ?>" required>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                Please enter your name.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-briefcase me-1 text-primary"></i>
                                            Job Title
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-briefcase"></i></span>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($admin_data['title'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-1 text-primary"></i>
                                            Email Address
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>" required>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                Please enter a valid email address.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-1 text-primary"></i>
                                            Phone Number
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($admin_data['phone'] ?? ($admin_data['contactNumber'] ?? '')); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-light border mt-4">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-info-circle text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Keep Your Information Updated</h6>
                                            <p class="mb-0 text-muted small">Your contact information will be used for important election notifications and system alerts.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bio & Details Tab -->
                            <div class="tab-pane fade" id="bio" role="tabpanel" aria-labelledby="bio-tab">
                                <div class="mb-4">
                                    <label for="bio" class="form-label">
                                        <i class="fas fa-file-signature me-1 text-primary"></i>
                                        Professional Bio
                                    </label>
                                    <textarea class="form-control" id="bio" name="bio" rows="6" 
                                              placeholder="Tell us a bit about yourself..."><?php echo htmlspecialchars($admin_data['bio'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This information may be displayed in election materials.
                                    </div>
                                </div>
                                
                                <div class="alert alert-light border">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-pencil-alt text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Writing Tips</h6>
                                            <ul class="mb-0 text-muted small ps-3">
                                                <li>Keep your bio professional and concise</li>
                                                <li>Highlight relevant experience in election management</li>
                                                <li>Mention any certifications or qualifications</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="reset" class="btn btn-light">
                                <i class="fas fa-sync-alt me-1"></i>
                                Reset
                            </button>
                            <button type="submit" name="update_profile" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Links Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-bolt me-2 text-primary"></i>
                        Quick Actions
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="security.php" class="text-decoration-none">
                                <div class="d-flex p-3 rounded h-100 hover-shadow">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-shield-alt text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Security</h6>
                                        <p class="text-muted small mb-0">Update password & 2FA</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="activity.php" class="text-decoration-none">
                                <div class="d-flex p-3 rounded h-100 hover-shadow">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-chart-line text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Activity</h6>
                                        <p class="text-muted small mb-0">View recent logins</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="appearance.php" class="text-decoration-none">
                                <div class="d-flex p-3 rounded h-100 hover-shadow">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-palette text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Appearance</h6>
                                        <p class="text-muted small mb-0">Customize UI settings</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image upload trigger
    document.querySelector('.edit-icon').addEventListener('click', function() {
        document.getElementById('profile_pic').click();
    });
    
    // Preview uploaded image
    document.getElementById('profile_pic').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const profileImg = document.querySelector('.profile-image-container img');
                profileImg.src = e.target.result;
                
                // Show success notification
                Swal.fire({
                    title: 'Image Selected!',
                    text: 'Click Save Changes to update your profile picture',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show validation error notification
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please check the form for errors',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                // Show saving message
                Swal.fire({
                    title: 'Saving...',
                    text: 'Updating your profile',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // Form will submit normally
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Reset button handler
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        const form = this.closest('form');
        form.classList.remove('was-validated');
        
        Swal.fire({
            title: 'Form Reset',
            text: 'All changes have been discarded',
            icon: 'info',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    });
    
    // Help modal
    document.getElementById('profileHelp').addEventListener('click', function() {
        Swal.fire({
            title: '<i class="fas fa-question-circle text-primary me-3"></i>Profile Help',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <h6><i class="fas fa-image me-2 text-primary"></i>Changing Your Profile Picture</h6>
                        <p class="text-muted mb-0">Hover over your profile picture and click on the camera icon to upload a new image.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-user-tag me-2 text-primary"></i>Personal Information</h6>
                        <p class="text-muted mb-0">Your name and email are required. Other fields are optional but recommended.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-shield-alt me-2 text-primary"></i>Security</h6>
                        <p class="text-muted mb-0">To change your password or security settings, visit the Security page.</p>
                    </div>
                </div>
            `,
            confirmButtonText: 'Got it!',
            confirmButtonColor: '#0d6efd',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    });
    
    // Show success message if it exists using SweetAlert2
    <?php if (!empty($success_message)): ?>
    Swal.fire({
        title: 'Success!',
        text: '<?php echo addslashes($success_message); ?>',
        icon: 'success',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php endif; ?>
    
    // Show error message if it exists using SweetAlert2
    <?php if (!empty($error_message)): ?>
    Swal.fire({
        title: 'Error!',
        text: '<?php echo addslashes($error_message); ?>',
        icon: 'error',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>