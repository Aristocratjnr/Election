<!doctype html>
<html
  lang="en"
  class="layout-navbar-fixed layout-wide"
  dir="ltr"
  data-skin="default"
  data-assets-path="assets/"
  data-template="front-pages"
  data-bs-theme="light">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Privacy Policy - SmartVote</title>

    <meta name="description" content="Privacy policy explaining how SmartVote collects, uses, and protects your personal information and voting data" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

    <!-- Fonts -->    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />
    
    <!-- Icon Fonts with Multiple CDN Fallbacks -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/vendor/css/pages/front-page.css" />
    <link rel="stylesheet" href="assets/css/modern-ui.css" />
    <link rel="stylesheet" href="assets/css/enhanced-navbar.css" />
    
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/front-config.js"></script>
    
    <!-- Immediate Icon Fallback Script -->
    <script>
      // Immediate fallback system to ensure icons show as soon as possible
      (function() {
        const iconMap = {
          'bx-shield-check': '🛡️', 'bx-info-circle': 'ℹ️', 'bx-user': '👤', 'bx-data': '📊',
          'bx-check': '✓', 'bx-lock-alt': '🔒', 'bx-server': '🖥️', 'bx-user-check': '👤✓',
          'bx-certification': '📜', 'bx-cog': '⚙️', 'bx-support': '💬', 'bx-chart': '📈',
          'bx-search': '🔍', 'bx-edit': '✏️', 'bx-trash': '🗑️', 'bx-download': '⬇️',
          'bx-block': '🚫', 'bx-x-circle': '❌', 'bx-envelope': '✉️', 'bx-phone': '📞',
          'bx-map': '📍', 'bx-time': '⏰', 'bx-file-blank': '📄', 'bx-home': '🏠',
          'bx-credit-card': '💳', 'bx-menu': '☰', 'bx-sun': '☀️', 'bx-moon': '🌙',
          'bx-desktop': '💻', 'bx-log-in-circle': '🔑'
        };
        
        function applyQuickFallbacks() {
          Object.keys(iconMap).forEach(cls => {
            const elements = document.querySelectorAll('.' + cls);
            elements.forEach(el => {
              if (!el.textContent.trim()) {
                el.textContent = iconMap[cls];
                el.style.fontFamily = 'system-ui, sans-serif';
              }
            });
          });
        }
        
        // Apply immediately and on DOM ready
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', applyQuickFallbacks);
        } else {
          applyQuickFallbacks();
        }
        
        // Also apply on page load
        window.addEventListener('load', applyQuickFallbacks);
      })();
    </script>
    
    <style>
      /* Enhanced Icon font fallbacks and display fixes */
      @font-face {
        font-family: 'boxicons-fallback';
        src: url('https://unpkg.com/boxicons@2.1.4/fonts/boxicons.woff2') format('woff2'),
             url('https://unpkg.com/boxicons@2.1.4/fonts/boxicons.woff') format('woff'),
             url('https://unpkg.com/boxicons@2.1.4/fonts/boxicons.ttf') format('truetype');
        font-display: swap;
      }
      
      [class^="bx-"], [class*=" bx-"], .bx {
        font-family: 'boxicons', 'boxicons-fallback', 'bootstrap-icons', sans-serif !important;
        font-style: normal;
        font-weight: normal;
        font-variant: normal;
        text-transform: none;
        line-height: 1;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        display: inline-block;
        vertical-align: middle;
        min-width: 1em;
        min-height: 1em;
        position: relative;
      }
      
      /* Ensure icons are always visible */
      .bx {
        min-width: 1em;
        min-height: 1em;
        position: relative;
      }
      
      /* Fallback for missing icons */
      .bx:before {
        content: attr(data-fallback);
        display: inline-block;
        font-weight: normal;
        font-family: system-ui, -apple-system, sans-serif;
      }
      
      /* Immediate visibility for icons with CSS fallbacks */
      .bx:empty:after {
        content: "📄";
        font-family: system-ui, sans-serif !important;
        display: inline-block;
        font-size: 1em;
        line-height: 1;
      }
      
      /* Specific immediate fallbacks for common icons */
      .bx-shield-check:empty:after { content: "🛡️"; }
      .bx-info-circle:empty:after { content: "ℹ️"; }
      .bx-user:empty:after { content: "👤"; }
      .bx-envelope:empty:after { content: "✉️"; }
      .bx-phone:empty:after { content: "📞"; }
      .bx-map:empty:after { content: "📍"; }
      .bx-time:empty:after { content: "⏰"; }
      .bx-home:empty:after { content: "🏠"; }
      .bx-menu:empty:after { content: "☰"; }
      .bx-check:empty:after { content: "✓"; }
      .bx-lock-alt:empty:after { content: "🔒"; }
      .bx-file-blank:empty:after { content: "📄"; }
      .bx-credit-card:empty:after { content: "💳"; }
      .bx-sun:empty:after { content: "☀️"; }
      .bx-moon:empty:after { content: "🌙"; }
      .bx-desktop:empty:after { content: "💻"; }
      .bx-log-in-circle:empty:after { content: "🔑"; }
      
      /* Force icon display with text fallbacks if fonts fail */
      .icon-fallback {
        font-family: system-ui, -apple-system, sans-serif !important;
        font-size: 1em !important;
        line-height: 1 !important;
        font-weight: normal !important;
      }
      
      /* Page-specific styles */
      .privacy-page-container {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.03) 0%, rgba(var(--bs-info-rgb), 0.03) 100%);
        position: relative;
        overflow: hidden;
      }
      
      .privacy-page-container::before,
      .privacy-page-container::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        z-index: 0;
      }
      
      .privacy-page-container::before {
        width: 500px;
        height: 500px;
        background: radial-gradient(rgba(var(--bs-success-rgb), 0.05), transparent 70%);
        top: -250px;
        left: -100px;
      }
      
      .privacy-page-container::after {
        width: 400px;
        height: 400px;
        background: radial-gradient(rgba(var(--bs-info-rgb), 0.05), transparent 70%);
        bottom: -200px;
        right: -100px;
      }
      
      .privacy-card {
        border-radius: 1.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(var(--bs-success-rgb), 0.1);
        position: relative;
        z-index: 1;
      }
      
      .privacy-content section {
        padding: 2rem;
        border-radius: 1rem;
        transition: all 0.3s ease;
        margin-bottom: 2rem;
      }
      
      .privacy-content section:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      }
      
      .privacy-content h3 {
        position: relative;
        padding-left: 2rem;
      }
      
      .privacy-content h3::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 30px;
        background: linear-gradient(45deg, var(--bs-success), var(--bs-info));
        border-radius: 2px;
      }
      
      .data-protection-card {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.1) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
        border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
      }
      
      .data-protection-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(var(--bs-success-rgb), 0.15);
      }
      
      .data-protection-card i {
        display: block;
        margin-bottom: 0.75rem;
        color: var(--bs-success);
      }
      
      .data-type-card {
        border: 2px solid var(--bs-border-color);
        border-radius: 1rem;
        transition: all 0.3s ease;
        height: 100%;
      }
      
      .data-type-card:hover {
        border-color: var(--bs-success);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(var(--bs-success-rgb), 0.1);
      }
      
      .rights-feature {
        background-color: rgba(var(--bs-info-rgb), 0.1);
        border: 1px solid rgba(var(--bs-info-rgb), 0.2);
        border-radius: 0.75rem;
        padding: 1rem;
        transition: all 0.3s ease;
      }
      
      .rights-feature:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(var(--bs-info-rgb), 0.15);
      }
      
      .cookie-table {
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      }
      
      .cookie-table th {
        background-color: var(--bs-success);
        color: white;
        border: none;
        font-weight: 600;
      }
      
      .cookie-table td {
        border: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        vertical-align: middle;
      }
      
      .contact-dpo-card {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.05) 0%, rgba(var(--bs-info-rgb), 0.05) 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid rgba(var(--bs-success-rgb), 0.1);
      }
      
      /* Animations */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      .animate-fade-in-up {
        animation: fadeInUp 0.5s ease forwards;
      }
      
      /* Dark mode adjustments */
      [data-bs-theme="dark"] .privacy-page-container {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.1) 0%, rgba(0,0,0,0) 100%);
      }
      
      [data-bs-theme="dark"] .privacy-card {
        background-color: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
      }
      
      [data-bs-theme="dark"] .privacy-content section {
        border-left-color: var(--bs-success);
      }
      
      [data-bs-theme="dark"] .privacy-content section:hover {
        background-color: rgba(255, 255, 255, 0.08);
      }
      
      [data-bs-theme="dark"] .data-protection-card {
        background: rgba(var(--bs-success-rgb), 0.15);
        border-color: rgba(var(--bs-success-rgb), 0.3);
      }
      
      [data-bs-theme="dark"] .data-type-card {
        background-color: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
      }
      
      [data-bs-theme="dark"] .rights-feature {
        background-color: rgba(var(--bs-info-rgb), 0.15);
        border-color: rgba(var(--bs-info-rgb), 0.3);
      }
      
      [data-bs-theme="dark"] .cookie-table th {
        background-color: var(--bs-success);
      }
      
      [data-bs-theme="dark"] .cookie-table td {
        border-color: rgba(255, 255, 255, 0.05);
      }
      
      [data-bs-theme="dark"] .contact-dpo-card {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
      }
      
      /* Table of contents */
      .toc-nav {
        position: sticky;
        top: 120px;
        background: var(--bs-body-bg);
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid rgba(var(--bs-success-rgb), 0.1);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      }
      
      .toc-nav .nav-link {
        color: var(--bs-body-color);
        padding: 0.5rem 0;
        border: none;
        border-radius: 0;
        border-left: 2px solid transparent;
        padding-left: 1rem;
        transition: all 0.3s ease;
      }
      
      .toc-nav .nav-link:hover,
      .toc-nav .nav-link.active {
        color: var(--bs-success);
        border-left-color: var(--bs-success);
        background-color: rgba(var(--bs-success-rgb), 0.05);
      }
      
      /* Responsive adjustments */
      @media (max-width: 991.98px) {
        .toc-nav {
          position: relative;
          top: auto;
          margin-bottom: 2rem;
        }
        
        .privacy-content section {
          padding: 1.5rem;
        }
      }
      
      /* Theme Toggle Styles */
      .theme-switch-wrapper {
        position: relative;
      }
      
      .theme-toggle-wrapper {
        display: flex;
        align-items: center;
      }
      
      .theme-toggle {
        position: relative;
        width: 50px;
        height: 25px;
        background: var(--bs-border-color);
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid var(--bs-border-color);
      }
      
      .theme-toggle:hover {
        background: var(--bs-secondary);
      }
      
      .theme-toggle-track {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: inherit;
        overflow: hidden;
      }
      
      .theme-toggle-sun,
      .theme-toggle-moon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        transition: all 0.3s ease;
        z-index: 1;
      }
      
      .theme-toggle-sun {
        left: 6px;
        color: #ffc107;
        opacity: 1;
      }
      
      .theme-toggle-moon {
        right: 6px;
        color: #6f42c1;
        opacity: 0.3;
      }
      
      .theme-toggle-thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 21px;
        height: 21px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 2;
      }
      
      /* Dark mode toggle state */
      .theme-toggle-dark,
      .theme-toggle[aria-checked="true"] {
        background: var(--bs-primary);
      }
      
      .theme-toggle-dark .theme-toggle-thumb,
      .theme-toggle[aria-checked="true"] .theme-toggle-thumb {
        left: 27px;
        background: white;
      }
      
      .theme-toggle-dark .theme-toggle-sun,
      .theme-toggle[aria-checked="true"] .theme-toggle-sun {
        opacity: 0.3;
      }
      
      .theme-toggle-dark .theme-toggle-moon,
      .theme-toggle[aria-checked="true"] .theme-toggle-moon {
        opacity: 1;
      }
      
      /* Enhanced dropdown styling */
      .enhanced-dropdown {
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        padding: 0.5rem;
        min-width: 160px;
      }
      
      .enhanced-dropdown .dropdown-item {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
      }
      
      .enhanced-dropdown .dropdown-item:hover,
      .enhanced-dropdown .dropdown-item.active {
        background-color: var(--bs-primary);
        color: white;
        transform: translateY(-1px);
      }
      
      .enhanced-dropdown .dropdown-item i {
        width: 16px;
        text-align: center;
      }
      
      /* Dark mode dropdown adjustments */
      [data-bs-theme="dark"] .enhanced-dropdown {
        background-color: var(--bs-dark);
        border-color: var(--bs-border-color);
      }
      
      [data-bs-theme="dark"] .theme-toggle {
        background: var(--bs-border-color);
        border-color: var(--bs-border-color);
      }
      
      [data-bs-theme="dark"] .theme-toggle-thumb {
        background: var(--bs-light);
      }
    </style>
  </head>

  <body>
    <!-- Navbar: Start -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary py-0">
      <div class="container">
        <!-- Brand & Mobile Toggle -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
          <img src="assets/img/favicon/favicon.ico" alt="logo" width="30" height="30" class="me-2">
          <span class="d-none d-sm-inline fw-bold brand-text">SmartVote</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
          <i class="bx bx-menu fs-3"></i>
        </button>

        <!-- Collapsible Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php#landingFeatures">Features</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php#landingPricing">Pricing</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index.php#landingContact">Contact</a>
            </li>
          </ul>

          <!-- Right Side Items -->
          <ul class="navbar-nav ms-auto align-items-center">
            <!-- Theme Switcher -->
            <li class="nav-item dropdown me-3 theme-switch-wrapper">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="theme-toggle-wrapper me-2">
                  <div class="theme-toggle" role="switch" aria-checked="false" tabindex="0">
                    <div class="theme-toggle-track">
                      <i class="bx bx-sun theme-toggle-sun"></i>
                      <i class="bx bx-moon theme-toggle-moon"></i>
                    </div>
                    <div class="theme-toggle-thumb"></div>
                  </div>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end enhanced-dropdown" aria-labelledby="themeDropdown">
                <li>
                  <button class="dropdown-item d-flex align-items-center theme-item" type="button" data-bs-theme-value="light">
                    <i class="bx bx-sun me-2"></i>
                    <span>Light</span>
                  </button>
                </li>
                <li>
                  <button class="dropdown-item d-flex align-items-center theme-item" type="button" data-bs-theme-value="dark">
                    <i class="bx bx-moon me-2"></i>
                    <span>Dark</span>
                  </button>
                </li>
                <li>
                  <button class="dropdown-item d-flex align-items-center theme-item" type="button" data-bs-theme-value="auto">
                    <i class="bx bx-desktop me-2"></i>
                    <span>System</span>
                  </button>
                </li>
              </ul>
            </li>

            <!-- Auth Buttons -->
            <li class="nav-item d-flex flex-wrap gap-2">
              <a href="login.php" class="btn btn-outline-primary navbar-cta">
                <i class="bx bx-log-in-circle d-none d-lg-inline me-1"></i>
                <span>Login</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- Navbar: End -->

    <!-- Main Content -->
    <div class="container-fluid py-5 privacy-page-container">
      <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-5 animate-fade-in-up">
          <span class="badge bg-label-success rounded-pill px-3 py-2 mb-2">Privacy & Security</span>
          <h1 class="fw-bold">Privacy Policy</h1>
          <p class="text-muted">Your privacy is important to us. Learn how we protect your data.</p>
          <div class="d-flex justify-content-center align-items-center gap-2 mt-3">
            <i class="bx bx-time text-muted"></i>
            <small class="text-muted">Effective Date: August 31, 2025</small>
          </div>
        </div>
        
        <div class="row g-4">
          <!-- Table of Contents -->
          <div class="col-lg-3">
            <div class="toc-nav animate-fade-in-up" style="animation-delay: 0.1s;">
              <h5 class="fw-bold mb-3">Contents</h5>
              <nav class="nav nav-pills flex-column">
                <a class="nav-link active" href="#information">1. Information We Collect</a>
                <a class="nav-link" href="#usage">2. How We Use Information</a>
                <a class="nav-link" href="#protection">3. Data Protection</a>
                <a class="nav-link" href="#sharing">4. Information Sharing</a>
                <a class="nav-link" href="#rights">5. Your Rights</a>
                <a class="nav-link" href="#cookies">6. Cookies & Tracking</a>
                <a class="nav-link" href="#contact">7. Contact & DPO</a>
              </nav>
            </div>
          </div>
          
          <!-- Privacy Content -->
          <div class="col-lg-9">
            <div class="card privacy-card border-0 shadow-sm animate-fade-in-up" style="animation-delay: 0.3s;">
              <div class="card-body p-0">
                <div class="privacy-content">
                  <div class="alert alert-info d-flex align-items-center mx-4 mt-4 mb-4">
                    <i class="bi bi-shield-check fs-4 me-3"></i>
                    <div>
                      <strong>Your Privacy Matters:</strong> We are committed to protecting your personal information.<br>
                      <small>This policy explains how we collect, use, and safeguard your data when using SmartVote.</small>
                    </div>
                  </div>

                  <section id="information">
                    <h3 class="fw-bold text-success mb-3">1. Information We Collect</h3>
                    <p>We collect different types of information to provide and improve our voting platform services:</p>
                    <div class="row g-4 mt-3">
                      <div class="col-md-6">
                        <div class="data-type-card border-primary">
                          <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0"><i class="bx bx-user me-2"></i>Personal Information</h6>
                          </div>
                          <div class="card-body">
                            <ul class="list-unstyled mb-0">
                              <li><i class="bx bx-check text-success me-2"></i>Full name and contact details</li>
                              <li><i class="bx bx-check text-success me-2"></i>Email address and phone number</li>
                              <li><i class="bx bx-check text-success me-2"></i>Organization/institution information</li>
                              <li><i class="bx bx-check text-success me-2"></i>Payment and billing information</li>
                              <li><i class="bx bx-check text-success me-2"></i>Account preferences and settings</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="data-type-card border-info">
                          <div class="card-header bg-info text-white py-3">
                            <h6 class="mb-0"><i class="bx bx-data me-2"></i>Usage Information</h6>
                          </div>
                          <div class="card-body">
                            <ul class="list-unstyled mb-0">
                              <li><i class="bx bx-check text-success me-2"></i>Device and browser information</li>
                              <li><i class="bx bx-check text-success me-2"></i>IP address and general location</li>
                              <li><i class="bx bx-check text-success me-2"></i>Platform usage patterns</li>
                              <li><i class="bx bx-check text-success me-2"></i>Performance and error logs</li>
                              <li><i class="bx bx-check text-success me-2"></i>Security and audit information</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="alert alert-warning mt-4">
                      <h6 class="alert-heading"><i class="bx bx-info-circle me-2"></i>Voting Data Protection</h6>
                      <p class="mb-0">All voting data is encrypted and anonymized. We cannot link votes to individual voters, ensuring complete ballot secrecy while maintaining election integrity.</p>
                    </div>
                  </section>

                  <section id="usage">
                    <h3 class="fw-bold text-success mb-3">2. How We Use Your Information</h3>
                    <p>We use your information responsibly and only for legitimate business purposes:</p>
                    <div class="row g-3 mt-3">
                      <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                          <div class="flex-shrink-0">
                            <i class="bx bx-cog fs-3 text-primary bg-light rounded-circle p-2"></i>
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Service Provision</h6>
                            <p class="mb-0 small text-muted">To provide, maintain, and improve our voting platform and related services</p>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                          <div class="flex-shrink-0">
                            <i class="bx bx-support fs-3 text-info bg-light rounded-circle p-2"></i>
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Customer Support</h6>
                            <p class="mb-0 small text-muted">To respond to inquiries, provide technical support, and resolve issues</p>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                          <div class="flex-shrink-0">
                            <i class="bx bx-chart fs-3 text-success bg-light rounded-circle p-2"></i>
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Service Improvement</h6>
                            <p class="mb-0 small text-muted">To analyze usage patterns and improve platform performance and features</p>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                          <div class="flex-shrink-0">
                            <i class="bi bi-shield-check fs-3 text-warning bg-light rounded-circle p-2"></i>
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Security & Compliance</h6>
                            <p class="mb-0 small text-muted">To protect against fraud, ensure security, and comply with legal obligations</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section id="protection">
                    <h3 class="fw-bold text-success mb-3">3. Data Protection Measures</h3>
                    <p>We implement comprehensive security measures to protect your information:</p>
                    <div class="row g-4 mt-3">
                      <div class="col-md-3 col-sm-6">
                        <div class="data-protection-card">
                          <i class="bx bx-lock-alt fs-1"></i>
                          <h6>Encryption</h6>
                          <p class="small mb-0">All data encrypted in transit and at rest using AES-256 encryption</p>
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="data-protection-card">
                          <i class="bx bx-server fs-1"></i>
                          <h6>Secure Infrastructure</h6>
                          <p class="small mb-0">Data stored on secure, monitored servers with regular backups</p>
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="data-protection-card">
                          <i class="bx bx-user-check fs-1"></i>
                          <h6>Access Control</h6>
                          <p class="small mb-0">Strict access controls with multi-factor authentication</p>
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="data-protection-card">
                          <i class="bx bx-certification fs-1"></i>
                          <h6>Compliance</h6>
                          <p class="small mb-0">GDPR compliant with regular security audits and assessments</p>
                        </div>
                      </div>
                    </div>
                    <div class="alert alert-success mt-4">
                      <h6 class="alert-heading"><i class="bi bi-shield-check me-2"></i>Blockchain Security</h6>
                      <p class="mb-0">All votes are recorded on an immutable blockchain, providing transparent verification while maintaining voter anonymity through advanced cryptographic techniques.</p>
                    </div>
                  </section>

                  <section id="sharing">
                    <h3 class="fw-bold text-success mb-3">4. Information Sharing</h3>
                    <div class="alert alert-warning">
                      <h6 class="alert-heading"><i class="bx bx-info-circle me-2"></i>We Do NOT Sell Your Data</h6>
                      <p class="mb-2">We never sell, rent, or trade your personal information to third parties. We only share data in these specific circumstances:</p>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <ul class="mb-0">
                            <li><i class="bx bx-check text-success me-2"></i>With your explicit written consent</li>
                            <li><i class="bx bx-check text-success me-2"></i>To comply with legal obligations or court orders</li>
                          </ul>
                        </div>
                        <div class="col-md-6">
                          <ul class="mb-0">
                            <li><i class="bx bx-check text-success me-2"></i>With trusted service providers under strict agreements</li>
                            <li><i class="bx bx-check text-success me-2"></i>To protect our rights and prevent fraud or abuse</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <p class="mt-3">All third-party service providers we work with are contractually bound to maintain the same level of data protection and are regularly audited for compliance.</p>
                  </section>

                  <section id="rights">
                    <h3 class="fw-bold text-success mb-3">5. Your Rights</h3>
                    <p>Under GDPR and other privacy laws, you have several important rights regarding your personal data:</p>
                    <div class="row g-3 mt-3">
                      <div class="col-md-6">
                        <div class="rights-feature">
                          <h6><i class="bx bx-search text-info me-2"></i>Right to Access</h6>
                          <p class="small mb-0">Request a copy of all personal data we hold about you</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="rights-feature">
                          <h6><i class="bx bx-edit text-info me-2"></i>Right to Rectification</h6>
                          <p class="small mb-0">Correct any inaccurate or incomplete personal information</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="rights-feature">
                          <h6><i class="bx bx-trash text-info me-2"></i>Right to Erasure</h6>
                          <p class="small mb-0">Request deletion of your personal data under certain conditions</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="rights-feature">
                          <h6><i class="bx bx-download text-info me-2"></i>Right to Data Portability</h6>
                          <p class="small mb-0">Export your data in a machine-readable format</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="rights-feature">
                          <h6><i class="bx bx-block text-info me-2"></i>Right to Object</h6>
                          <p class="small mb-0">Object to certain types of data processing</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="rights-feature">
                          <h6><i class="bx bx-x-circle text-info me-2"></i>Right to Withdraw Consent</h6>
                          <p class="small mb-0">Withdraw your consent for data processing at any time</p>
                        </div>
                      </div>
                    </div>
                    <div class="alert alert-info mt-4">
                      <h6 class="alert-heading">How to Exercise Your Rights</h6>
                      <p class="mb-2">To exercise any of these rights, contact our Data Protection Officer at:</p>
                      <p class="mb-0"><strong>Email:</strong> privacy@smartvote.com | <strong>Response time:</strong> Within 30 days</p>
                    </div>
                  </section>

                  <section id="cookies">
                    <h3 class="fw-bold text-success mb-3">6. Cookies and Tracking Technologies</h3>
                    <p>We use cookies and similar technologies to enhance your experience and improve our services:</p>
                    <div class="table-responsive mt-3">
                      <table class="table cookie-table">
                        <thead>
                          <tr>
                            <th>Cookie Type</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                            <th>Required</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td><span class="badge bg-primary">Essential</span></td>
                            <td>Platform functionality, security, and user authentication</td>
                            <td>Session only</td>
                            <td><i class="bx bx-check text-success"></i></td>
                          </tr>
                          <tr>
                            <td><span class="badge bg-info">Analytics</span></td>
                            <td>Usage statistics and performance monitoring</td>
                            <td>30 days</td>
                            <td><i class="bx bx-x text-danger"></i></td>
                          </tr>
                          <tr>
                            <td><span class="badge bg-success">Preferences</span></td>
                            <td>Remember your settings and preferences</td>
                            <td>1 year</td>
                            <td><i class="bx bx-x text-danger"></i></td>
                          </tr>
                          <tr>
                            <td><span class="badge bg-warning">Marketing</span></td>
                            <td>Personalized content and relevant communications</td>
                            <td>6 months</td>
                            <td><i class="bx bx-x text-danger"></i></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <p class="mt-3">You can manage your cookie preferences through your browser settings or our cookie consent manager. Note that disabling essential cookies may affect platform functionality.</p>
                  </section>

                  <section id="contact">
                    <h3 class="fw-bold text-success mb-3">7. Contact & Data Protection Officer</h3>
                    <div class="contact-dpo-card">
                      <div class="row g-4 align-items-center">
                        <div class="col-md-8">
                          <h5 class="mb-3">Privacy Questions or Concerns?</h5>
                          <p class="mb-3">Our dedicated Data Protection Officer and privacy team are here to help with any questions about your data or this privacy policy.</p>
                          <div class="d-flex flex-column gap-2">
                            <div><i class="bx bx-envelope text-success me-2"></i><strong>Privacy Team:</strong> privacy@smartvote.com</div>
                            <div><i class="bx bx-user text-info me-2"></i><strong>Data Protection Officer:</strong> Dr. Sarah Mensah</div>
                            <div><i class="bx bx-phone text-warning me-2"></i><strong>Privacy Hotline:</strong> +233 551 784 926</div>
                            <div><i class="bx bx-map text-primary me-2"></i><strong>Address:</strong> SmartVote Privacy Office, Accra, Ghana</div>
                            <div><i class="bx bx-time text-muted me-2"></i><strong>Response Time:</strong> Within 30 days for all privacy requests</div>
                          </div>
                        </div>
                        <div class="col-md-4 text-center">
                          <i class="bi bi-shield-check fs-1 text-success mb-3"></i>
                          <h6>GDPR Compliance</h6>
                          <p class="text-muted mb-3">Certified data protection practices</p>
                          <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-success">GDPR</span>
                            <span class="badge bg-info">ISO 27001</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="alert alert-info mt-4">
                      <h6 class="alert-heading">Privacy Policy Updates</h6>
                      <p class="mb-0">We may update this privacy policy from time to time. We will notify you of any material changes via email and on our platform. Your continued use after such notice constitutes acceptance of the updated policy.</p>
                    </div>
                  </section>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center mt-4 animate-fade-in-up" style="animation-delay: 0.5s;">
              <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="terms.php" class="btn btn-outline-success">
                  <i class="bx bx-file-blank me-1"></i>Terms & Conditions
                </a>
                <a href="payment-page.php" class="btn btn-success">
                  <i class="bx bx-credit-card me-1"></i>Subscribe Securely
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                  <i class="bx bx-home me-1"></i>Back to Home
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-4 mt-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
            <p class="mb-0">&copy; <script>document.write(new Date().getFullYear())</script> SmartVote. All rights reserved.</p>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-center justify-content-md-end gap-3">
              <a href="terms.php" class="text-muted">Terms</a>
              <a href="privacy.php" class="text-muted">Privacy</a>
              <a href="index.php#landingContact" class="text-muted">Support</a>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Core JS -->
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    
    <!-- Theme Switcher JS -->
    <script>
      // Theme switching functionality
      (function() {
        'use strict';
        
        // Get stored theme or default to light
        const getStoredTheme = () => localStorage.getItem('theme');
        const setStoredTheme = theme => localStorage.setItem('theme', theme);
        
        const getPreferredTheme = () => {
          const storedTheme = getStoredTheme();
          if (storedTheme) {
            return storedTheme;
          }
          return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
        
        const setTheme = theme => {
          if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
          } else {
            document.documentElement.setAttribute('data-bs-theme', theme);
          }
          updateThemeToggle(theme);
        };
        
        const updateThemeToggle = theme => {
          const themeToggle = document.querySelector('.theme-toggle');
          const themeItems = document.querySelectorAll('.theme-item');
          
          // Update toggle switch appearance
          if (themeToggle) {
            const isDark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            themeToggle.setAttribute('aria-checked', isDark);
            themeToggle.classList.toggle('theme-toggle-dark', isDark);
          }
          
          // Update dropdown items
          themeItems.forEach(item => {
            const itemTheme = item.getAttribute('data-bs-theme-value');
            item.classList.toggle('active', itemTheme === theme);
          });
        };
        
        // Set initial theme
        setTheme(getPreferredTheme());
        
        // Listen for theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
          const storedTheme = getStoredTheme();
          if (storedTheme !== 'light' && storedTheme !== 'dark') {
            setTheme(getPreferredTheme());
          }
        });
        
        // Handle theme selection
        document.addEventListener('DOMContentLoaded', () => {
          const themeItems = document.querySelectorAll('.theme-item');
          const themeToggle = document.querySelector('.theme-toggle');
          
          themeItems.forEach(item => {
            item.addEventListener('click', () => {
              const theme = item.getAttribute('data-bs-theme-value');
              setStoredTheme(theme);
              setTheme(theme);
            });
          });
          
          // Toggle switch click handler
          if (themeToggle) {
            themeToggle.addEventListener('click', () => {
              const currentTheme = document.documentElement.getAttribute('data-bs-theme');
              const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
              setStoredTheme(newTheme);
              setTheme(newTheme);
            });
          }
          
          // Initialize theme display
          updateThemeToggle(getStoredTheme() || getPreferredTheme());
        });
      })();
    </script>
    
    <!-- Page JS -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Smooth scrolling for table of contents
        const tocLinks = document.querySelectorAll('.toc-nav .nav-link');
        const sections = document.querySelectorAll('.privacy-content section');
        
        // Update active link on scroll
        function updateActiveLink() {
          let current = '';
          sections.forEach(section => {
            const sectionTop = section.offsetTop - 150;
            const sectionHeight = section.offsetHeight;
            if (pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
              current = section.getAttribute('id');
            }
          });
          
          tocLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
              link.classList.add('active');
            }
          });
        }
        
        // Smooth scroll on click
        tocLinks.forEach(link => {
          link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
              window.scrollTo({
                top: targetSection.offsetTop - 120,
                behavior: 'smooth'
              });
            }
          });
        });
        
        // Listen for scroll events
        window.addEventListener('scroll', updateActiveLink);
        
        // Initialize active link
        updateActiveLink();
        
        // Animate sections on scroll
        const observerOptions = {
          threshold: 0.1,
          rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.style.opacity = '1';
              entry.target.style.transform = 'translateY(0)';
            }
          });
        }, observerOptions);
        
        sections.forEach(section => {
          section.style.opacity = '0';
          section.style.transform = 'translateY(20px)';
          section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
          observer.observe(section);
        });
        
        // Icon font fallback check
        checkIconFonts();
      });
      
      // Function to check if icon fonts are loaded and provide fallbacks
      function checkIconFonts() {
        // Check if Boxicons font is loaded
        const testElement = document.createElement('span');
        testElement.className = 'bx bx-test';
        testElement.style.position = 'absolute';
        testElement.style.left = '-9999px';
        document.body.appendChild(testElement);
        
        setTimeout(() => {
          const styles = window.getComputedStyle(testElement);
          const fontFamily = styles.getPropertyValue('font-family');
          
          if (!fontFamily.includes('boxicons')) {
            console.warn('Boxicons font not loaded, applying fallbacks');
            addIconFallbacks();
          }
          
          document.body.removeChild(testElement);
        }, 100);
      }
      
      // Enhanced function to add text fallbacks for icons
      function addIconFallbacks() {
        const iconMappings = {
          'bx-shield-check': '🛡️',
          'bx-info-circle': 'ℹ️',
          'bx-user': '👤',
          'bx-data': '📊',
          'bx-check': '✓',
          'bx-lock-alt': '🔒',
          'bx-server': '🖥️',
          'bx-user-check': '👤✓',
          'bx-certification': '📜',
          'bx-cog': '⚙️',
          'bx-support': '💬',
          'bx-chart': '📈',
          'bx-search': '🔍',
          'bx-edit': '✏️',
          'bx-trash': '🗑️',
          'bx-download': '⬇️',
          'bx-block': '🚫',
          'bx-x-circle': '❌',
          'bx-envelope': '✉️',
          'bx-phone': '📞',
          'bx-map': '📍',
          'bx-time': '⏰',
          'bx-file-blank': '📄',
          'bx-home': '🏠',
          'bx-credit-card': '💳',
          'bx-menu': '☰',
          'bx-sun': '☀️',
          'bx-moon': '🌙',
          'bx-desktop': '💻',
          'bx-log-in-circle': '🔑',
          'bx-x': '✕',
          'bx-check-circle': '✅',
          'bx-error': '❌',
          'bx-warning': '⚠️',
          'bx-bell': '🔔',
          'bx-star': '⭐',
          'bx-heart': '❤️',
          'bx-bookmark': '🔖',
          'bx-share': '📤',
          'bx-link': '🔗',
          'bx-copy': '📋',
          'bx-print': '🖨️',
          'bx-refresh': '🔄',
          'bx-upload': '📤',
          'bx-folder': '📁',
          'bx-archive': '📦',
          'bx-calendar': '📅',
          'bx-clock': '🕐',
          'bx-location': '📍',
          'bx-globe': '🌐'
        };
        
        // Apply fallbacks to all matching elements
        Object.keys(iconMappings).forEach(iconClass => {
          const elements = document.querySelectorAll(`.${iconClass}`);
          elements.forEach(el => {
            if (el.textContent.trim() === '' || el.textContent === '?') {
              el.textContent = iconMappings[iconClass];
              el.style.fontFamily = 'system-ui, -apple-system, sans-serif';
              el.classList.add('icon-fallback');
            }
          });
        });
        
        // Also check for generic .bx elements without specific classes
        const genericBxElements = document.querySelectorAll('.bx:not([class*="bx-"])');
        genericBxElements.forEach(el => {
          if (el.textContent.trim() === '') {
            el.textContent = '📄'; // Generic document icon
            el.style.fontFamily = 'system-ui, -apple-system, sans-serif';
            el.classList.add('icon-fallback');
          }
        });
      }
      
      // Immediate icon check and fallback application
      function immediateIconCheck() {
        // Apply fallbacks immediately for better UX
        addIconFallbacks();
        
        // Re-check after a short delay to catch any dynamically loaded content
        setTimeout(addIconFallbacks, 500);
        setTimeout(addIconFallbacks, 1000);
      }
      
      // Run immediate check
      immediateIconCheck();
    </script>
  </body>
</html>
