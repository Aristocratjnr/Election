<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

if (!isset($_GET['id'])) {
    header('Location: elections.php');
    exit();
}

$electionID = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
$stmt->bind_param('i', $electionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: elections.php?error=not_found');
    exit();
}

$election = $result->fetch_assoc();
$stmt->close();

// Get candidates for this election (example)
// $candidates = $conn->query("SELECT * FROM candidates WHERE electionID = $electionID");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .election-header {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            border-left: 4px solid #4e73df;
            border-radius: 0.35rem;
        }
        @media (max-width: 767.98px) {
            .election-header {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-calendar-event me-2"></i>
                Election Details
            </h2>
            <a href="elections.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Elections
            </a>
        </div>

        <div class="election-header">
            <div class="row">
                <div class="col-md-8">
                    <h1><?= htmlspecialchars($election['name']) ?></h1>
                    <p class="lead">
                        <?= date('F j, Y g:i A', strtotime($election['startDate'])) ?> 
                        to 
                        <?= date('F j, Y g:i A', strtotime($election['endDate'])) ?>
                    </p>
                    
                    <span class="badge <?= 
                        $election['status'] === 'Ongoing' ? 'bg-success' : 
                        ($election['status'] === 'Scheduled' ? 'bg-warning' : 'bg-secondary') 
                    ?>">
                        <?= $election['status'] ?>
                    </span>
                    
                    <span class="badge <?= $election['visibility'] === 'Public' ? 'bg-info' : 'bg-dark' ?> ms-2">
                        <?= $election['visibility'] ?>
                    </span>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="elections.php?action=edit&id=<?= $electionID ?>" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button class="btn btn-danger delete-election" data-id="<?= $electionID ?>">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Candidates</h5>
                        <p class="display-6">0</p>
                        <a href="candidates.php?election=<?= $electionID ?>" class="btn btn-sm btn-outline-primary">
                            Manage Candidates
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Voters</h5>
                        <p class="display-6">0</p>
                        <a href="voters.php?election=<?= $electionID ?>" class="btn btn-sm btn-outline-primary">
                            Manage Voters
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Votes</h5>
                        <p class="display-6">0</p>
                        <a href="results.php?election=<?= $electionID ?>" class="btn btn-sm btn-outline-primary">
                            View Results
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Election Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-point"></div>
                        <div class="timeline-content">
                            <h6>Election Created</h6>
                            <p class="text-muted small"><?= date('F j, Y g:i A', strtotime($election['created_at'] ?? 'now')) ?></p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-point"></div>
                        <div class="timeline-content">
                            <h6>Election Starts</h6>
                            <p class="text-muted small"><?= date('F j, Y g:i A', strtotime($election['startDate'])) ?></p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-point"></div>
                        <div class="timeline-content">
                            <h6>Election Ends</h6>
                            <p class="text-muted small"><?= date('F j, Y g:i A', strtotime($election['endDate'])) ?></p>
                        </div>
                    </div>
                </div>
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
                    <p class="text-danger"><strong>Warning:</strong> All associated data will also be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete Election</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete election confirmation
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.querySelector('.delete-election').addEventListener('click', function() {
            const electionId = this.getAttribute('data-id');
            document.getElementById('confirmDelete').href = 'delete_election.php?id=' + electionId;
            deleteModal.show();
        });
    });
    </script>
</body>
</html>