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
    header('Location: election.php?error=not_found');
    exit;
}

$election = $result->fetch_assoc();
$stmt->close();

// Check if time columns exist in the table
$time_fields_exist = false;
try {
    $check_fields = $conn->query("SHOW COLUMNS FROM elections LIKE 'start_time'");
    $time_fields_exist = ($check_fields->num_rows > 0);
} catch (Exception $e) {
    // If the query fails, assume time fields don't exist
    $time_fields_exist = false;
}

// Set default times or use values from database
$start_time = "08:00";
$end_time = "17:00";

if ($time_fields_exist && isset($election['start_time']) && isset($election['end_time'])) {
    $start_time = substr($election['start_time'], 0, 5); // Format: HH:MM
    $end_time = substr($election['end_time'], 0, 5);     // Format: HH:MM
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
        .time-inputs {
            margin-top: 10px;
        }
        .time-group {
            display: flex;
            flex-direction: column;
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1><i class="bi bi-pencil-square me-2"></i>Edit Election</h1>
        
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php
            $errors = [
                'invalid_id' => 'Invalid election ID',
                'not_found' => 'Election not found',
                'missing_fields' => 'Required fields missing',
                'invalid_dates' => 'End date must be after start date',
                'update_failed' => 'Failed to update election'
            ];
            echo $errors[$_GET['error']] ?? 'An error occurred';
            ?>
            <?php if (isset($_GET['message'])): ?>
            <br>Details: <?= htmlspecialchars($_GET['message']) ?>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Use the election.php handler instead of self-processing -->
        <form method="POST" action="election.php?action=edit&id=<?= $electionID ?>" id="electionForm" class="needs-validation" novalidate>
            <input type="hidden" name="form_source" value="edit_elections">
            <div class="mb-4">
                <label for="name" class="form-label">Election Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= htmlspecialchars($election['name']) ?>" required aria-required="true">
                <div class="invalid-feedback">Please provide an election name.</div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="startDate" class="form-label"><i class="bi bi-calendar-date me-1"></i>Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="startDate" 
                           value="<?= date('Y-m-d', strtotime($election['startDate'])) ?>" required aria-required="true">
                    <div class="invalid-feedback">Please select a start date.</div>
                    
                    <div class="time-inputs">
                        <label for="start_time" class="form-label"><i class="bi bi-clock me-1"></i>Start Time</label>
                        <div class="input-group">
                            <input type="time" class="form-control" id="start_time" name="start_time" 
                                value="<?= $start_time ?>" required aria-required="true">
                            <span class="input-group-text"><i class="bi bi-alarm"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="endDate" class="form-label"><i class="bi bi-calendar-check me-1"></i>End Date</label>
                    <input type="date" class="form-control" id="endDate" name="endDate" 
                           value="<?= date('Y-m-d', strtotime($election['endDate'])) ?>" required aria-required="true">
                    <div class="invalid-feedback">Please select an end date.</div>
                    
                    <div class="time-inputs">
                        <label for="end_time" class="form-label"><i class="bi bi-clock me-1"></i>End Time</label>
                        <div class="input-group">
                            <input type="time" class="form-control" id="end_time" name="end_time" 
                                value="<?= $end_time ?>" required aria-required="true">
                            <span class="input-group-text"><i class="bi bi-alarm"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required aria-required="true">
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
                <a href="election.php" class="btn btn-secondary" id="cancelButton">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="updateButton" name="updateButton">
                    <i class="bi bi-save me-1"></i> Update Election
                </button>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('electionForm');
        
        document.getElementById('cancelButton').addEventListener('click', function() {
            window.location.href = 'election.php';
        });
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Get date and time values
            const startDate = document.getElementById('startDate').value;
            const startTime = document.getElementById('start_time').value;
            const endDate = document.getElementById('endDate').value;
            const endTime = document.getElementById('end_time').value;
            
            // Create combined date-time objects for comparison
            const startDateTime = new Date(`${startDate}T${startTime}`);
            const endDateTime = new Date(`${endDate}T${endTime}`);
            
            // Validate end date-time is after start date-time
            if (endDateTime <= startDateTime) {
                alert('End date and time must be after start date and time');
                event.preventDefault();
                return false;
            }
            
            // Set hidden fields for form submission if needed
            // No hidden fields needed as we're sending the separate values
            
            // Log form data to console for debugging
            console.log('Form data:', {
                name: document.getElementById('name').value,
                startDate: startDate,
                startTime: startTime,
                endDate: endDate,
                endTime: endTime,
                status: document.getElementById('status').value,
                visibility: document.getElementById('visibility').value
            });
            
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html>