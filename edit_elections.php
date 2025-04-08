<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Initialize variables
$error = '';
$success = '';
$election = null;

// Check if election ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid election ID provided';
    header('Location: elections.php');
    exit;
}

$electionID = (int)$_GET['id'];

// Fetch election data with error handling
try {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ? LIMIT 1");
    $stmt->bind_param('i', $electionID);
    $stmt->execute();
    $result = $stmt->get_result();
    $election = $result->fetch_assoc();
    $stmt->close();
    
    if (!$election) {
        $_SESSION['error'] = 'Election not found';
        header('Location: elections.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = 'Database error occurred';
    header('Location: elections.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['name']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];
    $visibility = $_POST['visibility'] ?? 'Public';
    
    // Validate inputs
    if (empty($name)) {
        $error = 'Election name is required';
    } elseif (empty($startDate)) {
        $error = 'Start date is required';
    } elseif (empty($endDate)) {
        $error = 'End date is required';
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        $error = 'End date cannot be earlier than start date';
    } else {
        // Update election in database
        try {
            $stmt = $conn->prepare("UPDATE elections SET name = ?, startDate = ?, endDate = ?, status = ?, visibility = ? WHERE electionID = ?");
            $stmt->bind_param('sssssi', $name, $startDate, $endDate, $status, $visibility, $electionID);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Election updated successfully';
                header("Location: elections.php");
                exit;
            } else {
                $error = 'Failed to update election: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if ($error) {
        $election = [
            'name' => $_POST['name'],
            'startDate' => $_POST['startDate'],
            'endDate' => $_POST['endDate'],
            'status' => $_POST['status'],
            'visibility' => $_POST['visibility'] ?? 'Public'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Election</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .form-control, .form-select {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fc;
        }
        .page-title {
            display: flex;
            align-items: center;
        }
        .page-title i {
            margin-right: 10px;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    <br><br><br><br>
    
    <div class="container-fluid py-3">
        <!-- Error Alert -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Edit Election
                        </h4>
                        <a href="elections.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Elections
                        </a>
                    </div>

                    <div class="card-body">
                        <form method="POST" id="electionForm" class="needs-validation" novalidate>
                            <input type="hidden" name="electionID" value="<?= $electionID ?>">
                            
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Election Information</h5>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label"><i class="bi bi-card-heading me-1"></i>Election Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                                                    <input type="text" class="form-control" name="name" required
                                                           value="<?= htmlspecialchars($election['name']) ?>"
                                                           placeholder="Enter election name">
                                                </div>
                                                <div class="invalid-feedback">
                                                    Please provide an election name.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Start Date <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                    <input type="datetime-local" class="form-control" name="startDate" required
                                                           value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($election['startDate']))) ?>">
                                                </div>
                                                <div class="invalid-feedback">
                                                    Please select a start date.
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label"><i class="bi bi-calendar-x me-1"></i>End Date <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                    <input type="datetime-local" class="form-control" name="endDate" required
                                                           value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($election['endDate']))) ?>">
                                                </div>
                                                <div class="invalid-feedback">
                                                    Please select an end date.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label"><i class="bi bi-info-square me-1"></i>Status <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-list-check"></i></span>
                                                    <select class="form-select" name="status" required>
                                                        <option value="Scheduled" <?= $election['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                        <option value="Ongoing" <?= $election['status'] === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                        <option value="Completed" <?= $election['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Please select a status.
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label"><i class="bi bi-eye me-1"></i>Visibility</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <select class="form-select" name="visibility">
                                                        <option value="Public" <?= ($election['visibility'] ?? 'Public') === 'Public' ? 'selected' : '' ?>>Public</option>
                                                        <option value="Private" <?= ($election['visibility'] ?? 'Public') === 'Private' ? 'selected' : '' ?>>Private</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="elections.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Update Election
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        (function () {
            'use strict'
            
            const form = document.getElementById('electionForm');
            
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                // Custom validation for dates
                const startDate = new Date(document.querySelector('input[name="startDate"]').value);
                const endDate = new Date(document.querySelector('input[name="endDate"]').value);
                
                if (endDate < startDate) {
                    event.preventDefault();
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show';
                    alert.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        End date cannot be earlier than start date.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('.container-fluid').prepend(alert);
                    
                    setTimeout(() => {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }, 5000);
                    
                    return false;
                }
                
                form.classList.add('was-validated')
            }, false)
        })()
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            });
        }, 5000);
    });
    </script>
</body>
</html>