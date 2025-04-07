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
if (empty($userData)) {
    $userData = $_SESSION['user_data'] ?? [];
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
                    <span class="d-none d-lg-block ps-2 fw-bold fs-4 text-secondary position-relative font-monospace">SmartVote</span>
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
                    
                    <!-- Notification Bell with Real-Time Updates -->
                    <li class="nav-item dropdown mx-2">
                        <a class="nav-link notification-bell position-relative" href="notifications.php" role="button" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                <span class="notification-count">0</span>
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        </a>
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
                                        <h6 class="mb-0"> <i class="bi bi-person-vcard profile-icon icon"></i>&nbsp;<?php echo htmlspecialchars($userData['name'] ?? 'Student'); ?></h6>
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

    // Notification system with refresh handling
let lastNotificationCheck = 0;
const NOTIFICATION_CACHE_TIME = 30000; // 30 seconds

async function loadNotifications(refresh = false) {
    try {
        // Only refresh if cache expired or forced
        const now = Date.now();
        if (!refresh && (now - lastNotificationCheck) < NOTIFICATION_CACHE_TIME) {
            return;
        }
        
        lastNotificationCheck = now;
        
        const response = await fetch('notification_handler.php?offset=0');
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        
        if (data.success) {
            updateNotificationBadge(data.total);
            renderNotifications(data.notifications);
            setupAutoRefresh();
        }
    } catch (error) {
        console.error('Notification error:', error);
        // Retry after delay
        setTimeout(() => loadNotifications(), 10000);
    }
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (!badge) return;
    
    const countElement = badge.querySelector('.notification-count');
    if (count > 0) {
        countElement.textContent = count;
        badge.style.display = 'block';
        
        // Add visual effect for new notifications
        if (count > parseInt(countElement.dataset.prevCount || 0)) {
            badge.classList.add('animate__animated', 'animate__tada');
            setTimeout(() => {
                badge.classList.remove('animate__animated', 'animate__tada');
            }, 1000);
        }
    } else {
        badge.style.display = 'none';
    }
    countElement.dataset.prevCount = count;
}

function renderNotifications(notifications) {
    const container = document.getElementById('notificationsContainer');
    if (!container) return;
    
    container.innerHTML = notifications.map(notif => `
        <div class="notification-item ${notif.bg_class} p-3 mb-2 rounded">
            <div class="d-flex align-items-center">
                <i class="bi ${notif.icon} ${notif.badge_class} p-2 rounded-circle me-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${notif.title || 'New Notification'}</h6>
                    <p class="mb-0 small">${notif.message}</p>
                </div>
                <span class="text-muted small">${notif.time_ago}</span>
            </div>
            ${notif.related_election ? `<div class="mt-2 small">
                <i class="bi bi-calendar-event"></i> ${notif.election_name}
            </div>` : ''}
        </div>
    `).join('');
}

function setupAutoRefresh() {
    // Refresh when page becomes visible
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            loadNotifications(true);
        }
    });
    
    // Periodic refresh (every 2 minutes)
    setInterval(() => {
        if (!document.hidden) {
            loadNotifications(true);
        }
    }, 120000);
}

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
    
    // Also load when navigating back to page
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            loadNotifications(true);
        }
    });
});
});
</script>