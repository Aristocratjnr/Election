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

    <title>Terms & Conditions - SmartVote</title>

    <meta name="description" content="Terms and conditions for using SmartVote's digital voting platform and election management services" />

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
          'bx-shield-check': '', 'bx-info-circle': '', 'bx-user': '', 'bx-data': '',
          'bx-check': '', 'bx-lock-alt': '', 'bx-server': '', 'bx-user-check': '',
          'bx-certification': '', 'bx-cog': '', 'bx-support': '', 'bx-chart': '',
          'bx-search': '', 'bx-edit': '', 'bx-trash': '', 'bx-download': '',
          'bx-block': '', 'bx-x-circle': '', 'bx-envelope': '', 'bx-phone': '',
          'bx-map': '', 'bx-time': '', 'bx-file-blank': '', 'bx-home': '',
          'bx-credit-card': '', 'bx-menu': '', 'bx-sun': '', 'bx-moon': '',
          'bx-desktop': '', 'bx-log-in-circle': '', 'bx-dollar': '', 'bx-calendar': '',
          'bx-star': '', 'bx-award': '', 'bx-gift': '', 'bx-notification': '',
          'bx-dot-circle-2': '', 'bx-buildings': ''
        };
        
        // Bootstrap Icons mapping with Unicode values
        const biMapping = {
          'bx-shield-check': '\uF4FD', 'bx-info-circle': '\uF431', 'bx-user': '\uF4DA', 'bx-data': '\uF1C0',
          'bx-check': '\uF26A', 'bx-lock-alt': '\uF470', 'bx-server': '\uF4F8', 'bx-user-check': '\uF4DC',
          'bx-certification': '\uF1F9', 'bx-cog': '\uF3E2', 'bx-support': '\uF268', 'bx-chart': '\uF1C0',
          'bx-search': '\uF52D', 'bx-edit': '\uF4CA', 'bx-trash': '\uF5DE', 'bx-download': '\uF1C1',
          'bx-block': '\uF275', 'bx-x-circle': '\uF659', 'bx-envelope': '\uF32F', 'bx-phone': '\uF4B2',
          'bx-map': '\uF3F0', 'bx-time': '\uF292', 'bx-file-blank': '\uF4A5', 'bx-home': '\uF425',
          'bx-credit-card': '\uF2E1', 'bx-menu': '\uF479', 'bx-sun': '\uF5A1', 'bx-moon': '\uF48C',
          'bx-desktop': '\uF390', 'bx-log-in-circle': '\uF1C3', 'bx-dollar': '\uF2F0', 'bx-calendar': '\uF1F5',
          'bx-star': '\uF586', 'bx-award': '\uF1F9', 'bx-gift': '\uF3F4', 'bx-notification': '\uF1F6',
          'bx-dot-circle-2': '\uF287', 'bx-buildings': '\uF1FC'
        };
        
        function applyQuickFallbacks() {
          Object.keys(biMapping).forEach(cls => {
            const elements = document.querySelectorAll('.' + cls);
            elements.forEach(el => {
              if (!el.textContent.trim()) {
                el.textContent = biMapping[cls];
                el.style.fontFamily = 'bootstrap-icons, sans-serif';
                el.classList.add('bi');
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
        content: "\F4A5"; /* bi-file-earmark */
        font-family: 'bootstrap-icons', sans-serif !important;
        display: inline-block;
        font-size: 1em;
        line-height: 1;
      }
      
      /* Specific immediate fallbacks using Bootstrap Icons */
      .bx-shield-check:empty:after { content: "\F4FD"; font-family: 'bootstrap-icons', sans-serif; } /* bi-shield-check */
      .bx-info-circle:empty:after { content: "\F431"; font-family: 'bootstrap-icons', sans-serif; } /* bi-info-circle */
      .bx-user:empty:after { content: "\F4DA"; font-family: 'bootstrap-icons', sans-serif; } /* bi-person */
      .bx-envelope:empty:after { content: "\F32F"; font-family: 'bootstrap-icons', sans-serif; } /* bi-envelope */
      .bx-phone:empty:after { content: "\F4B2"; font-family: 'bootstrap-icons', sans-serif; } /* bi-telephone */
      .bx-map:empty:after { content: "\F3F0"; font-family: 'bootstrap-icons', sans-serif; } /* bi-geo-alt */
      .bx-time:empty:after { content: "\F292"; font-family: 'bootstrap-icons', sans-serif; } /* bi-clock */
      .bx-home:empty:after { content: "\F425"; font-family: 'bootstrap-icons', sans-serif; } /* bi-house */
      .bx-menu:empty:after { content: "\F479"; font-family: 'bootstrap-icons', sans-serif; } /* bi-list */
      .bx-check:empty:after { content: "\F26A"; font-family: 'bootstrap-icons', sans-serif; } /* bi-check */
      .bx-lock-alt:empty:after { content: "\F470"; font-family: 'bootstrap-icons', sans-serif; } /* bi-lock */
      .bx-file-blank:empty:after { content: "\F4A5"; font-family: 'bootstrap-icons', sans-serif; } /* bi-file-earmark */
      .bx-credit-card:empty:after { content: "\F2E1"; font-family: 'bootstrap-icons', sans-serif; } /* bi-credit-card */
      .bx-sun:empty:after { content: "\F5A1"; font-family: 'bootstrap-icons', sans-serif; } /* bi-sun */
      .bx-moon:empty:after { content: "\F48C"; font-family: 'bootstrap-icons', sans-serif; } /* bi-moon */
      .bx-desktop:empty:after { content: "\F390"; font-family: 'bootstrap-icons', sans-serif; } /* bi-display */
      .bx-log-in-circle:empty:after { content: "\F470"; font-family: 'bootstrap-icons', sans-serif; } /* bi-box-arrow-in-right */
      .bx-dollar:empty:after { content: "\F2F0"; font-family: 'bootstrap-icons', sans-serif; } /* bi-currency-dollar */
      .bx-calendar:empty:after { content: "\F1F5"; font-family: 'bootstrap-icons', sans-serif; } /* bi-calendar */
      .bx-star:empty:after { content: "\F586"; font-family: 'bootstrap-icons', sans-serif; } /* bi-star */
      .bx-award:empty:after { content: "\F1F9"; font-family: 'bootstrap-icons', sans-serif; } /* bi-award */
      .bx-gift:empty:after { content: "\F3F4"; font-family: 'bootstrap-icons', sans-serif; } /* bi-gift */
      .bx-notification:empty:after { content: "\F1F6"; font-family: 'bootstrap-icons', sans-serif; } /* bi-bell */
      .bx-dot-circle-2:empty:after { content: "\F287"; font-family: 'bootstrap-icons', sans-serif; } /* bi-circle-fill */
      .bx-buildings:empty:after { content: "\F1FC"; font-family: 'bootstrap-icons', sans-serif; } /* bi-building */
      .bx-support:empty:after { content: "\F268"; font-family: 'bootstrap-icons', sans-serif; } /* bi-chat-dots */
      .bx-server:empty:after { content: "\F4F8"; font-family: 'bootstrap-icons', sans-serif; } /* bi-server */
      .bx-user-check:empty:after { content: "\F4DC"; font-family: 'bootstrap-icons', sans-serif; } /* bi-person-check */
      .bx-edit:empty:after { content: "\F4CA"; font-family: 'bootstrap-icons', sans-serif; } /* bi-pencil */
      
      /* Force icon display with text fallbacks if fonts fail */
      .icon-fallback {
        font-family: system-ui, -apple-system, sans-serif !important;
        font-size: 1em !important;
        line-height: 1 !important;
        font-weight: normal !important;
      }
      
      /* Bootstrap Icons fallback */
      .bi {
        font-family: 'bootstrap-icons', sans-serif !important;
        font-style: normal;
        font-weight: normal;
        display: inline-block;
        vertical-align: middle;
      }
      
      /* Page-specific styles */
      .terms-page-container {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.03) 0%, rgba(var(--bs-info-rgb), 0.03) 100%);
        position: relative;
        overflow: hidden;
      }
      
      .terms-page-container::before,
      .terms-page-container::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        z-index: 0;
      }
      
      .terms-page-container::before {
        width: 500px;
        height: 500px;
        background: radial-gradient(rgba(var(--bs-primary-rgb), 0.05), transparent 70%);
        top: -250px;
        left: -100px;
      }
      
      .terms-page-container::after {
        width: 400px;
        height: 400px;
        background: radial-gradient(rgba(var(--bs-info-rgb), 0.05), transparent 70%);
        bottom: -200px;
        right: -100px;
      }
      
      .terms-card {
        border-radius: 1.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(var(--bs-primary-rgb), 0.1);
        position: relative;
        z-index: 1;
      }
      
      .terms-content section {
        padding: 2rem;
        border-radius: 1rem;
        transition: all 0.3s ease;
        margin-bottom: 2rem;
      }
      
      .terms-content section:hover {
        background-color: rgba(var(--bs-light-rgb), 0.5);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      }
      
      .terms-content h3 {
        position: relative;
        padding-left: 2rem;
      }
      
      .terms-content h3::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 30px;
        background: linear-gradient(45deg, var(--bs-primary), var(--bs-info));
        border-radius: 2px;
      }
      
      .highlight-box {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0.05) 100%);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
        border-radius: 1rem;
        padding: 1.5rem;
        margin: 1.5rem 0;
      }
      
      .plan-card {
        border: 2px solid var(--bs-border-color);
        border-radius: 1rem;
        transition: all 0.3s ease;
      }
      
      .plan-card:hover {
        border-color: var(--bs-primary);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(var(--bs-primary-rgb), 0.1);
      }
      
      .plan-card.featured {
        border-color: var(--bs-primary);
        box-shadow: 0 5px 15px rgba(var(--bs-primary-rgb), 0.15);
      }
      
      .security-feature {
        background-color: rgba(var(--bs-success-rgb), 0.1);
        border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        border-radius: 0.75rem;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
      }
      
      .security-feature:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(var(--bs-success-rgb), 0.15);
      }
      
      .security-feature i {
        display: block;
        margin-bottom: 0.75rem;
        color: var(--bs-success);
      }
      
      .contact-card {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05) 0%, rgba(var(--bs-info-rgb), 0.05) 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid rgba(var(--bs-primary-rgb), 0.1);
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
      [data-bs-theme="dark"] .terms-page-container {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(0,0,0,0) 100%);
      }
      
      [data-bs-theme="dark"] .terms-card {
        background-color: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
      }
      
      [data-bs-theme="dark"] .terms-content section {
      }
      
      [data-bs-theme="dark"] .terms-content section:hover {
        background-color: rgba(255, 255, 255, 0.08);
      }
      
      [data-bs-theme="dark"] .highlight-box {
        background: rgba(var(--bs-primary-rgb), 0.15);
        border-color: rgba(var(--bs-primary-rgb), 0.3);
      }
      
      [data-bs-theme="dark"] .plan-card {
        background-color: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.1);
      }
      
      [data-bs-theme="dark"] .security-feature {
        background-color: rgba(var(--bs-success-rgb), 0.15);
        border-color: rgba(var(--bs-success-rgb), 0.3);
      }
      
      [data-bs-theme="dark"] .contact-card {
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
        border: 1px solid rgba(var(--bs-primary-rgb), 0.1);
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
        color: var(--bs-primary);
        border-left-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.05);
      }
      
      /* Responsive adjustments */
      @media (max-width: 991.98px) {
        .toc-nav {
          position: relative;
          top: auto;
          margin-bottom: 2rem;
        }
        
        .terms-content section {
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
    <div class="container-fluid py-5 terms-page-container">
      <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-5 animate-fade-in-up">
          <span class="badge bg-label-primary rounded-pill px-3 py-2 mb-2">Legal Information</span>
          <h1 class="fw-bold">Terms & Conditions</h1>
          <p class="text-muted">Understanding our service agreement and your rights</p>
          <div class="d-flex justify-content-center align-items-center gap-2 mt-3">
            <i class="bx bx-time text-muted"></i>
            <small class="text-muted">Last Updated: August 31, 2025</small>
          </div>
        </div>
        
        <div class="row g-4">
          <!-- Table of Contents -->
          <div class="col-lg-3">
            <div class="toc-nav animate-fade-in-up" style="animation-delay: 0.1s;">
              <h5 class="fw-bold mb-3">Contents</h5>
              <nav class="nav nav-pills flex-column">
                <a class="nav-link active" href="#acceptance">1. Acceptance of Terms</a>
                <a class="nav-link" href="#services">2. Service Description</a>
                <a class="nav-link" href="#accounts">3. User Accounts</a>
                <a class="nav-link" href="#payment">4. Payment Terms</a>
                <a class="nav-link" href="#usage">5. Acceptable Use</a>
                <a class="nav-link" href="#security">6. Data Security</a>
                <a class="nav-link" href="#liability">7. Limitation of Liability</a>
                <a class="nav-link" href="#termination">8. Termination</a>
                <a class="nav-link" href="#contact">9. Contact Information</a>
              </nav>
            </div>
          </div>
          
          <!-- Terms Content -->
          <div class="col-lg-9">
            <div class="card terms-card border-0 shadow-sm animate-fade-in-up" style="animation-delay: 0.3s;">
              <div class="card-body p-0">
                <div class="terms-content">
                  <div class="alert alert-info d-flex align-items-center mx-4 mt-4 mb-4">
                    <i class="bx bx-info-circle fs-4 me-3"></i>
                    <div>
                      <strong>Important Notice:</strong> Please read these terms carefully before using our service.<br>
                      <small>By using SmartVote, you agree to be bound by these terms and conditions.</small>
                    </div>
                  </div>

                  <section id="acceptance">
                    <h3 class="fw-bold text-primary mb-3">1. Acceptance of Terms</h3>
                    <p>By accessing and using SmartVote's voting platform and services, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions. If you do not agree to these terms, please do not use our services.</p>
                    <p>These terms constitute a legally binding agreement between you and SmartVote. We reserve the right to modify these terms at any time, and your continued use of our services constitutes acceptance of any changes.</p>
                  </section>

                  <section id="services">
                    <h3 class="fw-bold text-primary mb-3">2. Service Description</h3>
                    <p>SmartVote provides comprehensive digital voting and election management services including:</p>
                    <div class="row g-3 mt-3">
                      <div class="col-md-6">
                        <ul class="list-unstyled">
                          <li><i class="bx bx-check text-success me-2"></i>Online voting platform</li>
                          <li><i class="bx bx-check text-success me-2"></i>Election administration tools</li>
                          <li><i class="bx bx-check text-success me-2"></i>Voter registration management</li>
                          <li><i class="bx bx-check text-success me-2"></i>Ballot design and customization</li>
                        </ul>
                      </div>
                      <div class="col-md-6">
                        <ul class="list-unstyled">
                          <li><i class="bx bx-check text-success me-2"></i>Real-time results and analytics</li>
                          <li><i class="bx bx-check text-success me-2"></i>Secure blockchain verification</li>
                          <li><i class="bx bx-check text-success me-2"></i>Mobile voting capabilities</li>
                          <li><i class="bx bx-check text-success me-2"></i>24/7 technical support</li>
                        </ul>
                      </div>
                    </div>
                    <p class="mt-3">Our services are designed to provide secure, transparent, and accessible voting solutions for educational institutions, organizations, and government bodies.</p>
                  </section>

                  <section id="accounts">
                    <h3 class="fw-bold text-primary mb-3">3. User Accounts and Registration</h3>
                    <p>To use our premium services, you must create an account and provide accurate information. You are responsible for:</p>
                    <div class="highlight-box">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <h6><i class="bi bi-person-check text-primary me-2"></i>Account Security</h6>
                          <ul class="list-unstyled ms-3">
                            <li><i class="bi bi-dot text-muted me-2"></i>Maintaining confidential credentials</li>
                            <li><i class="bi bi-dot text-muted me-2"></i>All activities under your account</li>
                            <li><i class="bi bi-dot text-muted me-2"></i>Immediate notification of breaches</li>
                          </ul>
                        </div>
                        <div class="col-md-6">
                          <h6><i class="bi bi-pencil text-info me-2"></i>Information Accuracy</h6>
                          <ul class="list-unstyled ms-3">
                            <li><i class="bi bi-dot text-muted me-2"></i>Providing accurate registration data</li>
                            <li><i class="bi bi-dot text-muted me-2"></i>Keeping information up to date</li>
                            <li><i class="bi bi-dot text-muted me-2"></i>Verifying organizational authority</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </section>

                  <section id="payment">
                    <h3 class="fw-bold text-primary mb-3">4. Payment Terms</h3>
                    <p>Our subscription-based pricing model offers flexible payment options:</p>
                    <div class="row g-4 mt-3">
                      <div class="col-md-6">
                        <div class="plan-card p-4 text-center">
                          <div class="badge bg-primary rounded-pill mb-3">Popular</div>
                          <h5 class="text-primary">Team Plan</h5>
                          <div class="display-6 fw-bold text-primary">₵29<small class="fs-6">/month</small></div>
                          <div class="text-muted">or ₵264/year (Save 25%)</div>
                          <hr>
                          <p class="small text-muted">Perfect for schools and small organizations</p>
                          <ul class="list-unstyled small">
                            <li>Up to 5,000 voters</li>
                            <li>Advanced analytics</li>
                            <li>Priority support</li>
                          </ul>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="plan-card featured p-4 text-center">
                          <div class="badge bg-success rounded-pill mb-3">Enterprise</div>
                          <h5 class="text-success">Enterprise Plan</h5>
                          <div class="display-6 fw-bold text-success">₵49<small class="fs-6">/month</small></div>
                          <div class="text-muted">or ₵444/year (Save 25%)</div>
                          <hr>
                          <p class="small text-muted">For large institutions with advanced needs</p>
                          <ul class="list-unstyled small">
                            <li>Unlimited voters</li>
                            <li>Custom integrations</li>
                            <li>Dedicated support</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <div class="alert alert-info mt-4">
                      <h6 class="alert-heading"><i class="bx bx-info-circle me-2"></i>Payment Policies</h6>
                      <ul class="mb-0">
                        <li>All payments are processed securely through encrypted channels</li>
                        <li>Subscriptions automatically renew unless cancelled 24 hours before renewal</li>
                        <li>Refunds are available within 30 days of initial purchase</li>
                        <li>Failed payments may result in service suspension after 7 days</li>
                      </ul>
                    </div>
                  </section>

                  <section id="usage">
                    <h3 class="fw-bold text-primary mb-3">5. Acceptable Use Policy</h3>
                    <p>You agree to use our services responsibly and ethically. The following activities are strictly prohibited:</p>
                    <div class="row g-3 mt-3">
                      <div class="col-md-6">
                        <div class="alert alert-danger">
                          <h6 class="alert-heading text-danger">Prohibited Activities</h6>
                          <ul class="mb-0 small">
                            <li>Fraudulent voting or manipulation</li>
                            <li>Harassment or abusive behavior</li>
                            <li>Spam or malicious content</li>
                            <li>Unauthorized system access attempts</li>
                            <li>Interference with service operations</li>
                          </ul>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="alert alert-success">
                          <h6 class="alert-heading text-success">Encouraged Practices</h6>
                          <ul class="mb-0 small">
                            <li>Fair and transparent elections</li>
                            <li>Respect for voter privacy</li>
                            <li>Compliance with local regulations</li>
                            <li>Proper voter education</li>
                            <li>Timely support communication</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <p class="mt-3">Violation of these policies may result in account suspension or termination without notice.</p>
                  </section>

                  <section id="security">
                    <h3 class="fw-bold text-primary mb-3">6. Data Security & Privacy</h3>
                    <p>We implement comprehensive security measures to protect your data and ensure election integrity:</p>
                    <div class="row g-3 mt-3">
                      <div class="col-md-3 col-sm-6">
                        <div class="security-feature">
                          <i class="bx bx-lock-alt fs-1"></i>
                          <h6>Encryption</h6>
                          <p class="small mb-0">End-to-end encryption for all votes and data</p>
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="security-feature">
                          <i class="bx bx-server fs-1"></i>
                          <h6>Blockchain</h6>
                          <p class="small mb-0">Immutable voting records with verification</p>
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="security-feature">
                          <i class="bi bi-shield-check fs-1"></i>
                          <h6>Compliance</h6>
                          <p class="small mb-0">GDPR and international privacy standards</p>
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6">
                        <div class="security-feature">
                          <i class="bx bx-user-check fs-1"></i>
                          <h6>Access Control</h6>
                          <p class="small mb-0">Multi-factor authentication and audit logs</p>
                        </div>
                      </div>
                    </div>
                    <p class="mt-3">For detailed information about data handling, please refer to our <a href="privacy.php" class="text-primary">Privacy Policy</a>.</p>
                  </section>

                  <section id="liability">
                    <h3 class="fw-bold text-primary mb-3">7. Limitation of Liability</h3>
                    <div class="highlight-box">
                      <p><strong>Service Disclaimer:</strong> SmartVote provides services "as is" and makes no warranties, express or implied, regarding service availability, accuracy, or fitness for a particular purpose.</p>
                      <p><strong>Liability Limitation:</strong> Our total liability for any claims arising from your use of our services is limited to the amount paid for services in the preceding 12 months.</p>
                      <p><strong>Force Majeure:</strong> We are not liable for service interruptions caused by circumstances beyond our reasonable control, including natural disasters, government actions, or internet infrastructure failures.</p>
                    </div>
                  </section>

                  <section id="termination">
                    <h3 class="fw-bold text-primary mb-3">8. Termination</h3>
                    <p>Either party may terminate this agreement under the following conditions:</p>
                    <div class="row g-3 mt-3">
                      <div class="col-md-6">
                        <h6><i class="bx bx-user text-primary me-2"></i>User Termination</h6>
                        <ul class="list-unstyled ms-3">
                          <li><i class="bi bi-dot text-muted me-2"></i>Cancel anytime through account settings</li>
                          <li><i class="bi bi-dot text-muted me-2"></i>Access continues until billing period ends</li>
                          <li><i class="bi bi-dot text-muted me-2"></i>Data export available for 30 days</li>
                        </ul>
                      </div>
                      <div class="col-md-6">
                        <h6><i class="bx bx-buildings text-warning me-2"></i>Service Termination</h6>
                        <ul class="list-unstyled ms-3">
                          <li><i class="bi bi-dot text-muted me-2"></i>30 days notice for policy violations</li>
                          <li><i class="bi bi-dot text-muted me-2"></i>Immediate termination for severe breaches</li>
                          <li><i class="bi bi-dot text-muted me-2"></i>Pro-rated refunds when applicable</li>
                        </ul>
                      </div>
                    </div>
                  </section>

                  <section id="contact">
                    <h3 class="fw-bold text-primary mb-3">9. Contact Information</h3>
                    <div class="contact-card">
                      <div class="row g-4 align-items-center">
                        <div class="col-md-8">
                          <h5 class="mb-3">Questions about these terms?</h5>
                          <p class="mb-3">Our legal and support teams are here to help clarify any aspects of our terms and conditions.</p>
                          <div class="d-flex flex-column gap-2">
                            <div><i class="bx bx-envelope text-primary me-2"></i><strong>Legal Inquiries:</strong> legal@smartvote.com</div>
                            <div><i class="bx bx-support text-success me-2"></i><strong>General Support:</strong> support@smartvote.com</div>
                            <div><i class="bx bx-phone text-info me-2"></i><strong>Phone:</strong> +233 551 784 926</div>
                            <div><i class="bx bx-map text-warning me-2"></i><strong>Address:</strong> Accra, Ghana</div>
                          </div>
                        </div>
                        <div class="col-md-4 text-center">
                          <i class="bx bx-support fs-1 text-primary mb-3"></i>
                          <h6>Response Time</h6>
                          <p class="text-muted mb-0">We respond to all legal inquiries within 48 hours during business days.</p>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center mt-4 animate-fade-in-up" style="animation-delay: 0.5s;">
              <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="privacy.php" class="btn btn-outline-primary">
                  <i class="bi bi-shield-check me-1"></i>Privacy Policy
                </a>
                <a href="payment-page.php" class="btn btn-primary">
                  <i class="bx bx-credit-card me-1"></i>Subscribe Now
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
        const sections = document.querySelectorAll('.terms-content section');
        
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
      
      // Enhanced function to add Bootstrap Icons fallbacks
      function addIconFallbacks() {
        const biIconMappings = {
          'bx-shield-check': '\uF4FD',
          'bx-info-circle': '\uF431',
          'bx-user': '\uF4DA',
          'bx-data': '\uF1C0',
          'bx-check': '\uF26A',
          'bx-lock-alt': '\uF470',
          'bx-server': '\uF4F8',
          'bx-user-check': '\uF4DC',
          'bx-certification': '\uF1F9',
          'bx-cog': '\uF3E2',
          'bx-support': '\uF268',
          'bx-chart': '\uF1C0',
          'bx-search': '\uF52D',
          'bx-edit': '\uF4CA',
          'bx-trash': '\uF5DE',
          'bx-download': '\uF1C1',
          'bx-block': '\uF275',
          'bx-x-circle': '\uF659',
          'bx-envelope': '\uF32F',
          'bx-phone': '\uF4B2',
          'bx-map': '\uF3F0',
          'bx-time': '\uF292',
          'bx-file-blank': '\uF4A5',
          'bx-home': '\uF425',
          'bx-credit-card': '\uF2E1',
          'bx-menu': '\uF479',
          'bx-sun': '\uF5A1',
          'bx-moon': '\uF48C',
          'bx-desktop': '\uF390',
          'bx-log-in-circle': '\uF1C3',
          'bx-buildings': '\uF1FC',
          'bx-x': '\uF659',
          'bx-dot-circle-2': '\uF287',
          'bx-refresh': '\uF4FE',
          'bx-money': '\uF2F0',
          'bx-dollar': '\uF2F0',
          'bx-calendar': '\uF1F5',
          'bx-star': '\uF586',
          'bx-award': '\uF1F9',
          'bx-gift': '\uF3F4',
          'bx-notification': '\uF1F6',
          'bx-bell': '\uF1F6',
          'bx-heart': '\uF414',
          'bx-bookmark': '\uF1FA',
          'bx-share': '\uF52E',
          'bx-link': '\uF455',
          'bx-copy': '\uF2C7',
          'bx-print': '\uF4C0',
          'bx-upload': '\uF62F',
          'bx-folder': '\uF3D7',
          'bx-archive': '\uF1EE',
          'bx-clock': '\uF292',
          'bx-location': '\uF3F0',
          'bx-globe': '\uF3F9',
          'bx-warning': '\uF33C',
          'bx-error': '\uF33A',
          'bx-check-circle': '\uF26B'
        };
        
        // Apply fallbacks to all matching elements
        Object.keys(biIconMappings).forEach(iconClass => {
          const elements = document.querySelectorAll(`.${iconClass}`);
          elements.forEach(el => {
            if (el.textContent.trim() === '' || el.textContent === '?') {
              el.textContent = biIconMappings[iconClass];
              el.style.fontFamily = 'bootstrap-icons, sans-serif';
              el.classList.add('icon-fallback', 'bi');
            }
          });
        });
        
        // Also check for generic .bx elements without specific classes
        const genericBxElements = document.querySelectorAll('.bx:not([class*="bx-"])');
        genericBxElements.forEach(el => {
          if (el.textContent.trim() === '') {
            el.textContent = '\uF4A5'; // Bootstrap Icons file-earmark
            el.style.fontFamily = 'bootstrap-icons, sans-serif';
            el.classList.add('icon-fallback', 'bi');
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
