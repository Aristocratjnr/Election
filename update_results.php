<?php
// Update Results Tool for Admins
session_start();
require 'configs/dbconnection.php';

// Check admin authentication
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

// Include the calculate_vote_results function
require_once 'calculate_vote_results.php';

$message = '';
$error = '';
$electionID = $_GET['election'] ?? null;

// Automatically update the results for the selected election (if any)
if ($electionID) {
    $result = updateVoteResults($conn, $electionID);
    
    if ($result['success']) {
        $message = "Successfully updated results! Records updated: {$result['records_updated']}";
    } else {
        $error = "Error updating results: {$result['message']}";
    }
}

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Check if there are ongoing elections
$ongoing = $conn->query("SELECT COUNT(*) as count FROM elections WHERE status = 'Ongoing'")->fetch_assoc()['count'];

// Sanitize redirect URL
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'results.php';
$redirect = filter_var($redirect, FILTER_SANITIZE_URL);

// If redirect parameter exists, redirect after 2 seconds
if (isset($_GET['redirect'])) {
    header("refresh:2;url={$redirect}" . ($electionID ? "?election={$electionID}" : ""));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Results - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Update Election Results</h4>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i><?= $message ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">Select an Election to Update</h5>
                            <form method="GET" action="update_results.php">
                                <div class="input-group mb-3">
                                    <select name="election" class="form-select">
                                        <option value="">Select Election</option>
                                        <?php while ($election = $elections->fetch_assoc()): ?>
                                            <option value="<?= $election['electionID'] ?>" <?= $electionID == $election['electionID'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($election['name']) ?> (<?= $election['status'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Update Results
                                    </button>
                                </div>
                                
                                <input type="hidden" name="redirect" value="results.php">
                            </form>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle-fill me-2"></i>About This Tool</h6>
                            <p class="mb-0">This tool recalculates vote counts and updates the results table. Use it when:</p>
                            <ul class="mb-0 mt-2">
                                <li>Votes are not showing up in the results</li>
                                <li>Vote counts appear to be incorrect</li>
                                <li>Changes to the voting data need to be reflected in results</li>
                            </ul>
                        </div>
                        
                        <?php if ($ongoing > 0): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <strong>Note:</strong> There are currently <?= $ongoing ?> ongoing elections. Results for these elections will continue to change as more votes are cast.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-center">
                            <a href="results.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-1"></i> Back to Results
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 