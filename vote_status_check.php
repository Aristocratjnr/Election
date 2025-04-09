<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Verify if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

// Database connection
require 'configs/dbconnection.php';

$studentID = (int)$_SESSION['login_id'];
$response = [
    'success' => true,
    'has_voted' => false,
    'vote_count' => 0,
    'election_name' => ''
];

try {
    // Get ongoing elections
    $stmt = $conn->prepare("
        SELECT * FROM elections 
        WHERE status = 'Ongoing'
        ORDER BY startDate DESC
        LIMIT 1
    ");
    $stmt->execute();
    $election = $stmt->get_result()->fetch_assoc();
    
    if ($election) {
        $electionID = $election['electionID'];
        $response['election_name'] = $election['name'];
        
        // Check if student has voted in this election
        $voteStmt = $conn->prepare("
            SELECT COUNT(*) as vote_count 
            FROM votes 
            WHERE studentID = ? AND electionID = ?
        ");
        $voteStmt->bind_param('ii', $studentID, $electionID);
        $voteStmt->execute();
        $voteResult = $voteStmt->get_result()->fetch_assoc();
        
        $response['has_voted'] = ($voteResult['vote_count'] > 0);
        $response['vote_count'] = (int)$voteResult['vote_count'];
    }
} catch (Exception $e) {
    error_log("Vote status check error: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Error checking vote status'
    ];
}

// Return the response
header('Content-Type: application/json');
echo json_encode($response); 