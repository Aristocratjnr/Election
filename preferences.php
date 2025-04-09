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

// Default system settings
$system_settings = [
    'site_name' => 'Election System',
    'admin_email' => '',
    'max_candidates' => 10,
    'default_positions' => 5,
    'results_public' => 'after_end',
    'voter_registration' => 'enabled',
    'maintenance_mode' => 'disabled',
    'email_notifications' => 'enabled',
    'pagination_limit' => 20,
    'date_format' => 'd-m-Y',
    'time_format' => 'H:i'
];

// Fetch current system settings
try {
    $stmt = $conn->prepare("SELECT * FROM system_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $settings_data = $result->fetch_assoc();
        foreach ($settings_data as $key => $value) {
            if (array_key_exists($key, $system_settings)) {
                $system_settings[$key] = $value;
            }
        }
    }
} catch (Exception $e) {
    $error_message = "Error loading settings: " . $e->getMessage();
}

// Handle settings update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings'])) {
    $site_name = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
    $max_candidates = filter_input(INPUT_POST, 'max_candidates', FILTER_VALIDATE_INT);
    $default_positions = filter_input(INPUT_POST, 'default_positions', FILTER_VALIDATE_INT);
    $results_public = filter_input(INPUT_POST, 'results_public', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $voter_registration = filter_input(INPUT_POST, 'voter_registration', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $maintenance_mode = filter_input(INPUT_POST, 'maintenance_mode', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email_notifications = filter_input(INPUT_POST, 'email_notifications', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pagination_limit = filter_input(INPUT_POST, 'pagination_limit', FILTER_VALIDATE_INT);
    $date_format = filter_input(INPUT_POST, 'date_format', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $time_format = filter_input(INPUT_POST, 'time_format', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Validate inputs
    $validation_errors = [];
    if (empty($site_name)) {
        $validation_errors[] = "Site name is required.";
    }
    
    if (!empty($admin_email) && !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = "Please enter a valid email address.";
    }
    
    if ($max_candidates < 1 || $max_candidates > 50) {
        $validation_errors[] = "Maximum candidates must be between 1 and 50.";
    }
    
    if ($default_positions < 1 || $default_positions > 20) {
        $validation_errors[] = "Default positions must be between 1 and 20.";
    }
    
    if ($pagination_limit < 5 || $pagination_limit > 100) {
        $validation_errors[] = "Pagination limit must be between 5 and 100.";
    }
    
    if (!empty($validation_errors)) {
        $error_message = implode("<br>", $validation_errors);
    } else {
        try {
            // Check if settings already exist
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM system_settings WHERE id = 1");
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Update existing settings
                $update_stmt = $conn->prepare("UPDATE system_settings SET 
                    site_name = ?, admin_email = ?, max_candidates = ?, default_positions = ?,
                    results_public = ?, voter_registration = ?, maintenance_mode = ?,
                    email_notifications = ?, pagination_limit = ?, date_format = ?, time_format = ?
                    WHERE id = 1");
                
                $update_stmt->bind_param("ssiisssiiss", 
                    $site_name, $admin_email, $max_candidates, $default_positions,
                    $results_public, $voter_registration, $maintenance_mode,
                    $email_notifications, $pagination_limit, $date_format, $time_format
                );
                
                if ($update_stmt->execute()) {
                    $success_message = "System settings updated successfully.";
                    
                    // Update current settings
                    $system_settings = [
                        'site_name' => $site_name,
                        'admin_email' => $admin_email,
                        'max_candidates' => $max_candidates,
                        'default_positions' => $default_positions,
                        'results_public' => $results_public,
                        'voter_registration' => $voter_registration,
                        'maintenance_mode' => $maintenance_mode,
                        'email_notifications' => $email_notifications,
                        'pagination_limit' => $pagination_limit,
                        'date_format' => $date_format,
                        'time_format' => $time_format
                    ];
                } else {
                    $error_message = "Failed to update settings: " . $conn->error;
                }
            } else {
                // Insert new settings
                $insert_stmt = $conn->prepare("INSERT INTO system_settings (
                    id, site_name, admin_email, max_candidates, default_positions,
                    results_public, voter_registration, maintenance_mode,
                    email_notifications, pagination_limit, date_format, time_format
                ) VALUES (
                    1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )");
                
                $insert_stmt->bind_param("ssiisssiiss", 
                    $site_name, $admin_email, $max_candidates, $default_positions,
                    $results_public, $voter_registration, $maintenance_mode,
                    $email_notifications, $pagination_limit, $date_format, $time_format
                );
                
                if ($insert_stmt->execute()) {
                    $success_message = "System settings created successfully.";
                    
                    // Update current settings
                    $system_settings = [
                        'site_name' => $site_name,
                        'admin_email' => $admin_email,
                        'max_candidates' => $max_candidates,
                        'default_positions' => $default_positions,
                        'results_public' => $results_public,
                        'voter_registration' => $voter_registration,
                        'maintenance_mode' => $maintenance_mode,
                        'email_notifications' => $email_notifications,
                        'pagination_limit' => $pagination_limit,
                        'date_format' => $date_format,
                        'time_format' => $time_format
                    ];
                } else {
                    $error_message = "Failed to create settings: " . $conn->error;
                }
            }
        } catch (Exception $e) {
            $error_message = "Error saving settings: " . $e->getMessage();
        }
    }
}

// Page title
$page_title = "System Settings";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">
                                <i class="bi bi-sliders me-2"></i>
                                System Settings
                            </h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="downloadConfig">
                                    <i class="bi bi-download me-1"></i>
                                    Export Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Settings Form -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
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
                                    <i class="bi bi-gear me-1"></i>
                                    General
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
                                    <i class="bi bi-calendar-event me-1"></i>
                                    Election
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
                                    <i class="bi bi-display me-1"></i>
                                    Display
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                            <div class="tab-content">
                                <!-- General Settings Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="site_name" class="form-label">Site Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                                       value="<?php echo htmlspecialchars($system_settings['site_name']); ?>" required>
                                                <div class="invalid-feedback">
                                                    Please enter the site name.
                                                </div>
                                            </div>
                                            <div class="form-text">This name will appear throughout the system.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="admin_email" class="form-label">Administrator Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                                       value="<?php echo htmlspecialchars($system_settings['admin_email']); ?>">
                                                <div class="invalid-feedback">
                                                    Please enter a valid email address.
                                                </div>
                                            </div>
                                            <div class="form-text">Used for system notifications and alerts.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                                            <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                                <option value="disabled" <?php echo $system_settings['maintenance_mode'] === 'disabled' ? 'selected' : ''; ?>>Disabled (Site Active)</option>
                                                <option value="enabled" <?php echo $system_settings['maintenance_mode'] === 'enabled' ? 'selected' : ''; ?>>Enabled (Site Offline)</option>
                                            </select>
                                            <div class="form-text">When enabled, only admins can access the site.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email_notifications" class="form-label">Email Notifications</label>
                                            <select class="form-select" id="email_notifications" name="email_notifications">
                                                <option value="enabled" <?php echo $system_settings['email_notifications'] === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                                <option value="disabled" <?php echo $system_settings['email_notifications'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                            </select>
                                            <div class="form-text">Send email notifications for important events.</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Election Settings Tab -->
                                <div class="tab-pane fade" id="election" role="tabpanel" aria-labelledby="election-tab">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="max_candidates" class="form-label">Maximum Candidates Per Position</label>
                                            <input type="number" class="form-control" id="max_candidates" name="max_candidates" 
                                                   value="<?php echo htmlspecialchars($system_settings['max_candidates']); ?>" 
                                                   min="1" max="50" required>
                                            <div class="invalid-feedback">
                                                Please enter a number between 1 and 50.
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="default_positions" class="form-label">Default Positions Per Election</label>
                                            <input type="number" class="form-control" id="default_positions" name="default_positions" 
                                                   value="<?php echo htmlspecialchars($system_settings['default_positions']); ?>" 
                                                   min="1" max="20" required>
                                            <div class="invalid-feedback">
                                                Please enter a number between 1 and 20.
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="results_public" class="form-label">Results Visibility</label>
                                            <select class="form-select" id="results_public" name="results_public">
                                                <option value="after_end" <?php echo $system_settings['results_public'] === 'after_end' ? 'selected' : ''; ?>>After Election Ends</option>
                                                <option value="while_active" <?php echo $system_settings['results_public'] === 'while_active' ? 'selected' : ''; ?>>During Active Election</option>
                                                <option value="admin_only" <?php echo $system_settings['results_public'] === 'admin_only' ? 'selected' : ''; ?>>Admin Only</option>
                                            </select>
                                            <div class="form-text">Determines when election results are visible to voters.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="voter_registration" class="form-label">Voter Registration</label>
                                            <select class="form-select" id="voter_registration" name="voter_registration">
                                                <option value="enabled" <?php echo $system_settings['voter_registration'] === 'enabled' ? 'selected' : ''; ?>>Enabled (Self-registration)</option>
                                                <option value="admin_only" <?php echo $system_settings['voter_registration'] === 'admin_only' ? 'selected' : ''; ?>>Admin Only (Restricted)</option>
                                                <option value="disabled" <?php echo $system_settings['voter_registration'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                            </select>
                                            <div class="form-text">Controls who can register new voter accounts.</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Display Settings Tab -->
                                <div class="tab-pane fade" id="display" role="tabpanel" aria-labelledby="display-tab">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="pagination_limit" class="form-label">Items Per Page</label>
                                            <input type="number" class="form-control" id="pagination_limit" name="pagination_limit" 
                                                   value="<?php echo htmlspecialchars($system_settings['pagination_limit']); ?>" 
                                                   min="5" max="100" required>
                                            <div class="invalid-feedback">
                                                Please enter a number between 5 and 100.
                                            </div>
                                            <div class="form-text">Number of items to display in paginated lists.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="date_format" class="form-label">Date Format</label>
                                            <select class="form-select" id="date_format" name="date_format">
                                                <option value="d-m-Y" <?php echo $system_settings['date_format'] === 'd-m-Y' ? 'selected' : ''; ?>>DD-MM-YYYY (31-12-2023)</option>
                                                <option value="m-d-Y" <?php echo $system_settings['date_format'] === 'm-d-Y' ? 'selected' : ''; ?>>MM-DD-YYYY (12-31-2023)</option>
                                                <option value="Y-m-d" <?php echo $system_settings['date_format'] === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD (2023-12-31)</option>
                                                <option value="d/m/Y" <?php echo $system_settings['date_format'] === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY (31/12/2023)</option>
                                                <option value="m/d/Y" <?php echo $system_settings['date_format'] === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY (12/31/2023)</option>
                                            </select>
                                            <div class="form-text">Format for displaying dates throughout the system.</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="time_format" class="form-label">Time Format</label>
                                            <select class="form-select" id="time_format" name="time_format">
                                                <option value="H:i" <?php echo $system_settings['time_format'] === 'H:i' ? 'selected' : ''; ?>>24-hour (14:30)</option>
                                                <option value="h:i A" <?php echo $system_settings['time_format'] === 'h:i A' ? 'selected' : ''; ?>>12-hour (02:30 PM)</option>
                                            </select>
                                            <div class="form-text">Format for displaying times throughout the system.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Reset
                                </button>
                                <button type="submit" name="update_settings" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- System Status Card -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    System Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th scope="row" class="ps-0">PHP Version</th>
                                                    <td class="text-end pe-0"><?php echo phpversion(); ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="ps-0">Database</th>
                                                    <td class="text-end pe-0">MySQL <?php echo $conn->server_info ?? 'Unknown'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="ps-0">Server</th>
                                                    <td class="text-end pe-0"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th scope="row" class="ps-0">System Time</th>
                                                    <td class="text-end pe-0"><?php echo date('Y-m-d H:i:s'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="ps-0">System Version</th>
                                                    <td class="text-end pe-0">v2.1.0</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="ps-0">Last Settings Update</th>
                                                    <td class="text-end pe-0"><?php echo date('Y-m-d H:i:s'); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
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
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'election_system_settings.json';
        document.body.appendChild(a);
        a.click();
        
        // Clean up
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 0);
    });
});
</script>

<?php include 'includes/footer.php'; ?> 