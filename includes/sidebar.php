<!-- ======= Admin Premium Sidebar UI ======= -->
<aside id="sidebar" class="sidebar">
  <!-- Mobile Header -->
  <div class="mobile-header">
    <div class="logo">ElectionAdmin</div>
    <button class="mobile-toggle">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <?php
  // Enhanced secure session initialization
  if (session_status() === PHP_SESSION_NONE) {
    session_start([
      'cookie_secure' => true,
      'cookie_httponly' => true,
      'use_strict_mode' => true,
      'cookie_samesite' => 'Strict'
    ]);
  }

  // Initialize admin data with defaults
  $adminData = [
    'name' => 'Administrator',
    'profile_picture' => null,
    'role' => 'admin',
    'unread_notifications' => 0,
    'last_login' => null
  ];

  // Current page detection
  $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
  
  // Admin data
  $admin_name = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'David A.';
  $profile_pic = isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : null;
  $unread_notifications = isset($_SESSION['unread_notifications']) ? $_SESSION['unread_notifications'] : 0;
  
  // Menu configuration - Focused on administrative features
  $menu_items = [
    'dashboard' => [
      'title' => 'Admin Dashboard',
      'icon' => 'bi-speedometer2',
      'url' => 'index.php?page=dashboard',
      'active' => in_array($current_page, ['dashboard']) || $_SERVER['SCRIPT_NAME'] === 'index.php',
      'tooltip' => 'Administrative Dashboard'
    ],
    'elections' => [
      'title' => 'Election Control',
      'icon' => 'bi-calendar-event',
      'url' => 'index.php?page=election_config',
      'active' => in_array($current_page, ['election_config', 'candidates', 'positions']),
      'indicator' => 'bi-chevron-right',
      'tooltip' => 'Manage Elections',
      'subitems' => [
        'config' => [
          'title' => 'Configuration',
          'icon' => 'bi-gear',
          'url' => 'index.php?page=election_config'
        ],
        'positions' => [
          'title' => 'Positions',
          'icon' => 'bi-award',
          'url' => 'index.php?page=positions'
        ],
        'candidates' => [
          'title' => 'Candidates',
          'icon' => 'bi-person-badge',
          'url' => 'index.php?page=candidates'
        ],
        'ballots' => [
          'title' => 'Ballot Design',
          'icon' => 'bi-file-earmark-text',
          'url' => 'index.php?page=ballots'
        ]
      ]
    ],
    'voters' => [
      'title' => 'Voter Management',
      'icon' => 'bi-people-fill',
      'url' => 'index.php?page=voters',
      'active' => in_array($current_page, ['voters', 'voter_groups']),
      'tooltip' => 'Manage Voters and Groups',
      'subitems' => [
        'voters' => [
          'title' => 'Voter List',
          'icon' => 'bi-person-lines-fill',
          'url' => 'index.php?page=voters'
        ],
        'groups' => [
          'title' => 'Voter Groups',
          'icon' => 'bi-collection',
          'url' => 'index.php?page=voter_groups'
        ],
        'import' => [
          'title' => 'Bulk Import',
          'icon' => 'bi-upload',
          'url' => 'index.php?page=voter_import'
        ]
      ]
    ],
    'results' => [
      'title' => 'Results & Analytics',
      'icon' => 'bi-bar-chart-line',
      'url' => 'index.php?page=results',
      'active' => $current_page == 'results',
      'badge' => ['type' => 'success', 'text' => 'Live'],
      'tooltip' => 'Election Results',
      'subitems' => [
        'live' => [
          'title' => 'Live Results',
          'icon' => 'bi-graph-up-arrow',
          'url' => 'index.php?page=results'
        ],
        'reports' => [
          'title' => 'Detailed Reports',
          'icon' => 'bi-file-earmark-bar-graph',
          'url' => 'index.php?page=reports'
        ],
        'audit' => [
          'title' => 'Audit Logs',
          'icon' => 'bi-shield-check',
          'url' => 'index.php?page=audit_logs'
        ]
      ]
    ],
  
    'settings' => [
      'title' => 'Admin Preferences',
      'icon' => 'bi-person-gear',
      'active' => $current_page == 'profile',
      'tooltip' => 'Administrator Settings',
      'subitems' => [
        'profile' => [
          'title' => 'Admin Profile',
          'icon' => 'bi-person',
          'url' => 'index.php?page=profile'
        ],
        'security' => [
          'title' => 'Account Security',
          'icon' => 'bi-shield-lock',
          'url' => 'index.php?page=security'
        ],
        'notifications' => [
          'title' => 'Notification Settings',
          'icon' => 'bi-bell',
          'url' => 'index.php?page=notifications'
        ]
      ]
    ]
  ];
  ?>

  <!-- Admin Profile Card -->
<div class="profile-card">
  <div class="profile-avatar">
    <?php if ($profile_pic): ?>
      <img src="assets/img/profile/admins/<?php echo htmlspecialchars($profile_pic); ?>" 
           alt="<?php echo htmlspecialchars($admin_name); ?>" 
           onerror="this.src='assets/img/aristo.png'">
    <?php else: ?>
      <div class="avatar-fallback">
        <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="profile-info">
    <h4 class="profile-name"><?php echo htmlspecialchars($admin_name); ?></h4>
    <div class="profile-meta">
      <span class="profile-role">Admin</span>
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
      <?php foreach ($menu_items as $key => $item): ?>
        <?php 
        $hasChildren = isset($item['subitems']);
        $isActive = $item['active'] ?? false;
        ?>
        
        <li class="nav-item <?php echo $isActive ? 'active' : ''; ?>">
          <?php if ($hasChildren): ?>
            <div class="nav-parent">
              <div class="nav-link settings-toggle" data-tooltip="<?php echo $item['tooltip'] ?? $item['title']; ?>">
                <i class="bi <?php echo $item['icon']; ?>"></i>
                <span><?php echo $item['title']; ?></span>
                <i class="nav-arrow bi bi-chevron-down"></i>
              </div>
              <ul class="submenu settings-dropdown">
                <?php foreach ($item['subitems'] as $subkey => $subitem): ?>
                  <li class="submenu-item <?php echo $current_page == $subkey ? 'active' : ''; ?>">
                    <a href="<?php echo $subitem['url']; ?>">
                      <i class="bi <?php echo $subitem['icon']; ?>"></i>
                      <span><?php echo $subitem['title']; ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <a href="<?php echo $item['url']; ?>" class="nav-link" data-tooltip="<?php echo $item['tooltip'] ?? $item['title']; ?>">
              <i class="bi <?php echo $item['icon']; ?>"></i>
              <span><?php echo $item['title']; ?></span>
              <?php if (isset($item['badge'])): ?>
                <span class="nav-badge bg-<?php echo $item['badge']['type']; ?>">
                  <?php echo $item['badge']['text']; ?>
                </span>
              <?php endif; ?>
              <?php if (isset($item['indicator'])): ?>
                <i class="bi <?php echo $item['indicator']; ?>"></i>
              <?php endif; ?>
            </a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <div class="system-info">
      <div class="info-item last-login">
        <i class="bi bi-clock-history"></i>
        <span id="lastLoginTime">
          Last login: 
        </span>
      </div>
      <div class="info-item">
        <i class="bi bi-shield-check"></i>
        <span>Secure Admin Session</span>
      </div>
    </div>
    <a href="login.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
      <i class="bi bi-box-arrow-left"></i>
      <span>Logout</span>
    </a>
    <div class="version-info">
      Admin Console v2.1.0 · <?php echo date('Y'); ?>
    </div>
  </div>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<style>

/* ===== Premium Sidebar Styles ===== */
:root {
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
}

.sidebar {
  width: var(--sidebar-width);
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: var(--sidebar-bg);
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  border-right: 1px solid var(--sidebar-border);
  padding-top: 60px; /* Space for the header */
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
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
}

.profile-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  background: linear-gradient(135deg, #4361ee, #3a0ca3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback {
  color: white;
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
}

.profile-status {
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
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

.nav-item.active .nav-link {
  color: var(--sidebar-accent);
  background: rgba(67, 97, 238, 0.05);
  font-weight: 500;
}

.nav-item.active .nav-link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--sidebar-accent);
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
  padding: 1rem;
  border-top: 1px solid var(--sidebar-border);
  font-size: 0.8rem;
  background: #f8f9fa;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile sidebar toggle functionality
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

  // Function to update the last login time
function updateLastLoginTime() {
  const now = new Date();
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  };
  
  document.getElementById('lastLoginTime').textContent = 
    now.toLocaleDateString('en-US', options);
}

// Update immediately
updateLastLoginTime();

// Update every second to keep it current
setInterval(updateLastLoginTime, 1000);

});
</script>