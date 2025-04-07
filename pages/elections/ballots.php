<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Fetch active elections for ballot design
$elections = [];
try {
    $query = "SELECT * FROM elections WHERE status != 'Completed' ORDER BY startDate DESC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $elections[] = $row;
        }
    }
} catch (Exception $e) {
    $error = "Error fetching elections: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot Design - Election System</title>
    <!-- Include your CSS files -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ballot Design</h1>
                    <select class="form-select w-auto" id="electionSelect">
                        <option value="">Select Election</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?= $election['electionID'] ?>">
                                <?= htmlspecialchars($election['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Ballot preview area -->
                <div id="ballotPreview" class="card">
                    <div class="card-body">
                        <!-- Ballot content will be loaded here -->
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Include your JavaScript files -->
</body>
</html> 