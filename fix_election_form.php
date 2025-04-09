<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

$electionID = $_GET['id'] ?? null;
$action = 'edit';

// Style for the page
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Election Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 800px; margin-top: 2rem; }
        .debug-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="container">
    <h1>Fix Election Form Issues</h1>';

// Verify database connection
echo '<div class="card mb-4">
    <div class="card-header">Database Connection Status</div>
    <div class="card-body">';

if ($conn->ping()) {
    echo '<div class="alert alert-success">Database connection is active</div>';
} else {
    echo '<div class="alert alert-danger">Database connection failed</div>';
    // Try to reconnect
    echo 'Attempting to reconnect...';
    require_once 'configs/dbconnection.php';
    
    if ($conn->ping()) {
        echo '<div class="alert alert-success">Database reconnection successful</div>';
    } else {
        echo '<div class="alert alert-danger">Database reconnection failed</div>';
    }
}
echo '</div></div>';

// Check election ID
if (!$electionID) {
    echo '<div class="alert alert-warning">No election ID provided. <a href="election.php">Go back to elections list</a></div>';
    exit;
}

// Fetch election data
echo '<div class="card mb-4">
    <div class="card-header">Election Data</div>
    <div class="card-body">';

try {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $electionID);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $election = $result->fetch_assoc();
        echo '<div class="alert alert-success">Election found with ID: ' . $electionID . '</div>';
        echo '<pre>' . print_r($election, true) . '</pre>';
    } else {
        echo '<div class="alert alert-danger">Election not found with ID: ' . $electionID . '</div>';
        exit;
    }
    $stmt->close();
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    exit;
}
echo '</div></div>';

// Manual fix form
echo '<div class="card mb-4">
    <div class="card-header">Fix Election Form</div>
    <div class="card-body">
    <form method="POST" action="election.php?action=edit&id=' . $electionID . '" id="fixElectionForm" class="needs-validation" novalidate>
        <input type="hidden" name="form_source" value="fix_form">
        <div class="mb-3">
            <label for="fixName" class="form-label">Election Name</label>
            <input type="text" class="form-control" id="fixName" name="name" 
                   value="' . htmlspecialchars($election['name']) . '" required aria-required="true">
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fixStartDate" class="form-label">Start Date</label>
                <input type="datetime-local" class="form-control" id="fixStartDate" name="startDate" 
                       value="' . date('Y-m-d\TH:i', strtotime($election['startDate'])) . '" required aria-required="true">
            </div>
            <div class="col-md-6">
                <label for="fixEndDate" class="form-label">End Date</label>
                <input type="datetime-local" class="form-control" id="fixEndDate" name="endDate" 
                       value="' . date('Y-m-d\TH:i', strtotime($election['endDate'])) . '" required aria-required="true">
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fixStatus" class="form-label">Status</label>
                <select class="form-select" id="fixStatus" name="status" required aria-required="true">';
                $statusOptions = ['Scheduled', 'Ongoing', 'Completed'];
                foreach ($statusOptions as $option) {
                    $selected = ($election['status'] === $option) ? 'selected' : '';
                    echo "<option value=\"$option\" $selected>$option</option>";
                }
echo '          </select>
            </div>
            <div class="col-md-6">
                <label for="fixVisibility" class="form-label">Visibility</label>
                <select class="form-select" id="fixVisibility" name="visibility">';
                $visibilityOptions = ['Public', 'Private'];
                foreach ($visibilityOptions as $option) {
                    $selected = ($election['visibility'] === $option) ? 'selected' : '';
                    echo "<option value=\"$option\" $selected>$option</option>";
                }
echo '          </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" id="submitUpdateButton" name="submitUpdateButton">Update Election</button>
        <a href="election.php" class="btn btn-secondary" id="cancelFixButton" name="cancelFixButton">Cancel</a>
    </form>
    </div>
</div>';

// Direct SQL fix option
echo '<div class="card mb-4">
    <div class="card-header">Direct SQL Fix</div>
    <div class="card-body">';

if (isset($_GET['direct_fix'])) {
    try {
        // Escape name to prevent SQL injection
        $name = $conn->real_escape_string($election['name']);
        $startDate = $election['startDate'];
        $endDate = $election['endDate'];
        $status = $conn->real_escape_string($election['status']);
        $visibility = $conn->real_escape_string($election['visibility'] ?? 'Public');
        
        // Direct SQL update
        $sql = "UPDATE elections SET 
            name = '$name', 
            startDate = '$startDate', 
            endDate = '$endDate', 
            status = '$status', 
            visibility = '$visibility' 
            WHERE electionID = $electionID";
        
        echo '<p>Executing SQL: <code>' . htmlspecialchars($sql) . '</code></p>';
        
        if ($conn->query($sql)) {
            echo '<div class="alert alert-success">Direct SQL update successful</div>';
        } else {
            echo '<div class="alert alert-danger">Direct SQL update failed: ' . $conn->error . '</div>';
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

echo '<p><a href="?id=' . $electionID . '&direct_fix=1" class="btn btn-warning">Run Direct SQL Fix</a></p>';
echo '</div></div>';

echo '</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?> 