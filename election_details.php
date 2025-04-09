<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';
require_once 'update_election_status.php'; // Include the status updater

// Automatically update election statuses when viewing election details
updateElectionStatuses();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

$election_id = $_GET['id'] ?? null;

if (!$election_id) {
    header('Location: dashboard.php');
    exit();
}

try {
    // Get election details
    $election_query = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $election_query->bind_param("i", $election_id);
    $election_query->execute();
    $election = $election_query->get_result()->fetch_assoc();
    
    if (!$election) {
        throw new Exception("Election not found");
    }
    
    // Format dates for display
    $start_date = new DateTime($election['startDate']);
    $end_date = new DateTime($election['endDate']);
    
    // Check if time fields exist in the table
    $time_fields_exist = false;
    
    try {
        $check_fields = $conn->query("SHOW COLUMNS FROM elections LIKE 'start_time'");
        $time_fields_exist = ($check_fields->num_rows > 0);
    } catch (Exception $e) {
        // If the query fails, assume time fields don't exist
        $time_fields_exist = false;
    }
    
    // Set default times if not in database
    $start_time = "08:00 AM";
    $end_time = "05:00 PM";
    
    if ($time_fields_exist) {
        if (!empty($election['start_time'])) {
            $start_time_obj = new DateTime($election['start_time']);
            $start_time = $start_time_obj->format('h:i A');
        }
        
        if (!empty($election['end_time'])) {
            $end_time_obj = new DateTime($election['end_time']);
            $end_time = $end_time_obj->format('h:i A');
        }
    }
    
    // Format for display
    $formatted_start_date = $start_date->format('F j, Y');
    $formatted_end_date = $end_date->format('F j, Y');
    
    // Get total voters
    $voters_query = $conn->query("SELECT COUNT(*) as total FROM students WHERE status = 'Active'");
    $total_voters = $voters_query->fetch_assoc()['total'];
    
    // Get total votes cast
    $votes_query = $conn->prepare("SELECT COUNT(DISTINCT studentID) as total FROM votes WHERE electionID = ?");
    $votes_query->bind_param("i", $election_id);
    $votes_query->execute();
    $total_voted = $votes_query->get_result()->fetch_assoc()['total'];
    
    $participation_rate = ($total_voters > 0) ? round(($total_voted / $total_voters) * 100) : 0;
    
    // Get categories for this election
    $categories_query = $conn->prepare("SELECT * FROM categories WHERE electionID = ?");
    $categories_query->bind_param("i", $election_id);
    $categories_query->execute();
    $categories = $categories_query->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Election Details - SmartVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
     
        .main-content {
            margin-left: 120px;
            width: calc(100% - 210px);
        }
        
        .navbar {
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0.5rem 1rem;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s ease;
        }
        
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            padding: 1rem 1.35rem;
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.5rem;
        }
        
        .bg-primary-light {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }
        
        .bg-success-light {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }
        
        .progress-thin {
            height: 6px;
            border-radius: 3px;
        }
        
        .badge-pill {
            border-radius: 10rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            vertical-align: middle;
            border-top: none;
            padding: 1rem;
            background-color: white;
        }
        
        .table tr:first-child td {
            border-top-left-radius: 0.35rem;
            border-bottom-left-radius: 0.35rem;
        }
        
        .table tr:last-child td {
            border-top-right-radius: 0.35rem;
            border-bottom-right-radius: 0.35rem;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .page-header {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .text-success {
            color: var(--success-color) !important;
        }
        
        .text-warning {
            color: var(--warning-color) !important;
        }
        
        .animate-bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .stat-card {
            border-left: 4px solid;
        }
        
        .stat-card.primary {
            border-left-color: var(--primary-color);
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.info {
            border-left-color: var(--info-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?><br><br>
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-5">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 page-header">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <i class="bi bi-check2-circle text-primary me-2"></i>
                                Election Details: <strong><?php echo htmlspecialchars($election['name'] ?? ''); ?></strong>
                            </h1>
                            <p class="mb-0 text-muted">Detailed information about the election and participation statistics</p>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                    
                    <!-- Error Alert -->
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4 g-4">
                        <!-- Election Info Card -->
                        <div class="col-lg-6">
                            <div class="card stat-card primary h-100 hover-effect">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title text-primary mb-0">
                                            <i class="bi bi-info-circle me-2"></i>Election Information
                                        </h5>
                                        <span class="badge bg-primary bg-opacity-10 text-primary badge-pill">
                                            <?php echo htmlspecialchars($election['status'] ?? ''); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-event text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Start Date</small>
                                                <p class="mb-0 fw-bold">
                                                    <?php echo $formatted_start_date; ?>
                                                    <span class="badge bg-light text-primary ms-2">
                                                        <i class="bi bi-clock"></i> <?php echo $start_time; ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">End Date</small>
                                                <p class="mb-0 fw-bold">
                                                    <?php echo $formatted_end_date; ?>
                                                    <span class="badge bg-light text-primary ms-2">
                                                        <i class="bi bi-clock"></i> <?php echo $end_time; ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-journals text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Status</small>
                                                <p class="mb-0">
                                                    <span class="badge <?php echo ($election['status'] == 'Ongoing') ? 'bg-success' : (($election['status'] == 'Scheduled') ? 'bg-warning' : 'bg-secondary'); ?>">
                                                        <i class="bi <?php echo ($election['status'] == 'Ongoing') ? 'bi-play-circle' : (($election['status'] == 'Scheduled') ? 'bi-calendar-date' : 'bi-check-circle'); ?>"></i>
                                                        <?php echo htmlspecialchars($election['status'] ?? ''); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Description</small>
                                            <p class="mb-0"><?php echo htmlspecialchars($election['description'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Participation Card -->
                        <div class="col-lg-6">
                            <div class="card stat-card success h-100 hover-effect">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-4">
                                        <i class="bi bi-people-fill me-2"></i>Participation Statistics
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon bg-success-light me-3">
                                                    <i class="bi bi-person-check-fill"></i>
                                                </div>
                                                <div>
                                                    <h2 class="mb-0"><?php echo $total_voted; ?></h2>
                                                    <p class="text-muted mb-0">Votes Cast</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon bg-primary-light me-3">
                                                    <i class="bi bi-people-fill"></i>
                                                </div>
                                                <div>
                                                    <h2 class="mb-0"><?php echo $total_voters; ?></h2>
                                                    <p class="text-muted mb-0">Total Voters</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted"><i class="bi bi-bar-chart-line me-1"></i> Participation Rate</span>
                                                    <span class="fw-bold text-<?php echo ($participation_rate > 50) ? 'success' : 'warning'; ?>">
                                                        <?php echo $participation_rate; ?>%
                                                    </span>
                                                </div>
                                                <div class="progress progress-thin">
                                                    <div class="progress-bar bg-<?php echo ($participation_rate > 50) ? 'success' : 'warning'; ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $participation_rate; ?>%"
                                                         aria-valuenow="<?php echo $participation_rate; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    <?php echo $total_voted; ?> out of <?php echo $total_voters; ?> voters have participated
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories Card -->
                    <div class="card shadow-sm mb-4 hover-effect">
                        <div class="card-header py-3 d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <h5 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-list-task me-2"></i>Election Categories
                            </h5>
                            <div class="mt-2 mt-md-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <?php echo count($categories); ?> Categories
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($categories)): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th width="40%">Category Name</th>
                                                <th>Description</th>
                                                <th width="120">Candidates</th>
                                                <th width="150">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                            <tr class="animate__animated animate__fadeIn">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="card-icon bg-primary-light me-3">
                                                            <i class="bi bi-tag-fill"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="mb-0 text-muted"><?php echo isset($category['description']) ? htmlspecialchars($category['description']) : 'No description available'; ?></p>
                                                </td>
                                                <td>
                                                    <?php 
                                                    // Count candidates related to positions in this election
                                                    $cand_query = $conn->prepare("
                                                        SELECT COUNT(*) as count 
                                                        FROM candidates c
                                                        JOIN positions p ON c.positionID = p.positionID 
                                                        WHERE p.electionID = ?
                                                    ");
                                                    $cand_query->bind_param("i", $election_id);
                                                    $cand_query->execute();
                                                    $cand_count = $cand_query->get_result()->fetch_assoc()['count'];
                                                    ?>
                                                    <span class="badge bg-info bg-opacity-10 text-info">
                                                        <?php echo $cand_count; ?> Candidates
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="category_details.php?id=<?php echo $category['categoryID']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-folder-x text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 text-muted">No categories found for this election</h5>
                                    <p class="text-muted">Add categories to organize candidates and voting options</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white hover-effect">
                                <div class="card-body text-center">
                                    <i class="bi bi-pencil-square display-6 mb-3"></i>
                                    <h5 class="card-title">Edit Election</h5>
                                    <p class="card-text">Update election details and settings</p>
                                    <a href="edit_election.php?id=<?php echo $election_id; ?>" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i> Go to Editor
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-success text-white hover-effect">
                                <div class="card-body text-center">
                                    <i class="bi bi-graph-up-arrow display-6 mb-3"></i>
                                    <h5 class="card-title">View Results</h5>
                                    <p class="card-text">See live voting results and analytics</p>
                                    <a href="results.php?election=<?php echo $election_id; ?>" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i> View Results
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-warning text-white hover-effect">
                                <div class="card-body text-center">
                                    <i class="bi bi-send-fill display-6 mb-3"></i>
                                    <h5 class="card-title">Send Reminders</h5>
                                    <p class="card-text">Notify voters who haven't participated yet</p>
                                    <a href="send_reminders.php?election=<?php echo $election_id; ?>" class="btn btn-light btn-sm">
                                        <i class="bi bi-arrow-right me-1"></i> Send Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation to participation progress bar
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                setTimeout(() => {
                    progressBar.style.transition = 'width 1.5s ease';
                }, 300);
            }
            
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', () => {
                    row.classList.add('shadow-sm');
                });
                row.addEventListener('mouseleave', () => {
                    row.classList.remove('shadow-sm');
                });
            });
        });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>