<?php
session_start();
require 'configs/dbconnection.php';

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$duplicates = [];
$elections = [];

// Get all elections
$electionsQuery = $conn->query("SELECT electionID, name FROM elections ORDER BY startDate DESC");
while ($election = $electionsQuery->fetch_assoc()) {
    $elections[$election['electionID']] = $election;
}

// Find duplicate positions
$query = "SELECT electionID, title, COUNT(*) as count, GROUP_CONCAT(positionID) as position_ids 
          FROM positions 
          GROUP BY electionID, LOWER(title) 
          HAVING COUNT(*) > 1 
          ORDER BY electionID, title";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ids = explode(',', $row['position_ids']);
        
        // Get more details about each duplicate position
        $posDetails = [];
        foreach ($ids as $id) {
            $detailsQuery = $conn->prepare("SELECT positionID, title, description, maxVotes, display_order, 
                                           (SELECT COUNT(*) FROM candidates WHERE positionID = ?) as candidate_count 
                                           FROM positions WHERE positionID = ?");
            $detailsQuery->bind_param('ii', $id, $id);
            $detailsQuery->execute();
            $details = $detailsQuery->get_result()->fetch_assoc();
            $detailsQuery->close();
            
            if ($details) {
                $posDetails[] = $details;
            }
        }
        
        $duplicates[] = [
            'electionID' => $row['electionID'],
            'electionName' => $elections[$row['electionID']]['name'] ?? 'Unknown Election',
            'title' => $row['title'],
            'count' => $row['count'],
            'positions' => $posDetails
        ];
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_position'])) {
    $positionID = (int)$_POST['position_id'];
    
    // First check if this position has any candidates
    $checkCandidates = $conn->prepare("SELECT COUNT(*) as count FROM candidates WHERE positionID = ?");
    $checkCandidates->bind_param('i', $positionID);
    $checkCandidates->execute();
    $candidateCount = $checkCandidates->get_result()->fetch_assoc()['count'];
    $checkCandidates->close();
    
    if ($candidateCount > 0) {
        $message = '<div class="alert alert-danger">Cannot delete position ID ' . $positionID . ' because it has ' . $candidateCount . ' candidates. You must reassign or delete these candidates first.</div>';
    } else {
        // Delete the position
        $deleteQuery = $conn->prepare("DELETE FROM positions WHERE positionID = ?");
        $deleteQuery->bind_param('i', $positionID);
        
        if ($deleteQuery->execute()) {
            $message = '<div class="alert alert-success">Position ID ' . $positionID . ' has been deleted successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to delete position: ' . $conn->error . '</div>';
        }
        $deleteQuery->close();
        
        // Refresh the duplicates list
        header('Location: fix_duplicate_positions.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Duplicate Positions - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="mb-3"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Fix Duplicate Positions</h1>
                <p class="lead">
                    This tool identifies positions with duplicate titles within the same election and helps you clean them up.
                </p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>How it works:</strong> Positions with the same title in the same election are grouped together. 
                    You can delete duplicate positions that don't have any candidates assigned.
                </div>
                
                <?php echo $message; ?>
            </div>
        </div>
        
        <?php if (empty($duplicates)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Great!</strong> No duplicate positions found.
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    Found <?= count($duplicates) ?> duplicate position titles
                </div>
                <div class="card-body">
                    <p>Below are positions with duplicate titles. To fix them, you can delete positions that don't have candidates.</p>
                    
                    <?php foreach ($duplicates as $duplicate): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <span class="badge bg-primary me-2"><?= htmlspecialchars($duplicate['electionName']) ?></span>
                                    <?= htmlspecialchars($duplicate['title']) ?>
                                    <span class="badge bg-danger ms-2"><?= $duplicate['count'] ?> duplicates</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Position ID</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Max Votes</th>
                                                <th>Display Order</th>
                                                <th>Candidates</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($duplicate['positions'] as $position): ?>
                                                <tr>
                                                    <td><?= $position['positionID'] ?></td>
                                                    <td><?= htmlspecialchars($position['title']) ?></td>
                                                    <td><?= htmlspecialchars($position['description'] ?? '') ?></td>
                                                    <td><?= $position['maxVotes'] ?></td>
                                                    <td><?= $position['display_order'] ?? 'Not set' ?></td>
                                                    <td>
                                                        <?php if ($position['candidate_count'] > 0): ?>
                                                            <span class="badge bg-success"><?= $position['candidate_count'] ?> candidates</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">No candidates</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($position['candidate_count'] == 0): ?>
                                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this position?');">
                                                                <input type="hidden" name="position_id" value="<?= $position['positionID'] ?>">
                                                                <button type="submit" name="delete_position" class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-secondary" disabled title="Cannot delete positions with candidates">
                                                                <i class="bi bi-lock"></i> Delete
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="positions.php" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Positions
            </a>
        </div>
    </div>
    
    <?php include 'includes/admin_footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 