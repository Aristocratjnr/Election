<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_position'])) {
        $electionID = $_POST['electionID'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $maxVotes = $_POST['maxVotes'] ?? 1;
        
        $stmt = $conn->prepare("INSERT INTO positions (electionID, title, description, maxVotes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $electionID, $title, $description, $maxVotes);
        $stmt->execute();
        $success = "Position added successfully!";
    } elseif (isset($_POST['update_position'])) {
        $positionID = $_POST['positionID'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $maxVotes = $_POST['maxVotes'];
        
        $stmt = $conn->prepare("UPDATE positions SET title = ?, description = ?, maxVotes = ? WHERE positionID = ?");
        $stmt->bind_param("ssii", $title, $description, $maxVotes, $positionID);
        $stmt->execute();
        $success = "Position updated successfully!";
    } elseif (isset($_POST['delete_position'])) {
        $positionID = $_POST['positionID'];
        
        $stmt = $conn->prepare("DELETE FROM positions WHERE positionID = ?");
        $stmt->bind_param("i", $positionID);
        $stmt->execute();
        $success = "Position deleted successfully!";
    }
}

// Get all elections for dropdown
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Get all positions
$positions = $conn->query("
    SELECT p.*, e.name as electionName 
    FROM positions p
    JOIN elections e ON p.electionID = e.electionID
    ORDER BY p.positionID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Positions - EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
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
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .table {
            color: var(--text-primary);
        }
        
        .table th {
            font-weight: 600;
            border-top: none;
            background-color: #f8f9fc;
        }
        
        .badge-status {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        
        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .modal-content {
            border-radius: 0.35rem;
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-top-left-radius: 0.35rem;
            border-top-right-radius: 0.35rem;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .alert {
            border-radius: 0.35rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            padding: 1.5rem 1rem;
        }
        
        .search-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-wrapper .form-control {
            padding-left: 2.5rem;
            border-radius: 10rem;
            height: calc(1.5em + 0.75rem + 2px);
        }
        
        .search-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background-color: transparent;
            padding: 0;
        }
        
        .card-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            border-radius: 0.35rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .card-stats-icon {
            font-size: 2rem;
            padding: 1rem;
            border-radius: 50%;
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }
        
        .card-stats-info {
            text-align: right;
        }
        
        .card-stats-number {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .card-stats-label {
            color: var(--text-secondary);
            margin-bottom: 0;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .dropdown-item.active, .dropdown-item:active {
            background-color: var(--primary-color);
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
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Positions</li>
                    </ol>
                </nav>
                
                <div class="page-header">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">Manage Positions</h1>
                        <p class="mb-0 text-muted">Create and manage election positions</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Position
                    </button>
                </div>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card-stats bg-white">
                            <div class="card-stats-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="card-stats-info">
                                <p class="card-stats-number">
                                    <?php 
                                    $total = $conn->query("SELECT COUNT(*) as total FROM positions")->fetch_assoc();
                                    echo $total['total'];
                                    ?>
                                </p>
                                <p class="card-stats-label">Total Positions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card-stats bg-white">
                            <div class="card-stats-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="card-stats-info">
                                <p class="card-stats-number">
                                    <?php 
                                    $activeElections = $conn->query("SELECT COUNT(DISTINCT electionID) as total FROM positions")->fetch_assoc();
                                    echo $activeElections['total'];
                                    ?>
                                </p>
                                <p class="card-stats-label">Elections With Positions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card-stats bg-white">
                            <div class="card-stats-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="card-stats-info">
                            <p class="card-stats-number">
                                <?php 
                                $avgVotes = $conn->query("SELECT AVG(maxVotes) as avg FROM positions")->fetch_assoc();
                                echo ($avgVotes['avg'] !== null) ? number_format($avgVotes['avg'], 1) : '0.0';
                                ?>
                            </p>
                                <p class="card-stats-label">Avg. Max Votes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-list-check header-icon"></i>
                            <h5 class="mb-0">Current Positions</h5>
                        </div>
                        <div class="search-wrapper d-none d-md-block">
                            <input type="text" id="positionSearch" class="form-control" placeholder="Search positions...">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="positionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Position Title</th>
                                        <th>Election</th>
                                        <th>Max Votes</th>
                                        <th>Description</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($position = $positions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $position['positionID']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($position['title']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($position['electionName']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $position['maxVotes']; ?></span>
                                        </td>
                                        <td class="description-cell" title="<?php echo htmlspecialchars($position['description']); ?>">
                                            <?php echo htmlspecialchars($position['description']); ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons justify-content-center">
                                                <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPositionModal"
                                                        data-id="<?php echo $position['positionID']; ?>"
                                                        data-title="<?php echo htmlspecialchars($position['title']); ?>"
                                                        data-description="<?php echo htmlspecialchars($position['description']); ?>"
                                                        data-maxvotes="<?php echo $position['maxVotes']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="positionID" value="<?php echo $position['positionID']; ?>">
                                                    <button type="submit" name="delete_position" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this position? This action cannot be undone.')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1" aria-labelledby="addPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPositionModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Add New Position
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="electionID" class="form-label">Election</label>
                            <select class="form-select" id="electionID" name="electionID" required>
                                <option value="">Select Election</option>
                                <?php 
                                // Reset the elections result pointer
                                $elections->data_seek(0);
                                while ($election = $elections->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $election['electionID']; ?>"><?php echo htmlspecialchars($election['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Position Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="maxVotes" class="form-label">Max Votes Allowed</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <input type="number" class="form-control" id="maxVotes" name="maxVotes" value="1" min="1" required>
                            </div>
                            <small class="form-text text-muted">Number of candidates a voter can select for this position</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_position" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <div class="modal fade" id="editPositionModal" tabindex="-1" aria-labelledby="editPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPositionModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Edit Position
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_positionID" name="positionID">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Position Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_maxVotes" class="form-label">Max Votes Allowed</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <input type="number" class="form-control" id="edit_maxVotes" name="maxVotes" min="1" required>
                            </div>
                            <small class="form-text text-muted">Number of candidates a voter can select for this position</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_position" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Update Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#positionsTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                responsive: true,
                language: {
                    search: "",
                    searchPlaceholder: "Search positions..."
                }
            });
            
            // Style DataTable search input
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
            
            // Handle edit button clicks
            $('.edit-btn').on('click', function() {
                $('#edit_positionID').val($(this).data('id'));
                $('#edit_title').val($(this).data('title'));
                $('#edit_description').val($(this).data('description'));
                $('#edit_maxVotes').val($(this).data('maxvotes'));
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert-success').fadeOut('slow');
            }, 5000);
            
            // Enable tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Simple search functionality
            $("#positionSearch").on("keyup", function() {
                const value = $(this).val().toLowerCase();
                $("#positionsTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
  
</body>
</html>