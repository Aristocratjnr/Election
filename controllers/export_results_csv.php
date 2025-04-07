<?php
require_once '../includes/auth_check.php';
require_once '../configs/dbconnection.php';

// Get election ID
$electionID = $_GET['id'] ?? null;

if (!$electionID) {
    die('Election ID is required');
}

try {
    // Get election details
    $stmt = $conn->prepare("SELECT name FROM elections WHERE electionID = ?");
    $stmt->bind_param('i', $electionID);
    $stmt->execute();
    $election = $stmt->get_result()->fetch_assoc();

    // Get all positions for this election
    $stmt = $conn->prepare("
        SELECT p.positionID, p.position_name,
               c.candidateID, s.name as candidate_name,
               COUNT(v.id) as vote_count
        FROM positions p
        LEFT JOIN candidates c ON c.positionID = p.positionID
        LEFT JOIN students s ON c.studentID = s.studentID
        LEFT JOIN votes v ON v.candidateID = c.candidateID
        WHERE p.electionID = ?
        GROUP BY p.positionID, c.candidateID
        ORDER BY p.position_order, vote_count DESC
    ");
    $stmt->bind_param('i', $electionID);
    $stmt->execute();
    $results = $stmt->get_result();

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="election_results_' . date('Y-m-d') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write header row
    fputcsv($output, ['Election Results: ' . $election['name']]);
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []); // Empty row for spacing
    fputcsv($output, ['Position', 'Candidate', 'Votes', 'Percentage']);

    $current_position = '';
    $position_votes = 0;
    $position_candidates = [];

    while ($row = $results->fetch_assoc()) {
        if ($current_position != $row['position_name']) {
            // Write previous position's results
            if (!empty($position_candidates)) {
                foreach ($position_candidates as $candidate) {
                    $percentage = $position_votes > 0 ? 
                        round(($candidate['votes'] / $position_votes) * 100, 2) : 0;
                    fputcsv($output, [
                        $current_position,
                        $candidate['name'],
                        $candidate['votes'],
                        $percentage . '%'
                    ]);
                }
                fputcsv($output, []); // Empty row between positions
            }

            // Reset for new position
            $current_position = $row['position_name'];
            $position_votes = 0;
            $position_candidates = [];
        }

        $position_votes += $row['vote_count'];
        $position_candidates[] = [
            'name' => $row['candidate_name'],
            'votes' => $row['vote_count']
        ];
    }

    // Write last position's results
    if (!empty($position_candidates)) {
        foreach ($position_candidates as $candidate) {
            $percentage = $position_votes > 0 ? 
                round(($candidate['votes'] / $position_votes) * 100, 2) : 0;
            fputcsv($output, [
                $current_position,
                $candidate['name'],
                $candidate['votes'],
                $percentage . '%'
            ]);
        }
    }

    fclose($output);

} catch (Exception $e) {
    die('Error exporting results: ' . $e->getMessage());
} 