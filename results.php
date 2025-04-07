<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Initialize variables
$electionID = $_GET['election'] ?? null;
$electionDetails = null;
$resultsData = [];
$totalVotes = 0;

if ($electionID) {
    // Get election details
    $electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionDetails = $electionStmt->get_result()->fetch_assoc();

    // Get all positions for this election
    $positions = $conn->query("
        SELECT * FROM positions 
        WHERE electionID = $electionID
        ORDER BY positionID ASC
    ");

    // Get results grouped by position
    if ($positions->num_rows > 0) {
        while ($position = $positions->fetch_assoc()) {
            $positionID = $position['positionID'];
            
            // Get candidates and their results for this position
            $candidates = $conn->query("
                SELECT c.candidateID, c.studentID, c.position, s.name, s.profilePicture,
                       COALESCE(r.voteCount, 0) as voteCount,
                       COALESCE(r.percentage, 0) as percentage
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                LEFT JOIN results r ON c.candidateID = r.candidateID AND r.electionID = $electionID
                WHERE c.position = '{$position['title']}' AND c.status = 'Approved'
                ORDER BY voteCount DESC
            ");

            $positionResults = [
                'title' => $position['title'],
                'maxVotes' => $position['maxVotes'],
                'candidates' => [],
                'totalVotes' => 0
            ];

            while ($candidate = $candidates->fetch_assoc()) {
                $positionResults['candidates'][] = $candidate;
                $positionResults['totalVotes'] += $candidate['voteCount'];
                $totalVotes += $candidate['voteCount'];
            }

            // Calculate percentages if not stored
            foreach ($positionResults['candidates'] as &$candidate) {
                if ($positionResults['totalVotes'] > 0) {
                    $candidate['percentage'] = number_format(($candidate['voteCount'] / $positionResults['totalVotes']) * 100, 2);
                }
            }

            $resultsData[] = $positionResults;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
        }
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
        }
        .card {
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        .results-card {
            border-left: 4px solid #4e73df;
            margin-bottom: 2rem;
        }
        .candidate-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .progress {
            height: 25px;
            border-radius: 20px;
        }
        .progress-bar {
            background-color: #4e73df;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?>
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Election Results</h1>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Select Election</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <select class="form-select" name="election" onchange="this.form.submit()">
                                        <option value="">Select Election</option>
                                        <?php while ($election = $elections->fetch_assoc()): ?>
                                        <option value="<?php echo $election['electionID']; ?>" 
                                            <?php echo $electionID == $election['electionID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($election['name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Results Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card text-white bg-primary mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Votes Cast</h5>
                                            <h2 class="card-text"><?php echo number_format($totalVotes); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-white bg-success mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Election Status</h5>
                                            <h2 class="card-text"><?php echo $electionDetails['status']; ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-white bg-info mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Voting Period</h5>
                                            <p class="card-text">
                                                <?php echo date('M j, Y', strtotime($electionDetails['startDate'])); ?> - 
                                                <?php echo date('M j, Y', strtotime($electionDetails['endDate'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($resultsData)): ?>
                                <?php foreach ($resultsData as $position): ?>
                                <div class="card results-card mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">
                                            <?php echo htmlspecialchars($position['title']); ?>
                                            <small class="text-muted">(Max Votes: <?php echo $position['maxVotes']; ?>)</small>
                                        </h4>
                                        
                                        <div class="row g-4">
                                            <?php foreach ($position['candidates'] as $candidate): ?>
                                            <div class="col-md-6">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-4 text-center">
                                                                <?php if ($candidate['profilePicture']): ?>
                                                                <img src="assets/img/profile/students/<?php echo $candidate['profilePicture']; ?>" 
                                                                     class="candidate-photo" 
                                                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                                                <?php else: ?>
                                                                <div class="candidate-photo bg-light d-flex align-items-center justify-content-center">
                                                                    <i class="bi bi-person text-muted" style="font-size: 2.5rem;"></i>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-8">
                                                                <h5 class="mb-2"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-muted">Votes:</span>
                                                                    <strong><?php echo number_format($candidate['voteCount']); ?></strong>
                                                                </div>
                                                                <div class="progress">
                                                                    <div class="progress-bar" 
                                                                         role="progressbar" 
                                                                         style="width: <?php echo $candidate['percentage']; ?>%" 
                                                                         aria-valuenow="<?php echo $candidate['percentage']; ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100">
                                                                        <?php echo $candidate['percentage']; ?>%
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No results available for this election yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php elseif ($electionID): ?>
                    <div class="alert alert-warning">
                        Selected election not found.
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        Please select an election to view results.
                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>