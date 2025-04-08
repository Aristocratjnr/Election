<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    $status = $_POST['status'] ?? 'Scheduled';
    $visibility = $_POST['visibility'] ?? 'Public';
    
    // Validate inputs
    $errors = [];
    if (empty($name)) {
        $errors[] = "Election name is required";
    }
    if (empty($startDate)) {
        $errors[] = "Start date is required";
    }
    if (empty($endDate)) {
        $errors[] = "End date is required";
    }
    if (strtotime($endDate) <= strtotime($startDate)) {
        $errors[] = "End date must be after start date";
    }
    
    if (empty($errors)) {
        try {
            // First check if visibility column exists
            $checkColumn = $conn->query("SHOW COLUMNS FROM elections LIKE 'visibility'");
            
            if ($checkColumn->num_rows == 0) {
                // Column doesn't exist, use simpler INSERT query
                $stmt = $conn->prepare("
                    INSERT INTO elections (name, startDate, endDate, status)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("ssss", $name, $startDate, $endDate, $status);
            } else {
                // Column exists, use full INSERT query
                $stmt = $conn->prepare("
                    INSERT INTO elections (name, startDate, endDate, status, visibility)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("sssss", $name, $startDate, $endDate, $status, $visibility);
            }
            
            if ($stmt->execute()) {
                $electionID = $conn->insert_id;
                header("Location: election.php?success=created&id=" . $electionID);
                exit;
            } else {
                $errors[] = "Failed to create election";
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Election - SmartVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .input-group-text {
            border-radius: 0.5rem;
            background-color: #f8f9fa;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #224abe, #1a3a94);
            transform: translateY(-1px);
        }
        
        .section-title {
            position: relative;
            padding-left: 1rem;
            margin-bottom: 1.5rem;
            color: #4e73df;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #4e73df, #224abe);
            border-radius: 2px;
        }
        
        .required-field::after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
        }
        
        .alert {
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="bi bi-plus-circle-fill me-2"></i>
                                Create New Election
                            </h4>
                            <a href="election.php" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Please correct the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <h5 class="section-title">Basic Information</h5>
                            
                            <div class="mb-4">
                                <label class="form-label required-field">Election Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-pencil-fill"></i>
                                    </span>
                                    <input type="text" class="form-control" name="name" required
                                           value="<?php echo $_POST['name'] ?? ''; ?>"
                                           placeholder="Enter election name">
                                </div>
                                <div class="form-text">Choose a descriptive name for the election</div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required-field">Start Date & Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar-plus-fill"></i>
                                        </span>
                                        <input type="datetime-local" class="form-control" name="startDate" required
                                               value="<?php echo $_POST['startDate'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label required-field">End Date & Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar-x-fill"></i>
                                        </span>
                                        <input type="datetime-local" class="form-control" name="endDate" required
                                               value="<?php echo $_POST['endDate'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="section-title">Settings</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Initial Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-toggle-on"></i>
                                        </span>
                                        <select class="form-select" name="status">
                                            <option value="Scheduled" <?php echo ($_POST['status'] ?? '') === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                            <option value="Ongoing" <?php echo ($_POST['status'] ?? '') === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Visibility</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-eye-fill"></i>
                                        </span>
                                        <select class="form-select" name="visibility">
                                            <option value="Public" <?php echo ($_POST['visibility'] ?? '') === 'Public' ? 'selected' : ''; ?>>Public</option>
                                            <option value="Private" <?php echo ($_POST['visibility'] ?? '') === 'Private' ? 'selected' : ''; ?>>Private</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between pt-3">
                                <button type="button" class="btn btn-light" onclick="location.href='election.php'">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Create Election
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('.needs-validation');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Custom date validation
            const startDate = new Date(document.querySelector('input[name="startDate"]').value);
            const endDate = new Date(document.querySelector('input[name="endDate"]').value);
            
            if (endDate <= startDate) {
                event.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 5000);
        });
    });
    </script>
</body>
</html> 