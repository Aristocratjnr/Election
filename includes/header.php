<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Database connection and user data fetch
require 'configs/dbconnection.php';

$userData = [];
$defaultProfilePicture = 'assets/img/default-user.png'; // Changed to a different default image

if (isset($_SESSION['login_id'])) {
    try {
        $stmt = $conn->prepare("SELECT name, profile_picture, department FROM students WHERE StudentID = ?");
        $stmt->bind_param('i', $_SESSION['login_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Header error: " . $e->getMessage());
    }
}

// Determine profile picture path
$profilePicturePath = $defaultProfilePicture;
if (!empty($userData['profile_picture'])) {
    $userImagePath = 'assets/img/profile/users/' . basename($userData['profile_picture']);
    if (file_exists($userImagePath) && is_file($userImagePath)) {
        $profilePicturePath = $userImagePath;
    }
}
?>

<!-- ======= Enhanced Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center shadow-sm bg-white">
    <div class="d-flex align-items-center justify-content-between w-100">
        <!-- Logo Section -->
        <div class="d-flex align-items-center">
            <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
                <img src="assets/img/logo.png" alt="SmartVote Logo" class="d-md-none" width="40" height="40">
                <span class="d-none d-lg-block ps-3 fw-bold text-primary">SmartVote</span>
            </a>
            <button class="toggle-sidebar-btn btn btn-link text-dark ms-2 d-lg-none">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>

        <!-- Navigation Section -->
        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center list-unstyled mb-0">
                <!-- Notification Bell -->
                <li class="nav-item dropdown me-3 d-none d-sm-block">
                    <a class="nav-link notification-bell position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg p-0 border-0 shadow">
                        <li class="dropdown-header bg-light py-2 px-3">
                            <h6 class="mb-0">Notifications</h6>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li>
                            <a href="#" class="dropdown-item d-flex align-items-center py-2 px-3">
                                <div class="me-3 bg-primary bg-opacity-10 rounded-circle p-2">
                                    <i class="bi bi-calendar-check text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted">10 mins ago</small>
                                    <p class="mb-0">New election created</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if ($profilePicturePath !== $defaultProfilePicture): ?>
                            <img src="<?php echo htmlspecialchars($profilePicturePath); ?>" 
                                 alt="Profile" 
                                 class="rounded-circle object-fit-cover" 
                                 width="36" 
                                 height="36"
                                 onerror="this.src='<?php echo $defaultProfilePicture; ?>'">
                        <?php else: ?>
                            <img src="<?php echo $defaultProfilePicture; ?>" 
                                 alt="Profile" 
                                 class="rounded-circle object-fit-cover" 
                                 width="36" 
                                 height="36">
                        <?php endif; ?>
                        <span class="d-none d-md-block dropdown-toggle ps-2 fw-medium">
                            <?php echo isset($userData['name']) ? htmlspecialchars($userData['name']) : 'User'; ?>
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-sm" style="min-width: 220px;">
                        <li class="dropdown-header">
                            <h6 class="mb-1"><?php echo isset($userData['name']) ? htmlspecialchars($userData['name']) : 'User'; ?></h6>
                            <small class="text-muted"><?php echo isset($userData['department']) ? htmlspecialchars($userData['department']) : 'Member'; ?></small>
                        </li>
                        <li><hr class="dropdown-divider my-2"></li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="profile.php">
                                <i class="bi bi-person me-2"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="settings.php">
                                <i class="bi bi-gear me-2"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-2"></li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2 text-danger" 
                               href="controllers/app.php?action=logout"
                               onclick="return confirm('Are you sure you want to sign out?');">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>

<style>
/* Enhanced Header Styles */
.header {
    height: 60px;
    z-index: 999;
    padding: 0 1.5rem;
}

.logo img {
    max-height: 40px;
}

.toggle-sidebar-btn {
    transition: transform 0.3s;
}
.toggle-sidebar-btn.collapsed {
    transform: rotate(180deg);
}

.notification-bell {
    position: relative;
    color: #495057;
    transition: color 0.2s;
}
.notification-bell:hover {
    color: #0d6efd;
}
.notification-bell .badge {
    font-size: 0.6rem;
    padding: 0.25em 0.4em;
}

.nav-profile img {
    border: 2px solid #e9ecef;
    transition: border-color 0.2s;
}
.nav-profile:hover img {
    border-color: #dee2e6;
}

.dropdown-menu {
    border: none;
    margin-top: 0.5rem;
}
.dropdown-item {
    border-radius: 0.25rem;
    margin: 0.1rem 0.5rem;
    width: auto;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to dropdown when shown
    const profileDropdown = document.querySelector('.nav-profile');
    if (profileDropdown) {
        profileDropdown.addEventListener('shown.bs.dropdown', function () {
            this.classList.add('active');
        });
        profileDropdown.addEventListener('hidden.bs.dropdown', function () {
            this.classList.remove('active');
        });
    }
});
</script>