<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Fetch elections with results
$elections = [];
try {
    $query = "SELECT e.*, 
              (SELECT COUNT(DISTINCT v.studentID) FROM votes v WHERE v.electionID = e.electionID) as total_votes,
              (SELECT COUNT(*) FROM students WHERE status = 'Active') as total_voters
              FROM elections e 
              ORDER BY startDate DESC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $elections[] = $row;
        }
    }
} catch (Exception $e) {
    $error = "Error fetching results: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Election System</title>
    <!-- Include your CSS files -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Election Results</h1>
                    <div class="btn-toolbar">
                        <button class="btn btn-outline-secondary me-2" id="printResults">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-download"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="../../controllers/export_results_csv.php?id=<?= $election['electionID'] ?>">
                                    <i class="bi bi-file-earmark-excel"></i> Export to CSV
                                </a></li>
                                <li><a class="dropdown-item" href="../../controllers/export_results_pdf.php?id=<?= $election['electionID'] ?>">
                                    <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Results display -->
                <?php foreach ($elections as $election): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= htmlspecialchars($election['name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Results content will be loaded here -->
                    </div>
                </div>
                <?php endforeach; ?>
            </main>
        </div>
    </div>

    <!-- Include your JavaScript files -->
</body>
</html> 