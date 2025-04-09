<?php
/**
 * Filter votes by election time period
 * 
 * This file provides functions to filter votes based on the election's start and end dates
 * allowing only votes cast within the valid time period to be counted
 */

/**
 * Get all valid votes for an election (votes cast between start and end time)
 * 
 * @param mysqli $conn Database connection
 * @param int $electionID Election ID
 * @return array|false Array of vote records or false on error
 */
function getValidVotes($conn, $electionID) {
    try {
        // Validate input
        $electionID = (int)$electionID;
        
        // Prepare the statement
        $stmt = $conn->prepare("
            SELECT v.* 
            FROM votes v
            INNER JOIN elections e ON v.election_id = e.electionID
            WHERE v.election_id = ?
              AND v.timestamp >= e.startDate
              AND v.timestamp <= e.endDate
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('i', $electionID);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $votes = [];
        
        while ($row = $result->fetch_assoc()) {
            $votes[] = $row;
        }
        
        $stmt->close();
        return $votes;
    } catch (Exception $e) {
        error_log("Error getting valid votes: " . $e->getMessage());
        return false;
    }
}

/**
 * Count votes for a candidate in an election (only valid time period votes)
 * 
 * @param mysqli $conn Database connection
 * @param int $electionID Election ID
 * @param int $candidateID Candidate ID
 * @return int|false Number of votes or false on error
 */
function countValidVotesForCandidate($conn, $electionID, $candidateID) {
    try {
        // Validate input
        $electionID = (int)$electionID;
        $candidateID = (int)$candidateID;
        
        // Prepare the statement
        $stmt = $conn->prepare("
            SELECT COUNT(*) as vote_count 
            FROM votes v
            INNER JOIN elections e ON v.election_id = e.electionID
            WHERE v.election_id = ?
              AND v.candidate_id = ?
              AND v.timestamp >= e.startDate
              AND v.timestamp <= e.endDate
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('ii', $electionID, $candidateID);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $voteCount = $row['vote_count'];
        
        $stmt->close();
        return $voteCount;
    } catch (Exception $e) {
        error_log("Error counting valid votes: " . $e->getMessage());
        return false;
    }
}

/**
 * Get election results with only valid votes (within time period)
 * 
 * @param mysqli $conn Database connection
 * @param int $electionID Election ID
 * @return array|false Array of results or false on error
 */
function getElectionResultsWithValidVotes($conn, $electionID) {
    try {
        // Validate input
        $electionID = (int)$electionID;
        
        // Prepare the statement
        $stmt = $conn->prepare("
            SELECT c.candidateID, c.name, c.position, 
                   COUNT(v.id) as vote_count
            FROM candidates c
            LEFT JOIN votes v ON c.candidateID = v.candidate_id 
                AND v.election_id = ?
                AND v.timestamp >= (SELECT startDate FROM elections WHERE electionID = ?)
                AND v.timestamp <= (SELECT endDate FROM elections WHERE electionID = ?)
            WHERE c.election_id = ?
            GROUP BY c.candidateID, c.name, c.position
            ORDER BY c.position, vote_count DESC
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('iiii', $electionID, $electionID, $electionID, $electionID);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $results = [];
        
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        
        $stmt->close();
        return $results;
    } catch (Exception $e) {
        error_log("Error getting election results: " . $e->getMessage());
        return false;
    }
}

/**
 * Count the total number of valid votes (within time period) for an election
 * 
 * @param mysqli $conn Database connection
 * @param int $electionID Election ID
 * @return int|false Number of votes or false on error
 */
function countTotalValidVotes($conn, $electionID) {
    try {
        // Validate input
        $electionID = (int)$electionID;
        
        // Prepare the statement
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total_votes 
            FROM votes v
            INNER JOIN elections e ON v.election_id = e.electionID
            WHERE v.election_id = ?
              AND v.timestamp >= e.startDate
              AND v.timestamp <= e.endDate
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('i', $electionID);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $totalVotes = $row['total_votes'];
        
        $stmt->close();
        return $totalVotes;
    } catch (Exception $e) {
        error_log("Error counting total valid votes: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a vote was cast within valid election time period
 * 
 * @param mysqli $conn Database connection
 * @param int $voteID Vote ID
 * @return bool|null True if valid, False if invalid, null on error
 */
function isVoteWithinValidTimePeriod($conn, $voteID) {
    try {
        // Validate input
        $voteID = (int)$voteID;
        
        // Prepare the statement
        $stmt = $conn->prepare("
            SELECT 
                CASE 
                    WHEN v.timestamp >= e.startDate AND v.timestamp <= e.endDate THEN 1
                    ELSE 0
                END as is_valid
            FROM votes v
            INNER JOIN elections e ON v.election_id = e.electionID
            WHERE v.id = ?
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param('i', $voteID);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null; // Vote not found
        }
        
        $row = $result->fetch_assoc();
        $isValid = (bool)$row['is_valid'];
        
        $stmt->close();
        return $isValid;
    } catch (Exception $e) {
        error_log("Error checking vote validity: " . $e->getMessage());
        return null;
    }
}
?> 