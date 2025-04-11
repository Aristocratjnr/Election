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
            background-color: #f0f2f5;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
       
        
        .card-header {
            background: linear-gradient(45deg, #4e73df, #224abe);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
            transform: translateY(-2px);
        }
        
        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
            background-color: #eef1ff;
            border-color: #e0e0e0;
            color: #4e73df;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #224abe, #1a3a94);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(34, 74, 190, 0.4);
        }
        
        .btn-light {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .btn-light:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
            color: #4e73df;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
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
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-text i {
            margin-right: 0.5rem;
            color: #4e73df;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 0.5rem;
            color: #4e73df;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?><br><br>
    
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
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                                    <div>
                                        <strong>Please correct the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <h5 class="section-title">
                                <i class="bi bi-info-circle-fill me-2"></i>Basic Information
                            </h5>
                            
                            <div class="mb-4">
                                <label class="form-label required-field">
                                    <i class="bi bi-tag-fill me-2"></i>Election Name
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-pencil-fill"></i>
                                    </span>
                                    <input type="text" class="form-control" name="name" required
                                           value="<?php echo $_POST['name'] ?? ''; ?>"
                                           placeholder="Enter election name">
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>Choose a descriptive name for the election
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        <i class="bi bi-calendar-event-fill me-2"></i>Start Date & Time
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar-plus-fill"></i>
                                        </span>
                                        <input type="datetime-local" class="form-control" name="startDate" required
                                               value="<?php echo $_POST['startDate'] ?? ''; ?>">
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-clock"></i>When voting will begin
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        <i class="bi bi-calendar-event-fill me-2"></i>End Date & Time
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar-x-fill"></i>
                                        </span>
                                        <input type="datetime-local" class="form-control" name="endDate" required
                                               value="<?php echo $_POST['endDate'] ?? ''; ?>">
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-hourglass-split"></i>When voting will end
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="section-title">
                                <i class="bi bi-gear-fill me-2"></i>Settings
                            </h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-activity me-2"></i>Initial Status
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-toggle-on"></i>
                                        </span>
                                        <select class="form-select" name="status">
                                            <option value="Scheduled" <?php echo ($_POST['status'] ?? '') === 'Scheduled' ? 'selected' : ''; ?>>
                                                <i class="bi bi-clock-history"></i> Scheduled
                                            </option>
                                            <option value="Ongoing" <?php echo ($_POST['status'] ?? '') === 'Ongoing' ? 'selected' : ''; ?>>
                                                <i class="bi bi-play-fill"></i> Ongoing
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle"></i>Current state of the election
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-people-fill me-2"></i>Visibility
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-eye-fill"></i>
                                        </span>
                                        <select class="form-select" name="visibility">
                                            <option value="Public" <?php echo ($_POST['visibility'] ?? '') === 'Public' ? 'selected' : ''; ?>>
                                                Public
                                            </option>
                                            <option value="Private" <?php echo ($_POST['visibility'] ?? '') === 'Private' ? 'selected' : ''; ?>>
                                                Private
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-shield-lock"></i>Who can view this election
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between pt-3 mt-4 border-top">
                                <button type="button" class="btn btn-light" onclick="location.href='election.php'">
                                    <i class="bi bi-x-circle-fill me-2"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle-fill me-2"></i> Create Election
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
                const errorMessage = document.createElement('div');
                errorMessage.classList.add('alert', 'alert-danger', 'mt-3', 'animate__animated', 'animate__shakeX');
                errorMessage.innerHTML = '<i class="bi bi-exclamation-diamond-fill me-2"></i> End date must be after start date';
                form.prepend(errorMessage);
                
                setTimeout(() => {
                    errorMessage.remove();
                }, 5000);
                
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
        
        // Add hover effects to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    });
    </script>
</body>
</html> 