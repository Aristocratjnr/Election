<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Check if election ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: election.php?error=invalid_id');
    exit;
}

$electionID = (int)$_GET['id'];

// Fetch election data
$stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
$stmt->bind_param('i', $electionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: election.php?error=election_not_found');
    exit;
}

$election = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log POST data for debugging
    error_log('POST data: ' . print_r($_POST, true));
    
    // Validate inputs
    $required = ['name', 'startDate', 'endDate', 'status'];
    $missing_fields = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $error_msg = 'Missing fields: ' . implode(', ', $missing_fields);
        header('Location: edit_elections.php?id='.$electionID.'&error=missing_fields&message='.urlencode($error_msg));
        exit;
    }
    
    try {
        // Check date validity
        $startTimestamp = strtotime($_POST['startDate']);
        $endTimestamp = strtotime($_POST['endDate']);
        $now = time();
        
        // Format dates with time component
        $startDate = date('Y-m-d H:i:s', $startTimestamp);
        $endDate = date('Y-m-d H:i:s', $endTimestamp);
        
        // Debug log the times
        error_log("Updating election - Current time: " . date('Y-m-d H:i:s', $now));
        error_log("Start Date: $startDate");
        error_log("End Date: $endDate");
        
        // Validation for Ongoing status
        if ($_POST['status'] === 'Ongoing') {
            if ($startTimestamp > $now) {
                $error_msg = 'Start date/time must be in the past or now to set status to Ongoing.';
                header('Location: edit_elections.php?id='.$electionID.'&error=invalid_dates&message='.urlencode($error_msg));
                exit;
            }
            if ($endTimestamp <= $now) {
                $error_msg = 'End date/time must be in the future to set status to Ongoing.';
                header('Location: edit_elections.php?id='.$electionID.'&error=invalid_dates&message='.urlencode($error_msg));
                exit;
            }
        }
        
        if ($endDate < $startDate) {
            header('Location: edit_elections.php?id='.$electionID.'&error=invalid_dates');
            exit;
        }
        
        // Set default visibility if not provided
        $visibility = isset($_POST['visibility']) ? $_POST['visibility'] : 'Public';
        
        // Update election with precise datetime
        $stmt = $conn->prepare("UPDATE elections SET 
            name = ?, 
            startDate = ?, 
            endDate = ?, 
            status = ?, 
            visibility = ? 
            WHERE electionID = ?");
        
        $stmt->bind_param('sssssi', 
            $_POST['name'],
            $startDate,
            $endDate,
            $_POST['status'],
            $visibility,
            $electionID
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: election.php?success=election_updated');
            exit;
        } else {
            $error = "Database error: " . $stmt->error;
            error_log($error);
            $stmt->close();
            $conn->close();
            header('Location: edit_elections.php?id='.$electionID.'&error=update_failed&message='.urlencode($error));
            exit;
        }
    } catch (Exception $e) {
        $error = "Exception: " . $e->getMessage();
        error_log($error);
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
        header('Location: edit_elections.php?id='.$electionID.'&error=exception&message='.urlencode($error));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Election</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1><i class="bi bi-pencil-square me-2"></i>Edit Election</h1>
        
        <?php 
        // Display error messages
        if (isset($_GET['error'])): 
            $error_type = $_GET['error'];
            $error_msg = '';
            
            // Define error messages
            $errors = [
                'invalid_id' => 'Invalid election ID',
                'election_not_found' => 'Election not found',
                'missing_fields' => 'Required fields missing: ' . 
                    (isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Please fill all required fields'),
                'missing_name' => 'Election name is required',
                'missing_startDate' => 'Start date is required',
                'missing_endDate' => 'End date is required',
                'missing_status' => 'Status is required',
                'invalid_dates' => 'End date must be after start date',
                'update_failed' => 'Failed to update election: ' . 
                    (isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Unknown error'),
                'exception' => 'An error occurred: ' .
                    (isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Unknown error')
            ];
            
            // Get error message
            $error_msg = $errors[$error_type] ?? 'An error occurred';
        ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $error_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php 
            $success_messages = [
                'election_updated' => 'Election was updated successfully'
            ];
            echo $success_messages[$_GET['success']] ?? 'Operation completed successfully';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']).'?id='.$electionID ?>" id="electionForm" class="needs-validation">
            <div class="mb-4">
                <label for="name" class="form-label">Election Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= htmlspecialchars($election['name']) ?>" required>
                <div class="invalid-feedback">Please provide an election name.</div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control" id="startDate" name="startDate" 
                           value="<?= date('Y-m-d\TH:i', strtotime($election['startDate'])) ?>" required>
                    <div class="invalid-feedback">Please select a start date.</div>
                </div>
                <div class="col-md-6">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control" id="endDate" name="endDate" 
                           value="<?= date('Y-m-d\TH:i', strtotime($election['endDate'])) ?>" required>
                    <div class="invalid-feedback">Please select an end date.</div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Scheduled" <?= $election['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="Ongoing" <?= $election['status'] === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="Completed" <?= $election['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                    <div class="invalid-feedback">Please select a status.</div>
                </div>
                <div class="col-md-6">
                    <label for="visibility" class="form-label">Visibility</label>
                    <select class="form-select" id="visibility" name="visibility">
                        <option value="Public" <?= $election['visibility'] === 'Public' ? 'selected' : '' ?>>Public</option>
                        <option value="Private" <?= $election['visibility'] === 'Private' ? 'selected' : '' ?>>Private</option>
                    </select>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="elections.php" class="btn btn-secondary" id="cancelButton">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="updateButton">
                    <i class="bi bi-save me-1"></i> Update Election
                </button>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Enhanced form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('electionForm');
        
        // Direct link for cancel button (no preventDefault)
        document.getElementById('cancelButton').addEventListener('click', function() {
            window.location.href = 'elections.php';
        });
        
        // Form validation
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Custom date validation
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            
            if (endDate < startDate) {
                alert('End date must be after start date');
                event.preventDefault();
                return false;
            }
            
            form.classList.add('was-validated');
        });
        
        // Ensure all form controls are initialized properly
        document.querySelectorAll('.form-control, .form-select').forEach(function(element) {
            element.addEventListener('change', function() {
                this.classList.remove('is-invalid');
            });
        });
    });
    </script>
</body>
</html>