<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

require 'configs/dbconnection.php';

// Check if user is admin or student
$isAdmin = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Initialize variables
$electionID = $_GET['election'] ?? null;
$electionDetails = null;
$voteData = [];
$totalVotes = 0;
$uniqueVoters = 0;

if ($electionID) {
    // Get election details
    $electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionDetails = $electionStmt->get_result()->fetch_assoc();

    // Get all votes for this election
    $votesQuery = "
        SELECT v.voteID, v.timestamp, 
               s.studentID, s.name as voterName, s.department as voterDepartment,
               c.candidateID, c.position,
               st.name as candidateName, st.profilePicture as candidatePhoto
        FROM votes v
        JOIN students s ON v.studentID = s.studentID
        JOIN candidates c ON v.candidateID = c.candidateID
        JOIN students st ON c.studentID = st.studentID
        WHERE v.electionID = ?
        ORDER BY v.timestamp DESC
    ";
    
    $votesStmt = $conn->prepare($votesQuery);
    $votesStmt->bind_param("i", $electionID);
    $votesStmt->execute();
    $votesResult = $votesStmt->get_result();
    
    // Group votes by candidate
    while ($vote = $votesResult->fetch_assoc()) {
        if (!isset($voteData[$vote['candidateID']])) {
            $voteData[$vote['candidateID']] = [
                'candidateID' => $vote['candidateID'],
                'candidateName' => $vote['candidateName'],
                'position' => $vote['position'],
                'photo' => $vote['candidatePhoto'],
                'votes' => [],
                'voteCount' => 0
            ];
        }
        $voteData[$vote['candidateID']]['votes'][] = $vote;
        $voteData[$vote['candidateID']]['voteCount']++;
        $totalVotes++;
    }

    // Get unique voters count
    $uniqueQuery = $conn->prepare("SELECT COUNT(DISTINCT studentID) as count FROM votes WHERE electionID = ?");
    $uniqueQuery->bind_param("i", $electionID);
    $uniqueQuery->execute();
    $uniqueResult = $uniqueQuery->get_result();
    $uniqueVoters = $uniqueResult->fetch_assoc()['count'];
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vote Records - SmartVote</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .card-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.15);
            color: #0d6efd;
        }
        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }
        .bg-info-light {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
        }
        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        .progress-thin {
            height: 5px;
        }
        .search-box {
            position: relative;
        }
        .search-box:before {
            content: "\F52A";
            font-family: bootstrap-icons;
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        .search-box input {
            padding-left: 30px;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .initials-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            font-weight: bold;
            color: #6c757d;
        }
        .vote-item {
            border-left: 3px solid #0d6efd;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            background-color: white;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .vote-item:hover {
            transform: translateX(5px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        .vote-count-badge {
            background-color: #0d6efd;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
        }
        .candidate-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?>
                <br>
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4"><br>
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><i class="bi bi-list-check"></i> Vote Records</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <?php if ($isAdmin && $electionID): ?>
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark-excel"></i> Export
                                </button>
                            </div>
                            <?php endif; ?>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="votes.php">All Elections</a></li>
                                    <?php 
                                    $elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");
                                    while ($election = $elections->fetch_assoc()): 
                                    ?>
                                    <li>
                                        <a class="dropdown-item" href="votes.php?election=<?php echo $election['electionID']; ?>">
                                            <?php echo htmlspecialchars($election['name']); ?>
                                        </a>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Select Election</label>
                                    <select class="form-select" name="election" onchange="this.form.submit()">
                                        <option value="">-- All Elections --</option>
                                        <?php 
                                        $elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");
                                        while ($election = $elections->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $election['electionID']; ?>" 
                                            <?php echo $electionID == $election['electionID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($election['name']); ?>
                                            (<?php echo date('M Y', strtotime($election['startDate'])); ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <?php if ($electionID): ?>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Generated On</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo date('F j, Y, g:i a'); ?>" readonly>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <!-- Total Votes Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"><i class="bi bi-check2-circle"></i> Total Votes</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-primary-light me-3">
                                            <i class="bi bi-check2-circle fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo number_format($totalVotes); ?></h2>
                                            <p class="text-muted mb-0">Votes Cast</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Unique Voters Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"><i class="bi bi-people"></i> Unique Voters</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-success-light me-3">
                                            <i class="bi bi-person-check fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo number_format($uniqueVoters); ?></h2>
                                            <p class="text-muted mb-0">Distinct Voters</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Candidates Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"><i class="bi bi-person-badge"></i> Candidates</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-info-light me-3">
                                            <i class="bi bi-person-video2 fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo count($voteData); ?></h2>
                                            <p class="text-muted mb-0">Active Candidates</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Participation Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"><i class="bi bi-graph-up"></i> Participation</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-warning-light me-3">
                                            <i class="bi bi-activity fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">
                                                <?php 
                                                $totalVoters = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'Active'")->fetch_assoc()['count'];
                                                $participation = $totalVoters > 0 ? round(($uniqueVoters / $totalVoters) * 100) : 0;
                                                echo $participation; ?>%
                                            </h2>
                                            <p class="text-muted mb-1">Voter Turnout</p>
                                            <div class="progress progress-thin">
                                                <div class="progress-bar bg-<?php echo ($participation > 50) ? 'success' : 'warning'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $participation; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Election Info Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-calendar-event"></i> 
                                    <?php echo htmlspecialchars($electionDetails['name']); ?>
                                </h5>
                                <span class="badge bg-<?php 
                                    echo $electionDetails['status'] == 'Ongoing' ? 'success' : 
                                         ($electionDetails['status'] == 'Completed' ? 'secondary' : 'info'); 
                                ?>">
                                    <?php echo $electionDetails['status']; ?>
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-calendar-date fs-1 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Election Period</h6>
                                            <p class="mb-0">
                                                <?php echo date('M j, Y', strtotime($electionDetails['startDate'])); ?> - 
                                                <?php echo date('M j, Y', strtotime($electionDetails['endDate'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-people fs-1 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Voting Activity</h6>
                                            <div class="progress progress-thin mb-1">
                                                <div class="progress-bar bg-<?php echo ($participation > 50) ? 'success' : 'warning'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $participation; ?>%">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $uniqueVoters; ?> of <?php echo $totalVoters; ?> voters
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($voteData)): ?>
                        <!-- Vote Breakdown -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bar-chart"></i> Vote Breakdown by Candidate
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($voteData as $candidate): ?>
                                <div class="mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <?php if ($candidate['photo']): ?>
                                        <img src="assets/img/profile/students/<?php echo $candidate['photo']; ?>" 
                                             class="candidate-photo me-3" 
                                             alt="<?php echo htmlspecialchars($candidate['candidateName']); ?>">
                                        <?php else: ?>
                                        <div class="candidate-photo bg-light d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-person-fill text-muted fs-3"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <h4 class="mb-1"><?php echo htmlspecialchars($candidate['candidateName']); ?></h4>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted me-2"><?php echo htmlspecialchars($candidate['position']); ?></span>
                                                <span class="vote-count-badge">
                                                    <i class="bi bi-check2-circle me-1"></i>
                                                    <?php echo number_format($candidate['voteCount']); ?> votes
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h5 class="d-flex align-items-center mb-3">
                                        <i class="bi bi-people-fill me-2"></i> Voters
                                    </h5>
                                    
                                    <div class="row">
                                        <?php foreach ($candidate['votes'] as $vote): ?>
                                        <div class="col-md-6">
                                            <div class="vote-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <?php 
                                                        // Get voter photo (would need to be implemented in your system)
                                                        $voterPhoto = 'default-user.jpg'; // Placeholder
                                                        ?>
                                                        <img src="assets/img/profile/students/<?php echo $voterPhoto; ?>" 
                                                             class="user-avatar me-3" 
                                                             alt="<?php echo htmlspecialchars($vote['voterName']); ?>">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($vote['voterName']); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($vote['voterDepartment']); ?> • 
                                                            <?php echo date('M j, Y g:i a', strtotime($vote['timestamp'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Votes Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-info-circle-fill fs-1 text-muted mb-3"></i>
                                <h4 class="mb-2">No votes recorded yet</h4>
                                <p class="text-muted">Voting records will appear here once votes are cast.</p>
                                <button class="btn btn-primary mt-3" onclick="location.reload()">
                                    <i class="bi bi-arrow-repeat me-2"></i> Refresh
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php elseif ($electionID): ?>
                    <!-- Election Not Found -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-exclamation-triangle-fill fs-1 text-warning mb-3"></i>
                            <h4 class="mb-2">Election Not Found</h4>
                            <p class="text-muted">The election you selected doesn't exist or may have been removed.</p>
                            <a href="votes.php" class="btn btn-outline-primary mt-3">
                                <i class="bi bi-arrow-left me-2"></i> Back to All Elections
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Select Election -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-info-circle-fill fs-1 text-primary mb-3"></i>
                            <h4 class="mb-2">Select an Election</h4>
                            <p class="text-muted">Choose an election from the dropdown to view voting records.</p>
                            <button class="btn btn-primary mt-3" onclick="document.querySelector('select[name=\'election\']').focus()">
                                <i class="bi bi-arrow-up-circle me-2"></i> Select Election
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality for voters
        const searchInput = document.getElementById('searchVoters');
        const voterItems = document.querySelectorAll('.vote-item');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                voterItems.forEach(item => {
                    const name = item.querySelector('h6').textContent.toLowerCase();
                    const isVisible = name.includes(searchTerm);
                    item.style.display = isVisible ? '' : 'none';
                });
            });
        }
    });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>