<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-skin="default" data-assets-path="assets/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>SmartVote – Pricing Plans</title>
  <meta name="description" content="Choose the SmartVote subscription that fits your election needs" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

  <!-- Core & Theme CSS -->
  <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="assets/vendor/css/core.css" />
  <link rel="stylesheet" href="assets/vendor/css/pages/front-page.css" />
  <link rel="stylesheet" href="assets/css/modern-ui.css" />
  <link rel="stylesheet" href="assets/css/enhanced-navbar.css" />
  <link rel="stylesheet" href="assets/css/enhanced-pricing.css" />

  <script src="assets/vendor/js/helpers.js"></script>
  <script src="assets/js/front-config.js"></script>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg bg-body-tertiary py-0">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="assets/img/favicon/favicon.ico" alt="logo" width="30" height="30" class="me-2" />
        <span class="d-none d-sm-inline fw-bold">SmartVote</span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <i class="bx bx-menu fs-3"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="index.php#landingFeatures">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#landingReviews">Reviews</a></li>
          <li class="nav-item"><a class="nav-link active" href="pricing.php">Pricing</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php" target="_blank">Admin</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- /Navbar -->

  <!-- Pricing Section -->
  <section class="section-py bg-body landing-pricing">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary pricing-badge">Pricing Plans</span>
      </div>
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1 pricing-title-highlight">
          Tailored pricing plans
          <img src="assets/img/front-pages/icons/section-title-icon.png" alt="icon" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
        designed for all educational institutions
      </h4>
      <p class="text-center pb-2 mb-5">Choose the perfect plan for your election needs, from small organizations to large-scale elections.</p>

      <!-- Toggle -->
      <div class="text-center mb-5">
        <div class="position-relative d-inline-block pt-3 pt-md-0">
          <label class="switch switch-sm switch-primary me-0">
            <span class="switch-label fs-6 text-body me-3">Pay Monthly</span>
            <input type="checkbox" class="switch-input price-duration-toggler" checked />
            <span class="switch-toggle-slider toggled"><span class="switch-on"></span><span class="switch-off"></span></span>
            <span class="switch-label fs-6 text-body ms-3">Pay Annual</span>
          </label>
          <div class="pricing-plans-item position-absolute d-flex pricing-save-badge">
            <img src="assets/img/front-pages/icons/pricing-plans-arrow.png" alt="arrow" class="scaleX-n1-rtl" />
            <span class="fw-medium mt-2 ms-1">Save 25%</span>
          </div>
        </div>
      </div>

      <div class="row g-6 pt-lg-5">
        <!-- Free Plan -->
        <div class="col-xl-4 col-md-6 d-flex">
          <div class="card pricing-card h-100 d-flex flex-column">
            <div class="pricing-card-header">
              <img src="assets/img/front-pages/icons/paper-airplane.png" alt="basic" class="mb-4" />
              <h4 class="mb-0">Free</h4>
              <div class="d-flex align-items-center justify-content-center">
                <span class="price-monthly h2 text-primary fw-extrabold mb-0">₵0</span>
                <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">₵0</span>
                <sub class="h6 text-body-secondary ms-1 mb-0">/mo</sub>
              </div>
              <div class="price-yearly text-body-secondary d-none">₵0 /year</div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled pricing-list mb-4">
                <li><i class="bx bx-check me-2 text-success"></i> 1 Election</li>
                <li><i class="bx bx-check me-2 text-success"></i> Up to 500 voters</li>
                <li><i class="bx bx-check me-2 text-success"></i> Basic support</li>
              </ul>
              <a href="payment-page.php?plan=free" class="btn pricing-btn btn-outline-primary d-block">Get Started</a>
            </div>
          </div>
        </div>

        <!-- Standard Plan -->
        <div class="col-xl-4 col-md-6 d-flex">
          <div class="card pricing-card popular h-100 border-primary border-2">
            <div class="card-header text-center bg-primary bg-opacity-10">
              <img src="assets/img/front-pages/icons/plane.png" alt="standard" class="mb-4" />
              <h4 class="mb-0">Standard</h4>
              <div class="d-flex align-items-center justify-content-center">
                <span class="price-monthly h2 text-primary fw-extrabold mb-0">₵99</span>
                <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">₵899</span>
                <sub class="h6 text-body-secondary ms-1 mb-0">/mo</sub>
              </div>
              <div class="price-yearly text-body-secondary d-none">₵ 899 /year</div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled pricing-list mb-4">
                <li><i class="bx bx-check me-2 text-success"></i> Up to 5 concurrent elections</li>
                <li><i class="bx bx-check me-2 text-success"></i> Unlimited voters</li>
                <li><i class="bx bx-check me-2 text-success"></i> Email support</li>
                <li><i class="bx bx-check me-2 text-success"></i> Custom branding</li>
              </ul>
              <a href="payment-page.php?plan=standard" class="btn pricing-btn btn-primary d-block">Choose Plan</a>
            </div>
          </div>
        </div>

        <!-- Premium Plan -->
        <div class="col-xl-4 col-md-6 d-flex">
          <div class="card pricing-card h-100 d-flex flex-column">
            <div class="pricing-card-header">
              <img src="assets/img/front-pages/icons/shuttle-rocket.png" alt="premium" class="mb-4" />
              <h4 class="mb-0">Premium</h4>
              <div class="d-flex align-items-center justify-content-center">
                <span class="price-monthly h2 text-primary fw-extrabold mb-0">₵199</span>
                <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">₵1799</span>
                <sub class="h6 text-body-secondary ms-1 mb-0">/mo</sub>
              </div>
              <div class="price-yearly text-body-secondary d-none">₵ 1,799 /year</div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled pricing-list mb-4">
                <li><i class="bx bx-check me-2 text-success"></i> Unlimited elections</li>
                <li><i class="bx bx-check me-2 text-success"></i> Unlimited voters</li>
                <li><i class="bx bx-check me-2 text-success"></i> Priority 24/7 support</li>
                <li><i class="bx bx-check me-2 text-success"></i> Dedicated success manager</li>
              </ul>
              <a href="payment-page.php?plan=premium" class="btn pricing-btn btn-outline-primary d-block">Go Premium</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- /Pricing Section -->

  <!-- Footer -->
  <footer class="footer enhanced-footer py-4 mt-5">
    <div class="container text-center">
      <p class="mb-0">© <span id="copyrightYear"></span> SmartVote. All rights reserved.</p>
    </div>
  </footer>


  <!-- Pricing specific JS -->
  <script src="assets/js/pages-pricing.js"></script>
  <!-- Theme switcher -->
  <script src="assets/js/theme-switcher.js"></script>
  <script>
    document.getElementById('copyrightYear').textContent = new Date().getFullYear();
  </script>
</body>
</html>
