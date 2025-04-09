<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';
require_once 'vendor/autoload.php'; // You'll need to install the required packages

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

$electionID = $_GET['election'] ?? null;
$type = $_GET['type'] ?? 'excel'; // 'excel' or 'pdf'

if (!$electionID) {
    header('Location: results.php?error=missing_election');
    exit;
}

// Get election details and results
$electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
$electionStmt->bind_param("i", $electionID);
$electionStmt->execute();
$electionDetails = $electionStmt->get_result()->fetch_assoc();

// Get positions for this election
$positions = $conn->query("SELECT * FROM positions WHERE electionID = $electionID ORDER BY display_order, positionID ASC");
$resultsData = [];

while ($position = $positions->fetch_assoc()) {
    $candidates = $conn->query("
        SELECT c.candidateID, c.studentID, c.position, s.name,
               COALESCE(r.voteCount, 0) as voteCount,
               COALESCE(r.percentage, 0) as percentage
        FROM candidates c
        JOIN students s ON c.studentID = s.studentID
        LEFT JOIN results r ON c.candidateID = r.candidateID AND r.electionID = $electionID
        WHERE c.position = '{$position['title']}' AND c.status = 'Approved'
        ORDER BY voteCount DESC
    ");

    $positionResults = [
        'title' => $position['title'],
        'candidates' => []
    ];

    while ($candidate = $candidates->fetch_assoc()) {
        $positionResults['candidates'][] = $candidate;
    }

    $resultsData[] = $positionResults;
}

if ($type === 'excel') {
    // Create Excel file
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $sheet->setCellValue('A1', 'Election Results: ' . $electionDetails['name']);
    $sheet->setCellValue('A2', 'Generated on: ' . date('F j, Y, g:i a'));
    $sheet->mergeCells('A1:D1');
    $sheet->mergeCells('A2:D2');
    
    $row = 4;
    foreach ($resultsData as $position) {
        // Position header
        $sheet->setCellValue('A' . $row, 'Position: ' . $position['title']);
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        // Column headers
        $sheet->setCellValue('A' . $row, 'Candidate');
        $sheet->setCellValue('B' . $row, 'Votes');
        $sheet->setCellValue('C' . $row, 'Percentage');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $row++;
        
        // Candidate data
        foreach ($position['candidates'] as $candidate) {
            $sheet->setCellValue('A' . $row, $candidate['name']);
            $sheet->setCellValue('B' . $row, $candidate['voteCount']);
            $sheet->setCellValue('C' . $row, $candidate['percentage'] . '%');
            $row++;
        }
        
        $row++; // Add space between positions
    }
    
    // Auto-size columns
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Election_Results_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} else if ($type === 'pdf') {
    // Create PDF
    $dompdf = new Dompdf();
    
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .position { margin-top: 20px; }
            .position-title { 
                background-color: #f8f9fa;
                padding: 10px;
                margin-bottom: 10px;
                font-weight: bold;
            }
            table { 
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th { background-color: #f8f9fa; }
            .winner { background-color: #fff3cd; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>' . htmlspecialchars($electionDetails['name']) . '</h2>
            <p>Generated on: ' . date('F j, Y, g:i a') . '</p>
        </div>
    ';
    
    foreach ($resultsData as $position) {
        $html .= '
        <div class="position">
            <div class="position-title">' . htmlspecialchars($position['title']) . '</div>
            <table>
                <tr>
                    <th>Candidate</th>
                    <th>Votes</th>
                    <th>Percentage</th>
                </tr>';
        
        $maxVotes = !empty($position['candidates']) ? max(array_column($position['candidates'], 'voteCount')) : 0;
        foreach ($position['candidates'] as $candidate) {
            $isWinner = ($candidate['voteCount'] == $maxVotes && $maxVotes > 0);
            $html .= '
                <tr' . ($isWinner ? ' class="winner"' : '') . '>
                    <td>' . htmlspecialchars($candidate['name']) . '</td>
                    <td>' . number_format($candidate['voteCount']) . '</td>
                    <td>' . $candidate['percentage'] . '%</td>
                </tr>';
        }
        
        $html .= '
            </table>
        </div>';
    }
    
    $html .= '
    </body>
    </html>';
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Output PDF for download
    $dompdf->stream("Election_Results_" . date('Y-m-d') . ".pdf", array("Attachment" => true));
}

exit; 