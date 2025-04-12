<?php $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Registration successful!'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .success-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
            background-color: rgba(40, 167, 69, 0.1);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem;
        }
        .btn-primary {
            background-color: #5e72e4;
            border-color: #5e72e4;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: normal;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #4a5fd1;
            border-color: #4a5fd1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(94, 114, 228, 0.3);
        }
        .content-area {
            background-color: transparent;
            padding: 30px;
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container success-container">
        <div class="content-area">
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1 class="mb-3">Registration Successful!</h1>
            <p class="lead mb-4"><?php echo $message; ?></p>
            <p class="mb-4">
                <i class="bi bi-envelope-check"></i> Please check your email for confirmation.
            </p>
            <a href="login.php" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i> Continue to Login
            </a>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="bi bi-question-circle"></i> Need help? <a href="https://wa.me/233551784926?text=Hello%2C%20I%20need%20help">Contact support</a>
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>