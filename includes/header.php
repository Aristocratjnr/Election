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
$defaultProfilePicture = 'assets/img/aristo.png';

if (isset($_SESSION['login_id'])) {
    try {
        // Get more complete user data
        $stmt = $conn->prepare("SELECT name, department, email, contactNumber FROM Students WHERE studentID = ?");
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

// Get unread notifications count (example)
$unreadNotifications = 3; // You would query this from your database
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
                    
                    <!-- Notification Bell with Real Count -->
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
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg p-0 border-0 shadow-sm" style="width: 360px;">
                            <li class="dropdown-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Notifications</h6>
                                <a href="#" class="small">Mark all as read</a>
                            </li>
                            <li><hr class="dropdown-divider my-0"></li>
                            <!-- Sample Notification Items -->
                            <li>
                                <a href="#" class="dropdown-item py-3 px-3 d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 text-primary">
                                            <i class="bi bi-calendar-check fs-5"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">New Election</span>
                                            <small class="text-muted">10 mins ago</small>
                                        </div>
                                        <p class="mb-0 small text-muted">SRC elections have been scheduled for March 25th</p>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-0"></li>
                            <li>
                                <a href="#" class="dropdown-item py-3 px-3 d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-2 text-success">
                                            <i class="bi bi-check-circle fs-5"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Vote Submitted</span>
                                            <small class="text-muted">1 hour ago</small>
                                        </div>
                                        <p class="mb-0 small text-muted">Your vote for SRC President has been recorded</p>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-0"></li>
                            <li class="text-center py-2">
                                <a href="notifications.php" class="small">View all notifications</a>
                            </li>
                        </ul>
                    </li>

                    <!-- User Profile Dropdown with Enhanced Details -->
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="position-relative">
                                <img src="<?php echo $profilePicturePath; ?>" 
                                     alt="Profile" 
                                     class="rounded-circle object-fit-cover" 
                                     width="40" 
                                     height="40"
                                     onerror="this.src='<?php echo $defaultProfilePicture; ?>'">
                                <span class="position-absolute bottom-0 end-0 bg-success rounded-circle  border-2 border-white" style="width: 10px; height: 10px;"></span>
                            </div>
                            <span class="d-none d-md-block dropdown-toggle ps-2 fw-medium">
                                <?php echo isset($userData['name']) ? htmlspecialchars($userData['name']) : 'Student'; ?>
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
                                        <h6 class="mb-0"><?php echo isset($userData['name']) ? htmlspecialchars($userData['name']) : 'Student'; ?></h6>
                                        <small class="text-muted"><?php echo isset($userData['department']) ? htmlspecialchars($userData['department']) : 'Member'; ?></small>
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
        
        <!-- Mobile Search Bar (Hidden by default) -->
        <div class="mobile-search-bar d-lg-none bg-light p-3 border-top">
            <div class="input-group">
                <input type="text" class="form-control border-end-0" placeholder="Search..." aria-label="Search">
                <button class="btn btn-outline-secondary border-start-0" type="button">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<style>
/* Enhanced Header Styles */
.header {
    height: auto;
    min-height: 60px;
    z-index: 1030;
    box-shadow: 0 1px 10px rgba(0,0,0,0.1);
    background: #fff;
}

.logo img {
    max-height: 40px;
}

.toggle-sidebar-btn {
    transition: all 0.3s;
    padding: 0.5rem;
}
.toggle-sidebar-btn:hover {
    transform: scale(1.1);
}

.search-bar {
    max-width: 500px;
}
.search-bar .form-control {
    border-radius: 20px 0 0 20px;
    padding-left: 1.25rem;
}
.search-bar .btn {
    border-radius: 0 20px 20px 0;
    padding-right: 1.25rem;
}

.notification-bell {
    position: relative;
    color: #495057;
    transition: all 0.2s;
    padding: 0.5rem;
    border-radius: 50%;
}
.notification-bell:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}
.notification-bell .badge {
    font-size: 0.65rem;
    padding: 0.35em 0.5em;
    min-width: 1.25rem;
}

.nav-profile {
    padding: 0.25rem;
    transition: all 0.2s;
    border-radius: 50px;
}
.nav-profile:hover {
    background-color: rgba(0,0,0,0.05);
}

.dropdown-menu {
    border: none;
    margin-top: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    overflow: hidden;
}
.dropdown-item {
    border-radius: 0.25rem;
    margin: 0.1rem 0.5rem;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}

.mobile-search-bar {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1020;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .header {
        position: sticky;
        top: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile search toggle
    const searchToggle = document.querySelector('.search-toggle');
    const mobileSearchBar = document.querySelector('.mobile-search-bar');
    
    if (searchToggle && mobileSearchBar) {
        searchToggle.addEventListener('click', function() {
            mobileSearchBar.style.display = mobileSearchBar.style.display === 'block' ? 'none' : 'block';
        });
    }
    
    // Dropdown active state
    const profileDropdown = document.querySelector('.nav-profile');
    if (profileDropdown) {
        profileDropdown.addEventListener('shown.bs.dropdown', function() {
            this.classList.add('active');
        });
        profileDropdown.addEventListener('hidden.bs.dropdown', function() {
            this.classList.remove('active');
        });
    }
    
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }
});
</script>