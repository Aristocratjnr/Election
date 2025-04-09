<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

$success = null;
$error = null;

// Handle saving order updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    try {
        $positions = $_POST['positions'];
        $orderNumber = 1;
        
        foreach ($positions as $positionID) {
            $stmt = $conn->prepare("UPDATE positions SET display_order = ? WHERE positionID = ?");
            $stmt->bind_param("ii", $orderNumber, $positionID);
            $stmt->execute();
            $orderNumber++;
        }
        
        $success = "Position order updated successfully!";
    } catch (Exception $e) {
        $error = "Error updating position order: " . $e->getMessage();
    }
}

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Position Order - EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #224abe;
            --secondary-color: #f8f9fc;
            --text-primary: #5a5c69;
            --text-secondary: #858796;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-primary);
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1;
            position: fixed;
            height: 100vh;
        }
        
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 1.5rem;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top-left-radius: 0.75rem !important;
            border-top-right-radius: 0.75rem !important;
        }
        
        .header-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .position-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: white;
            border: 1px solid #e3e6f0;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: move;
            transition: background-color 0.2s, transform 0.2s;
        }
        
        .position-item:hover {
            background-color: rgba(78, 115, 223, 0.05);
            transform: translateY(-2px);
        }
        
        .position-handle {
            cursor: move;
            color: #adb5bd;
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        
        .position-title {
            font-weight: 600;
            flex-grow: 1;
        }
        
        .position-drag-ghost {
            opacity: 0.6;
        }
        
        .election-card {
            margin-bottom: 2rem;
        }
        
        .sortable-ghost {
            background-color: #e9ecef;
            border: 2px dashed #6c757d;
        }
        
        .sortable-chosen {
            background-color: #e7f1ff;
            border: 2px solid var(--primary-color);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }
        
        .order-indicator {
            background-color: var(--primary-color);
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <?php include 'includes/header.php'; ?><br><br>
            
            <div class="main-content">
                <nav aria-label="breadcrumb" class="mb-5">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="positions.php"><i class="bi bi-list-check me-1"></i>Positions</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-arrow-down-up me-1"></i>Manage Position Order</li>
                    </ol>
                </nav>
                
                <div class="page-header mb-4">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-arrows-move me-2"></i>Manage Position Order</h1>
                        <p class="mb-0 text-muted"><i class="bi bi-info-circle me-1"></i>Drag and drop to reorder positions within each election</p>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($elections->num_rows > 0): ?>
                    <ul class="nav nav-pills mb-4" id="electionsTab" role="tablist">
                        <?php $first = true; ?>
                        <?php while ($election = $elections->fetch_assoc()): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                        id="election-<?php echo $election['electionID']; ?>-tab" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#election-<?php echo $election['electionID']; ?>" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="election-<?php echo $election['electionID']; ?>" 
                                        aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                                    <i class="bi bi-megaphone me-1"></i> <?php echo htmlspecialchars($election['name']); ?>
                                </button>
                            </li>
                            <?php $first = false; ?>
                        <?php endwhile; ?>
                    </ul>
                    
                    <div class="tab-content" id="electionsTabContent">
                        <?php 
                        $elections->data_seek(0);
                        $first = true; 
                        ?>
                        
                        <?php while ($election = $elections->fetch_assoc()): ?>
                            <?php 
                            // Get positions for this election
                            $posQuery = $conn->prepare("
                                SELECT * FROM positions 
                                WHERE electionID = ? 
                                ORDER BY display_order, positionID ASC
                            ");
                            $posQuery->bind_param("i", $election['electionID']);
                            $posQuery->execute();
                            $positions = $posQuery->get_result();
                            ?>
                            
                            <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                                id="election-<?php echo $election['electionID']; ?>" 
                                role="tabpanel" 
                                aria-labelledby="election-<?php echo $election['electionID']; ?>-tab">
                                
                                <div class="card election-card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-megaphone header-icon"></i>
                                            <div>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($election['name']); ?></h5>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-range me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($election['startDate'])); ?> - 
                                                    <?php echo date('M d, Y', strtotime($election['endDate'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($positions->num_rows > 0): ?>
                                                <button class="save-order-btn btn btn-primary" data-election-id="<?php echo $election['electionID']; ?>">
                                                    <i class="bi bi-save me-1"></i> Save Order
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($positions->num_rows > 0): ?>
                                            <p class="mb-3">
                                                <i class="bi bi-info-circle-fill me-1"></i>
                                                Drag and drop positions to change their display order. The first position will appear at the top of the ballot.
                                            </p>
                                            
                                            <form id="order-form-<?php echo $election['electionID']; ?>" method="post">
                                                <div class="position-list-container" id="position-list-<?php echo $election['electionID']; ?>">
                                                    <?php $orderNum = 1; ?>
                                                    <?php while ($position = $positions->fetch_assoc()): ?>
                                                        <div class="position-item" data-id="<?php echo $position['positionID']; ?>">
                                                            <div class="order-indicator"><?php echo $orderNum++; ?></div>
                                                            <i class="bi bi-grip-vertical position-handle"></i>
                                                            <div class="position-title">
                                                                <?php echo htmlspecialchars($position['title']); ?>
                                                                <small class="text-muted ms-2">
                                                                    <i class="bi bi-123 me-1"></i>
                                                                    Max votes: <?php echo $position['maxVotes']; ?>
                                                                </small>
                                                            </div>
                                                            <div>
                                                                <span class="badge bg-secondary">
                                                                    <i class="bi bi-arrow-down-up me-1"></i>
                                                                    Display order: <?php echo $position['display_order']; ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="positions[]" class="position-input" value="<?php echo $position['positionID']; ?>">
                                                    <?php endwhile; ?>
                                                </div>
                                                
                                                <input type="hidden" name="save_order" value="1">
                                                <input type="hidden" name="election_id" value="<?php echo $election['electionID']; ?>">
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                No positions found for this election.
                                                <a href="positions.php" class="alert-link">Add positions</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php $first = false; ?>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        No elections found.
                        <a href="elections.php" class="alert-link">Add an election</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sortable lists
            document.querySelectorAll('.position-list-container').forEach(container => {
                const electionId = container.id.split('-').pop();
                
                new Sortable(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    handle: '.position-handle',
                    onEnd: function() {
                        // Update position inputs to match new order
                        const items = container.querySelectorAll('.position-item');
                        const inputs = document.querySelectorAll(`#order-form-${electionId} .position-input`);
                        
                        // Update order indicators
                        let orderNum = 1;
                        items.forEach((item, index) => {
                            const indicator = item.querySelector('.order-indicator');
                            if (indicator) {
                                indicator.textContent = orderNum++;
                            }
                            
                            if (inputs[index]) {
                                inputs[index].value = item.getAttribute('data-id');
                            }
                        });
                    }
                });
            });
            
            // Handle save button clicks
            document.querySelectorAll('.save-order-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const electionId = this.getAttribute('data-election-id');
                    document.querySelector(`#order-form-${electionId}`).submit();
                });
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert-success').forEach(alert => {
                    alert.classList.remove('show');
                });
            }, 5000);
        });
    </script>
</body>
</html> 