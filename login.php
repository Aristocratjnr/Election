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

    <title>Login - SmartVote</title>

    <meta name="description" content="Student voting system login page" />

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="/assets/vendor/js/template-customizer.js"></script>
    <script src="/assets/js/config.js"></script>
  </head>

  <body>
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Login Card -->
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

              <!-- Error Alert (will be shown dynamically) -->
              <div id="authAlert" class="alert alert-danger d-none"></div>

              <form id="formAuthentication" class="mb-6" method="POST">
                <div class="mb-6 form-control-validation">
                  <label for="studentID" class="form-label"><i class="bi bi-person-badge profile-icon icon"></i>&nbsp;Student ID:</label>
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
                  <label class="form-label" for="password"><i class="bi bi-key action-icon icon"></i>&nbsp;Password:</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password"
                      required />
                    <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                  </div>
                </div>
                <div class="mb-7">
                  <div class="d-flex justify-content-between">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="remember-me" />
                      <label class="form-check-label" for="remember-me"> Remember Me </label>
                    </div>
                    <a href="forgot_password.php">
                      <span>Forgot Password?</span>
                    </a>
                  </div>
                </div>
                <div class="mb-6">
                  <button class="btn btn-primary d-grid w-100" type="submit" id="loginBtn">
                  <i class="bi bi-box-arrow-in-right action-icon icon"></i>Sign In 
                  </button>
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
      </div>
    </div>

    <!-- Scripts -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="assets/vendor/libs/@form-validation/auto-focus.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('formAuthentication');
      const authAlert = document.getElementById('authAlert');
      const loginBtn = document.getElementById('loginBtn');
      
      // Toggle password visibility
      document.querySelector('.form-password-toggle .input-group-text').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.replace('bx-hide', 'bx-show');
        } else {
          passwordInput.type = 'password';
          icon.classList.replace('bx-show', 'bx-hide');
        }
      });
      
      // Form submission
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';
        
        // Hide previous alerts
        authAlert.classList.add('d-none');
        
        // Get form data
        const formData = new FormData(form);
        
        // AJAX request
        fetch('signInAuth.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            window.location.href = data.redirect_url || 'dashboard.php';
          } else {
            showError(data.message || 'Login failed. Please try again.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showError('An error occurred. Please try again later.');
        })
        .finally(() => {
          loginBtn.disabled = false;
          loginBtn.innerHTML = '<i class="ri-lock-2-fill"></i> Sign In';
        });
      });
      
      function showError(message) {
        authAlert.textContent = message;
        authAlert.classList.remove('d-none');
      }
      
      // Show any PHP error passed in URL
      const urlParams = new URLSearchParams(window.location.search);
      const error = urlParams.get('error');
      if (error) {
        showError(
          error === 'empty' ? 'Please fill all fields' :
          error === 'invalid' ? 'Invalid credentials' :
          'Login failed'
        );
      }
    });
    </script>

    <?php include 'includes/scripts.php'; ?>
  </body>
</html>