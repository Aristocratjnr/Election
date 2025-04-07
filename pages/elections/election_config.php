<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Fetch current configuration
$config = [];
try {
    $query = "SELECT * FROM election_config WHERE 1";
    $result = $conn->query($query);
    if ($result) {
        $config = $result->fetch_assoc();
    }
} catch (Exception $e) {
    $error = "Error fetching configuration: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Configuration - Election System</title>
    <!-- Include your CSS files -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Election Configuration</h1>
                    <button class="btn btn-primary" id="saveConfig">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
                
                <!-- Configuration form -->
                <div class="card">
                    <div class="card-body">
                        <form id="configForm">
                            <!-- Add your configuration options here -->
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Include your JavaScript files -->
</body>
</html> 