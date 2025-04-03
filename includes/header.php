<?php
// Start session securely
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
if (empty($userData)){
    $userData = $_SESSION['user_data'] ?? [];
}

// Get unread notifications count
$unreadNotifications = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE studentID = ? AND isRead = 0");
    $stmt->bind_param('i', $_SESSION['login_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadNotifications = $result->fetch_assoc()['unread'] ?? 0;
} catch (Exception $e) {
    error_log("Notification count error: " . $e->getMessage());
}
?>

<!-- ======= Enhanced Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center shadow-sm bg-white">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between w-100">
            <!-- Logo and Mobile Toggle -->
            <div class="d-flex align-items-center">
                <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
                    <img src="assets/img/logo.png" alt="SmartVote Logo" class="d-md-none" width="40" height="40">
                    <span class="d-none d-lg-block ps-2 fw-bold fs-4 text-secondary position-relative">SmartVote</span>
                </a>
                <button class="toggle-sidebar-btn btn btn-link text-dark ms-2 d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <!-- Navigation Icons -->
            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center list-unstyled mb-0">
                    <!-- Mobile Search Toggle -->
                    <li class="nav-item d-lg-none me-2">
                        <button class="btn btn-link text-dark search-toggle">
                            <i class="bi bi-search fs-5"></i>
                        </button>
                    </li>
                    
                    <!-- Notification Bell -->
                    <li class="nav-item dropdown mx-2">
                        <a class="nav-link notification-bell position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($unreadNotifications > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unreadNotifications; ?>
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                            <?php endif; ?>
                        </a>
                        <!-- Notification dropdown menu... -->
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
                                        <h6 class="mb-0"><?php echo htmlspecialchars($userData['name'] ?? 'Student'); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($userData['department'] ?? 'Member'); ?></small>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider mx-3 my-2"></li>
                            
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="profile.php">
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
                                   href="controllers/app.php?action=logout"
                                   onclick="return confirm('Are you sure you want to sign out?');">
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
        
       
</header>

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
});
</script>