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
}

// Page title
$page_title = "UI Appearance";
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
                                <i class="bi bi-palette-fill me-2"></i>
                                UI Appearance
                            </h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="d-inline">
                                    <button type="submit" name="reset_defaults" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Reset to Defaults
                                    </button>
                                </form>
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
                
                <div class="row">
                    <!-- Appearance Form -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Theme Mode</label>
                                            <div class="d-flex gap-3 theme-options">
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="theme-light" name="theme" value="light" 
                                                           <?php echo $ui_settings['theme'] === 'light' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-light">
                                                        <div class="theme-preview light-theme">
                                                            <i class="bi bi-sun-fill"></i>
                                                        </div>
                                                        <span>Light</span>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="theme-dark" name="theme" value="dark" 
                                                           <?php echo $ui_settings['theme'] === 'dark' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-dark">
                                                        <div class="theme-preview dark-theme">
                                                            <i class="bi bi-moon-stars-fill"></i>
                                                        </div>
                                                        <span>Dark</span>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="theme-auto" name="theme" value="auto" 
                                                           <?php echo $ui_settings['theme'] === 'auto' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="theme-auto">
                                                        <div class="theme-preview auto-theme">
                                                            <i class="bi bi-circle-half"></i>
                                                        </div>
                                                        <span>Auto</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Sidebar Color</label>
                                            <div class="color-options d-flex gap-2">
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="sidebar-default" name="sidebar_color" value="default" 
                                                           <?php echo $ui_settings['sidebar_color'] === 'default' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="sidebar-default">
                                                        <div class="color-circle bg-white border"></div>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="sidebar-blue" name="sidebar_color" value="blue" 
                                                           <?php echo $ui_settings['sidebar_color'] === 'blue' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="sidebar-blue">
                                                        <div class="color-circle bg-primary"></div>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="sidebar-dark" name="sidebar_color" value="dark" 
                                                           <?php echo $ui_settings['sidebar_color'] === 'dark' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="sidebar-dark">
                                                        <div class="color-circle bg-dark"></div>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="sidebar-green" name="sidebar_color" value="green" 
                                                           <?php echo $ui_settings['sidebar_color'] === 'green' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="sidebar-green">
                                                        <div class="color-circle bg-success"></div>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" id="sidebar-purple" name="sidebar_color" value="purple" 
                                                           <?php echo $ui_settings['sidebar_color'] === 'purple' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="sidebar-purple">
                                                        <div class="color-circle" style="background-color: #6f42c1;"></div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label for="font_size" class="form-label">Font Size</label>
                                            <select class="form-select" id="font_size" name="font_size">
                                                <option value="small" <?php echo $ui_settings['font_size'] === 'small' ? 'selected' : ''; ?>>Small</option>
                                                <option value="medium" <?php echo $ui_settings['font_size'] === 'medium' ? 'selected' : ''; ?>>Medium (Default)</option>
                                                <option value="large" <?php echo $ui_settings['font_size'] === 'large' ? 'selected' : ''; ?>>Large</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="layout_mode" class="form-label">Layout Mode</label>
                                            <select class="form-select" id="layout_mode" name="layout_mode">
                                                <option value="fluid" <?php echo $ui_settings['layout_mode'] === 'fluid' ? 'selected' : ''; ?>>Fluid (Full-width)</option>
                                                <option value="boxed" <?php echo $ui_settings['layout_mode'] === 'boxed' ? 'selected' : ''; ?>>Boxed</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="animations" name="animations" value="on"
                                                       <?php echo $ui_settings['animations'] === 'on' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="animations">
                                                    <i class="bi bi-magic me-2"></i>
                                                    Enable UI Animations
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="notifications" name="notifications" value="on"
                                                       <?php echo $ui_settings['notifications'] === 'on' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="notifications">
                                                    <i class="bi bi-bell me-2"></i>
                                                    Show Notification Alerts
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" name="update_appearance" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i>
                                            Save Appearance Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Panel -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-display me-2"></i>
                                    Live Preview
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="interface-preview p-3" id="interface-preview">
                                    <div class="sidebar-preview" id="sidebar-preview">
                                        <div class="sidebar-header"></div>
                                        <div class="sidebar-user"></div>
                                        <div class="sidebar-menu">
                                            <div class="menu-item active"></div>
                                            <div class="menu-item"></div>
                                            <div class="menu-item"></div>
                                        </div>
                                    </div>
                                    <div class="content-preview">
                                        <div class="content-header"></div>
                                        <div class="content-body">
                                            <div class="preview-card"></div>
                                            <div class="preview-card"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    About UI Settings
                                </h5>
                                <p class="text-muted">
                                    Your UI settings are saved to your account and will be applied whenever you log in.
                                    The interface will adapt automatically to your device and screen size.
                                </p>
                                <ul class="list-group list-group-flush mt-3">
                                    <li class="list-group-item d-flex align-items-center px-0">
                                        <i class="bi bi-moon-stars text-primary me-3"></i>
                                        <div>
                                            <strong>Dark Mode</strong>
                                            <p class="mb-0 text-muted small">Reduces eye strain in low-light environments</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center px-0">
                                        <i class="bi bi-speedometer2 text-primary me-3"></i>
                                        <div>
                                            <strong>Performance Settings</strong>
                                            <p class="mb-0 text-muted small">Disabling animations may improve performance</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.theme-options .form-check {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.theme-preview {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 5px;
    border: 2px solid transparent;
    transition: all 0.2s;
}

input:checked ~ label .theme-preview {
    border-color: #0d6efd;
}

.light-theme {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.dark-theme {
    background-color: #212529;
    color: #adb5bd;
}

.auto-theme {
    background: linear-gradient(to right, #f8f9fa 50%, #212529 50%);
    color: #0d6efd;
}

.color-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-block;
    transition: all 0.2s;
    cursor: pointer;
}

input:checked ~ label .color-circle {
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0d6efd;
}

.interface-preview {
    width: 100%;
    height: 200px;
    border-radius: 8px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    overflow: hidden;
    display: flex;
}

.sidebar-preview {
    width: 25%;
    height: 100%;
    background-color: #fdfdfd;
    border-right: 1px solid #dee2e6;
    padding: 10px;
}

.sidebar-header {
    height: 15px;
    background-color: #e9ecef;
    border-radius: 3px;
    margin-bottom: 10px;
}

.sidebar-user {
    height: 30px;
    background-color: #e9ecef;
    border-radius: 3px;
    margin-bottom: 15px;
}

.menu-item {
    height: 10px;
    background-color: #e9ecef;
    border-radius: 3px;
    margin-bottom: 8px;
}

.menu-item.active {
    background-color: #0d6efd;
}

.content-preview {
    width: 75%;
    height: 100%;
    padding: 10px;
}

.content-header {
    height: 20px;
    background-color: #e9ecef;
    border-radius: 3px;
    margin-bottom: 15px;
}

.content-body {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.preview-card {
    height: 60px;
    background-color: #e9ecef;
    border-radius: 3px;
    flex: 1 0 45%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview updates
    const themeInputs = document.querySelectorAll('input[name="theme"]');
    const sidebarInputs = document.querySelectorAll('input[name="sidebar_color"]');
    const preview = document.getElementById('interface-preview');
    const sidebarPreview = document.getElementById('sidebar-preview');
    
    // Update theme preview
    themeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const theme = this.value;
            
            if (theme === 'dark') {
                preview.style.backgroundColor = '#212529';
                sidebarPreview.style.backgroundColor = '#2c3034';
                preview.querySelectorAll('.preview-card, .content-header').forEach(el => {
                    el.style.backgroundColor = '#495057';
                });
                sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                    el.style.backgroundColor = '#495057';
                });
            } else {
                preview.style.backgroundColor = '#f8f9fa';
                sidebarPreview.style.backgroundColor = '#fdfdfd';
                preview.querySelectorAll('.preview-card, .content-header').forEach(el => {
                    el.style.backgroundColor = '#e9ecef';
                });
                sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                    el.style.backgroundColor = '#e9ecef';
                });
            }
        });
    });
    
    // Update sidebar preview
    sidebarInputs.forEach(input => {
        input.addEventListener('change', function() {
            const color = this.value;
            
            switch (color) {
                case 'blue':
                    sidebarPreview.style.backgroundColor = '#0d6efd';
                    sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                        el.style.backgroundColor = '#3d8bfd';
                    });
                    break;
                case 'dark':
                    sidebarPreview.style.backgroundColor = '#212529';
                    sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                        el.style.backgroundColor = '#495057';
                    });
                    break;
                case 'green':
                    sidebarPreview.style.backgroundColor = '#198754';
                    sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                        el.style.backgroundColor = '#479f76';
                    });
                    break;
                case 'purple':
                    sidebarPreview.style.backgroundColor = '#6f42c1';
                    sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                        el.style.backgroundColor = '#8c68d6';
                    });
                    break;
                default:
                    sidebarPreview.style.backgroundColor = '#fdfdfd';
                    sidebarPreview.querySelectorAll('.sidebar-header, .sidebar-user, .menu-item:not(.active)').forEach(el => {
                        el.style.backgroundColor = '#e9ecef';
                    });
            }
        });
    });
    
    // Initialize live preview based on current selection
    const currentTheme = document.querySelector('input[name="theme"]:checked').value;
    const currentSidebar = document.querySelector('input[name="sidebar_color"]:checked').value;
    
    // Trigger changes to update preview
    document.querySelector(`input[name="theme"][value="${currentTheme}"]`).dispatchEvent(new Event('change'));
    document.querySelector(`input[name="sidebar_color"][value="${currentSidebar}"]`).dispatchEvent(new Event('change'));
});
</script>

<?php include 'includes/footer.php'; ?> 