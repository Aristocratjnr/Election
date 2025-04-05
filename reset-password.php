<?php 
$email = isset($_GET['email']) ? $_GET['email'] : '';
?>
<!doctype html>

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

    <title>Reset Password</title>

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

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <!-- Vendor -->
    <link rel="stylesheet" href="assets/vendor/libs/@form-validation/form-validation.css" />

    <!-- Page CSS -->
    <!-- Page -->
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
          <!-- Reset Password -->
          <div class="card px-sm-6 px-0">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center">
                <a href="index.html" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                    <span class="text-primary">
                     
                  </span>
                  <span class="app-brand-text demo text-heading fw-bold">SmartVote</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Reset Password 🔒</h4>
              <p class="mb-6">
                <span class="fw-medium">Your new password must be different from previously used passwords</span>
              </p>
              <form id="formAuthentication" action="auth-login-basic.html" method="GET">
                <div class="mb-6 form-password-toggle form-control-validation">
                  <label class="form-label" for="password">New Password</label>
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
                <div class="mb-6 form-password-toggle form-control-validation">
                  <label class="form-label" for="confirm-password">Confirm Password</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="confirm-password"
                      class="form-control"
                      name="confirm-password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                  </div>
                </div>
                <button class="btn btn-primary d-grid w-100 mb-6">Set new password</button>
                <div class="text-center">
                  <a href="auth-login-basic.html">
                    <i class="icon-base bx bx-chevron-left scaleX-n1-rtl me-1 align-top"></i>
                    Back to login
                  </a>
                </div>
              </form>
            </div>
          </div>
          <!-- /Reset Password -->
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js  -->

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

    <script>
document.getElementById('formAuthentication').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm-password').value.trim();
    
    // Reset error states
    document.getElementById('password').classList.remove('is-invalid');
    document.getElementById('confirm-password').classList.remove('is-invalid');
    
    // Validate password
    if (!password) {
        showError('password', 'Password is required');
        return;
    }
    
    if (password.length < 8) {
        showError('password', 'Password must be at least 8 characters');
        return;
    }
    
    // Validate confirm password
    if (!confirmPassword) {
        showError('confirm-password', 'Please confirm your password');
        return;
    }
    
    if (password !== confirmPassword) {
        showError('confirm-password', 'Passwords do not match');
        return;
    }
    
    // If validation passes, submit the form
    this.submit();
});

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('is-invalid');
    
    // Remove any existing error message
    let errorContainer;
    if (field.parentNode.classList.contains('input-group')) {
        errorContainer = field.parentNode.parentNode;
    } else {
        errorContainer = field.parentNode;
    }
    
    const existingError = errorContainer.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    errorContainer.appendChild(errorDiv);
}
</script>
  </body>
</html>
