<?php
require __DIR__ . '/vendor/autoload.php';
session_start();
require 'configs/dbconnection.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';
$studentID = $data['studentID'] ?? 0;

try {
    // Verify password
    $stmt = $conn->prepare("SELECT password FROM students WHERE studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Student not found");
    }
    
    $student = $result->fetch_assoc();
    
    if (!password_verify($password, $student['password'])) {
        throw new Exception("Incorrect password");
    }
    
    // Fetch all student data
    $exportData = [];
    
    // 1. Basic profile info
    $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $exportData['profile'] = $stmt->get_result()->fetch_assoc();
    
    // 2. Voting history
    $stmt = $conn->prepare("SELECT * FROM votes WHERE studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $exportData['votes'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // 3. Election participation
    $stmt = $conn->prepare("SELECT * FROM election_participants WHERE studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $exportData['election_participation'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // 4. Group memberships
    $stmt = $conn->prepare("SELECT g.groupID, g.groupName FROM student_groups g 
                           JOIN student_group_members m ON g.groupID = m.groupID 
                           WHERE m.studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $exportData['group_memberships'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Return success with data
    echo json_encode([
        'success' => true,
        'message' => 'Data exported successfully',
        'data' => $exportData,
        'exported_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}