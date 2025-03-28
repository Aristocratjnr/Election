<!doctype html>;


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

    <title>Login</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
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
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="/assets/vendor/js/template-customizer.js"></script>
    <script src="/assets/js/config.js"></script>
  </head>

  <body>
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Login -->
          <div class="card px-sm-6 px-0">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center">
                <a href="index.php" class="app-brand-link gap-2">
                    <img src="assets/img/favicon/favicon.ico" alt="logo" width="24%" class="logo-img" />
                    <span class="app-brand-text demo text-heading fw-bold">SmartVote</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Welcome to SmartVote👋</h4>
              <p class="mb-6">Please sign-in to your account</p>

              <form id="formAuthentication" class="mb-6" action="signInAuth.php" method="POST">
                <div class="mb-6 form-control-validation">
                  <label for="studentID" class="form-label">Student ID:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="studentID"
                    name="student"
                    placeholder="Enter Student ID"
                    required
                    autofocus />
                </div>
                <div class="mb-6 form-password-toggle form-control-validation">
                  <label class="form-label" for="password">Password</label>
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
                <div class="mb-7">
                  <div class="d-flex justify-content-between">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="remember-me" />
                      <label class="form-check-label" for="remember-me"> Remember Me </label>
                    </div>
                    <a href="auth-forgot-password-basic.html">
                      <span>Forgot Password?</span>
                    </a>
                  </div>
                </div>
                <div class="mb-6">
                  <button class="btn btn-primary d-grid w-100" type="submit"><i class="ri-lock-2-fill"></i>Login</button>
                </div>
              </form>

              <p class="text-center">
                <span>New on our platform?</span>
                <a href="register.php">
                  <span>Create an account</span>
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
    <script src="assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="assets/vendor/libs/@form-validation/auto-focus.js"></script>

    <script src="assets/js/pages-auth.js"></script>

    <script>
  document.getElementById("formAuthentication").addEventListener("submit", async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const response = await fetch("SignInAuth.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();
    const alertBox = document.getElementById("alertBox");

    if (result.success) {
        alertBox.className = "alert alert-success mt-3";
        alertBox.textContent = `Welcome, ${result.name}! Redirecting...`;
        alertBox.classList.remove("d-none");

        setTimeout(() => {
            window.location.href = "dashboard.php";
        }, 2000);
    } else {
        alertBox.className = "alert alert-danger mt-3";
        alertBox.textContent = result.message || "Login failed.";
        alertBox.classList.remove("d-none");
    }
});
</script>

    <?php
  include 'includes/scripts.php';
  ?>

  </body>
</html>
