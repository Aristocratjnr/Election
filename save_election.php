<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

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
                    $success = 'Election created successfully!';
                    $electionId = $conn->insert_id;
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
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .creation-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin: 2rem auto;
            max-width: 800px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
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
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            transform: translateY(-1px);
        }
        
        .invalid-feedback {
            font-size: 0.85rem;
        }
        
        .alert {
            border-radius: 0.35rem;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge i {
            margin-right: 0.25rem;
        }
        
        .badge-scheduled {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }
        
        .badge-ongoing {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }
        
        .badge-completed {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        /* Success Modal */
        .success-modal .modal-header {
            background-color: var(--success-color);
            color: white;
            border-bottom: none;
        }
        
        .success-modal .modal-body {
            padding: 2rem;
            text-align: center;
        }
        
        .success-modal .modal-icon {
            font-size: 4rem;
            color: var(--success-color);
            margin-bottom: 1.5rem;
        }
        
        .success-modal .btn-close {
            filter: invert(1);
        }
        
        /* Mobile styles */
        @media (max-width: 767.98px) {
            .creation-card {
                margin: 1rem;
                box-shadow: none;
                border: 1px solid #e3e6f0;
            }
            
            .card-header {
                padding: 1.25rem;
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
        <div class="creation-card bg-white">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-calendar2-plus-fill me-2"></i>
                        Create New Election
                    </h4>
                    <p class="mb-0 opacity-75">Setup a new voting event with custom parameters</p>
                </div>
                <a href="election.php" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="create_election.php" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Election Name -->
                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-card-heading me-1"></i>
                                Election Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </span>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo htmlspecialchars($formData['name']); ?>" 
                                       placeholder="Enter election name" required>
                            </div>
                            <div class="invalid-feedback">
                                Please provide a valid election name.
                            </div>
                            <small class="text-muted">Example: Student Council Election 2023</small>
                        </div>

                        <!-- Date & Time -->
                        <div class="col-md-6">
                            <div class="datetime-picker">
                                <label class="form-label">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Start Date & Time <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-calendar2-check text-primary"></i>
                                    </span>
                                    <input type="datetime-local" class="form-control" name="startDate" 
                                           value="<?php echo htmlspecialchars($formData['startDate']); ?>" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please select a start date and time.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="datetime-picker">
                                <label class="form-label">
                                    <i class="bi bi-clock me-1"></i>
                                    End Date & Time <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-calendar2-x text-primary"></i>
                                    </span>
                                    <input type="datetime-local" class="form-control" name="endDate" 
                                           value="<?php echo htmlspecialchars($formData['endDate']); ?>" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please select an end date and time.
                                </div>
                            </div>
                        </div>

                        <!-- Status & Visibility -->
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-activity me-1"></i>
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="status" required>
                                <option value="Scheduled" <?php echo $formData['status'] === 'Scheduled' ? 'selected' : ''; ?>>
                                    <span class="status-badge badge-scheduled">
                                        <i class="bi bi-clock-history"></i> Scheduled
                                    </span>
                                </option>
                                <option value="Ongoing" <?php echo $formData['status'] === 'Ongoing' ? 'selected' : ''; ?>>
                                    <span class="status-badge badge-ongoing">
                                        <i class="bi bi-arrow-repeat"></i> Ongoing
                                    </span>
                                </option>
                                <option value="Completed" <?php echo $formData['status'] === 'Completed' ? 'selected' : ''; ?>>
                                    <span class="status-badge badge-completed">
                                        <i class="bi bi-check-circle"></i> Completed
                                    </span>
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a status.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-eye me-1"></i>
                                Visibility
                            </label>
                            <select class="form-select" name="visibility">
                                <option value="Public" <?php echo $formData['visibility'] === 'Public' ? 'selected' : ''; ?>>
                                    <i class="bi bi-globe me-1"></i> Public
                                </option>
                                <option value="Private" <?php echo $formData['visibility'] === 'Private' ? 'selected' : ''; ?>>
                                    <i class="bi bi-lock me-1"></i> Private
                                </option>
                            </select>
                            <small class="text-muted">Public elections are visible to all users</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i> Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="bi bi-save me-2"></i> Create Election
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade success-modal" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Success!
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h4 class="mb-3">Election Created Successfully!</h4>
                    <p class="mb-4">Your new election "<strong><?php echo isset($formData['name']) ? htmlspecialchars($formData['name']) : ''; ?></strong>" has been successfully created and is now ready for configuration.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="elections.php" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-list-ul me-2"></i> View All Elections
                        </a>
                        <a href="edit_election.php?id=<?php echo isset($electionId) ? $electionId : ''; ?>" class="btn btn-success px-4">
                            <i class="bi bi-gear me-2"></i> Configure Election
                        </a>
                    </div>
                </div>
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
            const pad = (num) => num.toString().padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
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
        
        // Show success modal if election was created
        <?php if (!empty($success)): ?>
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        <?php endif; ?>
    });
    </script>
</body>
</html>