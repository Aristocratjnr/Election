<?php
require_once '../configs/dbconnection.php';
require_once '../configs/session.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $query = "SELECT 
                c.*,
                e.name as election_name,
                e.status as election_status,
                e.start_date,
                e.end_date,
                u.name as created_by_name,
                (SELECT COUNT(*) FROM candidates WHERE categoryID = c.categoryID) as candidate_count
              FROM categories c 
              LEFT JOIN elections e ON c.electionID = e.electionID
              LEFT JOIN users u ON c.created_by = u.userID";
    
    // If election ID is provided, filter by it
    if (isset($_GET['electionID']) && !empty($_GET['electionID'])) {
        $electionID = $_GET['electionID'];
        $query .= " WHERE c.electionID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $electionID);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format dates
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        if ($row['start_date']) $row['start_date'] = date('Y-m-d', strtotime($row['start_date']));
        if ($row['end_date']) $row['end_date'] = date('Y-m-d', strtotime($row['end_date']));
        
        // Add editable status based on election status
        $row['is_editable'] = $row['election_status'] !== 'Completed';
        
        $categories[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => count($categories)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_categories.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch categories: ' . $e->getMessage()
    ]);
}