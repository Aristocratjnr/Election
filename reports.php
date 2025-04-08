<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

$action = $_GET['action'] ?? 'manage';
$electionID = $_GET['id'] ?? null;

// Fetch election data if editing
$election = null;
if ($action === 'edit' && $electionID) {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $stmt->bind_param('i', $electionID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $election = $result->fetch_assoc();
    } else {
        header('Location: elections.php?error=not_found');
        exit;
    }
    $stmt->close();
}

// Check for error messages
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elections Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-light: #7a9ef8;
            --primary-dark: #2e59d9;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --gray-light: #e9ecef;
            --gray-medium: #6c757d;
            --gray-dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: var(--secondary-color);
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }
        
        /* Status badges */
        .badge-scheduled {
            background-color: var(--warning-color);
            color: #000;
        }
        
        .badge-ongoing {
            background-color: var(--success-color);
            color: #fff;
        }
        
        .badge-completed {
            background-color: var(--secondary-color);
            color: var(--gray-dark);
            border: 1px solid #d1d3e2;
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        /* Tables */
        .table th {
            border-top: none;
            font-weight: 600;
            color: #5a5c69;
            white-space: nowrap;
        }
        
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        /* Form elements */
        .form-control, .form-select {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        /* Page header */
        .page-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-dark);
            margin-bottom: 0.5rem;
        }
        
        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
        
        /* Action buttons */
        .action-btns .btn {
            padding: 0.25rem 0.5rem;
            margin: 0 2px;
            transition: all 0.2s ease;
        }
        
        .action-btns .btn:hover {
            transform: translateY(-1px);
        }
        
        /* Mobile-specific styles */
        @media (max-width: 767.98px) {
            body {
                font-size: 14px;
            }
            
            /* Card adjustments */
            .card-header {
                padding: 0.75rem;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-header h4 {
                font-size: 1.1rem;
                margin-bottom: 10px;
            }
            
            /* Table adjustments */
            .table th, .table td {
                padding: 0.5rem;
            }
            
            /* Hide some columns on mobile */
            .mobile-hide {
                display: none;
            }
            
            /* Show mobile-specific elements */
            .mobile-show {
                display: block !important;
            }
            
            /* Form adjustments */
            .form-row > [class*="col-"] {
                margin-bottom: 15px;
            }
            
            .form-row > [class*="col-"]:last-child {
                margin-bottom: 0;
            }
            
            /* Button adjustments */
            .btn {
                padding: 0.5rem;
                font-size: 0.85rem;
            }
            
            /* Status badges */
            .badge {
                padding: 0.35em 0.5em;
                font-size: 0.75em;
            }
            
            /* Page header adjustments */
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-title {
                font-size: 1.3rem;
                margin-bottom: 1rem;
            }
            
            /* Add margin to action buttons */
            .action-btns .btn {
                margin-bottom: 5px;
            }
        }
        
        /* Small devices (landscape phones, 576px and up) */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .mobile-hide-sm {
                display: none;
            }
        }
        
        /* Extra small devices (portrait phones, less than 576px) */
        @media (max-width: 575.98px) {
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            /* Stack form fields */
            .form-row > [class*="col-"] {
                width: 100%;
            }
            
            /* Make datetime inputs more readable */
            input[type="datetime-local"] {
                font-size: 14px;
            }
        }
        
        /* Print styles */
        @media print {
            body {
                background-color: white;
                font-size: 12pt;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        <!-- Error/Success Alerts -->
        <?php if ($error === 'not_found'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Election not found or has been deleted.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success === 'created'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                Election created successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($success === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                Election updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($success === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                Election deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    <?= ucfirst($action) ?> Election
                </h4>
                <a href="elections.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body">
                <?php if ($action === 'manage'): ?>
                    <!-- Elections List -->
                    <div class="page-header">
                        <h1 class="page-title">Elections Management</h1>
                        <a href="save_election.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> New Election
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="electionsTable" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>Election</th>
                                    <th class="mobile-hide">Start Date</th>
                                    <th class="mobile-hide-sm">End Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM elections ORDER BY startDate DESC";
                                $result = $conn->query($query);
                                
                                if ($result && $result->num_rows > 0):
                                    while ($election = $result->fetch_assoc()):
                                        $statusClass = [
                                            'Scheduled' => 'badge-scheduled',
                                            'Ongoing' => 'badge-ongoing',
                                            'Completed' => 'badge-completed'
                                        ][$election['status'] ?? 'Scheduled'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($election['name']) ?></strong>
                                        <div class="mobile-show text-muted small mt-1" style="display: none;">
                                            <?= date('M d, Y', strtotime($election['startDate'])) ?> - 
                                            <?= date('M d, Y', strtotime($election['endDate'])) ?>
                                        </div>
                                    </td>
                                    <td class="mobile-hide"><?= date('M d, Y', strtotime($election['startDate'])) ?></td>
                                    <td class="mobile-hide-sm"><?= date('M d, Y', strtotime($election['endDate'])) ?></td>
                                    <td>
                                        <span class="badge <?= $statusClass ?>">
                                            <?php if ($election['status'] === 'Ongoing'): ?>
                                                <span class="status-indicator bg-white"></span>
                                            <?php elseif ($election['status'] === 'Scheduled'): ?>
                                                <span class="status-indicator bg-dark"></span>
                                            <?php else: ?>
                                                <span class="status-indicator bg-secondary"></span>
                                            <?php endif; ?>
                                            <span class="status-text"><?= $election['status'] ?></span>
                                        </span>
                                    </td>
                                    <td class="text-end action-btns">
                                        <div class="btn-group">
                                            <a href="elections.php?action=edit&id=<?= $election['electionID'] ?>" 
                                               class="btn btn-sm btn-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-election" 
                                                    data-id="<?= $election['electionID'] ?>"
                                                    data-bs-toggle="tooltip" 
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <a href="election_details.php?id=<?= $election['electionID'] ?>" 
                                               class="btn btn-sm btn-info"
                                               data-bs-toggle="tooltip" 
                                               title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="py-3">
                                            <i class="bi bi-calendar-x" style="font-size: 2rem; color: #d1d3e2;"></i>
                                            <h5 class="mt-2">No elections found</h5>
                                            <p class="text-muted">Create your first election to get started</p>
                                            <a href="elections.php?action=create" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Create Election
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <!-- Election Form -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <form method="POST" action="save_election.php" id="electionForm" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="<?= $action ?>">
                                <input type="hidden" name="electionID" value="<?= $electionID ?>">
                                
                                <div class="mb-4">
                                    <h5 class="mb-3">Election Information</h5>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Election Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" required
                                                           value="<?= $action === 'edit' ? htmlspecialchars($election['name'] ?? '') : '' ?>"
                                                           placeholder="Enter election name">
                                                    <div class="invalid-feedback">
                                                        Please provide an election name.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" class="form-control" name="startDate" required
                                                           value="<?= $action === 'edit' ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($election['startDate'] ?? ''))) : '' ?>">
                                                    <div class="invalid-feedback">
                                                        Please select a start date.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" class="form-control" name="endDate" required
                                                           value="<?= $action === 'edit' ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($election['endDate'] ?? ''))) : '' ?>">
                                                    <div class="invalid-feedback">
                                                        Please select an end date.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="Scheduled" <?= ($election['status'] ?? '') === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                        <option value="Ongoing" <?= ($election['status'] ?? '') === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                        <option value="Completed" <?= ($election['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select a status.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Visibility</label>
                                                    <select class="form-select" name="visibility">
                                                        <option value="Public" <?= ($election['visibility'] ?? '') === 'Public' ? 'selected' : '' ?>>Public</option>
                                                        <option value="Private" <?= ($election['visibility'] ?? '') === 'Private' ? 'selected' : '' ?>>Private</option>
                                                    </select>
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
                                        <i class="bi bi-save me-1"></i>
                                        <?= $action === 'edit' ? 'Update' : 'Create' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this election? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Warning:</strong> All associated data (candidates, votes, etc.) will also be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable with responsive settings
        if (document.getElementById('electionsTable')) {
            $('#electionsTable').DataTable({
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                return 'Election Details';
                            }
                        }),
                        renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                            tableClass: 'table'
                        })
                    }
                },
                "order": [[1, "desc"]],
                "language": {
                    "emptyTable": "No elections found",
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search elections...",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "previous": "<i class='bi bi-chevron-left'></i>",
                        "next": "<i class='bi bi-chevron-right'></i>"
                    }
                },
                "columnDefs": [
                    { "orderable": false, "targets": [4] },
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": 4 },
                    { "responsivePriority": 3, "targets": 3 }
                ]
            });
        }
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Delete election confirmation
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.querySelectorAll('.delete-election').forEach(function(button) {
            button.addEventListener('click', function() {
                const electionId = this.getAttribute('data-id');
                document.getElementById('confirmDelete').href = 'delete_election.php?id=' + electionId;
                deleteModal.show();
            });
        });
        
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
            })
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
        
        // Show mobile-specific elements
        function checkMobile() {
            if (window.innerWidth <= 767) {
                document.querySelectorAll('.mobile-show').forEach(el => el.style.display = 'block');
            } else {
                document.querySelectorAll('.mobile-show').forEach(el => el.style.display = 'none');
            }
        }
        
        // Run on load and resize
        checkMobile();
        window.addEventListener('resize', checkMobile);
    });
    </script>
</body>
</html>