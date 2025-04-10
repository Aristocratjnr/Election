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
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'configs/dbconnection.php';

// Initial variables
$admin_id = $_SESSION['login_id'];
$success_message = "";
$error_message = "";

// Default UI settings
$ui_settings = [
    'theme' => 'light',
    'sidebar_color' => 'default',
    'font_size' => 'medium',
    'layout_mode' => 'fluid',
    'animations' => 'on',
    'notifications' => 'on'
];

// Check if ui_preferences column exists in the admins table
$column_exists = false;
$check_column = $conn->query("SHOW COLUMNS FROM admins LIKE 'ui_preferences'");
if ($check_column && $check_column->num_rows > 0) {
    $column_exists = true;
    
    // Fetch admin UI preferences
    $stmt = $conn->prepare("SELECT ui_preferences FROM admins WHERE adminID = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
        if (!empty($admin_data['ui_preferences'])) {
            $saved_preferences = json_decode($admin_data['ui_preferences'], true);
            if (is_array($saved_preferences)) {
                $ui_settings = array_merge($ui_settings, $saved_preferences);
            }
        }
    }
} else {
    // Create ui_preferences column
    try {
        $alter_table = $conn->query("ALTER TABLE admins ADD COLUMN ui_preferences JSON NULL");
        if ($alter_table) {
            $success_message = "UI preferences system initialized successfully.";
        }
    } catch (Exception $e) {
        $error_message = "Could not create UI preferences column: " . $e->getMessage();
    }
}

// Handle appearance update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_appearance'])) {
    $theme = filter_input(INPUT_POST, 'theme', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $sidebar_color = filter_input(INPUT_POST, 'sidebar_color', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $font_size = filter_input(INPUT_POST, 'font_size', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $layout_mode = filter_input(INPUT_POST, 'layout_mode', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $animations = filter_input(INPUT_POST, 'animations', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'off';
    $notifications = filter_input(INPUT_POST, 'notifications', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'off';
    
    // Update settings array
    $new_settings = [
        'theme' => $theme,
        'sidebar_color' => $sidebar_color,
        'font_size' => $font_size,
        'layout_mode' => $layout_mode,
        'animations' => $animations,
        'notifications' => $notifications
    ];
    
    // Save to database as JSON
    if ($column_exists) {
        $preferences_json = json_encode($new_settings);
        $update_stmt = $conn->prepare("UPDATE admins SET ui_preferences = ? WHERE adminID = ?");
        $update_stmt->bind_param("si", $preferences_json, $admin_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Appearance settings updated successfully.";
            $ui_settings = $new_settings; // Update current settings
            
            // Set cookie for theme preference (optional)
            setcookie('admin_theme', $theme, time() + (86400 * 30), "/", "", true, true);
        } else {
            $error_message = "Failed to update appearance settings: " . $conn->error;
        }
    } else {
        $error_message = "Cannot save settings. UI preferences system is not initialized.";
    }
}

// Reset to defaults
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_defaults'])) {
    // Default settings
    $default_settings = [
        'theme' => 'light',
        'sidebar_color' => 'default',
        'font_size' => 'medium',
        'layout_mode' => 'fluid',
        'animations' => 'on',
        'notifications' => 'on'
    ];
    
    // Save defaults to database
    if ($column_exists) {
        $preferences_json = json_encode($default_settings);
        $update_stmt = $conn->prepare("UPDATE admins SET ui_preferences = ? WHERE adminID = ?");
        $update_stmt->bind_param("si", $preferences_json, $admin_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Appearance settings reset to defaults.";
            $ui_settings = $default_settings; // Update current settings
            
            // Reset theme cookie
            setcookie('admin_theme', 'light', time() + (86400 * 30), "/", "", true, true);
        } else {
            $error_message = "Failed to reset appearance settings: " . $conn->error;
        }
    } else {
        $error_message = "Cannot reset settings. UI preferences system is not initialized.";
        // Use default settings anyway for the current view
        $ui_settings = $default_settings;
    }
}

// Page title
$page_title = "UI Appearance";
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
    :root {
        --primary-color: #4e73df;
        --primary-light: rgba(78, 115, 223, 0.1);
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --border-radius: 0.5rem;
        --box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        --transition: all 0.3s ease;
    }
    
    body {
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f8f9fc;
        color: #333;
        line-height: 1.6;
    }
    
    .main-content {
        padding-top: 2rem;
        padding-bottom: 3rem;
    }
    
    /* Card styling */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .card-title {
        font-weight: 700;
        color: var(--dark-color);
    }
    
    /* Nav tabs */
    .nav-tabs {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: var(--secondary-color);
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: var(--transition);
        position: relative;
    }
    
    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        background-color: var(--primary-light);
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background-color: transparent;
        border-bottom: 3px solid var(--primary-color);
    }
    
    .nav-tabs .nav-link i {
        margin-right: 0.5rem;
        font-size: 1.1em;
    }
    
    /* Form controls */
    .form-control, .form-select {
        padding: 0.75rem 1rem;
        border: 1px solid #d1d3e2;
        border-radius: var(--border-radius);
        transition: var(--transition);
        font-size: 0.95rem;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    /* Input groups */
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #d1d3e2;
        color: var(--secondary-color);
    }
    
    /* System info tables */
    .system-info-table th {
        font-weight: 600;
        color: var(--dark-color);
        white-space: nowrap;
        padding-left: 0;
    }
    
    .system-info-table td {
        font-weight: 500;
        text-align: right;
        padding-right: 0;
    }
    
    /* Buttons */
    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn i {
        margin-right: 0.5rem;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: #3a5bd9;
        border-color: #3a5bd9;
        transform: translateY(-2px);
    }
    
    .btn-outline-secondary {
        border-color: #d1d3e2;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
    }
    
    /* Alert styling */
    .alert {
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        border: none;
    }
    
    .alert i {
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        background-color: transparent;
        padding: 0.75rem 0;
        font-size: 0.9rem;
    }
    
    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    /* Animation for form validation */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
    
    .shake-animation {
        animation: shake 0.5s ease-in-out;
    }
    
    /* Settings tabs content */
    .settings-tab-content {
        padding: 1.5rem 0;
    }
    
    /* Form text helper */
    .form-helper-text {
        font-size: 0.85rem;
        color: var(--secondary-color);
        margin-top: 0.5rem;
        display: block;
    }
    
    /* Status badges */
    .status-badge {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.4em 0.8em;
        border-radius: 50rem;
    }
    
    /* Page header */
    .page-header {
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .page-header h1 {
        font-weight: 700;
        color: var(--dark-color);
    }
    
    .page-header p {
        color: var(--secondary-color);
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .card-body {
            padding: 1.25rem;
        }
        
        .nav-tabs .nav-link {
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }
    
    /* Custom checkbox and radio */
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    /* Floating labels */
    .form-floating label {
        color: var(--secondary-color);
    }
    
    /* Toolbar buttons */
    .btn-toolbar .btn {
        box-shadow: none;
    }
    
    /* Hover effects */
    .hover-effect {
        transition: var(--transition);
    }
    
    .hover-effect:hover {
        transform: translateY(-3px);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #3a5bd9;
    }
    
    /* Custom tab indicator */
    .nav-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--primary-color);
    }
    
    /* Input group focus */
    .input-group:focus-within .input-group-text {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 class="h2 fw-bold mb-2">
                                        <i class="bi bi-sliders me-2 text-primary"></i>
                                        System Settings
                                    </h1>
                                    <p class="text-muted mb-0">Configure and manage all system preferences and options</p>
                                </div>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary hover-effect" id="downloadConfig">
                                            <i class="bi bi-download me-1"></i>
                                            Export Settings
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary hover-effect" id="helpButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Get help with settings">
                                            <i class="bi bi-question-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav aria-label="breadcrumb" class="mb-4">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-sliders me-1"></i>Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                        <div>
                            <?php echo $success_message; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <div>
                            <?php echo $error_message; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Settings Form -->
                    <div class="card mb-4 hover-effect">
                        <div class="card-header bg-white">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" 
                                            id="general-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#general" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="general" 
                                            aria-selected="true">
                                        <i class="bi bi-gear-fill me-1"></i>
                                        General Settings
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" 
                                            id="election-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#election" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="election" 
                                            aria-selected="false">
                                        <i class="bi bi-calendar-event-fill me-1"></i>
                                        Election Settings
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" 
                                            id="display-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#display" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="display" 
                                            aria-selected="false">
                                        <i class="bi bi-display-fill me-1"></i>
                                        Display Settings
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" 
                                            id="advanced-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#advanced" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="advanced" 
                                            aria-selected="false">
                                        <i class="bi bi-tools me-1"></i>
                                        Advanced
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                <div class="tab-content settings-tab-content">
                                    <!-- General Settings Tab -->
                                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="site_name" class="form-label fw-medium">Site Name</label>
                                                <div class="input-group has-validation">
                                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                                           value="<?php echo htmlspecialchars($system_settings['site_name']); ?>" required>
                                                    <div class="invalid-feedback">
                                                        Please enter the site name.
                                                    </div>
                                                </div>
                                                <small class="form-helper-text">This name will appear throughout the system.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="admin_email" class="form-label fw-medium">Administrator Email</label>
                                                <div class="input-group has-validation">
                                                    <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                           value="<?php echo htmlspecialchars($system_settings['admin_email']); ?>">
                                                    <div class="invalid-feedback">
                                                        Please enter a valid email address.
                                                    </div>
                                                </div>
                                                <small class="form-helper-text">Used for system notifications and alerts.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="maintenance_mode" class="form-label fw-medium">Maintenance Mode</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-tools"></i></span>
                                                    <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                                        <option value="disabled" <?php echo $system_settings['maintenance_mode'] === 'disabled' ? 'selected' : ''; ?>>Disabled (Site Active)</option>
                                                        <option value="enabled" <?php echo $system_settings['maintenance_mode'] === 'enabled' ? 'selected' : ''; ?>>Enabled (Site Offline)</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">When enabled, only admins can access the site.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="email_notifications" class="form-label fw-medium">Email Notifications</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-bell"></i></span>
                                                    <select class="form-select" id="email_notifications" name="email_notifications">
                                                        <option value="enabled" <?php echo $system_settings['email_notifications'] === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                                        <option value="disabled" <?php echo $system_settings['email_notifications'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">Send email notifications for important events.</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Election Settings Tab -->
                                    <div class="tab-pane fade" id="election" role="tabpanel" aria-labelledby="election-tab">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="max_candidates" class="form-label fw-medium">Maximum Candidates Per Position</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                                    <input type="number" class="form-control" id="max_candidates" name="max_candidates" 
                                                           value="<?php echo htmlspecialchars($system_settings['max_candidates']); ?>" 
                                                           min="1" max="50" required>
                                                    <div class="invalid-feedback">
                                                        Please enter a number between 1 and 50.
                                                    </div>
                                                </div>
                                                <small class="form-helper-text">Maximum number of candidates allowed for each position.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="default_positions" class="form-label fw-medium">Default Positions Per Election</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                                    <input type="number" class="form-control" id="default_positions" name="default_positions" 
                                                           value="<?php echo htmlspecialchars($system_settings['default_positions']); ?>" 
                                                           min="1" max="20" required>
                                                    <div class="invalid-feedback">
                                                        Please enter a number between 1 and 20.
                                                    </div>
                                                </div>
                                                <small class="form-helper-text">Default number of positions when creating a new election.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="results_public" class="form-label fw-medium">Results Visibility</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-eye"></i></span>
                                                    <select class="form-select" id="results_public" name="results_public">
                                                        <option value="after_end" <?php echo $system_settings['results_public'] === 'after_end' ? 'selected' : ''; ?>>After Election Ends</option>
                                                        <option value="while_active" <?php echo $system_settings['results_public'] === 'while_active' ? 'selected' : ''; ?>>During Active Election</option>
                                                        <option value="admin_only" <?php echo $system_settings['results_public'] === 'admin_only' ? 'selected' : ''; ?>>Admin Only</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">Determines when election results are visible to voters.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="voter_registration" class="form-label fw-medium">Voter Registration</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-person-plus"></i></span>
                                                    <select class="form-select" id="voter_registration" name="voter_registration">
                                                        <option value="enabled" <?php echo $system_settings['voter_registration'] === 'enabled' ? 'selected' : ''; ?>>Enabled (Self-registration)</option>
                                                        <option value="admin_only" <?php echo $system_settings['voter_registration'] === 'admin_only' ? 'selected' : ''; ?>>Admin Only (Restricted)</option>
                                                        <option value="disabled" <?php echo $system_settings['voter_registration'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">Controls who can register new voter accounts.</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Display Settings Tab -->
                                    <div class="tab-pane fade" id="display" role="tabpanel" aria-labelledby="display-tab">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="pagination_limit" class="form-label fw-medium">Items Per Page</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-list-columns"></i></span>
                                                    <input type="number" class="form-control" id="pagination_limit" name="pagination_limit" 
                                                           value="<?php echo htmlspecialchars($system_settings['pagination_limit']); ?>" 
                                                           min="5" max="100" required>
                                                    <div class="invalid-feedback">
                                                        Please enter a number between 5 and 100.
                                                    </div>
                                                </div>
                                                <small class="form-helper-text">Number of items to display in paginated lists.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="date_format" class="form-label fw-medium">Date Format</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                                    <select class="form-select" id="date_format" name="date_format">
                                                        <option value="d-m-Y" <?php echo $system_settings['date_format'] === 'd-m-Y' ? 'selected' : ''; ?>>DD-MM-YYYY (31-12-2023)</option>
                                                        <option value="m-d-Y" <?php echo $system_settings['date_format'] === 'm-d-Y' ? 'selected' : ''; ?>>MM-DD-YYYY (12-31-2023)</option>
                                                        <option value="Y-m-d" <?php echo $system_settings['date_format'] === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD (2023-12-31)</option>
                                                        <option value="d/m/Y" <?php echo $system_settings['date_format'] === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY (31/12/2023)</option>
                                                        <option value="m/d/Y" <?php echo $system_settings['date_format'] === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY (12/31/2023)</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">Format for displaying dates throughout the system.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="time_format" class="form-label fw-medium">Time Format</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                    <select class="form-select" id="time_format" name="time_format">
                                                        <option value="H:i" <?php echo $system_settings['time_format'] === 'H:i' ? 'selected' : ''; ?>>24-hour (14:30)</option>
                                                        <option value="h:i A" <?php echo $system_settings['time_format'] === 'h:i A' ? 'selected' : ''; ?>>12-hour (02:30 PM)</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">Format for displaying times throughout the system.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Theme Preview</label>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-theme-value="light">
                                                        <i class="bi bi-sun me-1"></i> Light
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-dark" data-bs-theme-value="dark">
                                                        <i class="bi bi-moon me-1"></i> Dark
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-theme-value="auto">
                                                        <i class="bi bi-circle-half me-1"></i> Auto
                                                    </button>
                                                </div>
                                                <small class="form-helper-text">Change the system's color scheme (preview only).</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Advanced Settings Tab -->
                                    <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">
                                        <div class="alert alert-warning d-flex align-items-center">
                                            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                            <div>
                                                <strong>Warning:</strong> These settings are for advanced users only. 
                                                Incorrect changes may affect system functionality.
                                            </div>
                                        </div>
                                        
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="session_timeout" class="form-label fw-medium">Session Timeout (minutes)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                                    <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                                           value="30" min="5" max="1440">
                                                </div>
                                                <small class="form-helper-text">Duration of user inactivity before automatic logout.</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="password_policy" class="form-label fw-medium">Password Policy</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                                    <select class="form-select" id="password_policy" name="password_policy">
                                                        <option value="low">Low (6 characters minimum)</option>
                                                        <option value="medium" selected>Medium (8 characters, 1 number)</option>
                                                        <option value="high">High (10 characters, mixed case, special chars)</option>
                                                    </select>
                                                </div>
                                                <small class="form-helper-text">Password complexity requirements for users.</small>
                                            </div>
                                            
                                            <div class="col-md-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="debug_mode" name="debug_mode">
                                                    <label class="form-check-label fw-medium" for="debug_mode">Debug Mode</label>
                                                </div>
                                                <small class="form-helper-text">Enable for detailed error reporting (not recommended for production).</small>
                                            </div>
                                            
                                            <div class="col-md-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="force_https" name="force_https" checked>
                                                    <label class="form-check-label fw-medium" for="force_https">Force HTTPS</label>
                                                </div>
                                                <small class="form-helper-text">Redirect all traffic to secure HTTPS connection.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                                    <button type="reset" class="btn btn-outline-secondary px-4 hover-effect">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        Reset Changes
                                    </button>
                                    <button type="submit" name="update_settings" class="btn btn-primary px-4 hover-effect">
                                        <i class="bi bi-save-fill me-1"></i>
                                        Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- System Status Card -->
                    <div class="card hover-effect">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                                System Information & Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm system-info-table mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="ps-0">
                                                    <i class="bi bi-filetype-php me-1 text-primary"></i>
                                                    PHP Version
                                                </th>
                                                <td class="text-end pe-0">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary status-badge">
                                                        <?php echo phpversion(); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="ps-0">
                                                    <i class="bi bi-database me-1 text-info"></i>
                                                    Database
                                                </th>
                                                <td class="text-end pe-0">
                                                    <span class="badge bg-info bg-opacity-10 text-info status-badge">
                                                        MySQL <?php echo $conn->server_info ?? 'Unknown'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="ps-0">
                                                    <i class="bi bi-server me-1 text-secondary"></i>
                                                    Server
                                                </th>
                                                <td class="text-end pe-0">
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary status-badge">
                                                        <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm system-info-table mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="ps-0">
                                                    <i class="bi bi-clock me-1 text-success"></i>
                                                    System Time
                                                </th>
                                                <td class="text-end pe-0">
                                                    <span class="badge bg-success bg-opacity-10 text-success status-badge">
                                                        <?php echo date('Y-m-d H:i:s'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="ps-0">
                                                    <i class="bi bi-tag me-1 text-warning"></i>
                                                    System Version
                                                </th>
                                                <td class="text-end pe-0">
                                                    <span class="badge bg-warning bg-opacity-10 text-warning status-badge">
                                                        v2.1.0
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="ps-0">
                                                    <i class="bi bi-calendar-check me-1 text-dark"></i>
                                                    Last Settings Update
                                                </th>
                                                <td class="text-end pe-0">
                                                    <span class="badge bg-dark bg-opacity-10 text-dark status-badge">
                                                        <?php echo date('Y-m-d H:i:s'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enable tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Form validation with better UX
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Add shake animation to invalid fields
                    form.querySelectorAll(':invalid').forEach(el => {
                        el.classList.add('is-invalid', 'shake-animation');
                        el.addEventListener('animationend', () => {
                            el.classList.remove('shake-animation');
                        }, { once: true });
                        
                        // Scroll to first invalid field
                        if (el === form.querySelector(':invalid')) {
                            el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    });
                }
                
                form.classList.add('was-validated');
            }, false);
        });
        
        // Export settings
        document.getElementById('downloadConfig').addEventListener('click', function() {
            // Collect settings from the form
            const settings = {
                site_name: document.getElementById('site_name').value,
                admin_email: document.getElementById('admin_email').value,
                max_candidates: document.getElementById('max_candidates').value,
                default_positions: document.getElementById('default_positions').value,
                results_public: document.getElementById('results_public').value,
                voter_registration: document.getElementById('voter_registration').value,
                maintenance_mode: document.getElementById('maintenance_mode').value,
                email_notifications: document.getElementById('email_notifications').value,
                pagination_limit: document.getElementById('pagination_limit').value,
                date_format: document.getElementById('date_format').value,
                time_format: document.getElementById('time_format').value,
                export_date: new Date().toISOString(),
                system_version: 'v2.1.0'
            };
            
            // Convert settings to JSON
            const settingsJson = JSON.stringify(settings, null, 2);
            
            // Create and download file
            const blob = new Blob([settingsJson], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            
            a.href = url;
            a.download = 'election_system_settings.json';
            document.body.appendChild(a);
            a.click();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 0);
            
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show d-flex align-items-center mt-3';
            alert.innerHTML = `
                <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                <div>Settings exported successfully!</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.container').prepend(alert);
        });
        
        // Theme switcher functionality
        document.querySelectorAll('[data-bs-theme-value]').forEach(button => {
            button.addEventListener('click', () => {
                const theme = button.getAttribute('data-bs-theme-value');
                document.documentElement.setAttribute('data-bs-theme', theme);
                
                // Show theme change notification
                const themeName = button.textContent.trim();
                const toast = document.createElement('div');
                toast.className = 'position-fixed bottom-0 end-0 p-3';
                toast.style.zIndex = '11';
                toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-primary text-white">
                            <i class="bi bi-palette me-2"></i>
                            <strong class="me-auto">Theme Changed</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Previewing ${themeName} theme. Changes won't be saved until you click "Save Settings".
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);
                
                // Auto-remove toast after 5 seconds
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            });
        });
        
        // Help button functionality
        document.getElementById('helpButton').addEventListener('click', function() {
            // Create modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'helpModal';
            modal.tabIndex = '-1';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-question-circle-fill me-2"></i>
                                System Settings Help
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="accordion" id="helpAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#helpGeneral">
                                            <i class="bi bi-gear me-2"></i> General Settings
                                        </button>
                                    </h2>
                                    <div id="helpGeneral" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Site Name:</strong> This will appear in page titles and throughout the system.</p>
                                            <p><strong>Administrator Email:</strong> Used for system notifications and password resets.</p>
                                            <p><strong>Maintenance Mode:</strong> When enabled, only administrators can access the system.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpElection">
                                            <i class="bi bi-calendar-event me-2"></i> Election Settings
                                        </button>
                                    </h2>
                                    <div id="helpElection" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Maximum Candidates:</strong> Limits how many candidates can run for each position.</p>
                                            <p><strong>Results Visibility:</strong> Controls when election results are shown to voters.</p>
                                            <p><strong>Voter Registration:</strong> Determines who can create voter accounts.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpDisplay">
                                            <i class="bi bi-display me-2"></i> Display Settings
                                        </button>
                                    </h2>
                                    <div id="helpDisplay" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Items Per Page:</strong> Affects how many items appear in lists before pagination.</p>
                                            <p><strong>Date/Time Format:</strong> Changes how dates and times are displayed system-wide.</p>
                                            <p><strong>Theme:</strong> Preview different color schemes (save to make permanent).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                <i class="bi bi-check-circle me-1"></i> Got it!
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Show modal
            const helpModal = new bootstrap.Modal(document.getElementById('helpModal'));
            helpModal.show();
            
            // Remove modal from DOM after it's hidden
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
        
        // Add animated hover effects to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transition = 'all 0.3s ease';
            });
        });
        
        // Add focus styles for better accessibility
        const focusableElements = document.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        focusableElements.forEach(el => {
            el.addEventListener('focus', function() {
                this.style.outline = '2px solid var(--primary-color)';
                this.style.outlineOffset = '2px';
            });
            
            el.addEventListener('blur', function() {
                this.style.outline = 'none';
            });
        });
        
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>