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

// Get current page and user role
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Initialize user data
$is_admin = false;
$user_name = 'User';
$user_role = 'Voter';
$profile_picture = null;

// Fetch user data if logged in
if (isset($_SESSION['login_id'])) {
    try {
        $stmt = $conn->prepare("SELECT name, profile_picture, type FROM students WHERE StudentID = ?");
        $stmt->bind_param('i', $_SESSION['login_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $user_name = $userData['name'] ?? 'User';
            $profile_picture = $userData['profile_picture'] ?? null;
            $is_admin = ($userData['type'] ?? 1) == 0; // Assuming 0 is admin
            $user_role = $is_admin ? 'Administrator' : 'Voter';
        }
    } catch (Exception $e) {
        error_log("Sidebar error: " . $e->getMessage());
    }
}

// Enhanced menu configuration
$menu_items = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'url' => 'dashboard.php',
        'active' => in_array($current_page, ['dashboard', 'index']),
        'badge' => $is_admin ? null : ['type' => 'info', 'text' => 'New']
    ],
    'elections' => [
        'title' => 'Elections',
        'icon' => 'bi-calendar-event',
        'url' => 'elections.php',
        'active' => $current_page == 'elections',
        'admin_only' => true
    ],
    'vote' => [
        'title' => 'Vote Now',
        'icon' => 'bi-check2-square',
        'url' => 'vote.php',
        'active' => $current_page == 'vote',
        'voter_only' => !$is_admin,
        'highlight' => !$is_admin
    ],
    'candidates' => [
        'title' => 'Candidates',
        'icon' => 'bi-people',
        'url' => 'candidates.php',
        'active' => $current_page == 'candidates',
        'admin_only' => true
    ],
    'categories' => [
        'title' => 'Categories',
        'icon' => 'bi-tags',
        'url' => 'categories.php',
        'active' => $current_page == 'categories',
        'admin_only' => true
    ],
    'results' => [
        'title' => 'Results',
        'icon' => 'bi-bar-chart',
        'url' => 'results.php',
        'active' => $current_page == 'results',
        'badge' => ['type' => 'success', 'text' => 'Live']
    ],
    'voters' => [
        'title' => 'Voters',
        'icon' => 'bi-person-lines-fill',
        'url' => 'voters.php',
        'active' => $current_page == 'voters',
        'admin_only' => true
    ],
    'reports' => [
        'title' => 'Reports',
        'icon' => 'bi-file-earmark-text',
        'url' => 'reports.php',
        'active' => $current_page == 'reports',
        'admin_only' => true
    ],
    'settings' => [
        'title' => 'Settings',
        'icon' => 'bi-gear',
        'active' => in_array($current_page, ['profile', 'settings', 'account']),
        'has_submenu' => true,
        'submenu' => [
            'profile' => [
                'title' => 'My Profile',
                'url' => 'profile.php',
                'icon' => 'bi-person'
            ],
            'account' => [
                'title' => 'Account',
                'url' => 'account.php',
                'icon' => 'bi-shield-lock',
                'admin_only' => true
            ],
            'system' => [
                'title' => 'System Settings',
                'url' => 'system.php',
                'icon' => 'bi-sliders',
                'admin_only' => true
            ]
        ]
    ]
];
?>

<!-- ======= Enhanced Sidebar ======= -->
<aside id="sidebar" class="sidebar border-end">
    <!-- Sidebar Header -->
    <div class="sidebar-header text-center p-4">
        <div class="sidebar-logo mb-3">
            <img src="assets/img/logo.png" alt="System Logo" width="40" height="40">
        </div>
        <h5 class="mb-1 fw-bold">SmartVote System</h5>
        <div class="user-badge badge bg-<?php echo $is_admin ? 'primary' : 'success'; ?>">
            <i class="bi bi-<?php echo $is_admin ? 'shield-shaded' : 'person-check'; ?> me-1"></i>
            <?php echo $user_role; ?>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="sidebar-nav px-2 pb-4" id="sidebar-nav">
        <?php foreach ($menu_items as $key => $item): ?>
            <?php 
            // Skip items based on user role
            if (($item['admin_only'] ?? false) && !$is_admin) continue;
            if (($item['voter_only'] ?? false) && $is_admin) continue;
            
            // Determine active state
            $is_active = $item['active'] ?? false;
            $has_submenu = $item['has_submenu'] ?? false;
            ?>
            
            <li class="nav-item my-1">
                <?php if ($has_submenu): ?>
                    <!-- Submenu Parent -->
                    <a class="nav-link py-2 px-3 rounded-3 d-flex align-items-center <?php echo $is_active ? 'active' : ''; ?> <?php echo $item['highlight'] ? 'highlight-item' : ''; ?>" 
                       data-bs-toggle="collapse" 
                       href="#<?php echo $key; ?>-submenu" 
                       role="button"
                       aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">
                        <i class="bi <?php echo $item['icon']; ?> me-3"></i>
                        <span class="flex-grow-1"><?php echo $item['title']; ?></span>
                        <i class="bi bi-chevron-down transition-all"></i>
                    </a>
                    
                    <!-- Submenu Items -->
                    <div class="collapse <?php echo $is_active ? 'show' : ''; ?> ps-4" id="<?php echo $key; ?>-submenu">
                        <ul class="submenu list-unstyled">
                            <?php foreach ($item['submenu'] as $subkey => $subitem): ?>
                                <?php if (($subitem['admin_only'] ?? false) && !$is_admin) continue; ?>
                                <li class="my-1">
                                    <a href="<?php echo $subitem['url']; ?>" 
                                       class="nav-link py-2 px-3 rounded-3 d-flex align-items-center">
                                        <i class="bi <?php echo $subitem['icon']; ?> me-3"></i>
                                        <span><?php echo $subitem['title']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Regular Menu Item -->
                    <a href="<?php echo $item['url']; ?>" 
                       class="nav-link py-2 px-3 rounded-3 d-flex align-items-center <?php echo $is_active ? 'active' : ''; ?> <?php echo $item['highlight'] ? 'highlight-item' : ''; ?>">
                        <i class="bi <?php echo $item['icon']; ?> me-3"></i>
                        <span class="flex-grow-1"><?php echo $item['title']; ?></span>
                        <?php if (isset($item['badge'])): ?>
                            <span class="badge bg-<?php echo $item['badge']['type']; ?> ms-2"><?php echo $item['badge']['text']; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer border-top px-3 py-2 text-center">
        <div class="user-info mb-2">
            <div class="user-avatar d-inline-block position-relative mb-2">
                <?php if (!empty($profile_picture)): ?>
                    <img src="assets/img/profile/users/<?php echo htmlspecialchars($profile_picture); ?>" 
                         class="rounded-circle" width="40" height="40" alt="User"
                         onerror="this.src='assets/img/default-user.png'">
                <?php else: ?>
                    <div class="avatar-initials rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px;">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <span class="user-status position-absolute bottom-0 end-0 bg-<?php echo $is_admin ? 'warning' : 'success'; ?>"></span>
            </div>
            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user_name); ?></h6>
            <small class="text-muted"><?php echo $user_role; ?></small>
        </div>
        
        <div class="logout-section">
            <a href="logout.php" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Are you sure you want to logout?');">
                <i class="bi bi-box-arrow-left me-1"></i> Logout
            </a>
        </div>
        
        <?php if ($is_admin): ?>
        <div class="system-info small mt-2 text-muted">
            <div>v2.1.0</div>
            <div class="last-login">
                <i class="bi bi-clock-history me-1"></i>
                <?php echo isset($_SESSION['last_login']) ? 
                    date('M j, g:i A', strtotime($_SESSION['last_login'])) : 'First login'; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</aside>

<style>
/* Enhanced Sidebar Styles */
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #fff;
    z-index: 1000;
    transition: all 0.3s;
    overflow-y: auto;
}

.sidebar-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.user-badge {
    font-size: 0.7rem;
    padding: 0.35em 0.65em;
    font-weight: 500;
}

.nav-link {
    color: #495057;
    transition: all 0.2s;
    margin-bottom: 0.25rem;
}
.nav-link:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}
.nav-link.active {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    font-weight: 500;
}

.highlight-item {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.user-avatar {
    width: 40px;
    height: 40px;
}
.user-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
}

@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.show {
        transform: translateX(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle submenu arrows
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const icon = this.querySelector('.bi-chevron-down');
            if (icon) icon.classList.toggle('rotate-180');
        });
    });
    
    // Mobile sidebar toggle
    document.querySelector('.toggle-sidebar-btn')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });
});
</script>