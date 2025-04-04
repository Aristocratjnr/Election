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
                <svg width="64px" height="64px" viewBox="-38.33 0 341.323 341.323" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><defs><style>.a{fill:#ffffff;}.b{fill:#908b8a;}.c{fill:#211715;}.d{fill:#e2e2e2;}</style></defs><path class="a" d="M164.219,135.526l-63.569-5.547c-1.408-6.5-3.546-10.521-4.436-17.417a29.882,29.882,0,0,1,1.509-14.1q.6-1.638,1.17-3.289c4.474-13,4.685-23.743,9.053-36.751a18.856,18.856,0,0,1,7.765-9.959c32.491-20.546,54.242-30.21,89.245-46.1l45.63,32.593C243.1,48.165,239.521,54.292,232.04,67.506c-2.531,4.47-5.1,8.992-8.657,12.7-5.678,5.916-13.411,9.3-20.385,13.616a83.29,83.29,0,0,0-7.678,5.368c-.038-1.718,8.29-.455,11.287.646,2.173.8,4.5,1.816,5.569,3.867a6.383,6.383,0,0,1-.735,6.439c-1.3,1.863-7.044,6.211-11.03,6.951l-1.313.243.293.1c3.958,1.345,5.9,6.351,4.611,10.328s-5.244,6.334-9.2,7.677c-3.2,1.088-13.352,1.088-19.416,1.088-1.586,0-8.683-.566-11.165-1"></path><path class="b" d="M10.818,339V305.752c15.213.108,34.458.34,47.707.34,15.17,0,114.941.057,150.367.057,11.165,0,34.06-.327,44.212-.327,0,15.967,0,33.182,0,33.182"></path><path class="a" d="M172.712,123.751A162.544,162.544,0,0,1,186.2,107.033a84.121,84.121,0,0,1,8.676-7.849,22.125,22.125,0,0,1,11.288.649c2.173.8,4.5,1.816,5.569,3.867a6.383,6.383,0,0,1-.735,6.439c-1.3,1.863-7.044,6.211-11.03,6.951l-1.31.244.29.1c3.958,1.345,5.9,6.351,4.611,10.328s-5.244,6.334-9.2,7.677c-3.2,1.088-13.352,1.088-19.416,1.088a54.323,54.323,0,0,1-10.811-1.435C167.1,131.417,169.9,127.58,172.712,123.751Z"></path><path class="b" d="M16.7,247.228c6.476-10.609,13.1-21.288,17.45-26.757,15.229.062,24.93.249,40.4.249,15.171,0,99.771.359,135.2.359,9.122,0,16.388.23,23.071.213,7.072,12.919,22.124,39.574,29.3,51.754.014,4.386.035,8.356.06,15.068.018,4.966.087,11.638.084,17.593-6.412,0-44.544.442-53.361.442-35.426,0-135.2-.057-150.367-.057-15.85,0-40.279-.332-56.124-.386-.027-6.094.216-13.035.2-18.159q-.031-8.739-.066-17.476C6.3,264.379,11.45,255.827,16.7,247.228Z"></path><path class="c" d="M18.771,248.439c3.092-5.063,6.192-10.121,9.4-15.113,2.433-3.787,4.888-7.632,7.677-11.158l-1.7.7c14.818.062,29.635.226,44.454.254q14.051.025,28.1.082,19.963.066,39.927.132,20.327.063,40.654.111c13.23.028,26.456.051,39.686.208,1.947.023,3.9.038,5.843.034L230.74,222.5c7.7,14.054,15.6,28,23.578,41.9q2.838,4.941,5.719,9.858l-.328-1.211c.036,10.887.147,21.774.144,32.661l2.4-2.4c-15.584,0-31.17.294-46.754.408-14.507.107-29.016.03-43.522.025l-59.011-.026-46.456-.02c-18.667-.006-37.332-.213-56-.344q-4.056-.029-8.111-.044l2.4,2.4c-.019-5.6.175-11.205.2-16.808.025-6.275-.041-12.552-.066-18.827L4.6,271.282c4.936-7.476,9.5-15.2,14.167-22.843,1.614-2.644-2.536-5.058-4.144-2.423-4.289,7.028-8.508,14.1-12.977,21.021a13.146,13.146,0,0,0-1.311,2.109,9.822,9.822,0,0,0-.2,3.5q.014,3.469.026,6.939c.016,4.144.058,8.288,0,12.432C.1,296.577-.014,301.141,0,305.706a2.436,2.436,0,0,0,2.4,2.4c20.245.071,40.488.381,60.734.387q21.509.006,43.017.018l59.016.026c15.3.006,30.6.074,45.905.006,16.1-.072,32.2-.322,48.3-.425.961-.006,1.921-.012,2.881-.011a2.435,2.435,0,0,0,2.4-2.4c0-7.563-.073-15.126-.1-22.689q-.019-4.483-.037-8.966a4.756,4.756,0,0,0-.453-2.425c-.258-.482-.555-.946-.831-1.417q-.88-1.507-1.757-3.018-9.077-15.654-17.89-31.458-4.378-7.809-8.7-15.653a2.4,2.4,0,0,0-2.072-1.189c-8.94.016-17.88-.208-26.821-.214q-9.354-.006-18.708-.028-22.892-.048-45.781-.128-21.582-.069-43.166-.142-14.73-.047-29.461-.069c-10.105-.03-20.209-.158-30.313-.218-1.3-.007-2.608-.046-3.909-.02-1.721.035-2.351.87-3.313,2.132-1.227,1.609-2.388,3.269-3.526,4.942-3.009,4.426-5.853,8.964-8.671,13.513q-2.272,3.669-4.517,7.356C13.012,248.66,17.162,251.074,18.771,248.439Z"></path><path class="c" d="M22.322,273.863c16.134.061,32.266.385,48.4.568,10.8.123,21.6.124,32.4.13l44.135.026q24.057.014,48.113.022,21.821.006,43.639.1,1.974,0,3.947.007c3.088,0,3.093-4.8,0-4.8-14.162,0-28.323-.1-42.484-.1q-23.581,0-47.162-.02-22.89-.012-45.778-.027c-10.337,0-20.674.026-31.009-.071-16.514-.155-33.027-.467-49.541-.6q-2.328-.019-4.656-.029c-3.089-.011-3.093,4.789,0,4.8Z"></path><path class="c" d="M8.418,309.073V339c0,3.089,4.8,3.094,4.8,0V309.073c0-3.088-4.8-3.094-4.8,0Z"></path><path class="c" d="M250.7,309.073V339c0,3.089,4.8,3.094,4.8,0V309.073c0-3.088-4.8-3.094-4.8,0Z"></path><path class="c" d="M197.448,100.75a24.207,24.207,0,0,1,6.427.8,20.583,20.583,0,0,1,2.881.9,10.072,10.072,0,0,1,2.558,1.454,2.763,2.763,0,0,1,1.032,2.092,4.922,4.922,0,0,1-.9,2.819c-.321.513.227-.2-.035.051-.113.111-.213.239-.323.354-.287.3-.59.581-.9.855a17.658,17.658,0,0,1-2.147,1.673,16.718,16.718,0,0,1-6.271,3.033,2.424,2.424,0,0,0-1.676,2.952,2.449,2.449,0,0,0,2.952,1.677,26.013,26.013,0,0,0,12.429-8,8.61,8.61,0,0,0,.563-9.268c-1.641-2.6-4.682-4-7.533-4.856a30.136,30.136,0,0,0-9.06-1.326c-3.085.063-3.095,4.863,0,4.8Z"></path><path class="c" d="M203.745.286c-21.5,9.76-43.066,19.412-63.715,30.9q-8.014,4.457-15.856,9.215-4.263,2.583-8.481,5.239a24.716,24.716,0,0,0-6.793,5.921,25.519,25.519,0,0,0-3.872,8.09c-1.007,3.233-1.824,6.522-2.536,9.831-1.354,6.29-2.37,12.651-4.033,18.872-.98,3.667-2.369,7.173-3.5,10.788a31.813,31.813,0,0,0-1.347,11.348c.434,7,3.223,13.361,4.719,20.13.667,3.015,5.3,1.739,4.629-1.276-1.261-5.7-3.618-11.133-4.374-16.951a28.082,28.082,0,0,1,.355-9.519c.7-3.215,2.045-6.253,3.026-9.387a179.687,179.687,0,0,0,4.35-18.838c.609-3.074,1.257-6.142,2.043-9.176a39.617,39.617,0,0,1,2.935-8.8c2.789-5.2,8.183-7.778,13.03-10.745Q132.086,41.177,140,36.7c9.671-5.454,19.542-10.538,29.542-15.359,12.112-5.841,24.38-11.351,36.622-16.909a2.417,2.417,0,0,0,.861-3.284,2.457,2.457,0,0,0-3.283-.861Z"></path><path class="c" d="M133.292,78.636q9.823,4.215,19.841,7.952a2.42,2.42,0,0,0,2.952-1.676,2.453,2.453,0,0,0-1.676-2.953q-9.436-3.507-18.694-7.468a2.481,2.481,0,0,0-3.284.861,2.417,2.417,0,0,0,.861,3.284Z"></path><path class="c" d="M142.327,80.175A77.852,77.852,0,0,1,130.843,98.1a9.888,9.888,0,0,0-2.581,4.738,19.277,19.277,0,0,0-.22,4.88c.281,3.353,1.157,6.676,1.029,10.057-.117,3.089,4.684,3.085,4.8,0,.121-3.19-.65-6.329-.974-9.486a16.167,16.167,0,0,1,.026-4.378c.265-1.542,1.494-2.612,2.448-3.773a79.564,79.564,0,0,0,11.1-17.542,2.472,2.472,0,0,0-.861-3.284,2.419,2.419,0,0,0-3.283.861Z"></path><path class="c" d="M180.028,117.066a40.849,40.849,0,0,1,15.186,1.436c.813.217,1.612.47,2.407.748.095.034.724.3.137.047.144.063.279.143.422.208a7.776,7.776,0,0,1,1.679.863,6.265,6.265,0,0,1,2.131,5.178c-.223,4.537-5.543,7.289-9.51,7.966a59.09,59.09,0,0,1-6.674.49c-2.631.09-5.263.114-7.9.121-2.134,0-4.238-.055-6.367-.22q-1.893-.147-3.783-.336c-1.241-.126-2-.2-2.9-.356a2.473,2.473,0,0,0-2.953,1.677,2.419,2.419,0,0,0,1.677,2.952,82.108,82.108,0,0,0,13.668,1.084c5.5-.008,11.181.144,16.626-.8,5.534-.963,11.746-5.116,12.737-10.974a11.393,11.393,0,0,0-1.722-8.279,11.647,11.647,0,0,0-6.195-4.32,42.455,42.455,0,0,0-8.455-1.914,39.265,39.265,0,0,0-10.212-.368,2.475,2.475,0,0,0-2.4,2.4,2.413,2.413,0,0,0,2.4,2.4Z"></path><path class="d" d="M70,246.759c-9.2-32.1-19.968-67.746-29.138-99.692,11.492-3.3,31.586-9.037,47.106-13.508,15.24-4.389,32.938-9.087,42.528-11.837,6.352-1.821,25.566-7.6,28.37-8.4.734,2.56,16.2,56.957,19.719,69.227,2.661,9.283,17.312,58.4,19.041,64.435Z"></path><path class="c" d="M72.315,246.121c-7.5-26.134-15.293-52.179-22.925-78.273q-3.131-10.7-6.214-21.419L41.5,149.381c17.6-5.047,35.187-10.155,52.808-15.133,15.666-4.426,31.406-8.571,47.011-13.208,6.062-1.8,12.108-3.66,18.185-5.407l-2.952-1.676q2.8,9.768,5.563,19.545,4.776,16.782,9.555,33.562c2.041,7.165,4.064,14.335,6.163,21.483q4.612,15.7,9.289,31.38c2.617,8.82,5.255,17.634,7.834,26.465q.179.613.356,1.227c.85,2.964,5.484,1.7,4.628-1.276-2.252-7.848-4.621-15.664-6.944-23.492q-4.84-16.312-9.666-32.631c-2.037-6.911-4.013-13.839-5.988-20.768q-4.743-16.641-9.476-33.283-3.248-11.412-6.5-22.823l-.189-.665a2.437,2.437,0,0,0-2.953-1.676c-11.3,3.249-22.516,6.8-33.847,9.945-18.632,5.18-37.257,10.365-55.848,15.694q-14.154,4.057-28.309,8.109a2.435,2.435,0,0,0-1.676,2.952c7.495,26.1,15.286,52.123,22.906,78.191q3.141,10.743,6.232,21.5c.85,2.964,5.483,1.7,4.629-1.276Z"></path><path class="c" d="M79.9,182.077c10.306-2.955,20.6-5.946,30.918-8.86,9.172-2.592,18.388-5.019,27.524-7.734,3.542-1.052,7.075-2.136,10.626-3.159,2.963-.853,1.7-5.486-1.276-4.628-6.622,1.907-13.193,3.981-19.833,5.827-10.912,3.034-21.82,6.07-32.707,9.191q-8.264,2.369-16.528,4.734c-2.964.85-1.7,5.483,1.276,4.629Z"></path><path class="c" d="M84.909,201.829c10.3-2.955,20.6-5.945,30.918-8.86,9.172-2.591,18.387-5.018,27.524-7.733,3.542-1.052,7.075-2.137,10.626-3.159,2.963-.854,1.7-5.487-1.276-4.629-6.622,1.907-13.194,3.982-19.833,5.827-10.912,3.034-21.82,6.071-32.708,9.192Q91.9,194.835,83.633,197.2c-2.964.85-1.7,5.483,1.276,4.628Z"></path><path class="c" d="M61.465,249.381c15.636,0,31.273-.247,46.908-.359,21.239-.151,42.474.163,63.713.206,10.628.022,21.255,0,31.883-.006,3.089,0,3.094-4.8,0-4.8-24.029,0-48.056.02-72.083-.239-16.189-.175-32.386.1-48.574.24-7.282.061-14.565.157-21.847.158-3.088,0-3.093,4.8,0,4.8Z"></path><path class="a" d="M153.674,74.1c-.042,5.231,1.668,8.109,1.974,13.094.033,16.967-10.318,31.975-17.144,47.546-1.906,4.346-3.39,9.881-.256,13.444,2.108,2.4,5.758,2.957,8.833,2.1s5.681-2.882,8.044-5.029c12.241-11.122,20.058-26.305,31.518-38.23A84,84,0,0,1,203,93.819Z"></path><path class="c" d="M151.274,74.1c.007,3.955,1.261,7.592,1.814,11.46s-.171,8.016-1.054,11.774c-1.9,8.105-5.573,15.627-9.268,23.037-1.887,3.785-3.8,7.559-5.553,11.408-1.61,3.539-3.227,7.274-3.18,11.243a9.755,9.755,0,0,0,5.834,9.156c3.776,1.591,7.977.851,11.474-1.09,3.62-2.009,6.694-5.117,9.52-8.1a123.2,123.2,0,0,0,8.089-9.609c5.021-6.508,9.626-13.338,14.909-19.642a85.1,85.1,0,0,1,18.866-16.912c6.951-4.5,14.71-7.825,20.869-13.479,6.475-5.944,10.335-14.335,14.622-21.861,3.714-6.522,7.454-13.03,11.165-19.554q1.641-2.886,3.277-5.775c1.524-2.692-2.621-5.113-4.145-2.422q-5.607,9.9-11.27,19.77-2.88,5.039-5.746,10.086c-2.1,3.706-4.124,7.486-6.583,10.974-4.721,6.7-11.65,10.565-18.619,14.528a93.931,93.931,0,0,0-19.963,14.83c-11.4,11.315-18.984,25.811-30.325,37.174-2.614,2.619-5.543,5.619-9.165,6.758-2.661.836-6.229.469-7.527-2.372-1.325-2.9.058-6.7,1.214-9.428,1.548-3.653,3.357-7.2,5.128-10.751,3.678-7.369,7.47-14.769,9.907-22.664a55.918,55.918,0,0,0,2.322-11.662,27.436,27.436,0,0,0-.112-6.3c-.253-1.785-.7-3.526-1.089-5.284a24.488,24.488,0,0,1-.611-5.294c-.006-3.088-4.806-3.094-4.8,0Z"></path></g></svg>
              </div>
              <h5 class="mb-2">Ballot</h5>
              <p class="features-icon-description">
              Create custom ballots with intuitive design tools for any type of election.
              </p>
            </div>
            <div class="col-lg-4 col-sm-6 text-center features-icon-box">
              <div class="text-center mb-4 text-primary">
                <svg width="64" height="65" viewBox="0 0 64 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    opacity="0.2"
                    d="M13.625 50.8413C11.325 48.5413 12.85 43.7163 11.675 40.8913C10.5 38.0663 6 35.5913 6 32.4663C6 29.3413 10.45 26.9663 11.675 24.0413C12.9 21.1163 11.325 16.3913 13.625 14.0913C15.925 11.7913 20.75 13.3163 23.575 12.1413C26.4 10.9663 28.875 6.46631 32 6.46631C35.125 6.46631 37.5 10.9163 40.425 12.1413C43.35 13.3663 48.075 11.7913 50.375 14.0913C52.675 16.3913 51.15 21.2163 52.325 24.0413C53.5 26.8663 58 29.3413 58 32.4663C58 35.5913 53.55 37.9663 52.325 40.8913C51.1 43.8163 52.675 48.5413 50.375 50.8413C48.075 53.1413 43.25 51.6163 40.425 52.7913C37.6 53.9663 35.125 58.4663 32 58.4663C28.875 58.4663 26.5 54.0163 23.575 52.7913C20.65 51.5663 15.925 53.1413 13.625 50.8413Z"
                    fill="#000000" />
                  <path
                    d="M43 26.4663L28.325 40.4663L21 33.4663M13.625 50.8413C11.325 48.5413 12.85 43.7163 11.675 40.8913C10.5 38.0663 6 35.5913 6 32.4663C6 29.3413 10.45 26.9663 11.675 24.0413C12.9 21.1163 11.325 16.3913 13.625 14.0913C15.925 11.7913 20.75 13.3163 23.575 12.1413C26.4 10.9663 28.875 6.46631 32 6.46631C35.125 6.46631 37.5 10.9163 40.425 12.1413C43.35 13.3663 48.075 11.7913 50.375 14.0913C52.675 16.3913 51.15 21.2163 52.325 24.0413C53.5 26.8663 58 29.3413 58 32.4663C58 35.5913 53.55 37.9663 52.325 40.8913C51.1 43.8163 52.675 48.5413 50.375 50.8413C48.075 53.1413 43.25 51.6163 40.425 52.7913C37.6 53.9663 35.125 58.4663 32 58.4663C28.875 58.4663 26.5 54.0163 23.575 52.7913C20.65 51.5663 15.925 53.1413 13.625 50.8413Z"
                    stroke="#000000"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </div>
              <h5 class="mb-2">Voter Registration</h5>
              <p class="features-icon-description">
              Easily manage voter registration, verification, and access with our comprehensive voter database.
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
        <a href="payment-page.html" class="btn btn-lg btn-primary">Get Started</a>
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
