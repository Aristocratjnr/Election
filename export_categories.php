<?php
require_once 'configs/dbconnection.php';
require_once 'configs/session.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get export format and election ID
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'excel';
$electionID = isset($_GET['electionID']) ? $_GET['electionID'] : null;

// Prepare query
$query = "SELECT 
            c.*,
            e.name as election_name,
            e.status as election_status,
            u.name as created_by_name,
            (SELECT COUNT(*) FROM candidates WHERE categoryID = c.categoryID) as candidate_count
          FROM categories c 
          LEFT JOIN elections e ON c.electionID = e.electionID
          LEFT JOIN users u ON c.created_by = u.userID";

if ($electionID) {
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
    $categories[] = $row;
}

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$headers = ['ID', 'Category Name', 'Election', 'Status', 'Candidates', 'Created By', 'Created Date', 'Last Updated'];
$sheet->fromArray([$headers], null, 'A1');

// Style headers
$headerStyle = $sheet->getStyle('A1:H1');
$headerStyle->getFont()->setBold(true);
$headerStyle->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FF1E90FF');
$headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');

// Add data
$row = 2;
foreach ($categories as $category) {
    $data = [
        $category['categoryID'],
        $category['name'],
        $category['election_name'],
        $category['election_status'],
        $category['candidate_count'],
        $category['created_by_name'],
        date('Y-m-d H:i:s', strtotime($category['created_at'])),
        $category['updated_at'] ? date('Y-m-d H:i:s', strtotime($category['updated_at'])) : 'N/A'
    ];
    $sheet->fromArray([$data], null, 'A' . $row);
    $row++;
}

// Auto-size columns
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set filename
$filename = 'categories_export_' . date('Y-m-d_His');

// Export based on format
switch ($format) {
    case 'pdf':
        require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
        
        // Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('SmartVote');
        $pdf->SetAuthor('SmartVote Admin');
        $pdf->SetTitle('Categories Export');
        
        // Add page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Add title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 15, 'Categories Report', 0, true, 'C');
        $pdf->SetFont('helvetica', '', 10);
        
        // Add date
        $pdf->Cell(0, 10, 'Generated: ' . date('Y-m-d H:i:s'), 0, true, 'R');
        $pdf->Ln(5);
        
        // Create table
        $pdf->SetFillColor(30, 144, 255);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(128, 128, 128);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        
        // Add headers
        foreach ($headers as $header) {
            $pdf->Cell(35, 7, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Reset colors
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');
        
        // Add data rows
        $fill = false;
        foreach ($categories as $category) {
            $pdf->Cell(35, 6, $category['categoryID'], 'LR', 0, 'L', $fill);
            $pdf->Cell(35, 6, $category['name'], 'LR', 0, 'L', $fill);
            $pdf->Cell(35, 6, $category['election_name'], 'LR', 0, 'L', $fill);
            $pdf->Cell(35, 6, $category['election_status'], 'LR', 0, 'L', $fill);
            $pdf->Cell(35, 6, $category['candidate_count'], 'LR', 0, 'C', $fill);
            $pdf->Cell(35, 6, $category['created_by_name'], 'LR', 0, 'L', $fill);
            $pdf->Cell(35, 6, date('Y-m-d', strtotime($category['created_at'])), 'LR', 0, 'C', $fill);
            $pdf->Cell(35, 6, $category['updated_at'] ? date('Y-m-d', strtotime($category['updated_at'])) : 'N/A', 'LR', 0, 'C', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
        
        // Close table
        $pdf->Cell(array_sum(array_fill(0, count($headers), 35)), 0, '', 'T');
        
        // Output PDF
        $pdf->Output($filename . '.pdf', 'D');
        break;
        
    case 'csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $writer = new Csv($spreadsheet);
        $writer->save('php://output');
        break;
        
    default: // Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
}