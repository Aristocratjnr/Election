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

    <title>Election Management System</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

    <!-- Fonts -->    
     <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />

    <link rel="stylesheet" href="assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <link rel="stylesheet" href="assets/vendor/css/pages/front-page.css" />

    <link rel="stylesheet" href="assets/vendor/libs/nouislider/nouislider.css" />    
    <link rel="stylesheet" href="assets/vendor/libs/swiper/swiper.css" />    
    <link rel="stylesheet" href="assets/vendor/css/pages/front-page-landing.css" />
    <link rel="stylesheet" href="assets/css/enhanced-features.css" />
    <link rel="stylesheet" href="assets/css/modern-ui.css" />
    <link rel="stylesheet" href="assets/css/improved-footer.css" />
    <link rel="stylesheet" href="assets/css/enhanced-reviews.css" />
    <link rel="stylesheet" href="assets/css/enhanced-navbar.css" />
    <link rel="stylesheet" href="assets/css/enhanced-cta.css">
    <link rel="stylesheet" href="assets/css/hero.css" />
    <link rel="stylesheet" href="assets/css/success-stories.css" />  
    <link rel="stylesheet" href="assets/css/enhanced-faq.css" />
      <link rel="stylesheet" href="assets/css/enhanced-pricing.css" />
    <link rel="stylesheet" href="assets/css/badge-icons.css" />
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/vendor/js/template-customizer.js"></script>   
     <script src="assets/js/front-config.js"></script>
     
    
  </head>

  <body>
  <style>
@media (max-width: 991.98px) {
  .navbar-collapse {
    padding: 1rem;
    background-color: var(--bs-body-bg);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    margin-top: 0.5rem;
  }
  
  .dropdown-menu {
    border: none;
    box-shadow: none;
    padding-left: 1rem;
  }
  
  .theme-icon {
    margin-right: 0.5rem;
  }
}
</style>
    <script src="assets/vendor/js/dropdown-hover.js"></script>
    <script src="assets/vendor/js/mega-dropdown.js"></script>
<!-- Navbar: Start -->
<nav class="navbar navbar-expand-lg bg-body-tertiary py-0">
  <div class="container">
    <!-- Brand & Mobile Toggle -->
    <a class="navbar-brand d-flex align-items-center" href="landing-page.html">
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
          <a class="nav-link" href="#landingFeatures">Features</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#landingReviews">Reviews</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#landingPricing">Pricing</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="login.php" target="_blank">Admin</a>
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
          <a href="login.php" class="btn btn-primary navbar-cta" target="_blank">
            <i class="bx bx-log-in-circle d-none d-lg-inline me-1"></i>
            <span>Login</span>
          </a>
          <a href="register.php" class="btn btn-success auth-btn" target="_blank">
            <i class="bx bx-user-plus d-none d-lg-inline me-1"></i>
            <span>Register</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- Navbar: End -->

    <!-- Sections:Start -->
    <div data-bs-spy="scroll" class="scrollspy-example">
      <!-- Hero: Start -->
      <section id="hero-animation">
        <div id="landingHero" class="landing-hero">
        
          <div class="hero-bg-pattern"></div>

          <div class="container">
            <div class="hero-content">
              <h1 class="hero-title">
                Transform <span>Election Management</span><br>
                with Smart Technology
              </h1>
              <p class="hero-subtitle">
                A secure, transparent, and efficient voting system designed for<br class="d-none d-lg-block">
                modern educational institutions. Experience the future of elections.
              </p>
              <a href="register.php" class="btn btn-primary hero-cta">
                Get Started Now
                <i class="bx bx-right-arrow-alt hero-cta-arrow"></i>
              </a>
            </div>
          <div class="scroll-down-indicator">
            <div class="scroll-down-arrow"></div>
          </div>
        </div>
      </section>
     <section id="landingFeatures" class="section-py landing-features">
  <div class="container">
    <!-- Section Header -->
    <div class="text-center mb-5">
      <span class="badge bg-label-primary rounded-pill px-3 py-2 mb-3 fs-6 fw-semibold">Powerful Features</span>
      <h2 class="display-6 mb-3">
        <span class="position-relative fw-extrabold z-1">
          Everything you need
          <img
            src="assets/img/front-pages/icons/section-title-icon.png"
            alt="section title icon"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
        <span class="d-block d-md-inline-block mt-2 mt-md-0">for your next election</span>
      </h2>
      <p class="text-center mb-5 mx-auto features-subtitle">
        Our comprehensive platform provides all the tools you need to run secure, transparent, and efficient elections of any scale.
      </p>
    </div>

    <!-- Features Cards Grid -->
    <div class="row g-4">
      <!-- Feature 1 -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100">
          <div class="feature-card-body p-4">
            <div class="feature-icon-wrapper mb-4">
              <div class="feature-icon-bg bg-primary bg-opacity-10"></div>
              <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="feature-icon text-primary">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier"> 
                  <path d="M14.5 10.75H8.5C8.09 10.75 7.75 10.41 7.75 10C7.75 9.59 8.09 9.25 8.5 9.25H14.5C14.91 9.25 15.25 9.59 15.25 10C15.25 10.41 14.91 10.75 14.5 10.75Z" fill="#696cff"></path> 
                  <path d="M11.5 13.75H8.5C8.09 13.75 7.75 13.41 7.75 13C7.75 12.59 8.09 12.25 8.5 12.25H11.5C11.91 12.25 12.25 12.59 12.25 13C12.25 13.41 11.91 13.75 11.5 13.75Z" fill="#696cff"></path> 
                  <path opacity="0.4" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z" fill="#696cff"></path> 
                  <path d="M21.3005 22.0001C21.1205 22.0001 20.9405 21.9301 20.8105 21.8001L18.9505 19.9401C18.6805 19.6701 18.6805 19.2301 18.9505 18.9501C19.2205 18.6801 19.6605 18.6801 19.9405 18.9501L21.8005 20.8101C22.0705 21.0801 22.0705 21.5201 21.8005 21.8001C21.6605 21.9301 21.4805 22.0001 21.3005 22.0001Z" fill="#696cff"></path> 
                </g>
              </svg>
            </div>
            <h4 class="feature-title mb-3">Real-time Results</h4>
            <p class="feature-description mb-4">
              Monitor election progress and view results in real-time with our advanced dashboard. Get instant insights into voter turnout and election statistics.
            </p>
            <div class="feature-benefits">
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Live vote counting</span>
              </div>
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Dynamic charts and graphs</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Exportable reports</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100">
          <div class="feature-card-body p-4">
            <div class="feature-icon-wrapper mb-4">
              <div class="feature-icon-bg bg-success bg-opacity-10"></div>
              <svg width="64px" height="64px" viewBox="-38.33 0 341.323 341.323" xmlns="http://www.w3.org/2000/svg" fill="#000000" class="feature-icon text-success">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                  <defs><style>.a{fill:#ffffff;}.b{fill:#71dd37;}.c{fill:#71dd37;}.d{fill:#e2e2e2;}</style></defs>
                  <path class="a" d="M164.219,135.526l-63.569-5.547c-1.408-6.5-3.546-10.521-4.436-17.417a29.882,29.882,0,0,1,1.509-14.1q.6-1.638,1.17-3.289c4.474-13,4.685-23.743,9.053-36.751a18.856,18.856,0,0,1,7.765-9.959c32.491-20.546,54.242-30.21,89.245-46.1l45.63,32.593C243.1,48.165,239.521,54.292,232.04,67.506c-2.531,4.47-5.1,8.992-8.657,12.7-5.678,5.916-13.411,9.3-20.385,13.616a83.29,83.29,0,0,0-7.678,5.368c-.038-1.718,8.29-.455,11.287.646,2.173.8,4.5,1.816,5.569,3.867a6.383,6.383,0,0,1-.735,6.439c-1.3,1.863-7.044,6.211-11.03,6.951l-1.313.243.293.1c3.958,1.345,5.9,6.351,4.611,10.328s-5.244,6.334-9.2,7.677c-3.2,1.088-13.352,1.088-19.416,1.088-1.586,0-8.683-.566-11.165-1"></path>
                  <path class="b" d="M10.818,339V305.752c15.213.108,34.458.34,47.707.34,15.17,0,114.941.057,150.367.057,11.165,0,34.06-.327,44.212-.327,0,15.967,0,33.182,0,33.182"></path>
                  <path class="a" d="M172.712,123.751A162.544,162.544,0,0,1,186.2,107.033a84.121,84.121,0,0,1,8.676-7.849,22.125,22.125,0,0,1,11.288.649c2.173.8,4.5,1.816,5.569,3.867a6.383,6.383,0,0,1-.735,6.439c-1.3,1.863-7.044,6.211-11.03,6.951l-1.31.244.29.1c3.958,1.345,5.9,6.351,4.611,10.328s-5.244,6.334-9.2,7.677c-3.2,1.088-13.352,1.088-19.416,1.088a54.323,54.323,0,0,1-10.811-1.435C167.1,131.417,169.9,127.58,172.712,123.751Z"></path>
                  <path class="b" d="M16.7,247.228c6.476-10.609,13.1-21.288,17.45-26.757,15.229.062,24.93.249,40.4.249,15.171,0,99.771.359,135.2.359,9.122,0,16.388.23,23.071.213,7.072,12.919,22.124,39.574,29.3,51.754.014,4.386.035,8.356.06,15.068.018,4.966.087,11.638.084,17.593-6.412,0-44.544.442-53.361.442-35.426,0-135.2-.057-150.367-.057-15.85,0-40.279-.332-56.124-.386-.027-6.094.216-13.035.2-18.159q-.031-8.739-.066-17.476C6.3,264.379,11.45,255.827,16.7,247.228Z"></path>
                  <path class="c" d="M18.771,248.439c3.092-5.063,6.192-10.121,9.4-15.113,2.433-3.787,4.888-7.632,7.677-11.158l-1.7.7c14.818.062,29.635.226,44.454.254q14.051.025,28.1.082,19.963.066,39.927.132,20.327.063,40.654.111c13.23.028,26.456.051,39.686.208,1.947.023,3.9.038,5.843.034L230.74,222.5c7.7,14.054,15.6,28,23.578,41.9q2.838,4.941,5.719,9.858l-.328-1.211c.036,10.887.147,21.774.144,32.661l2.4-2.4c-15.584,0-31.17.294-46.754.408-14.507.107-29.016.03-43.522.025l-59.011-.026-46.456-.02c-18.667-.006-37.332-.213-56-.344q-4.056-.029-8.111-.044l2.4,2.4c-.019-5.6.175-11.205.2-16.808.025-6.275-.041-12.552-.066-18.827L4.6,271.282c4.936-7.476,9.5-15.2,14.167-22.843,1.614-2.644-2.536-5.058-4.144-2.423-4.289,7.028-8.508,14.1-12.977,21.021a13.146,13.146,0,0,0-1.311,2.109,9.822,9.822,0,0,0-.2,3.5q.014,3.469.026,6.939c.016,4.144.058,8.288,0,12.432C.1,296.577-.014,301.141,0,305.706a2.436,2.436,0,0,0,2.4,2.4c20.245.071,40.488.381,60.734.387q21.509.006,43.017.018l59.016.026c15.3.006,30.6.074,45.905.006,16.1-.072,32.2-.322,48.3-.425.961-.006,1.921-.012,2.881-.011a2.435,2.435,0,0,0,2.4-2.4c0-7.563-.073-15.126-.1-22.689q-.019-4.483-.037-8.966a4.756,4.756,0,0,0-.453-2.425c-.258-.482-.555-.946-.831-1.417q-.88-1.507-1.757-3.018-9.077-15.654-17.89-31.458-4.378-7.809-8.7-15.653a2.4,2.4,0,0,0-2.072-1.189c-8.94.016-17.88-.208-26.821-.214q-9.354-.006-18.708-.028-22.892-.048-45.781-.128-21.582-.069-43.166-.142-14.73-.047-29.461-.069c-10.105-.03-20.209-.158-30.313-.218-1.3-.007-2.608-.046-3.909-.02-1.721.035-2.351.87-3.313,2.132-1.227,1.609-2.388,3.269-3.526,4.942-3.009,4.426-5.853,8.964-8.671,13.513q-2.272,3.669-4.517,7.356C13.012,248.66,17.162,251.074,18.771,248.439Z"></path>
                  <path class="c" d="M22.322,273.863c16.134.061,32.266.385,48.4.568,10.8.123,21.6.124,32.4.13l44.135.026q24.057.014,48.113.022,21.821.006,43.639.1,1.974,0,3.947.007c3.088,0,3.093-4.8,0-4.8-14.162,0-28.323-.1-42.484-.1q-23.581,0-47.162-.02-22.89-.012-45.778-.027c-10.337,0-20.674.026-31.009-.071-16.514-.155-33.027-.467-49.541-.6q-2.328-.019-4.656-.029c-3.089-.011-3.093,4.789,0,4.8Z"></path>
                  <path class="c" d="M8.418,309.073V339c0,3.089,4.8,3.094,4.8,0V309.073c0-3.088-4.8-3.094-4.8,0Z"></path>
                  <path class="c" d="M250.7,309.073V339c0,3.089,4.8,3.094,4.8,0V309.073c0-3.088-4.8-3.094-4.8,0Z"></path>
                  <path class="c" d="M197.448,100.75a24.207,24.207,0,0,1,6.427.8,20.583,20.583,0,0,1,2.881.9,10.072,10.072,0,0,1,2.558,1.454,2.763,2.763,0,0,1,1.032,2.092,4.922,4.922,0,0,1-.9,2.819c-.321.513.227-.2-.035.051-.113.111-.213.239-.323.354-.287.3-.59.581-.9.855a17.658,17.658,0,0,1-2.147,1.673,16.718,16.718,0,0,1-6.271,3.033,2.424,2.424,0,0,0-1.676,2.952,2.449,2.449,0,0,0,2.952,1.677,26.013,26.013,0,0,0,12.429-8,8.61,8.61,0,0,0,.563-9.268c-1.641-2.6-4.682-4-7.533-4.856a30.136,30.136,0,0,0-9.06-1.326c-3.085.063-3.095,4.863,0,4.8Z"></path>
                  <path class="c" d="M203.745.286c-21.5,9.76-43.066,19.412-63.715,30.9q-8.014,4.457-15.856,9.215-4.263,2.583-8.481,5.239a24.716,24.716,0,0,0-6.793,5.921,25.519,25.519,0,0,0-3.872,8.09c-1.007,3.233-1.824,6.522-2.536,9.831-1.354,6.29-2.37,12.651-4.033,18.872-.98,3.667-2.369,7.173-3.5,10.788a31.813,31.813,0,0,0-1.347,11.348c.434,7,3.223,13.361,4.719,20.13.667,3.015,5.3,1.739,4.629-1.276-1.261-5.7-3.618-11.133-4.374-16.951a28.082,28.082,0,0,1,.355-9.519c.7-3.215,2.045-6.253,3.026-9.387a179.687,179.687,0,0,0,4.35-18.838c.609-3.074,1.257-6.142,2.043-9.176a39.617,39.617,0,0,1,2.935-8.8c2.789-5.2,8.183-7.778,13.03-10.745Q132.086,41.177,140,36.7c9.671-5.454,19.542-10.538,29.542-15.359,12.112-5.841,24.38-11.351,36.622-16.909a2.417,2.417,0,0,0,.861-3.284,2.457,2.457,0,0,0-3.283-.861Z"></path>
                  <path class="c" d="M133.292,78.636q9.823,4.215,19.841,7.952a2.42,2.42,0,0,0,2.952-1.676,2.453,2.453,0,0,0-1.676-2.953q-9.436-3.507-18.694-7.468a2.481,2.481,0,0,0-3.284.861,2.417,2.417,0,0,0,.861,3.284Z"></path>
                  <path class="c" d="M142.327,80.175A77.852,77.852,0,0,1,130.843,98.1a9.888,9.888,0,0,0-2.581,4.738,19.277,19.277,0,0,0-.22,4.88c.281,3.353,1.157,6.676,1.029,10.057-.117,3.089,4.684,3.085,4.8,0,.121-3.19-.65-6.329-.974-9.486a16.167,16.167,0,0,1,.026-4.378c.265-1.542,1.494-2.612,2.448-3.773a79.564,79.564,0,0,0,11.1-17.542,2.472,2.472,0,0,0-.861-3.284,2.419,2.419,0,0,0-3.283.861Z"></path>
                  <path class="c" d="M180.028,117.066a40.849,40.849,0,0,1,15.186,1.436c.813.217,1.612.47,2.407.748.095.034.724.3.137.047.144.063.279.143.422.208a7.776,7.776,0,0,1,1.679.863,6.265,6.265,0,0,1,2.131,5.178c-.223,4.537-5.543,7.289-9.51,7.966a59.09,59.09,0,0,1-6.674.49c-2.631.09-5.263.114-7.9.121-2.134,0-4.238-.055-6.367-.22q-1.893-.147-3.783-.336c-1.241-.126-2-.2-2.9-.356a2.473,2.473,0,0,0-2.953,1.677,2.419,2.419,0,0,0,1.677,2.952,82.108,82.108,0,0,0,13.668,1.084c5.5-.008,11.181.144,16.626-.8,5.534-.963,11.746-5.116,12.737-10.974a11.393,11.393,0,0,0-1.722-8.279,11.647,11.647,0,0,0-6.195-4.32,42.455,42.455,0,0,0-8.455-1.914,39.265,39.265,0,0,0-10.212-.368,2.475,2.475,0,0,0-2.4,2.4,2.413,2.413,0,0,0,2.4,2.4Z"></path>
                </g>
              </svg>
            </div>
            <h4 class="feature-title mb-3">Custom Ballots</h4>
            <p class="feature-description mb-4">
              Create professional ballots with our intuitive design tools. Customize layouts, add candidate photos, and tailor ballot questions to fit any election type.
            </p>
            <div class="feature-benefits">
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Drag-and-drop designer</span>
              </div>
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Multiple question formats</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Accessible ballot options</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100">
          <div class="feature-card-body p-4">
            <div class="feature-icon-wrapper mb-4">
              <div class="feature-icon-bg bg-danger bg-opacity-10"></div>
              <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="feature-icon text-danger">
                <path
                  opacity="0.2"
                  d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z"
                  fill="#ff3e1d" />
                <path
                  d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z"
                  stroke="#ff3e1d"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round" />
                <path
                  d="M43 26L28.325 40L21 33"
                  stroke="#ff3e1d"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </div>
            <h4 class="feature-title mb-3">Voter Registration</h4>
            <p class="feature-description mb-4">
              Streamline the voter registration process with our comprehensive database system. Verify identities, manage access, and ensure election integrity.
            </p>
            <div class="feature-benefits">
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Secure identity verification</span>
              </div>
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Bulk registration tools</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Voter eligibility management</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 4 -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100">
          <div class="feature-card-body p-4">
            <div class="feature-icon-wrapper mb-4">
              <div class="feature-icon-bg bg-info bg-opacity-10"></div>
              <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="feature-icon text-info">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier"> 
                  <path opacity="0.4" d="M20.9099 11.12C20.9099 16.01 17.3599 20.59 12.5099 21.93C12.1799 22.02 11.8198 22.02 11.4898 21.93C6.63984 20.59 3.08984 16.01 3.08984 11.12V6.73006C3.08984 5.91006 3.70986 4.98007 4.47986 4.67007L10.0498 2.39007C11.2998 1.88007 12.7098 1.88007 13.9598 2.39007L19.5298 4.67007C20.2898 4.98007 20.9199 5.91006 20.9199 6.73006L20.9099 11.12Z" fill="#03c3ec"></path> 
                  <path d="M14.5 10.5C14.5 9.12 13.38 8 12 8C10.62 8 9.5 9.12 9.5 10.5C9.5 11.62 10.24 12.55 11.25 12.87V15.5C11.25 15.91 11.59 16.25 12 16.25C12.41 16.25 12.75 15.91 12.75 15.5V12.87C13.76 12.55 14.5 11.62 14.5 10.5Z" fill="#03c3ec"></path> 
                </g>
              </svg>
            </div>
            <h4 class="feature-title mb-3">Secure Voting</h4>
            <p class="feature-description mb-4">
              Protect your election with enterprise-grade security. Our platform uses end-to-end encryption and blockchain technology to ensure vote integrity.
            </p>
            <div class="feature-benefits">
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>End-to-end encryption</span>
              </div>
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Blockchain verification</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Tamper-proof audit logs</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 5 -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100">
          <div class="feature-card-body p-4">
            <div class="feature-icon-wrapper mb-4">
              <div class="feature-icon-bg bg-warning bg-opacity-10"></div>
              <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="feature-icon text-warning">
                <path
                  opacity="0.2"
                  d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z"
                  fill="#ffab00" />
                <path
                  d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z"
                  stroke="#ffab00"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round" />
                <path
                  d="M32 20V32L40 40"
                  stroke="#ffab00"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </div>
            <h4 class="feature-title mb-3">Automated Scheduling</h4>
            <p class="feature-description mb-4">
              Set up election timelines, send automated reminders, and manage the entire electoral process with our powerful scheduling tools.
            </p>
            <div class="feature-benefits">
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Election calendar management</span>
              </div>
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Automated email notifications</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Time-based voting activation</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 6 -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100">
          <div class="feature-card-body p-4">
            <div class="feature-icon-wrapper mb-4">
              <div class="feature-icon-bg bg-primary bg-opacity-10"></div>
              <svg width="64px" height="64px" viewBox="0 0 1024 1024" fill="#000000" class="icon feature-icon text-primary" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                  <path d="M296.542 160.114c-4.414 0-8.076-3.576-8.076-7.998s3.498-7.998 7.918-7.998h0.156c4.42 0 7.998 3.576 7.998 7.998s-3.574 7.998-7.996 7.998zM328.532 160.114c-4.412 0-8.074-3.576-8.074-7.998s3.498-7.998 7.918-7.998h0.156c4.422 0 7.998 3.576 7.998 7.998s-3.576 7.998-7.998 7.998zM360.522 160.114c-4.412 0-8.076-3.576-8.076-7.998s3.5-7.998 7.918-7.998h0.156c4.422 0 7.998 3.576 7.998 7.998s-3.574 7.998-7.996 7.998z" fill="#696cff"></path>
                  <path d="M775.918 176.11H264.076a7.994 7.994 0 0 1-7.998-7.998v-15.996c0-13.23 10.762-23.994 23.992-23.994h479.854c13.23 0 23.992 10.764 23.992 23.994v15.996a7.992 7.992 0 0 1-7.998 7.998z m-503.844-15.996h495.848v-7.998a8.008 8.008 0 0 0-7.996-7.998H280.072a8.004 8.004 0 0 0-7.998 7.998v7.998z" fill="#696cff"></path>
                  <path d="M775.918 512.006H264.076a7.994 7.994 0 0 1-7.998-7.998V168.112a7.994 7.994 0 0 1 7.998-7.998h511.842c4.422 0 8 3.578 8 7.998v335.896a7.994 7.994 0 0 1-8 7.998z m-503.844-15.994h495.848V176.11H272.074v319.902z" fill="#696cff"></path>
                  <path d="M743.93 304.072H296.066a7.994 7.994 0 0 1-7.998-7.998v-95.97a7.994 7.994 0 0 1 7.998-7.998h447.864a7.992 7.992 0 0 1 7.996 7.998v95.97a7.992 7.992 0 0 1-7.996 7.998z m-439.866-15.996h431.87V208.1H304.064v79.976z" fill="#696cff"></path>
                  <path d="M695.946 256.084H344.052c-4.42 0-7.998-3.576-7.998-7.998s3.578-7.998 7.998-7.998h351.894c4.418 0 7.996 3.576 7.996 7.998s-3.578 7.998-7.996 7.998zM743.93 352.056H535.992c-4.418 0-7.996-3.576-7.996-7.998s3.578-7.998 7.996-7.998h207.938c4.422 0 7.996 3.576 7.996 7.998s-3.574 7.998-7.996 7.998zM743.93 384.046H535.992c-4.418 0-7.996-3.576-7.996-7.998s3.578-7.998 7.996-7.998h207.938c4.422 0 7.996 3.576 7.996 7.998s-3.574 7.998-7.996 7.998zM743.93 416.036H535.992c-4.418 0-7.996-3.576-7.996-7.998s3.578-7.998 7.996-7.998h207.938c4.422 0 7.996 3.576 7.996 7.998s-3.574 7.998-7.996 7.998z" fill="#696cff"></path>
                </g>
              </svg>
            </div>
            <h4 class="feature-title mb-3">Multi-platform Access</h4>
            <p class="feature-description mb-4">
              Access SmartVote from any device - desktop, tablet, or mobile phone. Our responsive design ensures a seamless experience across all platforms.
            </p>
            <div class="feature-benefits">
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Responsive web application</span>
              </div>
              <div class="d-flex align-items-center mb-2">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Native mobile apps</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bx bx-check-circle text-success me-2"></i>
                <span>Offline voting capabilities</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Feature Highlight -->
    <div class="row mt-5 pt-3">
      <div class="col-12">
        <div class="feature-highlight p-4 p-md-5 rounded-3 bg-primary bg-opacity-10 position-relative overflow-hidden">
          <div class="row align-items-center">
            <div class="col-lg-7 pe-lg-5">
              <h3 class="mb-3 text-primary">Student Insights</h3>
              <p class="mb-4">
                Get deep insights into your election with our powerful analytics tools. Track voter turnout, analyze voting patterns, and generate detailed reports to make data-driven decisions.
              </p>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="d-flex align-items-center">
                    <div class="feature-mini-icon bg-primary text-white rounded-circle p-2 me-3">
                      <i class="bx bx-chart"></i>
                    </div>
                    <span>Real-time statistics</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex align-items-center">
                    <div class="feature-mini-icon bg-primary text-white rounded-circle p-2 me-3">
                      <i class="bx bx-export"></i>
                    </div>
                    <span>Exportable reports</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex align-items-center">
                    <div class="feature-mini-icon bg-primary text-white rounded-circle p-2 me-3">
                      <i class="bx bx-line-chart"></i>
                    </div>
                    <span>Historical comparison</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex align-items-center">
                    <div class="feature-mini-icon bg-primary text-white rounded-circle p-2 me-3">
                      <i class="bx bx-map"></i>
                    </div>
                    <span>Demographic insights</span>
                  </div>
                </div>
              </div>
              <a href="register.php" class="btn btn-primary">Try It Now</a>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
              <img src="assets/img/front-pages/landing-page/student.png" alt="Analytics Dashboard" class="img-fluid rounded-3 shadow-lg feature-highlight-img">
            </div>
          </div>
          <!-- Decorative elements -->
          <div class="feature-highlight-shape-1"></div>
          <div class="feature-highlight-shape-2"></div>
        </div>
      </div>
    </div>
  </div>
</section>

      <!-- Real customers reviews: Start -->
      <section id="landingReviews" class="section-py bg-body landing-reviews pb-0 position-relative">
        <!-- Decorative background elements -->
        <div class="reviews-bg-element reviews-bg-element-1"></div>
        <div class="reviews-bg-element reviews-bg-element-2"></div>
        <!-- What people say slider: Start -->
        <div class="container">
          <div class="row align-items-center gx-0 gy-4 g-lg-5 mb-5 pb-md-5">
            <div class="col-md-6 col-lg-5 col-xl-4">
              <div class="mb-4 reviews-badge-container">
                <span class="badge bg-label-primary reviews-badge">Real Customers Reviews</span>
              </div>
              <h2 class="mb-3 reviews-title">
                <span class="position-relative fw-extrabold z-1"
                  >What people say
                  <img
                    src="assets/img/front-pages/icons/section-title-icon.png"
                    alt="laptop charging"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
              </h2>
              <p class="mb-5 mb-md-6 reviews-subtitle">
                See what our customers have to<br class="d-none d-xl-block" />
                say about their experience with SmartVote.
              </p>
              <div class="landing-reviews-btns">
                <button id="reviews-previous-btn" class="btn btn-icon btn-primary reviews-btn me-3 shadow-sm" type="button">
                  <i class="icon-base bx bx-chevron-left icon-md scaleX-n1-rtl"></i>
                </button>
                <button id="reviews-next-btn" class="btn btn-icon btn-primary reviews-btn shadow-sm" type="button">
                  <i class="icon-base bx bx-chevron-right icon-md scaleX-n1-rtl"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6 col-lg-7 col-xl-8">
              <div class="swiper-reviews-carousel overflow-hidden">
                <div class="swiper" id="swiper-reviews">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <div class="card h-100 review-card shadow-sm">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <div class="mb-3 review-quote">
                            <i class="bx bxs-quote-alt-left text-primary review-quote-icon"></i>
                          </div>
                          <p class="review-text mb-4">
                          The Election Management System has completely transformed how we run our university student council elections. The process is now more transparent, secure, and efficient than ever before.
                          </p>
                          <div class="text-warning mb-4 review-stars">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                          </div>                          
                          <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 review-avatar">
                              <div class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-user"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0 fw-semibold">Sarah Afrifa</h6>
                              <p class="small text-body-secondary mb-0">SRC Organizer</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="card h-100 review-card shadow-sm">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <div class="mb-3 review-quote">
                            <i class="bx bxs-quote-alt-left text-primary review-quote-icon"></i>
                          </div>
                          <p class="review-text mb-4">
                          "As a city election commissioner, I needed a reliable system that could handle thousands of voters. This platform delivered beyond expectations with its robust security features and real-time reporting."
                          </p>
                          <div class="text-warning mb-4 review-stars">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                          </div>                          
                          <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 review-avatar">
                              <div class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-user"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0 fw-semibold">Emmanuel Danso</h6>
                              <p class="small text-body-secondary mb-0">SRC President</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  
                    <div class="swiper-slide">
                      <div class="card h-100 review-card shadow-sm">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <div class="mb-3 review-quote">
                            <i class="bx bxs-quote-alt-left text-primary review-quote-icon"></i>
                          </div>
                          <p class="review-text mb-4">
                          "The analytics and reporting features have been invaluable for our organization. We can now make data-driven decisions about our election processes and improve voter engagement."
                          </p>
                          <div class="text-warning mb-4 review-stars">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bx-star"></i>
                          </div>                         
                           <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 review-avatar">
                              <div class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-user"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0 fw-semibold">Lilian Maryes</h6>
                              <p class="small text-body-secondary mb-0">SRC Secretary</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="card h-100 review-card shadow-sm">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <div class="mb-3 review-quote">
                            <i class="bx bxs-quote-alt-left text-primary review-quote-icon"></i>
                          </div>
                          <p class="review-text mb-4">
                          "Setting up our corporate board elections used to be a logistical nightmare. With this system, we've cut preparation time by 70% and increased participation rates significantly."
                          </p>
                          <div class="text-warning mb-4 review-stars">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                          </div>                          
                          <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 review-avatar">
                              <div class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-user"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0 fw-semibold">Gloria Adams</h6>
                              <p class="small text-body-secondary mb-0">SRC Vice President</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="card h-100 review-card shadow-sm">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <div class="mb-3 review-quote">
                            <i class="bx bxs-quote-alt-left text-primary review-quote-icon"></i>
                          </div>
                          <p class="review-text mb-4">
                          "The accessibility features of this platform have allowed us to include voters with disabilities in our election process like never before. It's truly an inclusive solution."
                          </p>
                          <div class="text-warning mb-4 review-stars">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bx-star"></i>
                          </div>                          
                          <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 review-avatar">
                              <div class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-user"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0 fw-semibold">Joseph Appiah</h6>
                              <p class="small text-body-secondary mb-0">Lecturer</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-pagination reviews-pagination mt-5"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr class="m-0 mt-6 mt-md-12" />
        </div>
    
      </section>
      <!-- Real customers reviews: End -->
      <section id="landingTeam" class="section-py landing-team">
        <div class="container">
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">Our Great Team</span>
          </div>
          <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1"
              >Supported
              <img
                src="assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            by Real People
          </h4>
          <p class="text-center mb-md-11 pb-0 pb-xl-12">
          Our team of election specialists and technology experts is dedicated to creating secure and efficient voting solutions
          </p>
          <div class="row gy-12 mt-2">
            <div class="col-lg-3 col-sm-6">
              <div class="card mt-3 mt-lg-0 shadow-none">
                <div
                  class="bg-label-primary border border-bottom-0 border-primary-subtle position-relative team-image-box">
                  <img
                    src="assets/img/front-pages/landing-page/team-member-1.png"
                    class="position-absolute card-img-position bottom-0 start-50"
                    alt="human image" />
                </div>
                <div class="card-body border border-top-0 border-primary-subtle text-center py-5">
                  <h5 class="card-title mb-0">Obuobi Ayim David</h5>
                  <p class="text-body-secondary mb-0">Software Engineer</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="card mt-3 mt-lg-0 shadow-none">
                <div class="bg-label-info border border-bottom-0 border-info-subtle position-relative team-image-box">
                  <img
                    src="assets/img/front-pages/landing-page/team-member-2.png"
                    class="position-absolute card-img-position bottom-0 start-50"
                    alt="human image" />
                </div>
                <div class="card-body border border-top-0 border-info-subtle text-center py-5">
                  <h5 class="card-title mb-0">Mavis Maryes</h5>
                  <p class="text-body-secondary mb-0">UI Designer</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="card mt-3 mt-lg-0 shadow-none">
                <div
                  class="bg-label-danger border border-bottom-0 border-danger-subtle position-relative team-image-box">
                  <img
                    src="assets/img/front-pages/landing-page/team-member-3.png"
                    class="position-absolute card-img-position bottom-0 start-50"
                    alt="human image" />
                </div>
                <div class="card-body border border-top-0 border-danger-subtle text-center py-5">
                  <h5 class="card-title mb-0">Nannie HayFord</h5>
                  <p class="text-body-secondary mb-0">Development Lead</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="card mt-3 mt-lg-0 shadow-none">
                <div
                  class="bg-label-success border border-bottom-0 border-success-subtle position-relative team-image-box">
                  <img
                    src="assets/img/front-pages/landing-page/team-member-4.png"
                    class="position-absolute card-img-position bottom-0 start-50"
                    alt="human image" />
                </div>
                <div class="card-body border border-top-0 border-success-subtle text-center py-5">
                  <h5 class="card-title mb-0">Chris Jason</h5>
                  <p class="text-body-secondary mb-0">Marketing Manager</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>      <section id="landingPricing" class="section-py bg-body landing-pricing">
        <div class="container">
          <div class="text-center mb-4 pricing-header">
            <span class="badge bg-label-primary pricing-badge">Pricing Plans</span>
          </div>
          <h4 class="text-center mb-1 pricing-title">
            <span class="position-relative fw-extrabold z-1 pricing-title-highlight"
              >Tailored pricing plans
              <img
                src="assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            designed for all educational institutions
          </h4>
          <p class="text-center pb-2 mb-7 pricing-subtitle">
          Choose the perfect plan for your election needs, from small organizations to large-scale educational elections
          </p>
          <div class="text-center mb-12">
            <div class="position-relative d-inline-block pt-3 pt-md-0">
              <label class="switch switch-sm switch-primary me-0 pricing-toggle-container">
                <span class="switch-label fs-6 text-body me-3 pricing-toggle-label monthly active">Pay Monthly</span>
                <input type="checkbox" class="switch-input price-duration-toggler" checked />
                <span class="switch-toggle-slider pricing-toggle-switch toggled">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
                <span class="switch-label fs-6 text-body ms-3 pricing-toggle-label annual active">Pay Annual</span>
              </label>
              <div class="pricing-plans-item position-absolute d-flex pricing-save-badge">
                <img
                  src="assets/img/front-pages/icons/pricing-plans-arrow.png"
                  alt="pricing plans arrow"
                  class="scaleX-n1-rtl" />
                <span class="fw-medium mt-2 ms-1"> Save 25%</span>
              </div>
            </div>
          </div>
          <div class="row g-6 pt-lg-5">
            <!-- Basic Plan: Start -->
            <div class="col-xl-4 col-lg-6">
              <div class="card">
                <div class="card-header">
                  <div class="text-center">
                    <img
                      src="assets/img/front-pages/icons/paper-airplane.png"
                      alt="paper airplane icon"
                      class="mb-8 pb-2" />
                    <h4 class="mb-0">Free For Students</h4>
                    <div class="d-flex align-items-center justify-content-center">
                      <span class="price-monthly h2 text-primary fw-extrabold mb-0">₵0</span>
                      <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">₵0</span>
                      <sub class="h6 text-body-secondary mb-n1 ms-1">/mo</sub>
                    </div>
                    <div class="position-relative pt-2">
                      <div class="price-yearly text-body-secondary price-yearly-toggle d-none">₵ 0/ year</div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled pricing-list">
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Up to 500 registered voters
                       
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Standard security features
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Email support
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Advanced analytics
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Custom Branding
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Traffic analytics
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Basic Support
                      </h6>
                    </li>
                  </ul>
                  <div class="d-grid mt-8">
                    <a href="register.php" class="btn btn-label-primary">Get Started</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- Basic Plan: End -->

            <!-- Favourite Plan: Start -->
            <div class="col-xl-4 col-lg-6">
              <div class="card border border-primary shadow-xl">
                <div class="card-header">
                  <div class="text-center">
                    <img src="assets/img/front-pages/icons/plane.png" alt="plane icon" class="mb-8 pb-2" />
                    <h4 class="mb-0">Team</h4>
                    <div class="d-flex align-items-center justify-content-center">
                      <span class="price-monthly h2 text-primary fw-extrabold mb-0">₵29</span>
                      <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">₵22</span>
                      <sub class="h6 text-body-secondary mb-n1 ms-1">/mo</sub>
                    </div>
                    <div class="position-relative pt-2">
                      <div class="price-yearly text-body-secondary price-yearly-toggle d-none">₵ 264 / year</div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled pricing-list">
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Everything in basic
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Up to 5,000 registered voter
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Advanced ballot design tools
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Enhanced security features
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Priority email & phone support
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Advanced analytics
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Custom branding
                      </h6>
                    </li>
                  </ul>
                  <div class="d-grid mt-8">
                    <a href="payment-page.html" class="btn btn-primary">Get Started</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- Favourite Plan: End -->

            <!-- Standard Plan: Start -->
            <div class="col-xl-4 col-lg-6">
              <div class="card">
                <div class="card-header">
                  <div class="text-center">
                    <img
                      src="assets/img/front-pages/icons/shuttle-rocket.png"
                      alt="shuttle rocket icon"
                      class="mb-8 pb-2" />
                    <h4 class="mb-0">Enterprise</h4>
                    <div class="d-flex align-items-center justify-content-center">
                      <span class="price-monthly h2 text-primary fw-extrabold mb-0">₵49</span>
                      <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">₵37</span>
                      <sub class="h6 text-body-secondary mb-n1 ms-1">/mo</sub>
                    </div>
                    <div class="position-relative pt-2">
                      <div class="price-yearly text-body-secondary price-yearly-toggle d-none">₵ 444 / year</div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled pricing-list">
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Everything in premium
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Premium ballot design tools
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Military-grade security
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        24/7 dedicated support
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Custom branding & white labeling
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Social media automation
                      </h6>
                    </li>
                    <li>
                      <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"
                          ><i class="icon-base bx bx-check icon-12px"></i
                        ></span>
                        Sales automation tools
                      </h6>
                    </li>
                  </ul>
                  <div class="d-grid mt-8">
                    <a href="payment-page.html" class="btn btn-label-primary">Get Started</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- Standard Plan: End -->
          </div>
        </div>
      </section>
      <!-- Pricing plans: End -->

      <!-- Fun facts: Start -->
      <section id="landingFunFacts" class="section-py landing-fun-facts">
        <div class="container">
          <div class="row gy-6">
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-primary shadow-none">
                <div class="card-body text-center">
                  <div class="mb-4 text-primary">
                    <svg width="64" height="65" viewBox="0 0 64 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        opacity="0.2"
                        d="M10 44.4663V18.4663C10 17.4054 10.4214 16.388 11.1716 15.6379C11.9217 14.8877 12.9391 14.4663 14 14.4663H50C51.0609 14.4663 52.0783 14.8877 52.8284 15.6379C53.5786 16.388 54 17.4054 54 18.4663V44.4663H10Z"
                        fill="currentColor" />
                      <path
                        d="M10 44.4663V18.4663C10 17.4054 10.4214 16.388 11.1716 15.6379C11.9217 14.8877 12.9391 14.4663 14 14.4663H50C51.0609 14.4663 52.0783 14.8877 52.8284 15.6379C53.5786 16.388 54 17.4054 54 18.4663V44.4663M36 22.4663H28M6 44.4663H58V48.4663C58 49.5272 57.5786 50.5446 56.8284 51.2947C56.0783 52.0449 55.0609 52.4663 54 52.4663H10C8.93913 52.4663 7.92172 52.0449 7.17157 51.2947C6.42143 50.5446 6 49.5272 6 48.4663V44.4663Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                  <h3 class="mb-0">7.1k+</h3>
                  <p class="fw-medium mb-0">
                  Elections<br />
                  Successfully Managed
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-success shadow-none">
                <div class="card-body text-center">
                  <div class="mb-4 text-success">
                    <svg width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <g id="User">
                        <path
                          id="Vector"
                          opacity="0.2"
                          d="M32.4999 8.52881C27.6437 8.52739 22.9012 9.99922 18.899 12.7499C14.8969 15.5005 11.8233 19.4006 10.0844 23.9348C8.34542 28.4691 8.02291 33.4242 9.15945 38.1456C10.296 42.867 12.8381 47.1326 16.4499 50.3788C17.9549 47.4151 20.2511 44.9261 23.0841 43.1875C25.917 41.4489 29.176 40.5287 32.4999 40.5288C30.5221 40.5288 28.5887 39.9423 26.9442 38.8435C25.2997 37.7447 24.018 36.1829 23.2611 34.3556C22.5043 32.5284 22.3062 30.5177 22.6921 28.5779C23.0779 26.6381 24.0303 24.8563 25.4289 23.4577C26.8274 22.0592 28.6092 21.1068 30.549 20.721C32.4888 20.3351 34.4995 20.5331 36.3268 21.29C38.154 22.0469 39.7158 23.3286 40.8146 24.9731C41.9135 26.6176 42.4999 28.551 42.4999 30.5288C42.4999 33.181 41.4464 35.7245 39.571 37.5999C37.6956 39.4752 35.1521 40.5288 32.4999 40.5288C35.8238 40.5287 39.0829 41.4489 41.9158 43.1875C44.7487 44.9261 47.045 47.4151 48.5499 50.3788C52.1618 47.1326 54.7039 42.867 55.8404 38.1456C56.977 33.4242 56.6545 28.4691 54.9155 23.9348C53.1766 19.4006 50.103 15.5005 46.1008 12.7499C42.0987 9.99922 37.3562 8.52739 32.4999 8.52881Z"
                          fill="currentColor" />
                        <path
                          id="Vector_2"
                          d="M32.5 40.5288C38.0228 40.5288 42.5 36.0517 42.5 30.5288C42.5 25.006 38.0228 20.5288 32.5 20.5288C26.9772 20.5288 22.5 25.006 22.5 30.5288C22.5 36.0517 26.9772 40.5288 32.5 40.5288ZM32.5 40.5288C29.1759 40.5288 25.9168 41.4477 23.0839 43.1866C20.2509 44.9255 17.9548 47.4149 16.45 50.3788M32.5 40.5288C35.8241 40.5288 39.0832 41.4477 41.9161 43.1866C44.7491 44.9255 47.0452 47.4149 48.55 50.3788M56.5 32.5288C56.5 45.7836 45.7548 56.5288 32.5 56.5288C19.2452 56.5288 8.5 45.7836 8.5 32.5288C8.5 19.274 19.2452 8.52881 32.5 8.52881C45.7548 8.52881 56.5 19.274 56.5 32.5288Z"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round" />
                      </g>
                    </svg>
                  </div>
                  <h3 class="mb-0">50k+</h3>
                  <p class="fw-medium mb-0">
                    Join SmartVote<br />
                    Community
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-info shadow-none">
                <div class="card-body text-center">
                  <div class="mb-4 text-info">
                    <svg width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        opacity="0.2"
                        d="M46.5001 10.5288H32.5001L20.2251 26.5288L32.5001 56.5288L60.5001 26.5288L46.5001 10.5288Z"
                        fill="currentColor" />
                      <path
                        d="M18.5 10.5288H46.5L60.5 26.5288L32.5 56.5288L4.5 26.5288L18.5 10.5288Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M33.2934 9.92012C33.1042 9.67343 32.8109 9.52881 32.5 9.52881C32.1891 9.52881 31.8958 9.67343 31.7066 9.92012L19.7318 25.5288H4.5C3.94772 25.5288 3.5 25.9765 3.5 26.5288C3.5 27.0811 3.94772 27.5288 4.5 27.5288H19.5537L31.5745 56.9075C31.7282 57.2833 32.094 57.5288 32.5 57.5288C32.906 57.5288 33.2718 57.2833 33.4255 56.9075L45.4463 27.5288H60.5C61.0523 27.5288 61.5 27.0811 61.5 26.5288C61.5 25.9765 61.0523 25.5288 60.5 25.5288H45.2682L33.2934 9.92012ZM42.7474 25.5288L32.5 12.1717L22.2526 25.5288H42.7474ZM21.7146 27.5288L32.5 53.8881L43.2854 27.5288H21.7146Z"
                        fill="currentColor" />
                    </svg>
                  </div>
                  <h3 class="mb-0">4.8/5</h3>
                  <p class="fw-medium mb-0">
                  Highly Trusted<br />
                    Election System
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-warning shadow-none">
                <div class="card-body text-center">
                  <div class="mb-4 text-warning">
                    <svg width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        opacity="0.2"
                        d="M14.125 50.9038C11.825 48.6038 13.35 43.7788 12.175 40.9538C11 38.1288 6.5 35.6538 6.5 32.5288C6.5 29.4038 10.95 27.0288 12.175 24.1038C13.4 21.1788 11.825 16.4538 14.125 14.1538C16.425 11.8538 21.25 13.3788 24.075 12.2038C26.9 11.0288 29.375 6.52881 32.5 6.52881C35.625 6.52881 38 10.9788 40.925 12.2038C43.85 13.4288 48.575 11.8538 50.875 14.1538C53.175 16.4538 51.65 21.2788 52.825 24.1038C54 26.9288 58.5 29.4038 58.5 32.5288C58.5 35.6538 54.05 38.0288 52.825 40.9538C51.6 43.8788 53.175 48.6038 50.875 50.9038C48.575 53.2038 43.75 51.6788 40.925 52.8538C37.6 53.9663 35.125 58.4663 32 58.4663C28.875 58.4663 26.5 54.0163 23.575 52.7913C20.65 51.5663 15.925 53.1413 13.625 50.8413Z"
                        fill="currentColor" />
                      <path
                        d="M43.5 26.5288L28.825 40.5288L21.5 33.5288M14.125 50.9038C11.825 48.6038 13.35 43.7788 12.175 40.9538C11 38.1288 6.5 35.6538 6.5 32.5288C6.5 29.4038 10.95 27.0288 12.175 24.1038C13.4 21.1788 11.825 16.4538 14.125 14.1538C16.425 11.8538 21.25 13.3788 24.075 12.2038C26.9 11.0288 29.375 6.52881 32.5 6.52881C35.625 6.52881 38 10.9788 40.925 12.2038C43.85 13.4288 48.575 11.8538 50.875 14.1538C53.175 16.4538 51.65 21.2788 52.825 24.1038C54 26.9288 58.5 29.4038 58.5 32.5288C58.5 35.6538 54.05 38.0288 52.825 40.9538C51.6 43.8788 53.175 48.6038 50.875 50.9038C48.575 53.2038 43.75 51.6788 40.925 52.8538C37.6 53.9663 35.125 58.4663 32 58.4663C28.875 58.4663 26.5 54.0163 23.575 52.7913C20.65 51.5663 15.925 53.1413 13.625 50.8413Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                  </div>
                  <h5 class="mb-2">Money Back</h5>
                  <p class="features-icon-description">
                Guarantee
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Fun facts: Start -->      
       <!-- Success Stories: Start -->
      <section id="successStories" class="section-py bg-body landing-success-stories">
        <div class="container">
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">Success Stories</span>
          </div>
          <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1">
              Transforming Elections
              <img
                src="assets/img/front-pages/icons/section-title-icon.png"
                alt="success stories icon"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            Through Innovation
          </h4>
          <p class="text-center mb-5">
            Real success stories from institutions that have revolutionized their election process with SmartVote
          </p>

          <!-- Key Statistics -->
          <div class="row justify-content-center mb-8">
            <div class="col-sm-4 text-center mb-4 mb-sm-0">
              <div class="p-4 border rounded-3 bg-label-primary bg-opacity-10 h-100">
                <i class="bx bx-like fs-1 text-primary mb-2"></i>
                <h2 class="mb-2 fw-bold">98%</h2>
                <p class="mb-0">Satisfaction Rate</p>
              </div>
            </div>
            <div class="col-sm-4 text-center mb-4 mb-sm-0">
              <div class="p-4 border rounded-3 bg-label-success bg-opacity-10 h-100">
                <i class="bx bx-check-shield fs-1 text-success mb-2"></i>
                <h2 class="mb-2 fw-bold">2M+</h2>
                <p class="mb-0">Votes Processed</p>
              </div>
            </div>
            <div class="col-sm-4 text-center">
              <div class="p-4 border rounded-3 bg-label-info bg-opacity-10 h-100">
                <i class="bx bx-buildings fs-1 text-info mb-2"></i>
                <h2 class="mb-2 fw-bold">500+</h2>
                <p class="mb-0">Institutions Using SmartVote</p>
              </div>
            </div>
          </div>

          <div class="row gy-4 g-lg-5">
            <!-- Success Story 1 -->
            <div class="col-lg-4 col-md-6">
              <div class="card h-100 shadow-sm border-primary border-opacity-25">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0">
                      <div class="avatar avatar-lg">
                        <img src="assets/img/front-pages/landing-page/university.jpg" alt="University of Innovation" class="rounded-3">
                      </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="mb-1">University of Innovation</h5>
                      <span class="badge bg-label-primary">Higher Education</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <i class="bx bx-trending-up text-primary me-2"></i>
                      <span>85% increase in voter turnout</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                      <i class="bx bx-time text-primary me-2"></i>
                      <span>75% reduction in counting time</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="bx bx-check-shield text-primary me-2"></i>
                      <span>Zero security incidents</span>
                    </div>
                  </div>
                  <p class="mb-3">
                    "SmartVote transformed our student council elections completely. The mobile voting feature and real-time analytics helped us achieve record participation rates."
                  </p>
                  <div class="d-flex align-items-center pt-1">
                    <div class="avatar avatar-sm me-2">
                      <img src="assets/img/front-pages/landing-page/team-member-1.png" alt="Dr. Sarah Chen" class="rounded-circle">
                    </div>
                    <div>
                      <h6 class="mb-0">Dr. Sarah Chen</h6>
                      <small class="text-muted">Dean of Student Affairs</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Success Story 2 -->
            <div class="col-lg-4 col-md-6">
              <div class="card h-100 shadow-sm border-primary border-opacity-25">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0">
                      <div class="avatar avatar-lg">
                        <img src="assets/img/front-pages/landing-page/global.png" alt="Global Tech Academy" class="rounded-3">
                      </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="mb-1">Global Tech Academy</h5>
                      <span class="badge bg-label-success">Technical Institute</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <i class="bx bx-block text-success me-2"></i>
                      <span>100% transparency achieved</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                      <i class="bx bx-user-check text-success me-2"></i>
                      <span>15,000+ verified votes</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="bx bx-money text-success me-2"></i>
                      <span>40% cost reduction</span>
                    </div>
                  </div>
                  <p class="mb-3">
                    "The blockchain verification and real-time monitoring features revolutionized our election transparency. Our students now have complete confidence in the results."
                  </p>
                  <div class="d-flex align-items-center pt-1">
                    <div class="avatar avatar-sm me-2">
                      <img src="assets/img/front-pages/landing-page/team-member-3.png" alt="Prof. James Wilson" class="rounded-circle">
                    </div>
                    <div>
                      <h6 class="mb-0">Prof. James Wilson</h6>
                      <small class="text-muted">IT Director</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Success Story 3 -->
            <div class="col-lg-4 col-md-6">
              <div class="card h-100 shadow-sm border-primary border-opacity-25">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0">
                      <div class="avatar avatar-lg">
                        <img src="assets/img/front-pages/landing-page/metro.png" alt="Metropolitan College" class="rounded-3">
                      </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="mb-1">Metropolitan College</h5>
                      <span class="badge bg-label-info">Multi-Campus</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <i class="bx bx-globe text-info me-2"></i>
                      <span>5 campuses connected</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                      <i class="bx bx-mobile text-info me-2"></i>
                      <span>92% mobile voting rate</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="bx bx-timer text-info me-2"></i>
                      <span>Results in under 1 hour</span>
                    </div>
                  </div>
                  <p class="mb-3">
                    "SmartVote's multi-campus support and mobile voting capabilities made it possible to conduct our first-ever unified student election across all five campuses."
                  </p>
                  <div class="d-flex align-items-center pt-1">
                    <div class="avatar avatar-sm me-2">
                      <img src="assets/img/front-pages/landing-page/team-member-2.png" alt="Jennifer Martinez" class="rounded-circle">
                    </div>
                    <div>
                      <h6 class="mb-0">Jennifer Martinez</h6>
                      <small class="text-muted">Election Commissioner</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Metrics -->
          <div class="text-center mt-8">
            <h5 class="mb-4">Trusted by Leading Educational Institutions</h5>
            <div class="row justify-content-center">
              <div class="col-12 col-lg-8">                <div class="row row-cols-2 row-cols-md-4 g-4">
                  <div class="col">
                    <div class="p-3">                      <div class="badge-icon bg-label-primary rounded-circle p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" class="security-shield-svg">
                          <path fill="#696cff" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.13 8.42-7 9.88-3.87-1.46-7-5.21-7-9.88V6.3l7-3.12zm-2 8.82h4c0 1.1-.9 2-2 2s-2-.9-2-2zm1-7h2v5h-2V5z"/>
                        </svg>
                      </div>
                      <p class="small mt-2 mb-0 fw-semibold">Security Certified</p>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3">                      <div class="badge-icon bg-label-success rounded-circle p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" class="gdpr-svg">
                          <path fill="#71dd37" d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11zm-9-7h8v2H9v-2zm0 4h8v2H9v-2zm0-8h5v2H9V9z"/>
                          <path fill="#71dd37" d="M15 13l-4 4l-2-2l-1 1l3 3l5-5z"/>
                        </svg>
                      </div>
                      <p class="small mt-2 mb-0 fw-semibold">GDPR Compliant</p>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3">                      <div class="badge-icon bg-label-info rounded-circle p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" class="blockchain-svg">
                          <path fill="#03c3ec" d="M4 7v2c0 .55.45 1 1 1h1v6c0 .55.45 1 1 1h10c.55 0 1-.45 1-1V8h1c.55 0 1-.45 1-1V5c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1v2zm14 0v2H6V7h12zM7 10h10v6H7v-6zm3-5h4v1h-4V5z"/>
                          <path fill="#03c3ec" d="M12 12l-2 2l2 2l2-2l-2-2zm-6 6h12v2H6v-2z"/>
                        </svg>
                      </div>
                      <p class="small mt-2 mb-0 fw-semibold">Blockchain Verified</p>
                    </div>
                  </div>
                  <div class="col">
                    <div class="p-3">                      <div class="badge-icon bg-label-warning rounded-circle p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" class="uptime-svg">
                          <path fill="#ffab00" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8s8 3.59 8 8s-3.59 8-8 8zm.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67V7z"/>
                        </svg>
                      </div>
                      <p class="small mt-2 mb-0 fw-semibold">99.9% Uptime</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Success Stories: End -->

      <!-- FAQ: Start -->
<section id="landingFAQ" class="section-py bg-body landing-faq">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">FAQ</span>
    </div>
    <h4 class="text-center mb-1">
      Frequently asked
      <span class="position-relative fw-extrabold z-1"
        >questions
        <img
          src="assets/img/front-pages/icons/section-title-icon.png"
          alt="laptop charging"
          class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
      </span>
    </h4>
    <p class="text-center mb-5">
      Browse through these FAQs to find answers to commonly asked questions about SmartVote.
    </p>
    
    <div class="row gy-5">
      <div class="col-lg-5">
        <div class="faq-image-wrapper position-relative">
          <div class="faq-image-bg-shape"></div>
          <img
            src="assets/img/front-pages/landing-page/faq-boy-with-logos.png"
            alt="faq illustration"
            class="faq-image img-fluid rounded-4 shadow-lg" />
          <div class="faq-floating-icon faq-icon-1">
            <i class="bx bx-question-mark fs-2 text-primary"></i>
          </div>
          <div class="faq-floating-icon faq-icon-2">
            <i class="bx bx-bulb fs-2 text-warning"></i>
          </div>
          <div class="faq-tag">
            <span>Got questions?</span>
          </div>
        </div>
        
        <div class="faq-contact-card mt-5 bg-primary bg-opacity-10 p-4 rounded-3 border border-primary border-opacity-25">
          <h5 class="mb-3"><i class="bx bx-support me-2"></i> Still have questions?</h5>
          <p class="mb-3">Can't find the answer you're looking for? Please contact our friendly support team.</p>
          <a href="#landingContact" class="btn btn-primary">Contact Support</a>
        </div>
      </div>
      
      <div class="col-lg-7">
        <div class="accordion custom-accordion" id="faqAccordion">
          <div class="card accordion-item mb-3 shadow-sm border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#faqOne"
                aria-expanded="false"
                aria-controls="faqOne">
                <i class="bx bx-help-circle text-primary me-2 fs-5"></i>
                <span class="fw-medium">Is SmartVote free to use for elections?</span>
              </button>
            </h2>
            <div id="faqOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body pt-0">
                <div class="d-flex">
                  <div class="faq-answer-icon me-3">
                    <i class="bx bx-info-circle text-primary fs-5"></i>
                  </div>
                  <div class="faq-answer-content">
                    SmartVote offers a free tier for small-scale elections, such as student council votes or small community polls. For larger elections or advanced features, you can upgrade to a paid plan.
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card accordion-item mb-3 shadow-sm border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#faqTwo"
                aria-expanded="false"
                aria-controls="faqTwo">
                <i class="bx bx-help-circle text-primary me-2 fs-5"></i>
                <span class="fw-medium">Can SmartVote handle large-scale elections?</span>
              </button>
            </h2>
            <div id="faqTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body pt-0">
                <div class="d-flex">
                  <div class="faq-answer-icon me-3">
                    <i class="bx bx-info-circle text-primary fs-5"></i>
                  </div>
                  <div class="faq-answer-content">
                    Yes, SmartVote is designed to handle elections of all sizes, from small local polls to large national elections. Our platform scales to meet your needs.
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card accordion-item mb-3 shadow-sm border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button
                type="button"
                class="accordion-button"
                data-bs-toggle="collapse"
                data-bs-target="#faqThree"
                aria-expanded="true"
                aria-controls="faqThree">
                <i class="bx bx-help-circle text-primary me-2 fs-5"></i>
                <span class="fw-medium">How does SmartVote ensure election security?</span>
              </button>
            </h2>
            <div id="faqThree" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
              <div class="accordion-body pt-0">
                <div class="d-flex">
                  <div class="faq-answer-icon me-3">
                    <i class="bx bx-info-circle text-primary fs-5"></i>
                  </div>
                  <div class="faq-answer-content">
                    SmartVote uses advanced encryption, multi-factor authentication, and blockchain technology to ensure the integrity and security of your elections. Regular audits are conducted to maintain the highest standards.
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card accordion-item mb-3 shadow-sm border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#faqFour"
                aria-expanded="false"
                aria-controls="faqFour">
                <i class="bx bx-help-circle text-primary me-2 fs-5"></i>
                <span class="fw-medium">Can I customize the voting process with SmartVote?</span>
              </button>
            </h2>
            <div id="faqFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body pt-0">
                <div class="d-flex">
                  <div class="faq-answer-icon me-3">
                    <i class="bx bx-info-circle text-primary fs-5"></i>
                  </div>
                  <div class="faq-answer-content">
                    Absolutely! SmartVote allows you to customize ballot designs, voting rules, and eligibility criteria to fit the specific needs of your election.
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card accordion-item mb-3 shadow-sm border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#faqFive"
                aria-expanded="false"
                aria-controls="faqFive">
                <i class="bx bx-help-circle text-primary me-2 fs-5"></i>
                <span class="fw-medium">What kind of support does SmartVote offer?</span>
              </button>
            </h2>
            <div id="faqFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body pt-0">
                <div class="d-flex">
                  <div class="faq-answer-icon me-3">
                    <i class="bx bx-info-circle text-primary fs-5"></i>
                  </div>
                  <div class="faq-answer-content">
                    SmartVote provides 24/7 customer support via email, chat, and phone. We also offer detailed documentation and training resources to help you get started.
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card accordion-item shadow-sm border-0 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#faqSix"
                aria-expanded="false"
                aria-controls="faqSix">
                <i class="bx bx-help-circle text-primary me-2 fs-5"></i>
                <span class="fw-medium">Is my data secure with SmartVote?</span>
              </button>
            </h2>
            <div id="faqSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body pt-0">
                <div class="d-flex">
                  <div class="faq-answer-icon me-3">
                    <i class="bx bx-info-circle text-primary fs-5"></i>
                  </div>
                  <div class="faq-answer-content">
                    Yes, we take data security very seriously. SmartVote uses industry-leading encryption and security practices to protect your election data. We are fully GDPR compliant and never share your information with third parties.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- FAQ: End -->
      <!-- CTA: Start -->
      <section id="landingCTA" class="section-py landing-cta">
  <!-- Floating background shapes -->
  <div class="floating-shapes">
    <div></div>
    <div></div>
    <div></div>
  </div>

  <div class="container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6 text-lg-start text-center">
        <h2 class="position-relative fw-extrabold z-1 pricing-title-highlight mb-3">
          Transform Your Election Process
        </h2>
        <p class="cta-subtitle mb-4">
          Join thousands of institutions already using SmartVote to create secure, transparent, and efficient elections.
        </p>
        
        <div class="cta-btn-group">
          <a href="register.php" class="btn btn-outline-cta">
            Start Free Trial
            <i class="bx bx-right-arrow-alt fs-5"></i>
          </a>
          <a href="#landingContact" class="btn btn-outline-cta">
            Contact Sales
          </a>
        </div>
        
        <div class="cta-trust-badge">
          <i class="bx bx-check-shield fs-5"></i>
          <span>No credit card required • 30-day free trial</span>
        </div>
        
        <div class="cta-stats">
          <div class="cta-stat-item">
            <div class="cta-stat-number">98%</div>
            <div class="cta-stat-label">Satisfaction Rate</div>
          </div>
          <div class="cta-stat-item">
            <div class="cta-stat-number">2M+</div>
            <div class="cta-stat-label">Votes Processed</div>
          </div>
          <div class="cta-stat-item">
            <div class="cta-stat-number">500+</div>
            <div class="cta-stat-label">Institutions</div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6 text-center">
        <div class="cta-image-container">
          <div class="cta-image-glow"></div>
          <img
            src="assets/img/front-pages/landing-page/cta-dashboard.png"
            alt="SmartVote Dashboard"
            class="img-fluid cta-image"
          />
        </div>
      </div>
    </div>
  </div>
</section>
<!-- CTA: End -->

      <!-- Contact Us: Start -->
      <section id="landingContact" class="section-py bg-body landing-contact">
        <div class="container">
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">Contact US</span>
          </div>
          <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1"
              >Let's work
              <img
                src="assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            together
         
          </h4>
          <p class="text-center mb-12 pb-md-4">Any question or remark? just write us a message</p>
          <div class="row g-6">
            <div class="col-lg-5">
              <div class="contact-img-box position-relative border p-2 h-100">
                <img
                  src="assets/img/front-pages/icons/contact-border.png"
                  alt="contact border"
                  class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
                <img
                  src="assets/img/front-pages/landing-page/contact-customer-service.png"
                  alt="contact customer service"
                  class="contact-img w-100 scaleX-n1-rtl" />
                <div class="p-4 pb-2">
                  <div class="row g-4">
                    <div class="col-md-6 col-lg-12 col-xl-6">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-primary rounded p-1_5 me-3">
                          <i class="icon-base bx bx-envelope icon-lg"></i>
                        </div>
                        <div>
                          <p class="mb-0">Email</p>
                          <h6 class="mb-0">
                            <a href="ayimobuobi@gmail.com" class="text-heading">ayimobuobi@gmail.com</a>
                          </h6>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-lg-12 col-xl-6">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-success rounded p-1_5 me-3">
                          <i class="icon-base bx bx-phone-call icon-lg"></i>
                        </div>
                        <div>
                          <p class="mb-0">Phone</p>
                          <h6 class="mb-0"><a href="tel:+1234-568-963" class="text-heading">+233 551784926</a></h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card h-100">
                <div class="card-body">
                  <h4 class="mb-2">Send a message</h4>
                  <p class="mb-6">
                  If you would like to discuss anything related to elections, account setup, licensing,<br
                      class="d-none d-lg-block" />
                    partnerships, or have pre-sales questions, you&apos;re at the right place.
                  </p>
                  <form>
                    <div class="row g-4">
                      <div class="col-md-6">
                        <label class="form-label" for="contact-form-fullname">Full Name</label>
                        <input type="text" class="form-control" id="contact-form-fullname" placeholder="john" />
                      </div>
                      <div class="col-md-6">
                        <label class="form-label" for="contact-form-email">Email</label>
                        <input
                          type="text"
                          id="contact-form-email"
                          class="form-control"
                          placeholder="johndoe@gmail.com" />
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="contact-form-message">Message</label>
                        <textarea
                          id="contact-form-message"
                          class="form-control"
                          rows="11"
                          placeholder="Write a message"></textarea>
                      </div>
                      <div class="col-12">
                        <button type="submit" class="btn btn-primary">Send inquiry</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Contact Us: End -->
    </div>

    <!-- / Sections:End -->  
       <!-- Footer: Start -->    
     <footer class="landing-footer bg-body footer-text footer-animated">
      <!-- Link to modern footer stylesheet -->
      <link rel="stylesheet" href="assets/css/improved-footer.css">
      <link rel="stylesheet" href="assets/css/footer-animations.css">
      
      <!-- Back to top button -->
      <button id="back-to-top" class="btn btn-primary btn-icon scroll-top" type="button" aria-label="Back to top">
        <i class="bx bx-up-arrow-alt"></i>
      </button>
        <div class="footer-top position-relative overflow-hidden z-1">
        <img
          src="assets/img/front-pages/backgrounds/footer-bg-light.png"
          class="position-absolute bottom-0 end-0 scaleX-n1-rtl h-100 w-100 z-n1"
          alt="footer image"
          data-app-light-img="front-pages/backgrounds/footer-bg-light.png"
          data-app-dark-img="front-pages/backgrounds/footer-bg-dark.png" />
        </div>

        <div class="container">
          <div class="row gx-0 gy-6 g-lg-10">
            <div class="col-lg-4">
              <div class="footer-logo-container">
                <a href="index.php" class="app-brand-link mb-4" aria-label="SmartVote Home">
                  <span class="app-brand-logo demo">
                    <span class="text-primary">
                    <img src="assets/img/favicon/favicon.ico" alt="SmartVote logo" width="22%" class="logo-img" />
                  </span>
                  <span class="app-brand-text demo  fw-bold ms-2 ps-1">SmartVote</span>
                </a>
              </div>
              <p class="footer-tagline mb-4">
                SmartVote is a secure, developer-friendly, and highly customizable educational voting system designed to streamline elections and decision-making in academic institutions.
              </p>
              <form class="footer-form mb-4" aria-label="Newsletter subscription">
                <label for="footer-email" class="small">Subscribe to newsletter</label>
                <div class="fancy-input-group mt-2">
                  <input
                    type="email"
                    class="form-control"
                    id="footer-email"
                    placeholder="Your email"
                    aria-label="Enter your email" 
                    aria-required="true" />
                  <button
                    type="submit"
                    class="btn btn-primary"
                    aria-label="Subscribe to newsletter">
                    Subscribe
                  </button>
                </div>
              </form>
              <div class="footer-social d-flex mb-4">
                <a href="https://www.github.com/aristocratjnr" target="_blank" class="social-icon social-icon-fancy me-2" aria-label="GitHub">
                  <i class="bx bxl-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/obuobi-ayim-david/" class="social-icon social-icon-fancy me-2" aria-label="LinkedIn">
                  <i class="bx bxl-linkedin"></i>
                </a>
              </div>
            </div>
            
            <div class="col-lg-8">
              <div class="row">                
                <div class="col-md-6 col-sm-6 mb-4 mb-md-0">
                  <h5 class="footer-links-title mb-3">Quick Links</h5>
                  <ul class="footer-links-list list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">Profile</a></li>
                  </ul>
                </div>
                
                <div class="col-md-6 col-sm-6 mb-4 mb-md-0">
                  <h5 class="footer-links-title mb-3">Elections</h5>
                  <ul class="footer-links-list list-unstyled">
                    <li><a href="live_results.php">Live Results</a></li>
                    <li><a href="election_details.php">Election Details</a></li>
                    <li><a href="voters.php">Voters</a></li>
                    <li><a href="blockchain_learn.php">Blockchain</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
        <div class="footer-bottom py-3">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
              <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start">
                <span class="footer-bottom-text">
                  © <script>document.write(new Date().getFullYear());</script>
                  <a href="https://www.github.com/aristocratjnr" target="_blank" rel="noopener">Election Management System</a>
                </span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-center justify-content-md-end align-items-center">
                <span class="footer-bottom-text me-3">Developed by Obuobi Ayim David</span>
                <a href="https://www.github.com/aristocratjnr" class="social-icon social-icon-fancy" target="_blank" rel="noopener" aria-label="GitHub">
                  <i class="bx bxl-github"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    
    <!-- Schema.org JSON-LD for improved SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "SmartVote",
      "url": "https://smartvote.42web.io",
      "logo": "assets/img/favicon/favicon.ico",
      "description": "SmartVote is a secure, developer-friendly, and highly customizable educational voting system designed to streamline elections and decision-making in academic institutions.",
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer support",
        "email": "support@smartvote.example.com"
      },
      "sameAs": [
        "https://www.github.com/aristocratjnr"
      ]
    }
    </script>

    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <script src="assets/vendor/libs/pickr/pickr.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/nouislider/nouislider.js"></script>
    <script src="assets/vendor/libs/swiper/swiper.js"></script>

    <!-- Main JS -->

    <script src="assets/js/front-main.js"></script>    <!-- Page JS -->
    <script src="assets/js/front-page-landing.js"></script>
    <script src="assets/js/footer-interactions.js"></script>
    <script src="assets/js/enhanced-reviews.js"></script>
    <!-- Theme Switcher JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get current theme from localStorage or detect system preference
  const getPreferredTheme = () => {
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
      return storedTheme;
    }
    // Check system preference
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  // Set the theme with smooth transition
  const setTheme = (theme) => {
    const root = document.documentElement;
    if (theme === 'auto') {
      theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    
    // Add transition class for smooth color changes
    root.classList.add('theme-transition');
    root.setAttribute('data-bs-theme', theme);
    
    // Update theme-specific elements
    const header = document.getElementById('header');
    if (header) {
      if (theme === 'dark') {
        header.classList.remove('bg-white');
        header.classList.add('bg-dark');
      } else {
        header.classList.remove('bg-dark');
        header.classList.add('bg-white');
      }
    }

    // Store theme preference
    if (theme !== 'auto') {
      localStorage.setItem('theme', theme);
    }

    // Update theme icon
    const themeIcon = document.querySelector('.theme-icon');
    if (themeIcon) {
      themeIcon.className = `bx ${theme === 'dark' ? 'bx-moon' : 'bx-sun'} fs-5 me-2 theme-icon`;
    }
    
    // Update active state in dropdown
    document.querySelectorAll('.theme-item').forEach(item => {
      item.classList.toggle('active', item.getAttribute('data-bs-theme-value') === theme);
    });

    // Remove transition class after colors have changed
    setTimeout(() => {
      root.classList.remove('theme-transition');
    }, 300);

    // Dispatch theme change event
    document.dispatchEvent(new CustomEvent('themeChanged', { 
      detail: { theme: theme }
    }));
  };

  // Initialize theme
  setTheme(getPreferredTheme());

  // Watch for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (localStorage.getItem('theme') === 'auto') {
      setTheme('auto');
    }
  });

  // Handle theme selection from dropdown
  document.querySelectorAll('[data-bs-theme-value]').forEach(item => {
    item.addEventListener('click', () => {
      const theme = item.getAttribute('data-bs-theme-value');
      setTheme(theme);
    });
  });
});
</script>

<!-- Custom Footer Styling -->

<style>
/* Footer Customizations for Landing Page */
.landing-footer {
  margin-top: 2rem;
}

.landing-footer .social-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  background-color: rgba(var(--bs-primary-rgb), 0.1);
  color: var(--bs-primary);
  font-size: 1.25rem;
}

.landing-footer .social-icon:hover {
  transform: translateY(-3px);
  background-color: var(--bs-primary);
  color: #fff;
}

.landing-footer .footer-links-title {
  color: var(--bs-primary);
  font-weight: 600;
  margin-bottom: 1rem;
}

.landing-footer .footer-links-list a {
  color: var(--bs-body-color);
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
  margin-bottom: 0.5rem;
}

.landing-footer .footer-links-list a:hover {
  color: var(--bs-primary);
  transform: translateX(3px);
}

[data-bs-theme="dark"] .landing-footer {
  background-color: var(--bs-dark);
}

[data-bs-theme="dark"] .landing-footer .footer-links-list a {
  color: rgba(255, 255, 255, 0.8);
}

.footer-bottom-text {
  color: var(--bs-body-color);
}

.footer-form .form-control {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.footer-form .btn {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

/* Back to top button */
.scroll-top {
  position: fixed;
  right: 30px;
  bottom: 30px;
  z-index: 99;
  display: none;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.25);
  transition: all 0.3s ease;
  animation: fadeInUp 0.5s;
}

.scroll-top:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 15px rgba(var(--bs-primary-rgb), 0.35);
}

.scroll-top i {
  font-size: 20px;
}
</style>

<!-- Back to top button script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Back to top button functionality
    const backToTopButton = document.getElementById('back-to-top');
    
    if (backToTopButton) {
      // Show/hide button based on scroll position
      window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) { // Show after scrolling 300px
          backToTopButton.style.display = 'flex';
        } else {
          backToTopButton.style.display = 'none';
        }
      });
      
      // Scroll to top on click
      backToTopButton.addEventListener('click', function() {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }  });
</script>

<!-- Enhanced UI Scripts -->
<script src="assets/js/enhanced-navbar.js"></script>
<script src="assets/js/enhanced-pricing.js"></script>
<script>
  // Initialize enhanced hero section
  document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.getElementById('landingHero');
    if (heroSection) {
      heroSection.classList.add('enhanced-hero');
      
      // Add animated background
      const animatedBg = document.createElement('div');
      animatedBg.classList.add('hero-animated-bg');
      heroSection.prepend(animatedBg);
      
      // Add background pattern
      const bgPattern = document.createElement('div');
      bgPattern.classList.add('hero-bg-pattern');
      heroSection.prepend(bgPattern);
      
      // Enhance hero content
      const heroTextBox = heroSection.querySelector('.hero-text-box');
      if (heroTextBox) {
        heroTextBox.classList.add('hero-content');
        
        // Enhance hero title
        const heroTitle = heroTextBox.querySelector('.hero-title');
        if (heroTitle) {
          heroTitle.innerHTML = heroTitle.textContent.replace(
            'One platform to manage all your elections',
            '<span>One platform</span> to manage all your elections'
          );
        }
        
        // Enhance hero subtitle
        const heroSubTitle = heroTextBox.querySelector('.hero-sub-title');
        if (heroSubTitle) {
          heroSubTitle.classList.add('hero-subtitle');
        }
        
        // Enhance CTA button
        const heroCta = heroTextBox.querySelector('.btn-primary');
        if (heroCta) {
          heroCta.classList.add('hero-cta');
          heroCta.innerHTML = heroCta.textContent + '<i class="bx bx-right-arrow-alt hero-cta-arrow"></i>';
        }
      }
      
      // Add floating elements
      for (let i = 1; i <= 4; i++) {
        const floatingElement = document.createElement('div');
        floatingElement.classList.add('floating-element', `floating-element-${i}`);
        heroSection.appendChild(floatingElement);
      }
      
      // Add scroll down indicator
      const scrollIndicator = document.createElement('div');
      scrollIndicator.classList.add('scroll-down-indicator');
      scrollIndicator.innerHTML = `
        <div class="scroll-down-text">Scroll down</div>
        <div class="scroll-down-arrow"></div>
      `;
      heroSection.appendChild(scrollIndicator);
      
      // Add wave divider
      const waveDiv = document.createElement('div');
      waveDiv.classList.add('hero-wave');
      waveDiv.innerHTML = `
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
          <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
      `;
      heroSection.appendChild(waveDiv);
    }
  });
</script>
<script src="assets/js/theme-switcher.js"></script>
  </body>
</html>
