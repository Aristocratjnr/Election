<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Initialize variables
$error = '';
$success = '';
$formData = [
    'name' => '',
    'startDate' => '',
    'endDate' => '',
    'status' => 'Scheduled',
    'visibility' => 'Public'
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $formData = [
        'name' => trim($_POST['name']),
        'startDate' => $_POST['startDate'],
        'endDate' => $_POST['endDate'],
        'status' => $_POST['status'],
        'visibility' => $_POST['visibility']
    ];

    // Validate inputs
    if (empty($formData['name'])) {
        $error = 'Election name is required';
    } elseif (empty($formData['startDate']) || empty($formData['endDate'])) {
        $error = 'Both start and end dates are required';
    } else {
        $startDate = new DateTime($formData['startDate']);
        $endDate = new DateTime($formData['endDate']);
        
        if ($endDate < $startDate) {
            $error = 'End date cannot be earlier than start date';
        } else {
            // Check if election name already exists
            $stmt = $conn->prepare("SELECT electionID FROM elections WHERE name = ?");
            $stmt->bind_param('s', $formData['name']);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = 'An election with this name already exists';
            } else {
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO elections (name, startDate, endDate, status, visibility) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssss', 
                    $formData['name'],
                    $formData['startDate'],
                    $formData['endDate'],
                    $formData['status'],
                    $formData['visibility']
                );
                
                if ($stmt->execute()) {
                    header('Location: elections.php?success=created');
                    exit();
                } else {
                    $error = 'Failed to create election: ' . $conn->error;
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Election</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .creation-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .card-header {
            background-color: var(--secondary-color);
            border-bottom: 1px solid #e3e6f0;
            padding: 1.25rem 1.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .datetime-picker {
            position: relative;
        }
        
        .datetime-picker i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6e707e;
            pointer-events: none;
        }
        
        /* Mobile styles */
        @media (max-width: 767.98px) {
            .creation-card {
                margin: 1rem;
                box-shadow: none;
                border: 1px solid #e3e6f0;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .form-control, .form-select {
                padding: 0.65rem 0.9rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="creation-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-calendar-plus me-2"></i>
                    Create New Election
                </h4>
                <a href="../../dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="create_election.php" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <!-- Election Name -->
                        <div class="col-12">
                            <label class="form-label">Election Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($formData['name']); ?>" 
                                   placeholder="Enter election name" required>
                            <div class="invalid-feedback">
                                Please provide a valid election name.
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="col-md-6">
                            <div class="datetime-picker">
                                <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="startDate" 
                                       value="<?php echo htmlspecialchars($formData['startDate']); ?>" required>
                                <i class="bi bi-calendar-event"></i>
                                <div class="invalid-feedback">
                                    Please select a start date and time.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="datetime-picker">
                                <label class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="endDate" 
                                       value="<?php echo htmlspecialchars($formData['endDate']); ?>" required>
                                <i class="bi bi-calendar-event"></i>
                                <div class="invalid-feedback">
                                    Please select an end date and time.
                                </div>
                            </div>
                        </div>

                        <!-- Status & Visibility -->
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="Scheduled" <?php echo $formData['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="Ongoing" <?php echo $formData['status'] === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="Completed" <?php echo $formData['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a status.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Visibility</label>
                            <select class="form-select" name="visibility">
                                <option value="Public" <?php echo $formData['visibility'] === 'Public' ? 'selected' : ''; ?>>Public</option>
                                <option value="Private" <?php echo $formData['visibility'] === 'Private' ? 'selected' : ''; ?>>Private</option>
                            </select>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-eraser me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Create Election
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        (function () {
            'use strict'
            
            const forms = document.querySelectorAll('.needs-validation')
            
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    // Custom date validation
                    const startDate = new Date(form.querySelector('input[name="startDate"]').value);
                    const endDate = new Date(form.querySelector('input[name="endDate"]').value);
                    
                    if (endDate < startDate) {
                        event.preventDefault();
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-danger alert-dismissible fade show mb-4';
                        alert.innerHTML = `
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            End date cannot be earlier than start date.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        form.parentNode.insertBefore(alert, form);
                        
                        setTimeout(() => {
                            bootstrap.Alert.getOrCreateInstance(alert).close();
                        }, 5000);
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
        
        // Set default datetime values to now and +1 hour
        const now = new Date();
        const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);
        
        // Format for datetime-local input
        function formatDateTime(date) {
            return date.toISOString().slice(0, 16);
        }
        
        // Only set defaults if values aren't already set
        const startDateInput = document.querySelector('input[name="startDate"]');
        const endDateInput = document.querySelector('input[name="endDate"]');
        
        if (!startDateInput.value) {
            startDateInput.value = formatDateTime(now);
        }
        if (!endDateInput.value) {
            endDateInput.value = formatDateTime(oneHourLater);
        }
    });
    </script>
</body>
</html>