<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

require 'configs/dbconnection.php';

// Check if user is admin or student
$isAdmin = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');

if (!$isAdmin) {
    header('Location: voters.php');
    exit();
}

// Get parameters
$electionID = $_POST['election_id'] ?? null;
$exportFormat = $_POST['exportFormat'] ?? 'csv';
$exportStats = isset($_POST['exportStats']) ? true : false;
$exportVoters = isset($_POST['exportVoters']) ? true : false;
$exportBreakdown = isset($_POST['exportBreakdown']) ? true : false;

if (!$electionID) {
    header('Location: voters.php');
    exit();
}

// Get election details
$electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
$electionStmt->bind_param("i", $electionID);
$electionStmt->execute();
$electionDetails = $electionStmt->get_result()->fetch_assoc();

if (!$electionDetails) {
    header('Location: voters.php');
    exit();
}

// Get vote data
$voteData = [];
$totalVotes = 0;
$uniqueVoters = 0;

// Get all votes for this election - FIXED QUERY
$votesQuery = "
    SELECT v.voteID, v.timestamp, 
           s.studentID, s.name as voterName, s.department as voterDepartment,
           c.candidateID, p.title as position,
           st.name as candidateName, st.profilePicture as candidatePhoto
    FROM votes v
    JOIN students s ON v.studentID = s.studentID
    JOIN candidates c ON v.candidateID = c.candidateID
    JOIN positions p ON c.positionID = p.positionID
    JOIN students st ON c.studentID = st.studentID
    WHERE v.electionID = ?
    ORDER BY v.timestamp DESC
";

$votesStmt = $conn->prepare($votesQuery);
$votesStmt->bind_param("i", $electionID);
$votesStmt->execute();
$votesResult = $votesStmt->get_result();

// Group votes by candidate
while ($vote = $votesResult->fetch_assoc()) {
    if (!isset($voteData[$vote['candidateID']])) {
        $voteData[$vote['candidateID']] = [
            'candidateID' => $vote['candidateID'],
            'candidateName' => $vote['candidateName'],
            'position' => $vote['position'],
            'photo' => $vote['candidatePhoto'],
            'votes' => [],
            'voteCount' => 0
        ];
    }
    $voteData[$vote['candidateID']]['votes'][] = $vote;
    $voteData[$vote['candidateID']]['voteCount']++;
    $totalVotes++;
}

// Get unique voters count
$uniqueQuery = $conn->prepare("SELECT COUNT(DISTINCT studentID) as count FROM votes WHERE electionID = ?");
$uniqueQuery->bind_param("i", $electionID);
$uniqueQuery->execute();
$uniqueResult = $uniqueQuery->get_result();
$uniqueVoters = $uniqueResult->fetch_assoc()['count'];

// Get total voters
$totalVotersQuery = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'Active'");
$totalVoters = $totalVotersQuery->fetch_assoc()['count'];
$participation = $totalVoters > 0 ? round(($uniqueVoters / $totalVoters) * 100) : 0;

// Set headers based on format
$filename = "vote_records_" . $electionDetails['name'] . "_" . date('Y-m-d');

switch ($exportFormat) {
    case 'csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        $output = fopen('php://output', 'w');
        
        // Write CSV headers
        fputcsv($output, ['Election Vote Records']);
        fputcsv($output, ['Election', $electionDetails['name']]);
        fputcsv($output, ['Date', date('F j, Y, g:i a')]);
        fputcsv($output, []);
        
        if ($exportStats) {
            fputcsv($output, ['Election Statistics']);
            fputcsv($output, ['Total Votes', $totalVotes]);
            fputcsv($output, ['Unique Voters', $uniqueVoters]);
            fputcsv($output, ['Candidates', count($voteData)]);
            fputcsv($output, ['Participation Rate', $participation . '%']);
            fputcsv($output, []);
        }
        
        if ($exportBreakdown) {
            fputcsv($output, ['Vote Breakdown by Candidate']);
            fputcsv($output, ['Candidate', 'Position', 'Votes']);
            
            foreach ($voteData as $candidate) {
                fputcsv($output, [
                    $candidate['candidateName'],
                    $candidate['position'],
                    $candidate['voteCount']
                ]);
            }
            
            fputcsv($output, []);
        }
        
        if ($exportVoters) {
            fputcsv($output, ['Voters List']);
            fputcsv($output, ['Voter Name', 'Department', 'Candidate', 'Position', 'Timestamp']);
            
            foreach ($voteData as $candidate) {
                foreach ($candidate['votes'] as $vote) {
                    fputcsv($output, [
                        $vote['voterName'],
                        $vote['voterDepartment'],
                        $candidate['candidateName'],
                        $candidate['position'],
                        date('M j, Y g:i a', strtotime($vote['timestamp']))
                    ]);
                }
            }
        }
        
        fclose($output);
        break;
        
    case 'excel':
        // For Excel, we'll use CSV format with .xls extension
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Write Excel headers
        fputcsv($output, ['Election Vote Records']);
        fputcsv($output, ['Election', $electionDetails['name']]);
        fputcsv($output, ['Date', date('F j, Y, g:i a')]);
        fputcsv($output, []);
        
        if ($exportStats) {
            fputcsv($output, ['Election Statistics']);
            fputcsv($output, ['Total Votes', $totalVotes]);
            fputcsv($output, ['Unique Voters', $uniqueVoters]);
            fputcsv($output, ['Candidates', count($voteData)]);
            fputcsv($output, ['Participation Rate', $participation . '%']);
            fputcsv($output, []);
        }
        
        if ($exportBreakdown) {
            fputcsv($output, ['Vote Breakdown by Candidate']);
            fputcsv($output, ['Candidate', 'Position', 'Votes']);
            
            foreach ($voteData as $candidate) {
                fputcsv($output, [
                    $candidate['candidateName'],
                    $candidate['position'],
                    $candidate['voteCount']
                ]);
            }
            
            fputcsv($output, []);
        }
        
        if ($exportVoters) {
            fputcsv($output, ['Voters List']);
            fputcsv($output, ['Voter Name', 'Department', 'Candidate', 'Position', 'Timestamp']);
            
            foreach ($voteData as $candidate) {
                foreach ($candidate['votes'] as $vote) {
                    fputcsv($output, [
                        $vote['voterName'],
                        $vote['voterDepartment'],
                        $candidate['candidateName'],
                        $candidate['position'],
                        date('M j, Y g:i a', strtotime($vote['timestamp']))
                    ]);
                }
            }
        }
        
        fclose($output);
        break;
        
    case 'pdf':
        // For PDF, we'll use TCPDF library
        require_once('tcpdf/tcpdf.php');
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SmartVote System');
        $pdf->SetTitle('Vote Records - ' . $electionDetails['name']);
        
        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Vote Records', $electionDetails['name']);
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Add content
        $html = '<h1>Vote Records</h1>';
        $html .= '<p><strong>Election:</strong> ' . $electionDetails['name'] . '</p>';
        $html .= '<p><strong>Date:</strong> ' . date('F j, Y, g:i a') . '</p>';
        
        if ($exportStats) {
            $html .= '<h2>Election Statistics</h2>';
            $html .= '<table border="1" cellpadding="5">
                        <tr>
                            <th>Total Votes</th>
                            <td>' . $totalVotes . '</td>
                        </tr>
                        <tr>
                            <th>Unique Voters</th>
                            <td>' . $uniqueVoters . '</td>
                        </tr>
                        <tr>
                            <th>Candidates</th>
                            <td>' . count($voteData) . '</td>
                        </tr>
                        <tr>
                            <th>Participation Rate</th>
                            <td>' . $participation . '%</td>
                        </tr>
                    </table>';
        }
        
        if ($exportBreakdown) {
            $html .= '<h2>Vote Breakdown by Candidate</h2>';
            $html .= '<table border="1" cellpadding="5">
                        <tr>
                            <th>Candidate</th>
                            <th>Position</th>
                            <th>Votes</th>
                        </tr>';
            
            foreach ($voteData as $candidate) {
                $html .= '<tr>
                            <td>' . $candidate['candidateName'] . '</td>
                            <td>' . $candidate['position'] . '</td>
                            <td>' . $candidate['voteCount'] . '</td>
                        </tr>';
            }
            
            $html .= '</table>';
        }
        
        if ($exportVoters) {
            $html .= '<h2>Voters List</h2>';
            $html .= '<table border="1" cellpadding="5">
                        <tr>
                            <th>Voter Name</th>
                            <th>Department</th>
                            <th>Candidate</th>
                            <th>Position</th>
                            <th>Timestamp</th>
                        </tr>';
            
            foreach ($voteData as $candidate) {
                foreach ($candidate['votes'] as $vote) {
                    $html .= '<tr>
                                <td>' . $vote['voterName'] . '</td>
                                <td>' . $vote['voterDepartment'] . '</td>
                                <td>' . $candidate['candidateName'] . '</td>
                                <td>' . $candidate['position'] . '</td>
                                <td>' . date('M j, Y g:i a', strtotime($vote['timestamp'])) . '</td>
                            </tr>';
                }
            }
            
            $html .= '</table>';
        }
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output($filename . '.pdf', 'D');
        break;
        
    default:
        header('Location: voters.php?election=' . $electionID);
        exit();
}