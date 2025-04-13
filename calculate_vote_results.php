<?php
/**
 * Calculate Vote Results Script
 * 
 * This script calculates vote results from the votes table and updates the results table.
 * It can be run manually or via cron job to ensure vote counts are always up to date.
 */

// Set proper error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'configs/dbconnection.php';

// Set correct timezone
date_default_timezone_set('Africa/Accra');

/**
 * Updates the results table with data from the votes table
 * 
 * @param mysqli $conn Database connection
 * @param int|null $electionID Optional election ID to update only specific election results
 * @return array Result information
 */
function updateVoteResults($conn, $electionID = null) {
    $result = [
        'success' => false,
        'message' => '',
        'elections_updated' => 0,
        'records_updated' => 0,
        'records_deleted' => 0,
        'errors' => []
    ];
    
    try {
        // Start transaction for data consistency
        $conn->begin_transaction();
        
        // Get elections to process
        if ($electionID) {
            $electionQuery = "SELECT electionID FROM elections WHERE electionID = ?";
            $stmt = $conn->prepare($electionQuery);
            $stmt->bind_param('i', $electionID);
        } else {
            $electionQuery = "SELECT electionID FROM elections WHERE status IN ('Ongoing', 'Completed')";
            $stmt = $conn->prepare($electionQuery);
        }
        
        $stmt->execute();
        $electionsResult = $stmt->get_result();
        $elections = [];
        
        while ($row = $electionsResult->fetch_assoc()) {
            $elections[] = $row['electionID'];
        }
        $stmt->close();
        
        if (empty($elections)) {
            $conn->rollback();
            $result['message'] = "No elections found to update.";
            return $result;
        }
        
        // Process each election
        foreach ($elections as $currentElectionID) {
            // First, delete existing results for this election
            $deleteStmt = $conn->prepare("DELETE FROM results WHERE electionID = ?");
            $deleteStmt->bind_param('i', $currentElectionID);
            $deleteStmt->execute();
            $result['records_deleted'] += $deleteStmt->affected_rows;
            $deleteStmt->close();
            
            // Get all candidates for this election
            $candidatesQuery = "
                SELECT c.candidateID, c.positionID, p.electionID
                FROM candidates c
                JOIN positions p ON c.positionID = p.positionID
                WHERE p.electionID = ? AND c.status = 'Approved'
            ";
            
            $candidatesStmt = $conn->prepare($candidatesQuery);
            $candidatesStmt->bind_param('i', $currentElectionID);
            $candidatesStmt->execute();
            $candidatesResult = $candidatesStmt->get_result();
            
            // Insert new result records for each candidate
            $insertStmt = $conn->prepare("
                INSERT INTO results (electionID, candidateID, voteCount, percentage)
                VALUES (?, ?, ?, ?)
            ");
            
            // Get positions for this election and their total votes
            $positionVotes = [];
            $positionQuery = "
                SELECT p.positionID, COUNT(v.voteID) as totalVotes
                FROM positions p
                LEFT JOIN candidates c ON p.positionID = c.positionID
                LEFT JOIN votes v ON c.candidateID = v.candidateID AND v.electionID = p.electionID
                WHERE p.electionID = ?
                GROUP BY p.positionID
            ";
            
            $positionStmt = $conn->prepare($positionQuery);
            $positionStmt->bind_param('i', $currentElectionID);
            $positionStmt->execute();
            $positionsResult = $positionStmt->get_result();
            
            while ($position = $positionsResult->fetch_assoc()) {
                $positionVotes[$position['positionID']] = $position['totalVotes'];
            }
            $positionStmt->close();
            
            // Process each candidate
            while ($candidate = $candidatesResult->fetch_assoc()) {
                $candidateID = $candidate['candidateID'];
                $positionID = $candidate['positionID'];
                
                // Count votes for this candidate
                $voteQuery = "
                    SELECT COUNT(*) as voteCount
                    FROM votes
                    WHERE candidateID = ? AND electionID = ?
                ";
                
                $voteStmt = $conn->prepare($voteQuery);
                $voteStmt->bind_param('ii', $candidateID, $currentElectionID);
                $voteStmt->execute();
                $voteCount = $voteStmt->get_result()->fetch_assoc()['voteCount'];
                $voteStmt->close();
                
                // Debug output
                error_log("Counted {$voteCount} votes for candidate {$candidateID} in election {$currentElectionID}");
                
                // Calculate percentage
                $totalPositionVotes = $positionVotes[$positionID] ?? 0;
                $percentage = ($totalPositionVotes > 0) ? ($voteCount / $totalPositionVotes) * 100 : 0;
                
                // Insert into results table
                $insertStmt->bind_param('iidd', $currentElectionID, $candidateID, $voteCount, $percentage);
                $insertStmt->execute();
                $result['records_updated']++;
            }
            
            $candidatesStmt->close();
            $insertStmt->close();
            $result['elections_updated']++;
        }
        
        // Commit transaction
        $conn->commit();
        
        $result['success'] = true;
        $result['message'] = "Successfully updated results for {$result['elections_updated']} election(s).";
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $result['success'] = false;
        $result['errors'][] = $e->getMessage();
        $result['message'] = "Error updating results: " . $e->getMessage();
        
        // Log the error
        error_log("Vote results update error: " . $e->getMessage());
    }
    
    return $result;
}

// Process command-line arguments for CLI usage
if (php_sapi_name() === 'cli') {
    $electionID = null;
    
    if (isset($argv[1]) && is_numeric($argv[1])) {
        $electionID = (int)$argv[1];
    }
    
    $result = updateVoteResults($conn, $electionID);
    
    echo $result['message'] . "\n";
    
    if (!$result['success']) {
        echo "Errors encountered:\n";
        foreach ($result['errors'] as $error) {
            echo "- $error\n";
        }
    }
    
    echo "Statistics:\n";
    echo "- Elections updated: {$result['elections_updated']}\n";
    echo "- Records updated: {$result['records_updated']}\n";
    echo "- Old records deleted: {$result['records_deleted']}\n";
    
    exit($result['success'] ? 0 : 1);
}

// Process web request
if (isset($_GET['run']) || isset($_POST['run'])) {
    // Optional election ID filter
    $electionID = null;
    
    if (isset($_GET['election']) && is_numeric($_GET['election'])) {
        $electionID = (int)$_GET['election'];
    } elseif (isset($_POST['election']) && is_numeric($_POST['election'])) {
        $electionID = (int)$_POST['election'];
    }
    
    // Check for admin authorization if not run from command line
    session_start();
    $isAdmin = isset($_SESSION['login_id']) && $_SESSION['role'] === 'admin';
    
    // Only admins can run this manually
    if (!$isAdmin) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Only administrators can run this operation.'
        ]);
        exit;
    }
    
    // Run the update operation
    $result = updateVoteResults($conn, $electionID);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Handle AJAX requests for automatic updates
if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
    // Get election ID
    $electionID = null;
    
    if (isset($_GET['election']) && is_numeric($_GET['election'])) {
        $electionID = (int)$_GET['election'];
    } elseif (isset($_POST['election']) && is_numeric($_POST['election'])) {
        $electionID = (int)$_POST['election'];
    }
    
    if (!$electionID) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Election ID is required'
        ]);
        exit;
    }
    
    // Run update operation
    $result = updateVoteResults($conn, $electionID);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}