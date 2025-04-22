<?php
// Start session securely only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Database connection
require 'configs/dbconnection.php';

$userData = [];
$defaultProfilePicture = 'assets/img/aristo.png';
$profilePicturePath = $defaultProfilePicture;

if (isset($_SESSION['login_id'])) {
    try {
        // Get fresh data including profile picture
        $stmt = $conn->prepare("SELECT name, department, email, contactNumber, profilePicture FROM students WHERE studentID = ?");
        $stmt->bind_param('i', $_SESSION['login_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            
            // Check if profile picture exists
            if (!empty($userData['profilePicture'])) {
                $userImagePath = 'assets/img/profile/students/' . $userData['profilePicture'];
                if (file_exists($userImagePath)) {
                    // Add cache buster to force refresh when updated
                    $profilePicturePath = $userImagePath . '?t=' . (isset($_GET['cache']) ? $_GET['cache'] : time());
                }
            }
            
            // Store in session for quick access
            $_SESSION['user_data'] = $userData;
        }
    } catch (Exception $e) {
        error_log("Header error: " . $e->getMessage());
    }
}

// Fallback to session data if available
if (empty($userData)) {
    $userData = $_SESSION['user_data'] ?? [];
}
?>

<!-- ======= Enhanced Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center shadow-sm">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Logo and Mobile Toggle -->
            <div class="d-flex align-items-center">
                <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
                    <img src="assets/img/logo.png" alt="SmartVote Logo" class="d-md-none" width="40" height="40">
                    <span class="d-none d-lg-block ps-2 fw-bold fs-4 position-relative font-monospace">SmartVote</span>
                </a>
                <button class="toggle-sidebar-btn btn btn-link ms-2 d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <!-- Navigation Icons -->
            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center list-unstyled mb-0">
                    <!-- Mobile Search Toggle -->
                    <!-- Removing search icon from mobile view -->
                    
                    <!-- Theme Toggle Button -->
                    <li class="nav-item mx-1">
                        <button id="themeToggleBtn" class="nav-link d-flex align-items-center position-relative px-3 py-2 rounded-3 btn btn-link"
                           style="transition: all 0.3s ease;"
                           onmouseover="this.style.backgroundColor='rgba(67, 97, 238, 0.1)';"
                           onmouseout="this.style.backgroundColor='transparent';">
                            <i class="bi bi-sun-fill theme-icon-light fs-5" style="color: var(--primary);"></i>
                            <i class="bi bi-moon-fill theme-icon-dark fs-5 d-none" style="color: var(--primary);"></i>
                        </button>
                    </li>
                    
                                <!-- Live Results Tab - Improved UI -->
                <li class="nav-item mx-1">
                    <a class="nav-link d-flex align-items-center position-relative px-3 py-2 rounded-3" 
                    href="live_results.php"
                    style="transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(67, 97, 238, 0.1)';"
                    onmouseout="this.style.backgroundColor='transparent';">
                        
                        <!-- Animated Icon with Pulse Effect -->
                        <span class="position-relative">
                            <i class="bi bi-bar-chart-line-fill fs-5 me-2" style="color: var(--primary);"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                <span class="visually-hidden">Live updates</span>
                            </span>
                        </span>
                        
                        <!-- Text with subtle animation - using CSS variables instead of fixed color -->
                        <span class="d-none d-md-inline fw-medium live-results-text">
                            Live Results
                        </span>
                        
                    </a>
                </li>
                    
                    <!-- Notification Bell with Real-Time Updates -->
                    <li class="nav-item dropdown mx-2" id="notification-dropdown">
                        <a class="nav-link notification-bell position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                0
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        </a>
                        
                        <!-- Notifications Dropdown -->
                        <div class="dropdown-menu dropdown-menu-end dropdown-notifications shadow p-0" style="width: 461px; max-height: 500px; overflow-y: auto;">
                            <div class="dropdown-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-bell me-2"></i>
                                    <h6 class="mb-0 fw-semibold">Notifications</h6>
                                    <span id="notification-counter" class="badge bg-danger ms-2" style="display: none;">1 new</span>
                                </div>
                               
                            </div>
                            
                            <!-- Loading indicator -->
                            <div id="notification-loading" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading notifications...</p>
                            </div>
                            
                            <!-- Empty state -->
                            <div id="notification-empty" class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-bell-slash fs-1 text-muted"></i>
                                </div>
                                <h6>No notifications yet</h6>
                                <p class="text-muted mb-0 empty-message">When you get notifications, they'll appear here</p>
                            </div>
                            
                            <!-- Notifications list -->
                            <div id="notification-list" data-loaded="false">
                                <!-- Notifications will be loaded here by JavaScript -->
                            </div>
                            
                            <div class="dropdown-footer text-center p-2 border-top">
                                <a href="notifications.php" class="text-decoration-none">View all notifications</a>
                            </div>
                        </div>
                    </li>

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="position-relative">
                                <img src="<?php echo $profilePicturePath; ?>" 
                                     alt="Profile" 
                                     class="rounded-circle object-fit-cover" 
                                     width="40" 
                                     height="40"
                                     onerror="this.src='<?php echo $defaultProfilePicture; ?>'">
                                <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border-2 border-white" style="width: 10px; height: 10px;"></span>
                            </div>
                            <span class="d-none d-md-block dropdown-toggle ps-2 fw-medium">
                                <?php echo htmlspecialchars($userData['name'] ?? 'Student'); ?>
                            </span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-sm" style="min-width: 280px;">
                            <li class="dropdown-header px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $profilePicturePath; ?>" 
                                         alt="Profile" 
                                         class="rounded-circle me-2" 
                                         width="48" 
                                         height="48"
                                         onerror="this.src='<?php echo $defaultProfilePicture; ?>'">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($userData['name'] ?? 'Student'); ?></h6>
                                        <i class="bi bi-buildings department-icon icon"></i>&nbsp;<small class="text-muted"><?php echo htmlspecialchars($userData['department'] ?? 'Member'); ?></small>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider mx-3 my-2"></li>
                            
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="settings.php">
                                    <i class="bi bi-person me-3 fs-5"></i>
                                    <div>
                                        <span>My Profile</span>
                                        <small class="d-block text-muted">View your personal information</small>
                                    </div>
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="settings.php">
                                    <i class="bi bi-gear me-3 fs-5"></i>
                                    <div>
                                        <span>Account Settings</span>
                                        <small class="d-block text-muted">Update password and preferences</small>
                                    </div>
                                </a>
                            </li>
                            
                            <?php if (isset($userData['email'])): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="mailto:<?php echo htmlspecialchars($userData['email']); ?>">
                                    <i class="bi bi-envelope me-3 fs-5"></i>
                                    <div>
                                        <span>Email Support</span>
                                        <small class="d-block text-muted"><?php echo htmlspecialchars($userData['email']); ?></small>
                                    </div>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider mx-3 my-2"></li>
                            
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2 text-danger" 
                                   href="#"
                                   data-bs-toggle="modal" 
                                   data-bs-target="#logoutModal">
                                    <i class="bi bi-box-arrow-right me-3 fs-5"></i>
                                    <div>
                                        <span>Sign Out</span>
                                        <small class="d-block text-muted">End your current session</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!-- Hidden input field for current user ID -->
<input type="hidden" id="current-user-id" value="<?php echo isset($_SESSION['login_id']) ? $_SESSION['login_id'] : ''; ?>">

<style>
.nav-profile {
    display: flex;
    align-items: center;
    color: var(--text-color, #212529);
    text-decoration: none;
    transition: color 0.3s ease;
}

[data-bs-theme="dark"] .nav-profile,
[data-bs-theme="dark"] .nav-profile .dropdown-toggle {
    color: var(--text-color, #f8f9fa);
}

.dropdown-toggle {
    color: var(--text-color, #212529);
    transition: color 0.3s ease;
}

/* Notification styles */
.dropdown-notifications {
    width: 320px;
    max-height: 500px;
    overflow-y: auto;
}

.notification-item {
    border-bottom: 1px solid var(--border);
    transition: background-color 0.3s ease;
}

.notification-item:hover {
    background-color: var(--surface-hover);
}

.notification-item.unread {
    background-color: rgba(67, 97, 238, 0.05);
}

.notification-item.unread:hover {
    background-color: rgba(67, 97, 238, 0.1);
}
</style>

<!-- Theme toggle and notifications script -->
<script>
// Auto-update header after profile changes
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have a cache parameter (set after profile update)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('cache')) {
        // Force refresh profile images
        document.querySelectorAll('img[src*="profile/students"]').forEach(img => {
            const baseSrc = img.src.split('?')[0];
            img.src = baseSrc + '?t=' + Date.now();
        });
        
        // Update profile name in header
        const profileName = '<?php echo isset($_SESSION['user_data']['name']) ? 
                            addslashes($_SESSION['user_data']['name']) : 
                            'Student'; ?>';
        document.querySelectorAll('.nav-profile span').forEach(el => {
            el.textContent = profileName;
        });
    }

    // Theme Toggle Functionality
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const lightIcon = document.querySelector('.theme-icon-light');
    const darkIcon = document.querySelector('.theme-icon-dark');
    
    // Get stored theme or default to light
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Apply theme on page load
    document.documentElement.setAttribute('data-bs-theme', currentTheme);
    
    // Update header background color based on theme
    updateHeaderStyles(currentTheme);
    
    // Update the toggle button icon based on current theme
    if (currentTheme === 'dark') {
        lightIcon.classList.add('d-none');
        darkIcon.classList.remove('d-none');
    } else {
        darkIcon.classList.add('d-none');
        lightIcon.classList.remove('d-none');
    }
    
    // Toggle theme when button is clicked
    themeToggleBtn.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Update theme
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update header styles based on new theme
        updateHeaderStyles(newTheme);
        
        // Toggle icon visibility
        lightIcon.classList.toggle('d-none');
        darkIcon.classList.toggle('d-none');
        
        // Custom event for other scripts to update their UI
        document.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: newTheme }
        }));
    });
    
    // Function to update header styles based on theme
    function updateHeaderStyles(theme) {
        const header = document.getElementById('header');
        if (!header) return;
        
        if (theme === 'dark') {
            header.classList.remove('bg-white');
            header.classList.add('bg-dark');
            
            // Ensure all header elements use the correct color
            header.querySelectorAll('.nav-link, .logo span, .nav-profile span, .btn-link').forEach(el => {
                el.style.color = 'var(--text)';
            });
            
            // Fix logo color
            const logoSpan = header.querySelector('.logo span');
            if (logoSpan) logoSpan.style.color = 'var(--text)';
            
            // Fix live results text
            const liveResultsText = header.querySelector('.nav-link span[style*="color"]');
            if (liveResultsText) liveResultsText.style.color = 'var(--text)';
        } else {
            header.classList.remove('bg-dark');
            header.classList.add('bg-white');
            
            // Reset colors
            header.querySelectorAll('.nav-link, .btn-link').forEach(el => {
                el.style.removeProperty('color');
            });
            
            // Reset logo color
            const logoSpan = header.querySelector('.logo span');
            if (logoSpan) logoSpan.style.removeProperty('color');
            
            // Reset live results text
            const liveResultsText = header.querySelector('.nav-link span[style*="color"]');
            if (liveResultsText) liveResultsText.style.color = '#2b3445';
        }
    }
});
</script>

<!-- Load notifications script -->
<script src="assets/js/notifications.js"></script>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="logoutModalLabel">Sign Out</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <i class="bi bi-box-arrow-right text-danger fs-1 mb-3"></i>
          <h5>Are you sure you want to sign out?</h5>
          <p class="text-muted">You will need to sign in again to access your account.</p>
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        <a href="controllers/app.php?action=logout" class="btn btn-danger px-4">Sign Out</a>
      </div>
    </div>
  </div>
</div>