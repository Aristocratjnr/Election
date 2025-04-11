<?php
require_once '../configs/dbconnection.php';
header('Content-Type: application/json');

try {
    // Check if filtering by election ID
    $query = "SELECT 
                c.categoryID, 
                c.electionID, 
                c.name as category_name,
                c.description, 
                e.name as election_name,
                e.status as election_status,
                e.startDate,
                e.endDate,
                c.addedBy,
                a.name as added_by_name,
                c.updatedBy,
                ua.name as updated_by_name 
              FROM categories c
              LEFT JOIN elections e ON c.electionID = e.electionID
              LEFT JOIN admins a ON c.addedBy = a.adminID
              LEFT JOIN admins ua ON c.updatedBy = ua.adminID";
    
    $params = [];
    $types = "";
    
    // Add filtering if election ID is provided
    if (isset($_GET['electionID']) && !empty($_GET['electionID'])) {
        $query .= " WHERE c.electionID = ?";
        $params[] = $_GET['electionID'];
        $types .= "i";
    }
    
    // Add ordering
    $query .= " ORDER BY e.status = 'Ongoing' DESC, e.startDate DESC, c.name ASC";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all categories
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        // Add created/updated dates if they exist
        if (isset($row['createdAt'])) {
            $row['created_date'] = $row['createdAt'];
        } elseif (isset($row['created_at'])) {
            $row['created_date'] = $row['created_at'];
        } else {
            $row['created_date'] = null;
        }
        
        if (isset($row['updatedAt'])) {
            $row['updated_date'] = $row['updatedAt'];
        } elseif (isset($row['updated_at'])) {
            $row['updated_date'] = $row['updated_at'];
        } else {
            $row['updated_date'] = null;
        }
        
        $categories[] = $row;
    }
    
    echo json_encode($categories);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve categories: ' . $e->getMessage()
    ]);
} 