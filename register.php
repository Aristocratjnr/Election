<?php

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

<style>
     
      .modal-backdrop {
        filter: none !important;
      }

    
      .modal-content {
        filter: none !important;  
        opacity: 1 !important;   
        transform: none !important; 
      }
      #signupButton {
      position: relative;
    }

    #signupSpinner {
      position: absolute;
      right: 1rem;
    }
    </style>

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
                <a href="index.php" class="app-brand-link gap-2">
                  <img src="assets/img/favicon/favicon.ico" alt="logo" width="50%" class="logo-img" />
                  <span class="app-brand-text demo text-heading fw-bold">SmartVote</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Adventure starts here 🚀</h4>
              <p class="mb-6">Make your Voting System Secure and User Friendly</p>

              <form id="registerForm" class="mb-6" action="signUpAuth.php" method="POST">
                <div class="mb-6 form-control-validation">
                  <label for="studentID" class="form-label"><i class="bi bi-person-vcard profile-icon icon"></i>&nbsp;Student ID:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="studentID"
                    name="student"
                    placeholder="Enter your student id"
                    autofocus />
                </div>
                <div class="mb-6 form-control-validation">
                  <label for="name" class="form-label"><i class="bi bi-person profile-icon icon"></i>&nbsp;Name:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    placeholder="Enter your name"
                    autofocus />
                </div>
                <div class="mb-6 form-control-validation">
                  <label for="email" class="form-label">  <i class="bi bi-envelope-check action-icon icon"></i>&nbsp;Student Mail:</label>
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
                </div><br>
                <div class="mb-6 form-control-validation">
                  <div class="form-password-toggle form-control-validation">
                    <label class="form-label" for="confirmPassword">Confirm Password:</label>
                    <div class="input-group input-group-merge">
                      <input
                        type="password"
                        id="confirmPassword"
                        class="form-control"
                        name="confirmPassword"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                        aria-describedby="confrimPassword" />
                      <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                    </div>
                  </div>
                </div>
                <div class="mb-6 form-control-validation">
                  <label for="department" class="form-label">Department:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="department"
                    name="department"
                    placeholder="Enter your department"
                    autofocus />
                </div>
                <div class="mb-6 form-control-validation">
                  <label for="dob" class="form-label">Date Of Birth:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="dob"
                    name="dob"
                    placeholder="YYYY-MM-DD"
                    autofocus />
                </div>
                <div class="mb-6 form-control-validation">
                  <label for="contact" class="form-label">Contact:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="contact"
                    name="contact"
                    autocomplete="off"
                    placeholder="Enter your contact number"
                    autofocus />
                </div>
            

                <button class="btn btn-primary d-grid w-100" id="signupButton">
  <span id="signupText">Sign up</span>
  <span id="signupSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
</button>
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
    <script src="scripts/register.js"></script>

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="assets/vendor/libs/@form-validation/auto-focus.js"></script>

    <!-- Main JS -->
    <?php
    include 'includes/scripts.php';
    ?>