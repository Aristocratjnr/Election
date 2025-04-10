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

    <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />

    <link rel="stylesheet" href="assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <link rel="stylesheet" href="assets/vendor/css/pages/front-page.css" />

    <link rel="stylesheet" href="assets/vendor/libs/nouislider/nouislider.css" />
    <link rel="stylesheet" href="assets/vendor/libs/swiper/swiper.css" />

    <link rel="stylesheet" href="assets/vendor/css/pages/front-page-landing.css" />
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
      <span class="d-none d-sm-inline fw-bold">SmartVote</span>
    </a>
    
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <i class="bx bx-menu fs-3"></i>
    </button>

    <!-- Collapsible Content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="login.php" target="_blank">Admin</a>
        </li>
      </ul>

      <!-- Right Side Items -->
      <ul class="navbar-nav ms-auto align-items-center">
        <!-- Theme Switcher -->
        <li class="nav-item dropdown me-3">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bx fs-5 me-2 theme-icon"></i>
            <span class="d-none d-lg-inline">Theme</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
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
          <a href="login.php" class="btn btn-primary" target="_blank">
            <i class="bx bx-log-in-circle d-none d-lg-inline me-1"></i>
            <span>Login</span>
          </a>
          <a href="register.php" class="btn btn-success" target="_blank">
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
        <div id="landingHero" class="section-py landing-hero position-relative">
          <img
            src="assets/img/front-pages/backgrounds/hero-bg.png"
            alt="hero background"
            class="position-absolute top-0 start-50 translate-middle-x object-fit-cover w-100 h-100"
            data-speed="1" />
          <div class="container">
            <div class="hero-text-box text-center position-relative">
              <h1 class="text-primary hero-title display-6 fw-extrabold">
              One platform to manage all your elections
              </h1>
              <h2 class="hero-sub-title h6 mb-6">
              Secure, transparent, and efficient election management system <br class="d-none d-lg-block" />
              for educational institution of all sizes.
              </h2>
              <div class="landing-hero-btn d-inline-block position-relative">
                <span class="hero-btn-item position-absolute d-none d-md-flex fw-medium"
                  >Try it out
                  <img
                    src="assets/img/front-pages/icons/Join-community-arrow.png"
                    alt="Join community arrow"
                    class="scaleX-n1-rtl"
                /></span>
                <a href="register.php" class="btn btn-primary btn-lg">Get early access</a>
              </div>
            </div><br><br>
            <div id="heroDashboardAnimation" class="hero-animation-img">
              <a href="../vertical-menu-template/app-ecommerce-dashboard.html" target="_blank">
                <div id="heroAnimationImg" class="position-relative hero-dashboard-img">
                  <img
                    src="assets/img/front-pages/landing-page/hero-dashboard-light.png"
                    alt="hero dashboard"
                    class="animation-img"
                    data-app-light-img="front-pages/landing-page/hero-dashboard-light.png"
                    data-app-dark-img="front-pages/landing-page/hero-dashboard-dark.png" />
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="landing-hero-blank"></div>
      </section>
      <!-- Useful features: Start -->
      <section id="landingFeatures" class="section-py landing-features">
        <div class="container">
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">Useful Features</span>
          </div>
          <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1"
              >Everything you need
              <img
                src="assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            to start your next project
          </h4>
          <p class="text-center mb-12">
            Not just a set of tools, the package includes ready-to-deploy conceptual application.
          </p>
          <div class="features-icon-wrapper row gx-0 gy-6 g-sm-12">
            <div class="col-lg-4 col-sm-6 text-center features-icon-box">
              <div class="mb-4 text-primary text-center">
               <svg width="64px" height="64px" viewBox="0 0 1024 1024" fill="#000000" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M296.542 160.114c-4.414 0-8.076-3.576-8.076-7.998s3.498-7.998 7.918-7.998h0.156c4.42 0 7.998 3.576 7.998 7.998s-3.574 7.998-7.996 7.998zM328.532 160.114c-4.412 0-8.074-3.576-8.074-7.998s3.498-7.998 7.918-7.998h0.156c4.422 0 7.998 3.576 7.998 7.998s-3.576 7.998-7.998 7.998zM360.522 160.114c-4.412 0-8.076-3.576-8.076-7.998s3.5-7.998 7.918-7.998h0.156c4.422 0 7.998 3.576 7.998 7.998s-3.574 7.998-7.996 7.998z" fill=""></path><path d="M775.918 176.11H264.076a7.994 7.994 0 0 1-7.998-7.998v-15.996c0-13.23 10.762-23.994 23.992-23.994h479.854c13.23 0 23.992 10.764 23.992 23.994v15.996a7.992 7.992 0 0 1-7.998 7.998z m-503.844-15.996h495.848v-7.998a8.008 8.008 0 0 0-7.996-7.998H280.072a8.004 8.004 0 0 0-7.998 7.998v7.998z" fill=""></path><path d="M775.918 512.006H264.076a7.994 7.994 0 0 1-7.998-7.998V168.112a7.994 7.994 0 0 1 7.998-7.998h511.842c4.422 0 8 3.578 8 7.998v335.896a7.994 7.994 0 0 1-8 7.998z m-503.844-15.994h495.848V176.11H272.074v319.902z" fill=""></path><path d="M743.93 304.072H296.066a7.994 7.994 0 0 1-7.998-7.998v-95.97a7.994 7.994 0 0 1 7.998-7.998h447.864a7.992 7.992 0 0 1 7.996 7.998v95.97a7.992 7.992 0 0 1-7.996 7.998z m-439.866-15.996h431.87V208.1H304.064v79.976z" fill=""></path><path d="M695.946 256.084H344.052c-4.42 0-7.998-3.576-7.998-7.998s3.578-7.998 7.998-7.998h351.894c4.418 0 7.996 3.576 7.996 7.998s-3.578 7.998-7.996 7.998zM743.93 352.056H535.992c-4.418 0-7.996-3.576-7.996-7.998s3.578-7.998 7.996-7.998h207.938c4.422 0 7.996 3.576 7.996 7.998s-3.574 7.998-7.996 7.998zM743.93 384.046H535.992c-4.418 0-7.996-3.576-7.996-7.998s3.578-7.998 7.996-7.998h207.938c4.422 0 7.996 3.576 7.996 7.998s-3.574 7.998-7.996 7.998zM743.93 416.036H535.992c-4.418 0-7.996-3.576-7.996-7.998s3.578-7.998 7.996-7.998h207.938c4.422 0 7.996 3.576 7.996 7.998s-3.574 7.998-7.996 7.998zM639.96 448.026h-103.968a7.992 7.992 0 0 1-7.996-7.998 7.994 7.994 0 0 1 7.996-7.998h103.968a7.992 7.992 0 0 1 7.996 7.998 7.988 7.988 0 0 1-7.996 7.998zM504.002 480.016H296.066a7.994 7.994 0 0 1-7.998-7.998v-143.956a7.994 7.994 0 0 1 7.998-7.998h207.936a7.994 7.994 0 0 1 7.998 7.998v143.956a7.994 7.994 0 0 1-7.998 7.998z m-199.938-15.994h191.942v-127.96h-191.942v127.96z" fill=""></path><path d="M488.014 480.016a7.988 7.988 0 0 1-6.864-3.88l-41.128-68.55-41.128 68.55a7.992 7.992 0 0 1-10.972 2.74 7.996 7.996 0 0 1-2.742-10.972l47.986-79.976c2.89-4.812 10.824-4.812 13.714 0l47.984 79.976a7.996 7.996 0 0 1-6.85 12.112z" fill=""></path><path d="M344.044 480.016a7.988 7.988 0 0 1-4.428-1.344 7.988 7.988 0 0 1-2.218-11.09l31.99-47.986c2.968-4.452 10.34-4.452 13.308 0l24.25 36.364a7.992 7.992 0 0 1-2.218 11.09 8.008 8.008 0 0 1-11.09-2.218l-17.596-26.382-25.336 38.004a7.988 7.988 0 0 1-6.662 3.562z" fill=""></path><path d="M344.052 400.042c-13.23 0-23.992-10.764-23.992-23.994s10.762-23.994 23.992-23.994 23.992 10.762 23.992 23.994c0 13.23-10.762 23.994-23.992 23.994z m0-31.99c-4.412 0-7.998 3.584-7.998 7.998s3.586 7.998 7.998 7.998 7.998-3.584 7.998-7.998-3.586-7.998-7.998-7.998z" fill=""></path><path d="M48.618 751.942c-4.412 0-8.076-3.576-8.076-7.996 0-4.422 3.5-7.998 7.918-7.998h0.156a7.992 7.992 0 0 1 7.998 7.998 7.988 7.988 0 0 1-7.996 7.996zM80.608 751.942c-4.412 0-8.076-3.576-8.076-7.996 0-4.422 3.5-7.998 7.918-7.998h0.156a7.994 7.994 0 0 1 7.998 7.998 7.988 7.988 0 0 1-7.996 7.996zM112.598 751.942c-4.412 0-8.076-3.576-8.076-7.996 0-4.422 3.5-7.998 7.918-7.998h0.156a7.994 7.994 0 0 1 7.998 7.998 7.988 7.988 0 0 1-7.996 7.996z" fill=""></path><path d="M280.072 767.938H8.156a7.994 7.994 0 0 1-7.998-7.998v-15.994c0-13.23 10.762-23.992 23.992-23.992h239.926c13.23 0 23.994 10.762 23.994 23.992v15.994a7.994 7.994 0 0 1-7.998 7.998z m-263.92-15.996h255.92v-7.996a7.996 7.996 0 0 0-7.998-7.998H24.15a8 8 0 0 0-7.998 7.998v7.996z" fill=""></path><path d="M280.072 959.878H8.156a7.994 7.994 0 0 1-7.998-7.998v-191.94a7.994 7.994 0 0 1 7.998-7.998h271.916a7.994 7.994 0 0 1 7.998 7.998v191.942a7.994 7.994 0 0 1-7.998 7.996z m-263.92-15.996h255.92v-175.946H16.152v175.946z" fill=""></path><path d="M248.082 847.912H40.146a7.992 7.992 0 0 1-7.998-7.998v-47.984a7.994 7.994 0 0 1 7.998-7.998h207.936a7.994 7.994 0 0 1 7.998 7.998v47.984a7.994 7.994 0 0 1-7.998 7.998z m-199.94-15.994h191.94v-31.99h-191.94v31.99zM128.118 927.888H40.146a7.994 7.994 0 0 1-7.998-7.998v-47.984a7.992 7.992 0 0 1 7.998-7.998h87.972a7.994 7.994 0 0 1 7.998 7.998v47.984a7.994 7.994 0 0 1-7.998 7.998z m-79.976-15.996H120.12v-31.99H48.142v31.99zM248.082 879.902H160.108a7.992 7.992 0 0 1-7.998-7.996 7.994 7.994 0 0 1 7.998-7.998h87.972a7.994 7.994 0 0 1 7.998 7.998 7.99 7.99 0 0 1-7.996 7.996z" fill=""></path><path d="M248.082 911.892H160.108a7.994 7.994 0 0 1-7.998-7.998 7.992 7.992 0 0 1 7.998-7.996h87.972a7.992 7.992 0 0 1 7.998 7.996 7.992 7.992 0 0 1-7.996 7.998z" fill=""></path><path d="M784.386 751.942c-4.406 0-8.062-3.576-8.062-7.996 0-4.422 3.5-7.998 7.906-7.998h0.156a7.982 7.982 0 0 1 7.996 7.998 7.982 7.982 0 0 1-7.996 7.996zM816.376 751.942c-4.402 0-8.058-3.576-8.058-7.996 0-4.422 3.5-7.998 7.902-7.998h0.156c4.438 0 8 3.576 8 7.998a7.984 7.984 0 0 1-8 7.996zM848.368 751.942c-4.406 0-8.062-3.576-8.062-7.996 0-4.422 3.5-7.998 7.906-7.998h0.156a7.982 7.982 0 0 1 7.996 7.998 7.984 7.984 0 0 1-7.996 7.996z" fill=""></path><path d="M1015.848 767.938H743.93a7.992 7.992 0 0 1-7.996-7.998v-15.994c0-13.23 10.762-23.992 23.992-23.992h239.926c13.23 0 23.992 10.762 23.992 23.992v15.994a7.994 7.994 0 0 1-7.996 7.998z m-263.922-15.996h255.922v-7.996a8.002 8.002 0 0 0-7.996-7.998H759.926c-4.406 0-8 3.576-8 7.998v7.996z" fill=""></path><path d="M1015.848 959.878H743.93a7.992 7.992 0 0 1-7.996-7.998v-191.94a7.992 7.992 0 0 1 7.996-7.998h271.918a7.994 7.994 0 0 1 7.996 7.998v191.942a7.994 7.994 0 0 1-7.996 7.996z m-263.922-15.996h255.922v-175.946H751.926v175.946z" fill=""></path><path d="M983.856 847.912H775.918a7.992 7.992 0 0 1-7.996-7.998v-47.984a7.994 7.994 0 0 1 7.996-7.998h207.938a7.992 7.992 0 0 1 7.996 7.998v47.984a7.99 7.99 0 0 1-7.996 7.998z m-199.938-15.994h191.942v-31.99h-191.942v31.99zM863.89 927.888h-87.972a7.994 7.994 0 0 1-7.996-7.998v-47.984a7.992 7.992 0 0 1 7.996-7.998h87.972c4.422 0 8 3.576 8 7.998v47.984a7.994 7.994 0 0 1-8 7.998z m-79.972-15.996h71.976v-31.99h-71.976v31.99zM983.856 879.902h-87.972a7.99 7.99 0 0 1-7.996-7.996 7.99 7.99 0 0 1 7.996-7.998h87.972a7.99 7.99 0 0 1 7.996 7.998 7.99 7.99 0 0 1-7.996 7.996z" fill=""></path><path d="M983.856 911.892h-87.972a7.99 7.99 0 0 1-7.996-7.998 7.99 7.99 0 0 1 7.996-7.996h87.972a7.99 7.99 0 0 1 7.996 7.996 7.99 7.99 0 0 1-7.996 7.998z" fill=""></path><path d="M416.506 799.928c-4.414 0-8.076-3.576-8.076-7.998 0-4.42 3.498-7.998 7.918-7.998h0.156a7.994 7.994 0 0 1 7.998 7.998 7.992 7.992 0 0 1-7.996 7.998zM448.496 799.928c-4.414 0-8.076-3.576-8.076-7.998 0-4.42 3.498-7.998 7.918-7.998h0.156a7.994 7.994 0 0 1 7.998 7.998 7.99 7.99 0 0 1-7.996 7.998zM480.486 799.928c-4.414 0-8.076-3.576-8.076-7.998 0-4.42 3.498-7.998 7.918-7.998h0.156a7.994 7.994 0 0 1 7.998 7.998 7.988 7.988 0 0 1-7.996 7.998z" fill=""></path><path d="M647.958 815.922H376.042a7.992 7.992 0 0 1-7.998-7.996v-15.996c0-13.23 10.762-23.992 23.994-23.992h239.928c13.23 0 23.992 10.762 23.992 23.992v15.996c0 4.42-3.58 7.996-8 7.996z m-263.918-15.994h255.92v-7.998a8.004 8.004 0 0 0-7.996-7.998H392.038a8 8 0 0 0-7.998 7.998v7.998z" fill=""></path><path d="M647.958 1007.864H376.042a7.992 7.992 0 0 1-7.998-7.996v-191.942a7.994 7.994 0 0 1 7.998-7.998h271.916c4.422 0 8 3.576 8 7.998v191.942c0 4.42-3.58 7.996-8 7.996z m-263.918-15.994h255.92v-175.948H384.04v175.948z" fill=""></path><path d="M615.968 895.898H408.032a7.994 7.994 0 0 1-7.998-7.998v-47.986a7.992 7.992 0 0 1 7.998-7.996h207.936a7.99 7.99 0 0 1 7.996 7.996v47.986a7.992 7.992 0 0 1-7.996 7.998z m-199.938-15.996h191.942v-31.99h-191.942v31.99zM496.004 975.874h-87.972a7.994 7.994 0 0 1-7.998-7.998v-47.984a7.994 7.994 0 0 1 7.998-7.998h87.972a7.994 7.994 0 0 1 7.998 7.998v47.984a7.994 7.994 0 0 1-7.998 7.998z m-79.974-15.996h71.976v-31.99h-71.976v31.99zM615.968 927.888h-87.972c-4.422 0-8-3.578-8-7.998s3.578-7.998 8-7.998h87.972c4.422 0 7.996 3.578 7.996 7.998s-3.574 7.998-7.996 7.998z" fill=""></path><path d="M615.968 959.878h-87.972c-4.422 0-8-3.578-8-7.998s3.578-7.998 8-7.998h87.972c4.422 0 7.996 3.578 7.996 7.998s-3.574 7.998-7.996 7.998z" fill=""></path><path d="M575.98 112.13h-111.966a7.994 7.994 0 0 1-7.998-7.998V56.148c0-2.774 1.438-5.35 3.796-6.802a8.018 8.018 0 0 1 7.778-0.352l24.774 12.386 19.946-40.738a8.004 8.004 0 0 1 7.124-4.482c2.644-0.25 5.808 1.664 7.184 4.358l20.882 40.926 24.898-12.45a7.964 7.964 0 0 1 7.782 0.352 7.994 7.994 0 0 1 3.792 6.802v47.984a7.986 7.986 0 0 1-7.992 7.998z m-103.968-15.996h95.972V69.088l-20.418 10.208a8.008 8.008 0 0 1-10.7-3.514l-17.222-33.74-16.462 33.614a8 8 0 0 1-4.624 4.062 7.91 7.91 0 0 1-6.138-0.422l-20.408-10.208v27.046z" fill=""></path><path d="M519.996 703.958A7.994 7.994 0 0 1 512 695.96v-111.964a7.994 7.994 0 0 1 7.996-7.998c4.422 0 8 3.578 8 7.998v111.964a7.996 7.996 0 0 1-8 7.998z" fill=""></path><path d="M519.996 703.958a7.994 7.994 0 0 1-5.652-13.652l15.996-15.996a7.996 7.996 0 1 1 11.308 11.31l-15.996 15.994a7.976 7.976 0 0 1-5.656 2.344z" fill=""></path><path d="M519.996 703.958a7.974 7.974 0 0 1-5.652-2.344l-15.996-15.994a7.996 7.996 0 1 1 11.308-11.31l15.996 15.996a7.994 7.994 0 0 1-5.656 13.652z" fill=""></path><path d="M168.106 655.972a7.994 7.994 0 0 1-5.654-13.652l79.976-79.976a7.994 7.994 0 0 1 11.308 0 7.994 7.994 0 0 1 0 11.308L173.76 653.628a7.964 7.964 0 0 1-5.654 2.344z" fill=""></path><path d="M190.724 655.972H168.106c-4.42 0-7.998-3.578-7.998-7.998s3.576-7.998 7.998-7.998h22.618c4.42 0 7.998 3.578 7.998 7.998s-3.578 7.998-7.998 7.998z" fill=""></path><path d="M168.106 655.972a7.994 7.994 0 0 1-7.998-7.998v-22.62c0-4.42 3.576-7.996 7.998-7.996s7.998 3.576 7.998 7.996v22.62a7.996 7.996 0 0 1-7.998 7.998z" fill=""></path><path d="M871.89 655.972a7.976 7.976 0 0 1-5.656-2.344l-79.972-79.976a7.994 7.994 0 0 1 0-11.308 7.994 7.994 0 0 1 11.308 0l79.972 79.976a7.994 7.994 0 0 1 0 11.308 7.948 7.948 0 0 1-5.652 2.344z" fill=""></path><path d="M871.89 655.972h-22.618c-4.422 0-8-3.578-8-7.998s3.578-7.998 8-7.998h22.618c4.418 0 7.996 3.578 7.996 7.998s-3.578 7.998-7.996 7.998z" fill=""></path><path d="M871.89 655.972c-4.422 0-8-3.578-8-7.998v-22.62a7.994 7.994 0 0 1 8-7.996 7.992 7.992 0 0 1 7.996 7.996v22.62a7.994 7.994 0 0 1-7.996 7.998z" fill=""></path></g></svg>
              </div>
              <h5 class="mb-2">Multi-platform Access</h5>
              <p class="features-icon-description">
              Access the system from any device - desktop, tablet, or mobile phone.
              </p>
            </div>
            <div class="col-lg-4 col-sm-6 text-center features-icon-box">
              <div class="mb-4 text-primary text-center">
              <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M14.5 10.75H8.5C8.09 10.75 7.75 10.41 7.75 10C7.75 9.59 8.09 9.25 8.5 9.25H14.5C14.91 9.25 15.25 9.59 15.25 10C15.25 10.41 14.91 10.75 14.5 10.75Z" fill="#292D32"></path> <path d="M11.5 13.75H8.5C8.09 13.75 7.75 13.41 7.75 13C7.75 12.59 8.09 12.25 8.5 12.25H11.5C11.91 12.25 12.25 12.59 12.25 13C12.25 13.41 11.91 13.75 11.5 13.75Z" fill="#292D32"></path> <path opacity="0.4" d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z" fill="#292D32"></path> <path d="M21.3005 22.0001C21.1205 22.0001 20.9405 21.9301 20.8105 21.8001L18.9505 19.9401C18.6805 19.6701 18.6805 19.2301 18.9505 18.9501C19.2205 18.6801 19.6605 18.6801 19.9405 18.9501L21.8005 20.8101C22.0705 21.0801 22.0705 21.5201 21.8005 21.8001C21.6605 21.9301 21.4805 22.0001 21.3005 22.0001Z" fill="#292D32"></path> </g></svg>
              </div>
              <h5 class="mb-2">Real-time Result</h5>
              <p class="features-icon-description">
              Monitor election progress and view results in real-time with our advanced dashboard.
              </p>
            </div>
            <div class="col-lg-4 col-sm-6 text-center features-icon-box">
              <div class="text-center mb-4 text-primary">
                <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path opacity="0.4" d="M20.9099 11.12C20.9099 16.01 17.3599 20.59 12.5099 21.93C12.1799 22.02 11.8198 22.02 11.4898 21.93C6.63984 20.59 3.08984 16.01 3.08984 11.12V6.73006C3.08984 5.91006 3.70986 4.98007 4.47986 4.67007L10.0498 2.39007C11.2998 1.88007 12.7098 1.88007 13.9598 2.39007L19.5298 4.67007C20.2898 4.98007 20.9199 5.91006 20.9199 6.73006L20.9099 11.12Z" fill="#292D32"></path> <path d="M14.5 10.5C14.5 9.12 13.38 8 12 8C10.62 8 9.5 9.12 9.5 10.5C9.5 11.62 10.24 12.55 11.25 12.87V15.5C11.25 15.91 11.59 16.25 12 16.25C12.41 16.25 12.75 15.91 12.75 15.5V12.87C13.76 12.55 14.5 11.62 14.5 10.5Z" fill="#292D32"></path> </g></svg>
                    
              </div>
              <h5 class="mb-2">Secure Voting</h5>
              <p class="features-icon-description">End-to-end encryption and blockchain technology ensure the integrity and security of every vote.</p>
            </div>
            <div class="col-lg-4 col-sm-6 text-center features-icon-box">
              <div class="text-center mb-4 text-primary">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    opacity="0.2"
                    d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z"
                    fill="#000000" />
                  <path
                    d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z"
                    stroke="#000000"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round" />
                  <path
                    d="M32 20V32L40 40"
                    stroke="#000000"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </div>
              <h5 class="mb-2">Automated Scheduling</h5>
              <p class="features-icon-description">Set up election timelines, reminders, and automated notifications for voters and administrators</p>
            </div>
          </div>
        </div>
      </section>
      <!-- Useful features: End -->

      <!-- Real customers reviews: Start -->
      <section id="landingReviews" class="section-py bg-body landing-reviews pb-0">
        <!-- What people say slider: Start -->
        <div class="container">
          <div class="row align-items-center gx-0 gy-4 g-lg-5 mb-5 pb-md-5">
            <div class="col-md-6 col-lg-5 col-xl-3">
              <div class="mb-4">
                <span class="badge bg-label-primary">Real Customers Reviews</span>
              </div>
              <h4 class="mb-1">
                <span class="position-relative fw-extrabold z-1"
                  >What people say
                  <img
                    src="assets/img/front-pages/icons/section-title-icon.png"
                    alt="laptop charging"
                    class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
              </h4>
              <p class="mb-5 mb-md-12">
                See what our customers have to<br class="d-none d-xl-block" />
                say about their experience.
              </p>
              <div class="landing-reviews-btns">
                <button id="reviews-previous-btn" class="btn btn-icon btn-label-primary reviews-btn me-3" type="button">
                  <i class="icon-base bx bx-chevron-left icon-md scaleX-n1-rtl"></i>
                </button>
                <button id="reviews-next-btn" class="btn btn-icon btn-label-primary reviews-btn" type="button">
                  <i class="icon-base bx bx-chevron-right icon-md scaleX-n1-rtl"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6 col-lg-7 col-xl-9">
              <div class="swiper-reviews-carousel overflow-hidden">
                <div class="swiper" id="swiper-reviews">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <div class="card h-100">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          
                          <p>
                          The Election Management System has completely transformed how we run our university student council elections. The process is now more transparent, secure, and efficient than ever before
                          </p>
                          <div class="text-warning mb-4">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                          </div>
                          <div class="d-flex align-items-center">
                            
                            <div>
                              <h6 class="mb-0">Sarah Afrifa</h6>
                              <p class="small text-body-secondary mb-0">SRC Organizer</p>
                             
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="card h-100">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <p>
                          "As a city election commissioner, I needed a reliable system that could handle thousands of voters. This platform delivered beyond expectations with its robust security features and real-time reporting."
                          </p>
                          <div class="text-warning mb-4">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                          </div>
                          <div class="d-flex align-items-center">
                           
                            <div>
                              <h6 class="mb-0">Emmanuel Danso</h6>
                              <p class="small text-body-secondary mb-0">SRC President</p>
                             
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  
                    <div class="swiper-slide">
                      <div class="card h-100">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <p>
                          "The analytics and reporting features have been invaluable for our organization. We can now make data-driven decisions about our election processes and improve voter engagement."
                          </p>
                          <div class="text-warning mb-4">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bx-star"></i>
                          </div>
                          <div class="d-flex align-items-center">
                            <div>
                              <h6 class="mb-0">Lilian Maryes</h6>
                              <p class="small text-body-secondary mb-0"> SRC Secretary</p>
                           
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="card h-100">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                          <p>
                          "Setting up our corporate board elections used to be a logistical nightmare. With this system, we've cut preparation time by 70% and increased participation rates significantly."
                          </p>
                          <div class="text-warning mb-4">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                          </div>
                          <div class="d-flex align-items-center">
                            
                            <div>
                              <h6 class="mb-0">Gloria Adams</h6>
                              <p class="small text-body-secondary mb-0">SRC Vice President</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="card h-100">
                        <div class="card-body text-body d-flex flex-column justify-content-between h-100">
    
                          <p>
                          "The accessibility features of this platform have allowed us to include voters with disabilities in our election process like never before. It's truly an inclusive solution."
                          </p>
                          <div class="text-warning mb-4">
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bxs-star"></i>
                            <i class="icon-base bx bx-star"></i>
                          </div>
                          <div class="d-flex align-items-center">
                              <h6 class="mb-0">Joseph Appiah</h6>
                              <p class="small text-body-secondary mb-0">Lecturer</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-button-next"></div>
                  <div class="swiper-button-prev"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr class="m-0 mt-6 mt-md-12" />
        </div>
    
      </section>
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
      </section>
      <section id="landingPricing" class="section-py bg-body landing-pricing">
        <div class="container">
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">Pricing Plans</span>
          </div>
          <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1"
              >Tailored pricing plans
              <img
                src="assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            designed for all educational institution
          </h4>
          <p class="text-center pb-2 mb-7">
          Choose the perfect plan for your election needs, from small organizations to large-scale educational elections
          </p>
          <div class="text-center mb-12">
            <div class="position-relative d-inline-block pt-3 pt-md-0">
              <label class="switch switch-sm switch-primary me-0">
                <span class="switch-label fs-6 text-body me-3">Pay Monthly</span>
                <input type="checkbox" class="switch-input price-duration-toggler" checked />
                <span class="switch-toggle-slider">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
                <span class="switch-label fs-6 text-body ms-3">Pay Annual</span>
              </label>
              <div class="pricing-plans-item position-absolute d-flex">
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
                    <a href="payment-page.php?plan=team" class="btn btn-primary">Get Started</a>
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
                    <a href="payment-page.php?plan=enterprise" class="btn btn-label-primary">Get Started</a>
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
                        d="M14.125 50.9038C11.825 48.6038 13.35 43.7788 12.175 40.9538C11 38.1288 6.5 35.6538 6.5 32.5288C6.5 29.4038 10.95 27.0288 12.175 24.1038C13.4 21.1788 11.825 16.4538 14.125 14.1538C16.425 11.8538 21.25 13.3788 24.075 12.2038C26.9 11.0288 29.375 6.52881 32.5 6.52881C35.625 6.52881 38 10.9788 40.925 12.2038C43.85 13.4288 48.575 11.8538 50.875 14.1538C53.175 16.4538 51.65 21.2788 52.825 24.1038C54 26.9288 58.5 29.4038 58.5 32.5288C58.5 35.6538 54.05 38.0288 52.825 40.9538C51.6 43.8788 53.175 48.6038 50.875 50.9038C48.575 53.2038 43.75 51.6788 40.925 52.8538C38.1 54.0288 35.625 58.5288 32.5 58.5288C29.375 58.5288 27 54.0788 24.075 52.8538C21.15 51.6288 16.425 53.2038 14.125 50.9038Z"
                        fill="currentColor" />
                      <path
                        d="M43.5 26.5288L28.825 40.5288L21.5 33.5288M14.125 50.9038C11.825 48.6038 13.35 43.7788 12.175 40.9538C11 38.1288 6.5 35.6538 6.5 32.5288C6.5 29.4038 10.95 27.0288 12.175 24.1038C13.4 21.1788 11.825 16.4538 14.125 14.1538C16.425 11.8538 21.25 13.3788 24.075 12.2038C26.9 11.0288 29.375 6.52881 32.5 6.52881C35.625 6.52881 38 10.9788 40.925 12.2038C43.85 13.4288 48.575 11.8538 50.875 14.1538C53.175 16.4538 51.65 21.2788 52.825 24.1038C54 26.9288 58.5 29.4038 58.5 32.5288C58.5 35.6538 54.05 38.0288 52.825 40.9538C51.6 43.8788 53.175 48.6038 50.875 50.9038C48.575 53.2038 43.75 51.6788 40.925 52.8538C38.1 54.0288 35.625 58.5288 32.5 58.5288C29.375 58.5288 27 54.0788 24.075 52.8538C21.15 51.6288 16.425 53.2038 14.125 50.9038Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                  <h3 class="mb-0">100%</h3>
                  <p class="fw-medium mb-0">
                    Money Back<br />
                    Guarantee
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    

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
    <p class="text-center mb-12 pb-md-4">
      Browse through these FAQs to find answers to commonly asked questions about SmartVote.
    </p>
    <div class="row gy-12 align-itSmartVote-center">
      <div class="col-lg-5">
        <div class="text-center">
          <img
            src="assets/img/front-pages/landing-page/faq-boy-with-logos.png"
            alt="faq boy with logos"
            class="faq-image" />
        </div>
      </div>
      <div class="col-lg-7">
        <div class="accordion" id="accordionExample">
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button
                type="button"
                class="accordion-button"
                data-bs-toggle="collapse"
                data-bs-target="#accordionOne"
                aria-expanded="true"
                aria-controls="accordionOne">
                Is SmartVote free to use for elections?
              </button>
            </h2>
            <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                SmartVote offers a free tier for small-scale elections, such as student council votes or small community polls. For larger elections or advanced features, you can upgrade to a paid plan.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#accordionTwo"
                aria-expanded="false"
                aria-controls="accordionTwo">
                Can SmartVote handle large-scale elections?
              </button>
            </h2>
            <div
              id="accordionTwo"
              class="accordion-collapse collapse"
              aria-labelledby="headingTwo"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Yes, SmartVote is designed to handle elections of all sizes, from small local polls to large national elections. Our platform scales to meet your needs.
              </div>
            </div>
          </div>
          <div class="card accordion-item active">
            <h2 class="accordion-header" id="headingThree">
              <button
                type="button"
                class="accordion-button"
                data-bs-toggle="collapse"
                data-bs-target="#accordionThree"
                aria-expanded="false"
                aria-controls="accordionThree">
                How does SmartVote ensure election security?
              </button>
            </h2>
            <div
              id="accordionThree"
              class="accordion-collapse collapse show"
              aria-labelledby="headingThree"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                SmartVote uses advanced encryption, multi-factor authentication, and blockchain technology to ensure the integrity and security of your elections. Regular audits are conducted to maintain the highest standards.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFour">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#accordionFour"
                aria-expanded="false"
                aria-controls="accordionFour">
                Can I customize the voting process with SmartVote?
              </button>
            </h2>
            <div
              id="accordionFour"
              class="accordion-collapse collapse"
              aria-labelledby="headingFour"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Absolutely! SmartVote allows you to customize ballot designs, voting rules, and eligibility criteria to fit the specific needs of your election.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFive">
              <button
                type="button"
                class="accordion-button collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#accordionFive"
                aria-expanded="false"
                aria-controls="accordionFive">
                What kind of support does SmartVote offer?
              </button>
            </h2>
            <div
              id="accordionFive"
              class="accordion-collapse collapse"
              aria-labelledby="headingFive"
              data-bs-parent="#accordionExample">
              <div class="accordion-body">
                SmartVote provides 24/7 customer support via email, chat, and phone. We also offer detailed documentation and training resources to help you get started.
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
      <section id="landingCTA" class="section-py landing-cta position-relative p-lg-0 pb-0">
        <img
          src="assets/img/front-pages/backgrounds/cta-bg-light.png"
          class="position-absolute bottom-0 end-0 scaleX-n1-rtl h-100 w-100 z-n1"
          alt="cta image"
          data-app-light-img="front-pages/backgrounds/cta-bg-light.png"
          data-app-dark-img="front-pages/backgrounds/cta-bg-dark.png" />
        <div class="container">
        <div class="row align-items-center gy-12">
      <div class="col-lg-6 text-start text-sm-center text-lg-start">
        <h3 class="cta-title text-primary fw-bold mb-1">Ready to Streamline Your Elections?</h3>
        <h5 class="text-body mb-8">Experience SmartVote EMS with a 30-day free trial</h5>
        <a href="payment-page.php?plan=team" class="btn btn-lg btn-primary">Get Started</a>
      </div>
            <div class="col-lg-6 pt-lg-12 text-center text-lg-end">
              <img
                src="assets/img/front-pages/landing-page/cta-dashboard.png"
                alt="cta dashboard"
                class="img-fluid mt-lg-4" />
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
    <foote class="landing-footer bg-body footer-text">
      <div class="footer-top position-relative overflow-hidden z-1">
        <img
          src="assets/img/front-pages/backgrounds/footer-bg.png"
          alt="footer bg"
          class="footer-bg banner-bg-img z-n1" />
        <div class="container">
          <div class="row gx-0 gy-6 g-lg-10">
            <div class="col-lg-5">
              <a href="landing-page.html" class="app-brand-link mb-6">
                <span class="app-brand-logo demo">
                  <span class="text-primary">
                  <img src="assets/img/favicon/favicon.ico" alt="logo" width="22%" class="logo-img" />
                </span>
                <span class="app-brand-text demo text-white fw-bold ms-2 ps-1">SmartVote</span>
              </a>
              <p class="footer-text footer-logo-description mb-6">
SmartVote is a secure, developer-friendly, and highly customizable educational voting system designed to streamline elections and decision-making in academic institutions.
              </p>
              <form class="footer-form">
                <label for="footer-email" class="small">Subscribe to newsletter</label>
                <div class="d-flex mt-1">
                  <input
                    type="email"
                    class="form-control rounded-0 rounded-start-bottom rounded-start-top"
                    id="footer-email"
                    placeholder="Your email" />
                  <button
                    type="submit"
                    class="btn btn-primary shadow-none rounded-0 rounded-end-bottom rounded-end-top">
                    Subscribe
                  </button>
                </div>
              </form>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
             
            </div>
            
          </div>
        </div>
        </div>
      </div>
      
      <div class="footer-bottom py-3 py-md-5">
        <div
          class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
          <div class="mb-2 mb-md-0">
            <span class="footer-bottom-text"
              >©
              <script>
                document.write(new Date().getFullYear());
              </script>
            </span>
            <a href="https://www.github.com/aristocratjnr" target="_blank" class="text-white">Election Management System</a><br>
            <span class="footer-bottom-text"> Made by Obuobi Ayim David</span>
          </div>
          <div>
            <a href="https://www.github.com/aristocratjnr" class="me-4 text-white" target="_blank">
              <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M10.7184 2.19556C6.12757 2.19556 2.40674 5.91639 2.40674 10.5072C2.40674 14.1789 4.78757 17.2947 8.0909 18.3947C8.50674 18.4697 8.65674 18.2139 8.65674 17.9939C8.65674 17.7964 8.65007 17.2731 8.64757 16.5806C6.33507 17.0822 5.84674 15.4656 5.84674 15.4656C5.47007 14.5056 4.92424 14.2497 4.92424 14.2497C4.17007 13.7339 4.98174 13.7456 4.98174 13.7456C5.81674 13.8039 6.25424 14.6022 6.25424 14.6022C6.9959 15.8722 8.2009 15.5056 8.67257 15.2931C8.7484 14.7556 8.96507 14.3889 9.20174 14.1814C7.35674 13.9722 5.41674 13.2589 5.41674 10.0731C5.41674 9.16722 5.74091 8.42389 6.27007 7.84389C6.1859 7.63306 5.89841 6.78722 6.35257 5.64389C6.35257 5.64389 7.05007 5.41972 8.63757 6.49472C9.31557 6.31028 10.0149 6.21614 10.7176 6.21472C11.4202 6.21586 12.1196 6.31001 12.7976 6.49472C14.3859 5.41889 15.0826 5.64389 15.0826 5.64389C15.5367 6.78722 15.2517 7.63306 15.1651 7.84389C15.6984 8.42389 16.0184 9.16639 16.0184 10.0731C16.0184 13.2672 14.0767 13.9689 12.2251 14.1747C12.5209 14.4314 12.7876 14.9381 12.7876 15.7131C12.7876 16.8247 12.7776 17.7214 12.7776 17.9939C12.7776 18.2164 12.9259 18.4747 13.3501 18.3931C16.6517 17.2914 19.0301 14.1781 19.0301 10.5072C19.0301 5.91639 15.3092 2.19556 10.7184 2.19556Z"
                  fill="currentColor" />
              </svg>
        </div>
      
    </footer>

    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <script src="assets/vendor/libs/pickr/pickr.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/nouislider/nouislider.js"></script>
    <script src="assets/vendor/libs/swiper/swiper.js"></script>

    <!-- Main JS -->

    <script src="assets/js/front-main.js"></script>

    <!-- Page JS -->
    <script src="assets/js/front-page-landing.js"></script>
    
<!-- Theme Switcher JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get current theme from localStorage or detect system preference
  const getPreferredTheme = () => {
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
      return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  // Set the theme
  const setTheme = (theme) => {
    if (theme === 'auto') {
      document.documentElement.setAttribute('data-bs-theme', 
        window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
      );
    } else {
      document.documentElement.setAttribute('data-bs-theme', theme);
    }
    
    // Update icon
    const themeIcon = document.querySelector('.theme-icon');
    if (themeIcon) {
      themeIcon.className = theme === 'dark' ? 'bx bx-moon fs-5 me-2 theme-icon' : 'bx bx-sun fs-5 me-2 theme-icon';
    }
    
    // Update active state in dropdown
    document.querySelectorAll('.theme-item').forEach(item => {
      item.classList.toggle('active', item.getAttribute('data-bs-theme-value') === theme);
    });
  };

  // Initialize theme
  setTheme(getPreferredTheme());

  // Watch for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (localStorage.getItem('theme') === 'auto') {
      setTheme('auto');
    }
  });

  // Handle theme selection
  document.querySelectorAll('[data-bs-theme-value]').forEach(item => {
    item.addEventListener('click', () => {
      const theme = item.getAttribute('data-bs-theme-value');
      localStorage.setItem('theme', theme);
      setTheme(theme);
    });
  });
});
</script>
  </body>
</html>
