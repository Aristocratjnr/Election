<!-- ======= Premium Sidebar UI ======= -->
<aside id="sidebar" class="sidebar">
  <!-- Mobile Header -->
  <div class="mobile-header">
    <div class="logo">SmartVote</div>
    <button class="mobile-toggle">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <?php
  // Your existing PHP code remains exactly the same
  // Enhanced secure session initialization
  if (session_status() === PHP_SESSION_NONE) {
    session_start([
      'cookie_secure' => true,
      'cookie_httponly' => true,
      'use_strict_mode' => true,
      'cookie_samesite' => 'Strict'
    ]);
  }

  // Initialize user data with defaults
  $userData = [
    'name' => 'Guest',
    'profile_picture' => null,
    'role' => 'guest',
    'unread_notifications' => 0,
    'last_login' => null
  ];

  // Current page detection
  $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
  
  // User role detection
  $is_admin = isset($_SESSION['login_type']) && $_SESSION['login_type'] == 0;
  $user_role = $is_admin ? 'Administrator' : 'Voter';
  
  // User data
  $user_name = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'User';
  $profile_pic = isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : null;
  $unread_notifications = isset($_SESSION['unread_notifications']) ? $_SESSION['unread_notifications'] : 0;
  
  // Menu configuration
  $menu_items = [
    'dashboard' => [
      'title' => 'Dashboard',
      'icon' => 'bi-speedometer2',
      'url' => 'index.php?' . ($is_admin ? 'page=dashboard' : 'page=vote'),
      'active' => in_array($current_page, ['dashboard', 'vote']) || $_SERVER['SCRIPT_NAME'] === 'index.php',
      'badge' => !$is_admin ? ['type' => 'secondary', 'text' => 'New'] : null,
      'tooltip' => 'Dashboard'
    ],
    'elections' => [
      'title' => 'Elections',
      'icon' => 'bi-calendar-event',
      'url' => 'index.php?page=election_config',
      'admin_only' => true,
      'active' => in_array($current_page, ['election_config', 'candidates']),
      'indicator' => $is_admin ? 'bi-chevron-right' : null,
      'tooltip' => 'Election Management'
    ],
    'categories' => [
      'title' => 'Categories',
      'icon' => 'bi-tags',
      'url' => 'index.php?page=categories',
      'admin_only' => true,
      'active' => $current_page == 'categories',
      'tooltip' => 'Categories'
    ],
    'vote' => [
      'title' => 'Cast Vote',
      'icon' => 'bi-check-circle',
      'url' => 'index.php?page=vote',
      'voter_only' => true,
      'active' => $current_page == 'vote',
      'highlight' => true,
      'tooltip' => 'Voting Panel'
    ],
    'results' => [
      'title' => 'Live Results',
      'icon' => 'bi-bar-chart-line',
      'url' => 'index.php?page=results',
      'active' => $current_page == 'results',
      'badge' => ['type' => 'success', 'text' => 'Live'],
      'tooltip' => 'Election Results'
    ],
    'voters' => [
      'title' => 'Voter Management',
      'icon' => 'bi-people',
      'url' => 'index.php?page=voters',
      'admin_only' => true,
      'active' => $current_page == 'voters',
      'tooltip' => 'Voter Management'
    ],
    'reports' => [
      'title' => 'Analytics',
      'icon' => 'bi-graph-up',
      'url' => 'index.php?page=reports',
      'admin_only' => true,
      'active' => $current_page == 'reports',
      'tooltip' => 'Reports & Analytics'
    ],
    'settings' => [
      'title' => 'Settings',
      'icon' => 'bi-gear',
      'active' => $current_page == 'profile',
      'tooltip' => 'System Settings',
      'subitems' => [
        'profile' => [
          'title' => 'My Profile',
          'icon' => 'bi-person',
          'url' => 'index.php?page=profile'
        ],
        'security' => [
          'title' => 'Security',
          'icon' => 'bi-shield-lock',
          'url' => 'index.php?page=security'
        ],
        // Added more settings options
        'notifications' => [
          'title' => 'Notifications',
          'icon' => 'bi-bell',
          'url' => 'index.php?page=notifications'
        ],
        'preferences' => [
          'title' => 'Preferences',
          'icon' => 'bi-sliders',
          'url' => 'index.php?page=preferences'
        ]
      ]
    ]
  ];
  ?>

  <!-- User Profile Card -->
  <div class="profile-card">
    <div class="profile-avatar">
      <?php if ($profile_pic): ?>
        <img src="assets/img/profile/<?php echo htmlspecialchars($profile_pic); ?>" 
             alt="<?php echo htmlspecialchars($user_name); ?>" 
             onerror="this.src='assets/img/default-avatar.jpg'">
      <?php else: ?>
        <div class="avatar-fallback">
          <?php echo strtoupper(substr($user_name, 0, 1)); ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="profile-info">
      <h4 class="profile-name"><?php echo htmlspecialchars($user_name); ?></h4>
      <div class="profile-meta">
        <span class="profile-role"><?php echo $user_role; ?></span>
        <span class="profile-status <?php echo $is_admin ? 'admin' : 'voter'; ?>">
          <i class="bi bi-circle-fill"></i>
          <?php echo $is_admin ? 'Admin' : 'Active'; ?>
        </span>
      </div>
    </div>
    <a href="notifications.php" class="notification-icon">
      <i class="bi bi-bell"></i>
      <?php if ($unread_notifications > 0): ?>
        <span class="notification-bubble"><?php echo $unread_notifications; ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- Main Navigation -->
  <nav class="sidebar-navigation">
    <ul class="nav-menu">
      <?php foreach ($menu_items as $key => $item): ?>
        <?php 
        // Skip unauthorized items
        if (($item['admin_only'] ?? false) && !$is_admin) continue;
        if (($item['voter_only'] ?? false) && $is_admin) continue;
        
        $hasChildren = isset($item['subitems']);
        $isActive = $item['active'] ?? false;
        ?>
        
        <li class="nav-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $item['highlight'] ?? false ? 'highlight' : ''; ?>">
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
      <div class="info-item">
        <i class="bi bi-clock-history"></i>
        <span>
          Last login: <?php echo $userData['last_login'] 
            ? date('M j, g:i A', strtotime($userData['last_login'])) 
            : 'First login'; ?>
        </span>
      </div>
      <div class="info-item">
        <i class="bi bi-shield-check"></i>
        <span>Secure connection</span>
      </div>
    </div>
    <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
      <i class="bi bi-box-arrow-left"></i>
      <span>Logout</span>
    </a>
    <div class="version-info">
      v2.1.0 · <?php echo date('Y'); ?> © SmartVote
    </div>
  </div>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<style>
/* ===== Premium Sidebar Styles ===== */
:root {
  --sidebar-width: 280px;
  --sidebar-bg: #fdfdfd; /* Soft White */
  --sidebar-accent: #1e3a8a; /* Deep Blue */
  --sidebar-text: #1a1a2e; /* Dark Navy */
  --sidebar-text-light: #64748b; /* Muted Slate */
  --sidebar-border: #e5e7eb; /* Light Gray */
  --sidebar-hover: #f3f4f6; /* Subtle Hover */
  --admin-color: #b91c1c; /* Rich Crimson */
  --voter-color: #059669; /* Deep Teal */
  --highlight-color: #3b82f6; /* Bright Royal Blue */
  --success-badge: #16a34a; /* Elegant Green */
  --primary-badge: #1e40af; /* Bold Blue */
  --notification-badge: #be123c; /* Luxurious Red */
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
}

/* Profile Card */
.profile-card {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 1px solid var(--sidebar-border);
  position: relative;
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
  color: var(--admin-color);
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

.nav-item.highlight .nav-link {
  color: var(--highlight-color);
  background: rgba(76, 201, 240, 0.05);
}

.nav-item.highlight .nav-link::before {
  background: var(--highlight-color);
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

/* Submenu */
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
    transition: var(--sidebar-transition);
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

.system-status {
  margin: 1rem 0;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--sidebar-text-light);
  margin-bottom: 0.5rem;
}

.status-item:last-child {
  margin-bottom: 0;
}

.status-icon {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.status-icon.online {
  background: var(--success-badge);
  box-shadow: 0 0 0 2px rgba(56, 176, 0, 0.2);
}

.app-version {
  display: flex;
  justify-content: space-between;
  font-size: 0.7rem;
  color: var(--sidebar-text-light);
  padding-top: 0.5rem;
  border-top: 1px solid var(--sidebar-border);
}

/* Collapsed State */
.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

.sidebar.collapsed .brand-name,
.sidebar.collapsed .brand-badge,
.sidebar.collapsed .profile-info,
.sidebar.collapsed .profile-actions,
.sidebar.collapsed .nav-text,
.sidebar.collapsed .nav-badge,
.sidebar.collapsed .nav-arrow,
.sidebar.collapsed .info-item span,
.sidebar.collapsed .logout-btn span,
.sidebar.collapsed .version-info {
    display: none;
}

.sidebar.collapsed .sidebar-brand {
    justify-content: center;
}

.sidebar.collapsed .sidebar-toggle {
    margin: 0 auto;
}

.sidebar.collapsed .user-profile {
    justify-content: center;
    padding: 1rem 0.5rem;
}

.sidebar.collapsed .profile-avatar {
    width: 36px;
    height: 36px;
}

.sidebar.collapsed .nav-link, 
.sidebar.collapsed .nav-parent {
    justify-content: center;
    padding: 0.75rem 0.5rem;
}

.sidebar.collapsed .nav-icon {
    margin-right: 0;
}

.sidebar.collapsed .submenu {
    display: none;
}

.sidebar.collapsed .logout-btn {
    justify-content: center;
    padding: 0.5rem;
}



/* Responsive */
@media (max-width: 992px) {
  .sidebar {
    transform: translateX(-100%);
    box-shadow: none;
  }
  
  .sidebar.show {
    transform: translateX(0);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
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
  
  // Close sidebar when clicking outside on mobile
  document.addEventListener('click', function(e) {
    if (window.innerWidth <= 992 && 
        !sidebar.contains(e.target) && 
        !e.target.classList.contains('mobile-toggle')) {
      sidebar.classList.remove('show');
      overlay.classList.remove('active');
    }
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
});
</script>