<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

  // Define the current page
  $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
  
  // Check if user is admin
  $is_admin = isset($_SESSION['login_type']) && $_SESSION['login_type'] == 0;
  
  // Define menu items with their properties
  $menu_items = [
    [
      'id' => 'dashboard',
      'title' => 'Dashboard',
      'icon' => 'bi-grid',
      'url' => 'index.php?' . ($is_admin ? 'page=dashboard' : 'page=vote'),
      'active' => ($current_page == 'dashboard' || $current_page == 'vote' || $_SERVER['SCRIPT_NAME'] === 'index.php')
    ],
    [
      'id' => 'config',
      'title' => 'Configuration',
      'icon' => 'bi-gear',
      'admin_only' => true,
      'has_submenu' => true,
      'active' => in_array($current_page, array('election_config', 'candidates', 'categories')),
      'submenu' => [
        [
          'id' => 'elections',
          'title' => 'Elections',
          'url' => 'index.php?page=election_config',
          'active' => ($current_page == 'election_config' || $current_page == 'candidates')
        ],
        [
          'id' => 'categories',
          'title' => 'Categories',
          'url' => 'index.php?page=categories',
          'active' => ($current_page == 'categories')
        ]
      ]
    ],
    [
      'id' => 'results',
      'title' => 'Results',
      'icon' => 'bi-receipt',
      'url' => 'index.php?page=results',
      'active' => ($current_page == 'results')
    ],
    [
      'id' => 'reports',
      'title' => 'Reports',
      'icon' => 'bi-bookmark',
      'url' => 'index.php?page=reports',
      'admin_only' => true,
      'hidden' => true, // Currently commented out in original code
      'active' => ($current_page == 'reports')
    ],
    [
      'id' => 'profile',
      'title' => 'Profile',
      'icon' => 'bi-person',
      'url' => 'index.php?page=profile',
      'active' => ($current_page == 'profile')
    ],
    [
      'id' => 'logout',
      'title' => 'Logout',
      'icon' => 'bi-box-arrow-in-right',
      'url' => 'controllers/app.php?action=logout',
      'active' => false
    ]
  ];
  ?>

  <div class="sidebar-header">
    <h3>Election System</h3>
    <p><?php echo $is_admin ? 'Administrator' : 'Voter'; ?></p>
  </div>

  <ul class="sidebar-nav" id="sidebar-nav">
    <?php foreach ($menu_items as $item): ?>
      <?php 
      // Skip items that are admin-only if user is not admin
      if (isset($item['admin_only']) && $item['admin_only'] && !$is_admin) continue;
      
      // Skip hidden items
      if (isset($item['hidden']) && $item['hidden']) continue;
      ?>
      
      <li class="nav-item">
        <?php if (isset($item['has_submenu']) && $item['has_submenu']): ?>
          <!-- Item with submenu -->
          <a class="nav-link<?php echo $item['active'] ? '' : ' collapsed'; ?>" 
             data-bs-target="#<?php echo $item['id']; ?>-nav" 
             data-bs-toggle="collapse" 
             href="#">
            <i class="bi <?php echo $item['icon']; ?>"></i>
            <span><?php echo $item['title']; ?></span>
            <i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="<?php echo $item['id']; ?>-nav" 
              class="nav-content collapse<?php echo $item['active'] ? ' show' : ''; ?>" 
              data-bs-parent="#sidebar-nav">
            <?php foreach ($item['submenu'] as $submenu): ?>
              <li>
                <a href="<?php echo $submenu['url']; ?>" 
                   class="<?php echo $submenu['active'] ? 'active' : ''; ?>">
                  <i class="bi bi-circle"></i>
                  <span><?php echo $submenu['title']; ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <!-- Regular menu item -->
          <a class="nav-link<?php echo $item['id'] == 'dashboard' ? ' nav-dashboard' : ''; ?><?php echo $item['active'] ? '' : ' collapsed'; ?>" 
             href="<?php echo $item['url']; ?>">
            <i class="bi <?php echo $item['icon']; ?>"></i>
            <span><?php echo $item['title']; ?></span>
            <?php if (isset($item['badge'])): ?>
              <span class="badge bg-<?php echo $item['badge']['type']; ?> ms-auto"><?php echo $item['badge']['text']; ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if ($is_admin): ?>
  <div class="sidebar-footer">
    <div class="system-info">
      <p>Version 1.0</p>
      <p>Last Login: 
        <?php 
          echo isset($_SESSION['last_login']) && $_SESSION['last_login'] != null 
          ? date('M d, Y h:i A', strtotime($_SESSION['last_login'])) 
          : 'N/A'; 
        ?>
      </p>
    </div>
  </div>
  <?php endif; ?>
</aside><!-- End Sidebar -->
