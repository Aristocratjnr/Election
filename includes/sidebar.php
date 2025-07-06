<?php
// Secure session initialization - This check is already here but let's make sure it works
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Current page detection - works with direct PHP files
$current_script = basename($_SERVER['SCRIPT_NAME']);
$current_page = str_replace('.php', '', $current_script);
$current_action = $_GET['action'] ?? null;

// Admin data from session
$admin_name = $_SESSION['login_name'] ?? 'Administrator';
$role = $_SESSION['role'] ?? 'admin';
$last_login = $_SESSION['last_login'] ?? null;

$default_profile_pic = 'assets/img/aristocrat.jpeg';

$profile_pic_path = '';
if (!empty($profile_pic)) {
    // Construct the full path
    $profile_pic_path = 'assets/img/profile/admins/' . $profile_pic;
    
    // Verify the file actually exists
    if (!file_exists($profile_pic_path)) {
        $profile_pic_path = $default_profile_pic;
    }
} else {
    $profile_pic_path = $default_profile_pic;
}
?>

<style>

/* ===== Premium Sidebar Styles ===== */
:root {
  /* Light mode variables (default) */
  --sidebar-width: 280px;
  --sidebar-bg: #fdfdfd;
  --sidebar-accent: #1e3a8a;
  --sidebar-text: #1a1a2e;
  --sidebar-text-light: #64748b;
  --sidebar-border: #e5e7eb;
  --sidebar-hover: #f3f4f6;
  --admin-color: #b91c1c;
  --voter-color: #059669;
  --highlight-color: #3b82f6;
  --success-badge: #16a34a;
  --primary-badge: #1e40af;
  --notification-badge: #be123c;
  --profile-card-bg: rgba(255, 255, 255, 0.2);
  --avatar-gradient-start: #4361ee;
  --avatar-gradient-end: #3a0ca3;
  --avatar-text: #ffffff;
  --sidebar-shadow: rgba(0, 0, 0, 0.03);
  --active-item-bg: rgba(59, 130, 246, 0.08);
  --active-item-border: #3b82f6;
  --logout-bg: #ffffff;
  --logout-border: #e9ecef;
  --logout-hover-bg: #f8f9fa;
  --version-color: #adb5bd;
  --info-color: #6c757d;
  --info-icon-color: #adb5bd;
}

/* Dark Mode Variables */
[data-bs-theme="dark"] {
  --sidebar-bg: #212529;
  --sidebar-accent: #6ea8fe;
  --sidebar-text: #f8f9fa;
  --sidebar-text-light: #adb5bd;
  --sidebar-border: #495057;
  --sidebar-hover: #343a40;
  --admin-color: #ea868f;
  --voter-color: #75b798;
  --highlight-color: #6ea8fe;
  --success-badge: #75b798;
  --primary-badge: #6ea8fe;
  --notification-badge: #ea868f;
  --profile-card-bg: rgba(33, 37, 41, 0.5);
  --avatar-gradient-start: #6ea8fe;
  --avatar-gradient-end: #0d6efd;
  --avatar-text: #ffffff;
  --sidebar-shadow: rgba(0, 0, 0, 0.2);
  --active-item-bg: rgba(110, 168, 254, 0.15);
  --active-item-border: #6ea8fe;
  --logout-bg: #343a40;
  --logout-border: #495057;
  --logout-hover-bg: #495057;
  --version-color: #6c757d;
  --info-color: #adb5bd;
  --info-icon-color: #6c757d;
}

.sidebar {
  width: var(--sidebar-width);
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: var(--sidebar-bg);
  box-shadow: 4px 0 20px var(--sidebar-shadow);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  border-right: 1px solid var(--sidebar-border);
  padding-top: 60px; /* Space for the header */
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Mobile Header */
.mobile-header {
  display: none;
  padding: 1rem;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--sidebar-border);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: var(--sidebar-bg);
  transition: background-color 0.3s ease;
}

.mobile-header .logo {
  font-weight: 600;
  color: var(--sidebar-accent);
}

.mobile-toggle {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--sidebar-text);
  cursor: pointer;
}

/* Profile Card */
.profile-card {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 1px solid var(--sidebar-border);
  position: relative;
  margin: 20px 15px 0;
  background: var(--profile-card-bg);
  border-radius: 8px;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.profile-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  background: linear-gradient(135deg, var(--avatar-gradient-start), var(--avatar-gradient-end));
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease;
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback {
  color: var(--avatar-text);
  font-weight: 600;
  font-size: 1.25rem;
}

.profile-info {
  flex: 1;
  min-width: 0;
}

.profile-name {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--sidebar-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.3s ease;
}

.profile-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.profile-role {
  font-size: 0.75rem;
  color: var(--sidebar-text-light);
  background: var(--sidebar-hover);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: color 0.3s ease, background-color 0.3s ease;
}

.profile-status {
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  transition: color 0.3s ease;
}

.profile-status.admin {
  color: var(--voter-color);
}

.profile-status.voter {
  color: var(--voter-color);
}

.profile-status i {
  font-size: 0.5rem;
}

.notification-icon {
  color: var(--sidebar-text-light);
  font-size: 1.25rem;
  position: relative;
  transition: all 0.2s ease;
}

.notification-icon:hover {
  color: var(--sidebar-accent);
}

.notification-bubble {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--notification-badge);
  color: white;
  font-size: 0.6rem;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Navigation */
.sidebar-navigation {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 0;
}

.nav-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-item {
  margin: 0.25rem 0;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: var(--sidebar-text);
  text-decoration: none;
  transition: all 0.2s ease;
  gap: 0.75rem;
  position: relative;
}

.nav-link:hover {
  background: var(--sidebar-hover);
  color: var(--sidebar-accent);
}

.nav-link i:first-child {
  font-size: 1.1rem;
  width: 24px;
  text-align: center;
}

.nav-link.active {
  color: var(--sidebar-accent);
  background: var(--active-item-bg);
  font-weight: 500;
  border-left: 3px solid var(--active-item-border);
  padding-left: calc(1.5rem - 3px); /* Adjust for border */
}

.nav-item.active .nav-link {
  color: var(--sidebar-accent);
  background: var(--active-item-bg);
  font-weight: 500;
}

.nav-item.active .nav-link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--active-item-border);
  border-radius: 0 3px 3px 0;
}

.nav-badge {
  font-size: 0.65rem;
  padding: 0.25rem 0.5rem;
  border-radius: 10px;
  font-weight: 600;
  margin-left: auto;
}

.nav-arrow {
  margin-left: auto;
  font-size: 0.8rem;
  transition: transform 0.3s ease;
}

.nav-item.active .nav-arrow {
  transform: rotate(180deg);
}

.submenu {
  list-style: none;
  padding: 0;
  margin: 0;
  overflow: hidden;
  max-height: 0;
  transition: max-height 0.3s ease;
}

.nav-item.active .submenu {
  max-height: 500px;
}

.submenu-item {
  padding-left: 1rem;
}

.submenu-item a {
  display: flex;
  align-items: center;
  padding: 0.6rem 1.5rem 0.6rem 3rem;
  color: var(--sidebar-text-light);
  text-decoration: none;
  transition: all 0.2s ease;
  gap: 0.75rem;
  font-size: 0.9rem;
}

.submenu-item a i {
  font-size: 0.9rem;
  width: 20px;
  text-align: center;
}

.submenu-item:hover a {
  color: var(--sidebar-accent);
}

.submenu-item.active a {
  color: var(--sidebar-accent);
  font-weight: 500;
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 1rem 1.5rem;
  font-size: 0.8rem;
  color: var(--sidebar-text-light);
  border-top: 1px solid var(--sidebar-border);
  margin-top: auto;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.sidebar-footer-text {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sidebar-footer-text i {
  font-size: 0.9rem;
  color: var(--sidebar-accent);
}

.system-info {
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
  color: var(--info-color);
}

.info-item i {
  margin-right: 0.5rem;
  font-size: 1rem;
  color: var(--info-icon-color);
}

.logout-btn {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background: var(--logout-bg);
  color: var(--admin-color);
  border: 1px solid var(--logout-border);
  border-radius: 4px;
  text-decoration: none;
  transition: all 0.2s ease;
  margin-bottom: 1rem;
}

.logout-btn:hover {
  background: var(--logout-hover-bg);
  color: var(--admin-color);
  border-color: var(--sidebar-border);
}

.logout-btn i {
  margin-right: 0.5rem;
}

.version-info {
  text-align: center;
  color: var(--version-color);
  font-size: 0.7rem;
}

/* Sidebar Overlay for Mobile */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
}

.sidebar-overlay.active {
  display: block;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
  .sidebar {
    transform: translateX(-100%);
    box-shadow: none;
    padding-top: 0;
  }
  
  .sidebar.show {
    transform: translateX(0);
    box-shadow: 4px 0 20px var(--sidebar-shadow);
  }
  
  .mobile-header {
    display: flex;
  }
  
  .profile-card {
    margin-top: 70px; /* Extra space for mobile header */
  }
}
</style>

<!-- Add additional dark mode specific styles outside the main style block -->
<style>
/* Additional dark mode fixes */
[data-bs-theme="dark"] .nav-parent > .nav-link, 
[data-bs-theme="dark"] .submenu-item a {
  color: var(--sidebar-text);
}

[data-bs-theme="dark"] .submenu-item a {
  color: var(--sidebar-text-light);
}

[data-bs-theme="dark"] .submenu-item.active a,
[data-bs-theme="dark"] .submenu-item a:hover {
  color: var(--sidebar-accent);
}

[data-bs-theme="dark"] .mobile-header {
  background-color: var(--sidebar-bg);
  border-color: var(--sidebar-border);
}

[data-bs-theme="dark"] .mobile-header .logo {
  color: var(--sidebar-accent);
}

[data-bs-theme="dark"] .main-content {
  background-color: var(--sidebar-bg);
  color: var(--sidebar-text);
}

/* Fix the main content area in dark mode */
.main-content {
  transition: background-color 0.3s ease, color 0.3s ease;
}

[data-bs-theme="dark"] .main-content h1,
[data-bs-theme="dark"] .main-content h2,
[data-bs-theme="dark"] .main-content h3,
[data-bs-theme="dark"] .main-content h4,
[data-bs-theme="dark"] .main-content h5,
[data-bs-theme="dark"] .main-content h6 {
  color: var(--sidebar-text);
}

[data-bs-theme="dark"] .main-content .bg-white {
  background-color: var(--sidebar-bg) !important;
}

[data-bs-theme="dark"] .main-content .border-bottom {
  border-color: var(--sidebar-border) !important;
}

[data-bs-theme="dark"] .main-content .text-muted {
  color: var(--sidebar-text-light) !important;
}

/* Fix any possible color issues with sidebar elements */
[data-bs-theme="dark"] .sidebar {
  background-color: var(--sidebar-bg);
  border-color: var(--sidebar-border);
}

[data-bs-theme="dark"] .profile-card {
  background-color: var(--profile-card-bg);
  border-color: var(--sidebar-border);
}

[data-bs-theme="dark"] .profile-name {
  color: var(--sidebar-text);
}

[data-bs-theme="dark"] .profile-role {
  color: var(--sidebar-text-light);
  background-color: var(--sidebar-hover);
}

/* Ensure submenu backgrounds are consistent */
[data-bs-theme="dark"] .submenu {
  background-color: var(--sidebar-bg);
}

/* Fix any elements that might have hardcoded colors */
[data-bs-theme="dark"] .nav-arrow {
  color: var(--sidebar-text-light);
}

/* Add styles for the last login info */
.last-login-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
    color: var(--sidebar-text-light);
    font-size: 0.85rem;
}

.last-login-info i {
    font-size: 1rem;
    color: var(--sidebar-accent);
}

.timeago {
    color: var(--sidebar-text);
    font-weight: 500;
    cursor: help;
}

/* Tooltip style enhancement */
.timeago:hover {
    text-decoration: underline dotted;
}
</style>

<aside id="sidebar" class="sidebar">
  <!-- Mobile Header -->
  <div class="mobile-header">
    <div class="logo">ElectionAdmin</div>
    <button class="mobile-toggle">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <!-- Admin Profile Card -->
  <div class="profile-card">
  <div class="profile-avatar">
    <img src="<?= htmlspecialchars($profile_pic_path) ?>" 
         alt="<?= htmlspecialchars($admin_name) ?>"
         onerror="this.onerror=null;this.src='<?= htmlspecialchars($default_profile_pic) ?>'">
</div>
    <div class="profile-info">
      <h4 class="profile-name"><?= htmlspecialchars($admin_name) ?></h4>
      <div class="profile-meta">
        <span class="profile-role"><?= ucfirst($role) ?></span>
        <span class="profile-status admin">
          <i class="bi bi-circle-fill"></i>
          Active
        </span>
      </div>
    </div>
  </div>

  <!-- Main Navigation -->
  <nav class="sidebar-navigation">
    <ul class="nav-menu">
      <!-- Dashboard -->
      <li class="nav-item <?= ($current_script === 'dashboard.php' || $current_script === 'index.php') ? 'active' : '' ?>">
        <a href="dashboard.php" class="nav-link" data-tooltip="Administrative Dashboard">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Admin Dashboard</span>
        </a>
      </li>

      <!-- Election Control -->
      <li class="nav-item <?= in_array($current_script, ['election.php','positions.php','candidates.php','ballots.php','election_results.php','election_config.php','categories.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="Manage Elections">
            <i class="bi bi-trophy-fill"></i>
            <span>Election Control</span>
            <i class="nav-arrow bi bi-chevron-down"></i>
          </div>
          <ul class="submenu settings-dropdown">
            <!-- Elections Submenu -->
            <li class="submenu-item <?= ($current_script === 'election.php' && (!$current_action || $current_action === 'manage')) ? 'active' : '' ?>">
              <a href="election.php">
                <i class="bi bi-list-check"></i>
                <span>All Elections</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'elections.php' && $current_action === 'create') ? 'active' : '' ?>">
              <a href="election.php">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Create New</span>
              </a>
            </li>

            <!-- Categories Submenu -->
            <li class="submenu-item <?= ($current_script === 'categories.php') ? 'active' : '' ?>">
              <a href="categories.php">
                <i class="bi bi-tags-fill"></i>
                <span>Categories</span>
              </a>
            </li>

            <!-- Positions Submenu -->
            <li class="submenu-item <?= ($current_script === 'positions.php') ? 'active' : '' ?>">
              <a href="positions.php">
                <i class="bi bi-person-badge-fill"></i>
                <span>Positions</span>
              </a>
            </li>

            <!-- Candidates Submenu -->
            <li class="submenu-item <?= ($current_script === 'candidates.php') ? 'active' : '' ?>">
              <a href="candidates.php">
                <i class="bi bi-people-fill"></i>
                <span>Candidates</span>
              </a>
            </li>

            <!-- Ballots Submenu -->
            <li class="submenu-item <?= ($current_script === 'ballots.php') ? 'active' : '' ?>">
              <a href="ballots.php">
                <i class="bi bi-ui-checks-grid"></i>
                <span>Ballot Design</span>
              </a>
            </li>

            <!-- Results Submenu -->
            <li class="submenu-item <?= ($current_script === 'results.php') ? 'active' : '' ?>">
              <a href="results.php">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Results</span>
                <span class="badge bg-primary rounded-pill ms-auto">Live</span>
              </a>
            </li>

          </ul>
        </div>
      </li>

      <!-- Voter Management -->
      <li class="nav-item <?= in_array($current_script, ['voters.php','voter_groups.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="Manage Voters">
            <i class="bi bi-people-fill"></i>
            <span>Voter Management</span>
            <i class="nav-arrow bi bi-chevron-down"></i>
          </div>
          <ul class="submenu settings-dropdown">
            <li class="submenu-item <?= ($current_script === 'voters.php') ? 'active' : '' ?>">
              <a href="voters.php">
                <i class="bi bi-person-lines-fill"></i>
                <span>Voter List</span>
              </a>
            </li>
            
          </ul>
        </div>
      </li>

      <!-- Blockchain Management -->
      <li class="nav-item <?= in_array($current_script, ['blockchain.php','transactions.php','verify.php','blockchain_config.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="Blockchain Operations">
            <i class="bi bi-link-45deg"></i>
            <span>Blockchain</span>
            <i class="nav-arrow bi bi-chevron-down"></i>
          </div>
          <ul class="submenu settings-dropdown">
            <li class="submenu-item <?= ($current_script === 'blockchain.php') ? 'active' : '' ?>">
              <a href="admin_blockchain_setup.php">
                <i class="bi bi-boxes"></i>
                <span>Explorer</span>
              </a>
            </li>
          </ul>
        </div>
      </li>

      <!-- System Settings -->
      <li class="nav-item <?= in_array($current_script, ['profile.php','security.php','preferences.php','activity.php','appearance.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="System Configuration">
            <i class="bi bi-gear-fill"></i>
            <span>System Settings</span>
            <i class="nav-arrow bi bi-chevron-down"></i>
          </div>
          <ul class="submenu settings-dropdown">
            <li class="submenu-item <?= ($current_script === 'profile.php') ? 'active' : '' ?>">
              <a href="profile.php">
                <i class="bi bi-person-circle"></i>
                <span>Admin Profile</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'security.php') ? 'active' : '' ?>">
              <a href="security.php">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Security</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'appearance.php') ? 'active' : '' ?>">
              <a href="appearance.php">
                <i class="bi bi-palette2"></i>
                <span>Appearance</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'activity.php') ? 'active' : '' ?>">
              <a href="activity.php">
                <i class="bi bi-activity"></i>
                <span>Activity Log</span>
                <span class="badge bg-danger rounded-pill ms-auto">New</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <div class="system-info">
      <div class="info-item">
        <i class="bi bi-cpu-fill"></i>
        <span>System Status: <span class="text-success">Active</span></span>
      </div>
      <div class="info-item last-login-info">
        <i class="bi bi-clock-history"></i>
        <span>Last login: </span>
        <time id="lastLoginTime" 
              class="timeago" 
              datetime="<?= $last_login ? date('c', strtotime($last_login)) : date('c') ?>"
              title="<?= $last_login ? date('F j, Y, g:i a', strtotime($last_login)) : 'Just now' ?>">
          <?= $last_login ? date('M d, Y H:i', strtotime($last_login)) : 'Just now' ?>
        </time>
      </div>
    </div>
    
    <a href="#" class="logout-btn mt-3" id="logoutBtn">
      <i class="bi bi-power"></i>
      <span>Logout</span>
    </a>
    
    <div class="version-info mt-2">
      <i class="bi bi-info-circle-fill me-1"></i>
      <span>Admin Console v1.0</span>
    </div>
  </div>
</aside>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="logoutModalLabel">
          <i class="bi bi-box-arrow-right me-2"></i> Confirm Logout
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <i class="bi bi-question-circle-fill text-danger me-3" style="font-size: 2rem;"></i>
          <div>
            <p class="mb-1">Are you sure you want to log out of your account?</p>
            <p class="text-muted mb-0">You will need to log in again to access the admin panel.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cancel
        </button>
        <a href="controllers/app.php?action=logout" class="btn btn-danger">
          <i class="bi bi-box-arrow-right me-1"></i> Yes, Log Out
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile toggle functionality
  const mobileToggle = document.querySelector('.mobile-header .mobile-toggle');
  const headerToggle = document.getElementById('sidebarToggle'); // Get header toggle button if it exists
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  
  // Mobile header toggle button
  if (mobileToggle) {
    mobileToggle.addEventListener('click', function() {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('active');
    });
  }
  
  // Header toggle button (if it exists from header.php)
  if (headerToggle) {
    headerToggle.addEventListener('click', function() {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('active');
    });
  }
  
  // Close sidebar when clicking overlay
  if (overlay) {
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('show');
      overlay.classList.remove('active');
    });
  }
  
  // Auto-expand active submenus
  document.querySelectorAll('.nav-item.active').forEach(item => {
    const submenu = item.querySelector('.submenu');
    if (submenu) {
      submenu.style.maxHeight = submenu.scrollHeight + 'px';
    }
  });
  
  // Toggle submenus when clicking parent items
  document.querySelectorAll('.nav-parent > .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
      if (this.getAttribute('href')) return;
      
      e.preventDefault();
      const parent = this.parentElement;
      const submenu = parent.querySelector('.submenu');
      const isActive = parent.parentElement.classList.contains('active');
      
      if (isActive) {
        submenu.style.maxHeight = '0';
        parent.parentElement.classList.remove('active');
      } else {
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        parent.parentElement.classList.add('active');
      }
    });
  });
  
  // Handle window resize
  window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
      sidebar.classList.remove('show');
      overlay.classList.remove('active');
    }
  });

  // Function to update last login time
  function updateLastLoginTime() {
    const lastLoginElement = document.getElementById('lastLoginTime');
    if (!lastLoginElement) return;
    
    // If it's a PHP timestamp, format it as "X minutes/hours ago"
    const loginTime = '<?php echo $last_login ? date('c', strtotime($last_login)) : ''; ?>';
    if (loginTime) {
        const loginDate = new Date(loginTime);
        const now = new Date();
        const diffMs = now - loginDate;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) {
            lastLoginElement.textContent = 'Just now';
        } else if (diffMins < 60) {
            lastLoginElement.textContent = diffMins + ' minute' + (diffMins > 1 ? 's' : '') + ' ago';
        } else if (diffMins < 1440) {
            const hours = Math.floor(diffMins / 60);
            lastLoginElement.textContent = hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
        } else {
            const days = Math.floor(diffMins / 1440);
            lastLoginElement.textContent = days + ' day' + (days > 1 ? 's' : '') + ' ago';
        }
    }
  }
  
  // Call once and set up interval
  updateLastLoginTime();
  setInterval(updateLastLoginTime, 60000); // Update every minute
  
  // Initialize theme from localStorage
  const currentTheme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', currentTheme);
  
  // Apply initial theme styles
  updateThemeSpecificElements(currentTheme);
  
  // Listen for theme changes
  document.addEventListener('themeChanged', function(e) {
    // Update theme-specific elements when theme changes
    updateThemeSpecificElements(e.detail.theme);
    console.log('Theme changed to:', e.detail.theme);
  });
  
  // Function to update theme-specific elements that might not be covered by CSS variables
  function updateThemeSpecificElements(theme) {
    // Update any elements that need manual adjustment
    const isDark = theme === 'dark';
    
    // Update background colors for elements that might have hardcoded colors
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
      if (isDark) {
        // Remove any bg-white or bg-light classes on main content
        mainContent.classList.remove('bg-white', 'bg-light');
      }
    }
    
    // Find all bg-white elements in the page and update them for dark mode
    const bgWhiteElements = document.querySelectorAll('.bg-white');
    bgWhiteElements.forEach(el => {
      if (isDark) {
        el.classList.remove('bg-white');
        el.classList.add('bg-dark-subtle');
      } else {
        el.classList.remove('bg-dark-subtle');
        el.classList.add('bg-white');
      }
    });
    
    // Adjust border colors
    const borderElements = document.querySelectorAll('.border, .border-top, .border-bottom, .border-start, .border-end');
    borderElements.forEach(el => {
      if (isDark) {
        // Add a dark border class or adjust inline style if needed
        el.style.borderColor = 'var(--sidebar-border)';
      } else {
        // Reset to default
        el.style.borderColor = '';
      }
    });
  }
});

// Timeago formatting function
function timeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval > 1) return interval + ' years ago';
    if (interval === 1) return 'a year ago';
    
    interval = Math.floor(seconds / 2592000);
    if (interval > 1) return interval + ' months ago';
    if (interval === 1) return 'a month ago';
    
    interval = Math.floor(seconds / 86400);
    if (interval > 1) return interval + ' days ago';
    if (interval === 1) return 'yesterday';
    
    interval = Math.floor(seconds / 3600);
    if (interval > 1) return interval + ' hours ago';
    if (interval === 1) return 'an hour ago';
    
    interval = Math.floor(seconds / 60);
    if (interval > 1) return interval + ' minutes ago';
    if (interval === 1) return 'a minute ago';
    
    if (seconds < 10) return 'just now';
    
    return Math.floor(seconds) + ' seconds ago';
}

// Update all timeago elements
function updateTimeagoElements() {
    const timeElements = document.querySelectorAll('time.timeago');
    timeElements.forEach(timeEl => {
        const datetime = new Date(timeEl.getAttribute('datetime'));
        timeEl.textContent = timeAgo(datetime);
        
        // Update the full date tooltip
        const fullDate = datetime.toLocaleString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        timeEl.setAttribute('title', fullDate);
    });
}

// Update times immediately and then every minute
document.addEventListener('DOMContentLoaded', function() {
    updateTimeagoElements();
    setInterval(updateTimeagoElements, 60000);
});
</script>

<!-- Additional script for logout modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Logout button functionality
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
      logoutModal.show();
    });
  }
});
</script>