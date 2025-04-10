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
  color: #6c757d;
}

.info-item i {
  margin-right: 0.5rem;
  font-size: 1rem;
  color: #adb5bd;
}

.logout-btn {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background: white;
  color: var(--admin-color);
  border: 1px solid #e9ecef;
  border-radius: 4px;
  text-decoration: none;
  transition: all 0.2s ease;
  margin-bottom: 1rem;
}

.logout-btn:hover {
  background: #f8f9fa;
  color: var(--admin-color);
  border-color: #dee2e6;
}

.logout-btn i {
  margin-right: 0.5rem;
}

.version-info {
  text-align: center;
  color: #adb5bd;
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
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  }
  
  .mobile-header {
    display: flex;
  }
  
  .profile-card {
    margin-top: 70px; /* Extra space for mobile header */
  }
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
          <i class="bi bi-speedometer2"></i>
          <span>Admin Dashboard</span>
        </a>
      </li>

      <!-- Election Control -->
      <li class="nav-item <?= in_array($current_script, ['election.php','positions.php','candidates.php','ballots.php','election_results.php','election_config.php','categories.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="Manage Elections, Candidates, Positions and Ballots">
            <i class="bi bi-calendar-event"></i>
            <span>Election Control</span>
            <i class="nav-arrow bi bi-chevron-down"></i>
          </div>
          <ul class="submenu settings-dropdown">
            <!-- Elections Submenu -->
            <li class="submenu-item <?= ($current_script === 'election.php' && (!$current_action || $current_action === 'manage')) ? 'active' : '' ?>">
              <a href="election.php">
                <i class="bi bi-list-ul"></i>
                <span>All Elections</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'elections.php' && $current_action === 'create') ? 'active' : '' ?>">
              <a href="election.php">
                <i class="bi bi-plus-circle"></i>
                <span>Create New</span>
              </a>
            </li>

            <!-- Categories Submenu -->
            <li class="submenu-item <?= ($current_script === 'categories.php') ? 'active' : '' ?>">
              <a href="categories.php">
                <i class="bi bi-bookmark-fill"></i>
                <span>Categories</span>
              </a>
            </li>

            <!-- Positions Submenu -->
            <li class="submenu-item <?= ($current_script === 'positions.php') ? 'active' : '' ?>">
              <a href="positions.php">
                <i class="bi bi-award"></i>
                <span>Positions</span>
              </a>
            </li>

            <!-- Candidates Submenu -->
            <li class="submenu-item <?= ($current_script === 'candidates.php') ? 'active' : '' ?>">
              <a href="candidates.php">
                <i class="bi bi-person-badge"></i>
                <span>Candidates</span>
              </a>
            </li>

            <!-- Ballots Submenu -->
            <li class="submenu-item <?= ($current_script === 'ballots.php') ? 'active' : '' ?>">
              <a href="ballots.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Ballot Design</span>
              </a>
            </li>

            <!-- Results Submenu -->
            <li class="submenu-item <?= ($current_script === 'results.php') ? 'active' : '' ?>">
              <a href="results.php">
                <i class="bi bi-graph-up"></i>
                <span>Results</span>
              </a>
            </li>

          </ul>
        </div>
      </li>

      <!-- Voter Management -->
      <li class="nav-item <?= in_array($current_script, ['voters.php','voter_groups.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="Manage Voters and Groups">
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

      <!-- Admin Preferences -->
      <li class="nav-item <?= in_array($current_script, ['profile.php','security.php','preferences.php','activity.php','appearance.php']) ? 'active' : '' ?>">
        <div class="nav-parent">
          <div class="nav-link settings-toggle" data-tooltip="Administrator Settings">
            <i class="bi bi-person-gear"></i>
            <span>Admin Preferences</span>
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
                <span>Account Security</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'appearance.php') ? 'active' : '' ?>">
              <a href="appearance.php">
                <i class="bi bi-palette-fill"></i>
                <span>UI Appearance</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'preferences.php') ? 'active' : '' ?>">
              <a href="preferences.php">
                <i class="bi bi-sliders"></i>
                <span>System Settings</span>
              </a>
            </li>
            <li class="submenu-item <?= ($current_script === 'activity.php') ? 'active' : '' ?>">
              <a href="activity.php">
                <i class="bi bi-activity"></i>
                <span>Activity Log</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <span class="sidebar-footer-text">
        <i class="bi bi-clock"></i>
        Last login: <span id="lastLoginTime">
            <?php 
                if ($last_login) {
                    echo date('M d, Y H:i', strtotime($last_login));
                } else {
                    echo 'Just now';
                }
            ?>
        </span>
    </span>
    
    <a href="controllers/app.php?action=logout" class="logout-btn mt-3" onclick="return confirm('Are you sure you want to logout?');">
      <i class="bi bi-box-arrow-right"></i>
      <span>Logout</span>
    </a>
    
    <div class="version-info mt-2">
      <span>Admin Console, v1.0</span>
    </div>
  </div>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile toggle functionality
  const mobileToggle = document.querySelector('.mobile-toggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  
  if (mobileToggle) {
    mobileToggle.addEventListener('click', function() {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('active');
    });
  }
  
  // Close sidebar when clicking overlay
  overlay.addEventListener('click', function() {
    sidebar.classList.remove('show');
    overlay.classList.remove('active');
  });
  
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
  
  // Listen for theme changes
  document.addEventListener('themeChanged', function(e) {
    // The CSS variables will handle most style changes automatically
    console.log('Theme changed to:', e.detail.theme);
  });
});
</script>