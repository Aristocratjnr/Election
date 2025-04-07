<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_SESSION['login_id'] ?? null;
    $electionID = $_POST['electionID'] ?? null;
    $candidateID = $_POST['candidateID'] ?? null;
    $categoryID = $_POST['categoryID'] ?? null;

    if (!$studentID || !$electionID || !$candidateID || !$categoryID) {
        die(json_encode(['success' => false, 'message' => 'Missing required parameters']));
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Check if user has already voted for this category in this election
        $checkStmt = $conn->prepare("SELECT id FROM votes WHERE studentID = ? AND electionID = ? AND categoryID = ?");
        $checkStmt->bind_param('iii', $studentID, $electionID, $categoryID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $conn->rollback();
            die(json_encode(['success' => false, 'message' => 'You have already voted for this category']));
        }

        // Record the vote
        $currentTime = date('Y-m-d H:i:s'); // Get current time with correct timezone
        $stmt = $conn->prepare("INSERT INTO votes (studentID, electionID, categoryID, candidateID, timestamp) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiis', $studentID, $electionID, $categoryID, $candidateID, $currentTime);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Vote recorded successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Vote recording error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error recording vote']);
    }

    $stmt->close();
    $conn->close();
} 