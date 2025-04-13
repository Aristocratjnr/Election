<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_SESSION['login_id'] ?? null;
    $electionID = $_POST['electionID'] ?? null;
    $candidateID = $_POST['candidateID'] ?? null;

    if (!$studentID || !$electionID || !$candidateID) {
        die(json_encode(['success' => false, 'message' => 'Missing required parameters']));
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Check if user has already voted for this election
        $checkStmt = $conn->prepare("SELECT voteID FROM votes WHERE studentID = ? AND electionID = ?");
        $checkStmt->bind_param('ii', $studentID, $electionID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $conn->rollback();
            die(json_encode(['success' => false, 'message' => 'You have already voted in this election']));
        }

        // Record the vote
        $currentTime = date('Y-m-d H:i:s'); // Get current time with correct timezone
        $stmt = $conn->prepare("INSERT INTO votes (studentID, electionID, candidateID, timestamp) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiis', $studentID, $electionID, $candidateID, $currentTime);
        $stmt->execute();

        // Get election details for notification
        $electionStmt = $conn->prepare("SELECT name FROM elections WHERE electionID = ?");
        $electionStmt->bind_param("i", $electionID);
        $electionStmt->execute();
        $electionResult = $electionStmt->get_result();
        $election = $electionResult->fetch_assoc();

        // Create voter notification
        $voterNotifyStmt = $conn->prepare("
            INSERT INTO notifications (user_id, user_type, title, message, type, related_election, is_read, created_at)
            VALUES (?, ?, ?, ?, 'vote', ?, 0, NOW())
        ");
        
        $title = "Vote Recorded";
        $message = "Your vote for the election '{$election['name']}' has been successfully recorded.";
        $userRole = $_SESSION['role'] ?? 'student';
        
        $voterNotifyStmt->bind_param(
            "isssi",
            $studentID,
            $userRole,
            $title,
            $message,
            $electionID
        );
        
        $voterNotifyStmt->execute();

        // Create notification for admin
        $adminNotifyStmt = $conn->prepare("
            INSERT INTO notifications (user_id, user_type, title, message, type, related_election, is_read, created_at)
            SELECT studentID, role, ?, ?, 'vote', ?, 0, NOW()
            FROM students 
            WHERE role = 'admin'
        ");
        
        $adminTitle = "New Vote Cast";
        $adminMessage = "A new vote has been cast in the election '{$election['name']}'";
        
        $adminNotifyStmt->bind_param(
            "ssi",
            $adminTitle,
            $adminMessage,
            $electionID
        );
        
        $adminNotifyStmt->execute();

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
?>