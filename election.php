<?php
// Enable error reporting at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';
require_once 'update_election_status.php'; // Include the status updater

// Automatically update election statuses when managing elections
updateElectionStatuses();

$action = $_GET['action'] ?? 'manage';
$electionID = $_GET['id'] ?? null;

// Define error and success messages
$errorMessages = [
    'not_found' => 'Election not found or has been deleted.',
    'delete_failed' => 'Failed to delete the election. Please try again.',
    'invalid_request' => 'Invalid request. Please try again.',
    'update_failed' => 'Failed to update election. Please try again.',
    'missing_fields' => 'Required fields are missing.',
    'invalid_dates' => 'End date must be after start date.',
    'db_error' => 'Database error occurred. Please try again later.',
    'database_error' => 'Database error occurred. Please try again later.'
];

$successMessages = [
    'created' => 'Election created successfully.',
    'updated' => 'Election updated successfully.',
    'deleted' => 'Election deleted successfully.'
];

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    // Debug log
    error_log('Received POST data from edit form: ' . print_r($_POST, true));
    error_log('Election ID: ' . $electionID);
    
    // Check if $electionID is valid
    if (!$electionID || !is_numeric($electionID)) {
        error_log("Invalid electionID: $electionID");
        header('Location: election.php?error=invalid_request&message='.urlencode("Invalid election ID"));
        exit;
    }
    
    // Validate required fields
    $required = ['name', 'startDate', 'endDate', 'status'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            error_log("Missing required field: $field");
            header('Location: election.php?action=edit&id='.$electionID.'&error=missing_fields');
            exit;
        }
    }
    
    try {
        // Process dates with validation
        $startTimestamp = strtotime($_POST['startDate']);
        $endTimestamp = strtotime($_POST['endDate']);
        
        if ($startTimestamp === false || $endTimestamp === false) {
            error_log("Invalid date format. Start: {$_POST['startDate']}, End: {$_POST['endDate']}");
            throw new Exception("Invalid date format");
        }
        
        $startDate = date('Y-m-d H:i:s', $startTimestamp);
        $endDate = date('Y-m-d H:i:s', $endTimestamp);
        
        if ($endDate < $startDate) {
            header('Location: election.php?action=edit&id='.$electionID.'&error=invalid_dates');
            exit;
        }
        
        // Ensure visibility has a value
        $visibility = !empty($_POST['visibility']) ? $_POST['visibility'] : 'Public';
        error_log("Using visibility: $visibility");
        
        // Verify database connection is active
        if (!$conn || mysqli_connect_errno()) {
            error_log("Database connection lost. Attempting to reconnect...");
            // Try to reconnect
            require_once 'configs/dbconnection.php';
            
            if (!$conn || mysqli_connect_errno()) {
                throw new Exception("Database connection failed after reconnect attempt");
            }
            error_log("Database reconnection successful");
        }
        
        // Construct the SQL query for better error tracking
        $sql = "UPDATE elections SET 
            name = ?, 
            startDate = ?, 
            endDate = ?, 
            status = ?, 
            visibility = ? 
            WHERE electionID = ?";
            
        error_log("SQL Query: $sql");
        
        // Update election in database
        $stmt = $conn->prepare($sql);
        
        error_log("Preparing to execute SQL query for updating election...");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        error_log("Binding parameters: Name={$_POST['name']}, Start={$startDate}, End={$endDate}, Status={$_POST['status']}, Visibility={$visibility}, ID={$electionID}");
        
        $stmt->bind_param('sssssi', 
            $_POST['name'],
            $startDate,
            $endDate,
            $_POST['status'],
            $visibility,
            $electionID
        );
        
        error_log("Executing update query...");
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        error_log("Update successful. Affected rows: " . $stmt->affected_rows);
        $stmt->close();
        header('Location: election.php?success=updated');
        exit;
    } catch (Exception $e) {
        error_log("Election update error: " . $e->getMessage());
        header('Location: election.php?error=db_error&message='.urlencode($e->getMessage()));
        exit;
    }
}

// Fetch election data if editing
$election = null;
if ($action === 'edit' && $electionID) {
    try {
        // Verify database connection is active
        if (!$conn || mysqli_connect_errno()) {
            error_log("Database connection lost in fetch section. Attempting to reconnect...");
            // Try to reconnect
            require_once 'configs/dbconnection.php';
            
            if (!$conn || mysqli_connect_errno()) {
                throw new Exception("Database connection failed after reconnect attempt");
            }
            error_log("Database reconnection successful");
        }
        
        $stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $electionID);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $election = $result->fetch_assoc();
        } else {
            header('Location: election.php?error=not_found');
            exit;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Election fetch error: " . $e->getMessage());
        header('Location: election.php?error=database_error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

// Check for messages
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
$errorDetail = $_GET['message'] ?? null;
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
        .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(78, 115, 223, 0.03);
            --bs-table-hover-bg: rgba(78, 115, 223, 0.05);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #5a5c69;
            white-space: nowrap;
            background-color: #f8f9fc;
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
            padding: 0.5rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-dark);
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 10px;
            color: var(--primary-color);
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
        
        /* Compact Alert Styles */
        .alert {
            border-radius: 0.25rem;
            border-left-width: 4px;
        }
        
        .alert.py-2 {
            margin-bottom: 0.75rem;
        }
        
        .alert-danger {
            border-left-color: var(--danger-color);
        }
        
        .alert-success {
            border-left-color: var(--success-color);
        }
        
        .alert-warning {
            border-left-color: var(--warning-color);
        }
        
        .alert .small {
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        .alert .btn-close.btn-sm {
            font-size: 0.65rem;
            padding: 0.25rem;
        }
        
        .alert i {
            opacity: 0.85;
        }
        
        /* Auto-dismiss animation */
        @keyframes fadeOutAlert {
            from { opacity: 1; }
            to { opacity: 0; height: 0; margin: 0; padding: 0; border: 0; }
        }
        
        .alert.auto-dismiss {
            animation: fadeOutAlert 0.5s ease forwards;
            animation-delay: 5s;
        }
    </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?><br><br><br><br>
    <div class="container-fluid py-3">
        <!-- Error/Success Alerts -->
        <?php if ($error === 'not_found'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-exclamation-octagon-fill text-danger" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Election Not Found</strong> - The election doesn't exist or has been deleted.
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php elseif ($error === 'delete_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-x-octagon-fill text-danger" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Deletion Failed</strong> - Please try again or contact support.
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php elseif ($error === 'invalid_request'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Invalid Request</strong> - Please try again with valid parameters.
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php elseif ($error === 'db_error' || $error === 'database_error'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-database-exclamation text-danger" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Database Error</strong> - Please try again later.
                        <?php if (!empty($errorDetail)): ?>
                            <small class="d-block text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($errorDetail) ?></small>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success === 'created'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Election Created</strong> - Your new election has been created successfully.
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php elseif ($success === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Election Updated</strong> - The election details have been updated successfully.
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php elseif ($success === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm py-2" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 1rem;"></i>
                    </div>
                    <div class="small">
                        <strong>Election Deleted</strong> - The election has been successfully deleted.
                    </div>
                    <button type="button" class="btn-close btn-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <div class="card w-50 mx-auto">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    <?= ucfirst($action) ?> Election
                </h4>
                <a href="election.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body">
                <?php if ($action === 'manage'): ?>
                    <!-- Elections List -->
                    <div class="page-header">
                        <h1 class="page-title">
                            <i class="bi bi-clipboard2-data me-2 text-primary"></i> 
                            Elections Management
                            <span class="badge bg-primary ms-2 rounded-pill"><?= $result->num_rows ?? 0 ?></span>
                        </h1>
                        <a href="new_election.php" class="btn btn-primary position-relative">
                            <i class="bi bi-plus-circle-fill me-2"></i> New Election
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <i class="bi bi-stars"></i>
                                <span class="visually-hidden">New Election</span>
                            </span>
                        </a>
                    </div>
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-check me-2 text-primary"></i>All Elections</h5>
                            <button id="refreshElectionStatus" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i> Update Status
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0" id="electionsTable" style="width:100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="bi bi-card-heading me-1"></i> Election</th>
                                            <th class="mobile-hide"><i class="bi bi-calendar-check me-1"></i> Start Date</th>
                                            <th class="mobile-hide-sm"><i class="bi bi-calendar-x me-1"></i> End Date</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th class="text-end"><i class="bi bi-gear-fill me-1"></i> Actions</th>
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
                                                
                                                $statusIcon = [
                                                    'Scheduled' => 'bi-calendar-event',
                                                    'Ongoing' => 'bi-play-circle-fill',
                                                    'Completed' => 'bi-flag-fill'
                                                ][$election['status'] ?? 'Scheduled'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar2-event me-2 text-primary"></i>
                                                    <div>
                                                        <strong><?= htmlspecialchars($election['name']) ?></strong>
                                                        <div class="mobile-show text-muted small mt-1" style="display: none;">
                                                            <i class="bi bi-calendar-check me-1"></i><?= date('M d, Y', strtotime($election['startDate'])) ?> 
                                                            <i class="bi bi-arrow-right mx-1"></i>
                                                            <i class="bi bi-calendar-x me-1"></i><?= date('M d, Y', strtotime($election['endDate'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="mobile-hide">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar-check me-2 text-muted"></i>
                                                    <?= date('M d, Y', strtotime($election['startDate'])) ?>
                                                </div>
                                            </td>
                                            <td class="mobile-hide-sm">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar-x me-2 text-muted"></i>
                                                    <?= date('M d, Y', strtotime($election['endDate'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge <?= $statusClass ?>">
                                                        <span class="status-indicator"></span>
                                                        <i class="bi <?= $statusIcon ?> me-1"></i>
                                                        <span class="status-text"><?= $election['status'] ?></span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-end action-btns">
                                                <div class="btn-group">
                                                    <a href="election.php?action=edit&id=<?= $election['electionID'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Edit Election">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger delete-election" 
                                                            data-id="<?= $election['electionID'] ?>"
                                                            data-bs-toggle="tooltip" 
                                                            title="Delete Election">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                    <a href="election_details.php?id=<?= $election['electionID'] ?>" 
                                                       class="btn btn-sm btn-info"
                                                       data-bs-toggle="tooltip" 
                                                       title="View Details">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <a href="results.php?id=<?= $election['electionID'] ?>" 
                                                       class="btn btn-sm btn-success"
                                                       data-bs-toggle="tooltip" 
                                                       title="View Results">
                                                        <i class="bi bi-bar-chart-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile; 
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="text-center py-5">
                                                    <div class="mb-3">
                                                        <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                                                    </div>
                                                    <h4 class="text-muted mb-3">No Elections Found</h4>
                                                    <p class="text-muted mb-4">You haven't created any elections yet. Get started by creating your first election.</p>
                                                    <a href="new_election.php" class="btn btn-primary btn-lg">
                                                        <i class="bi bi-plus-circle-fill me-2"></i> Create Your First Election
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Election Form -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <?php if ($action === 'edit'): ?>
                            <!-- Direct Edit Form -->
                            <form method="POST" action="election.php?action=edit&id=<?= $electionID ?>" id="electionForm" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Edit Election</h5>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label for="editName" class="form-label"><i class="bi bi-card-heading me-1"></i>Election Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="editName" name="name" required
                                                           value="<?= htmlspecialchars($election['name'] ?? '') ?>"
                                                           placeholder="Enter election name">
                                                    <div class="invalid-feedback">Please provide an election name.</div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="editStartDate" class="form-label"><i class="bi bi-calendar-check me-1"></i>Start Date <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" class="form-control" id="editStartDate" name="startDate" required
                                                           value="<?= date('Y-m-d\TH:i', strtotime($election['startDate'] ?? '')) ?>">
                                                    <div class="invalid-feedback">Please select a start date.</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEndDate" class="form-label"><i class="bi bi-calendar-x me-1"></i>End Date <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" class="form-control" id="editEndDate" name="endDate" required
                                                           value="<?= date('Y-m-d\TH:i', strtotime($election['endDate'] ?? '')) ?>">
                                                    <div class="invalid-feedback">Please select an end date.</div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="editStatus" class="form-label"><i class="bi bi-info-square me-1"></i>Status <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="editStatus" name="status" required>
                                                        <option value="Scheduled" <?= ($election['status'] ?? '') === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                        <option value="Ongoing" <?= ($election['status'] ?? '') === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                        <option value="Completed" <?= ($election['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please select a status.</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editVisibility" class="form-label"><i class="bi bi-eye me-1"></i>Visibility</label>
                                                    <select class="form-select" id="editVisibility" name="visibility">
                                                        <option value="Public" <?= ($election['visibility'] ?? '') === 'Public' ? 'selected' : '' ?>>Public</option>
                                                        <option value="Private" <?= ($election['visibility'] ?? '') === 'Private' ? 'selected' : '' ?>>Private</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="election.php" class="btn btn-secondary" id="cancelDirectEdit" name="cancelDirectEdit">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitDirectEdit" name="submitDirectEdit">
                                        <i class="bi bi-save me-1"></i> Update Election
                                    </button>
                                </div>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="save_election.php" id="electionForm" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="<?= $action ?>">
                                <input type="hidden" name="electionID" value="<?= $electionID ?>">
                                
                                <div class="mb-4">
                                    <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Election Information</h5>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label for="name" class="form-label"><i class="bi bi-card-heading me-1"></i>Election Name <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                                                        <input type="text" class="form-control" id="name" name="name" required
                                                               value="<?= $action === 'edit' ? htmlspecialchars($election['name'] ?? '') : '' ?>"
                                                               placeholder="Enter election name">
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Please provide an election name.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="startDate" class="form-label"><i class="bi bi-calendar-check me-1"></i>Start Date <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                        <input type="datetime-local" class="form-control" id="startDate" name="startDate" required
                                                               value="<?= $action === 'edit' ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($election['startDate'] ?? ''))) : '' ?>">
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Please select a start date.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="endDate" class="form-label"><i class="bi bi-calendar-x me-1"></i>End Date <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                        <input type="datetime-local" class="form-control" id="endDate" name="endDate" required
                                                               value="<?= $action === 'edit' ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($election['endDate'] ?? ''))) : '' ?>">
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Please select an end date.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="status" class="form-label"><i class="bi bi-info-square me-1"></i>Status <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-list-check"></i></span>
                                                        <select class="form-select" id="status" name="status" required>
                                                            <option value="Scheduled" <?= ($election['status'] ?? '') === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                            <option value="Ongoing" <?= ($election['status'] ?? '') === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                            <option value="Completed" <?= ($election['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                        </select>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Please select a status.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="visibility" class="form-label"><i class="bi bi-eye me-1"></i>Visibility</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                        <select class="form-select" id="visibility" name="visibility">
                                                            <option value="Public" <?= ($election['visibility'] ?? '') === 'Public' ? 'selected' : '' ?>>Public</option>
                                                            <option value="Private" <?= ($election['visibility'] ?? '') === 'Private' ? 'selected' : '' ?>>Private</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="election.php" class="btn btn-secondary" id="cancelCreateEdit" name="cancelCreateEdit">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitCreateEdit" name="submitCreateEdit">
                                        <i class="bi bi-save me-1"></i>
                                        <?= $action === 'edit' ? 'Update Election' : 'Create Election' ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="mb-3">Are you sure you want to delete this election? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading">Warning</h6>
                                <p class="mb-0">All associated data (candidates, votes, positions) will also be permanently deleted.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" id="cancelDeleteModal" name="cancelDeleteModal" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <a href="#" id="confirmDelete" class="btn btn-danger" name="confirmDelete">
                        <i class="bi bi-trash-fill me-1"></i>Delete Permanently
                    </a>
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
            const dataTable = $('#electionsTable').DataTable({
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                return '<i class="bi bi-info-circle me-2"></i>Election Details';
                            }
                        }),
                        renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                            tableClass: 'table'
                        })
                    }
                },
                "order": [[1, "desc"]],
                "language": {
                    "emptyTable": "<div class='text-center py-4'><i class='bi bi-calendar-x' style='font-size: 2rem; color: #d1d3e2;'></i><p class='mt-2'>No elections found</p></div>",
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
                ],
                "initComplete": function(settings, json) {
                    // Add search icon to search input
                    $('.dataTables_filter input').before('<i class="bi bi-search text-muted me-2"></i>');
                    
                    // Add refresh button next to filter
                    const refreshButton = $('<button class="btn btn-sm btn-outline-primary ms-2" id="refreshElectionStatus"><i class="bi bi-arrow-clockwise"></i> Update Status</button>');
                    $('.dataTables_filter').append(refreshButton);
                    
                    // Handle refresh button click
                    $('#refreshElectionStatus').on('click', function() {
                        updateElectionStatuses();
                    });
                }
            });
            
            // Function to update election statuses via AJAX
            function updateElectionStatuses() {
                // Show loading indicator
                const refreshBtn = $('#refreshElectionStatus');
                refreshBtn.html('<i class="bi bi-arrow-clockwise"></i> Updating...').prop('disabled', true);
                
                // Send AJAX request to update statuses
                $.ajax({
                    url: 'update_election_status.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-check-circle-fill me-2"></i>' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>');
                                
                            $('.container-fluid').prepend(alert);
                            
                            // Auto dismiss after 3 seconds
                            setTimeout(function() {
                                alert.alert('close');
                            }, 3000);
                            
                            // Reload table if any elections were updated
                            if (response.updated > 0) {
                                dataTable.ajax.reload();
                                // If ajax isn't available, just reload the page
                                if (!dataTable.ajax) {
                                    location.reload();
                                }
                            }
                        } else {
                            // Show error message
                            const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-exclamation-triangle-fill me-2"></i>' +
                                'Failed to update election statuses: ' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>');
                                
                            $('.container-fluid').prepend(alert);
                        }
                    },
                    error: function() {
                        // Show error message
                        const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-exclamation-triangle-fill me-2"></i>' +
                            'Failed to communicate with the server. Please try again.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>');
                            
                        $('.container-fluid').prepend(alert);
                    },
                    complete: function() {
                        // Reset button state
                        refreshBtn.html('<i class="bi bi-arrow-clockwise"></i> Update Status').prop('disabled', false);
                    }
                });
            }
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
        
        // Add auto-dismiss class to alerts for automatic hiding
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.classList.add('auto-dismiss');
        });
        
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