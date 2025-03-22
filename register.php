<?php
session_start();
if (isset($_SESSION['login_id'])) {
  if ($_SESSION['login_type'] == 0) {
    header("location:index.php?page=dashboard");
  } else {
    header("location:index.php?page=vote");
  }
  exit();
}

echo '<!doctype html>';
?>

<html
  lang="en"
  class="layout-wide customizer-hide"
  dir="ltr"
  data-skin="default"
  data-assets-path="assets/"
  data-template="vertical-menu-template"
  data-bs-theme="light">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Register</title>

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

    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/vendor/js/template-customizer.js"></script>
    <script src="assets/js/config.js"></script>
  </head>

  <body>
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card px-sm-6 px-0">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-6">
                <a href="index.html" class="app-brand-link gap-2">
                <img src="assets/img/favicon/favicon.ico" alt="logo" width="50%" class="logo-img" />
                <span class="app-brand-text demo text-heading fw-bold">SmartVote</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Adventure starts here 🚀</h4>
              <p class="mb-6">Make your Voting System Secure and User Friendly</p>

              <form id="formAuthentication" class="mb-6" action="index.html" method="GET">
                <div class="mb-6 form-control-validation">
                  <label for="username" class="form-label">Student ID:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="student_id"
                    name="student"
                    placeholder="Enter Student ID"
                    autofocus />
                </div>
                <div class="mb-6 form-control-validation">
                  <label for="email" class="form-label">Student Mail:</label>
                  <input type="text" class="form-control" id="email" name="email" placeholder="Enter your student mail" />
                </div>
                <div class="form-password-toggle form-control-validation">
                  <label class="form-label" for="password">Password:</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                  </div>
                </div>
                <div class="my-7 form-control-validation">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                    <label class="form-check-label" for="terms-conditions">
                      I agree to
                      <a href="javascript:void(0);">privacy policy & terms</a>
                    </label>
                  </div>
                </div>
                <button class="btn btn-primary d-grid w-100">Sign up</button>
              </form>

              <p class="text-center">
                <span>Already have an account?</span>
                <a href="login.php">
                  <span>Sign in instead</span>
                </a>
              </p>

        </div>
      </div>
    </div>

    <script src="assets/vendor/libs/jquery/jquery.js"></script>

    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <script src="assets/vendor/libs/pickr/pickr.js"></script>

    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="assets/vendor/libs/hammer/hammer.js"></script>

    <script src="assets/vendor/libs/i18n/i18n.js"></script>

    <script src="assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="assets/vendor/libs/@form-validation/auto-focus.js"></script>

    <!-- Main JS -->

    <script src="assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="assets/js/pages-auth.js"></script>

    <?php
  include 'includes/scripts.php';
  ?>
  </body>
</html>
