<?php
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Registration successful!';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container success-container">
        <div class="success-icon">✓</div>
        <h1 class="mb-3">Registration Successful!</h1>
        <p class="lead mb-4"><?php echo $message; ?></p>
        <a href="login.php" class="btn btn-primary btn-lg">Continue to Login</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
                <span class="app-brand-text demo text-body fw-bolder">Voting System</span>
              </a>
              <!-- /Logo -->
              <h4 class="mb-2">Welcome to Voting System! 🎉</h4>
              <p class="mb-4">You have successfully registered. Please check your email for confirmation.</p>
            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>
    <!-- / Content -->
  </body>