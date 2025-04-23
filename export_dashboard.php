<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require 'configs/dbconnection.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$format = $data['format'] ?? 'csv';
$includeStats = $data['includeStats'] ?? true;
$includeStudents = $data['includeStudents'] ?? true;
$includeElections = $data['includeElections'] ?? true;

// Prepare data for export
$exportData = [];

// Get dashboard statistics
if ($includeStats) {
    try {
        $stats_query = "
        SELECT 
            (SELECT COUNT(*) FROM elections) AS total_elections,
            (SELECT COUNT(*) FROM categories) AS total_categories,
            (SELECT COUNT(*) FROM students WHERE status = 'Active') AS total_voters,
            (SELECT COUNT(DISTINCT studentID) FROM votes) AS total_voted
        ";
        
        $stats_result = $conn->query($stats_query);
        if ($stats_result && $stats_result->num_rows > 0) {
            $stats = $stats_result->fetch_assoc();
            $exportData['statistics'] = [
                'Total Elections' => $stats['total_elections'],
                'Total Categories' => $stats['total_categories'],
                'Total Voters' => $stats['total_voters'],
                'Total Votes Cast' => $stats['total_voted'],
                'Participation Rate' => ($stats['total_voters'] > 0) ? round(($stats['total_voted'] / $stats['total_voters']) * 100) . '%' : '0%'
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching statistics: " . $e->getMessage());
    }
}

// Get students data
if ($includeStudents) {
    try {
        $students_query = "SELECT studentID, name, email, department, status, role FROM students";
        $students_result = $conn->query($students_query);
        
        if ($students_result && $students_result->num_rows > 0) {
            $exportData['students'] = [];
            while ($student = $students_result->fetch_assoc()) {
                $exportData['students'][] = [
                    'ID' => $student['studentID'],
                    'Name' => $student['name'],
                    'Email' => $student['email'],
                    'Department' => $student['department'],
                    'Status' => $student['status'],
                    'Role' => $student['role']
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching students: " . $e->getMessage());
    }
}

// Get elections data
if ($includeElections) {
    try {
        $elections_query = "SELECT electionID, name, description, startDate, endDate, status FROM elections";
        $elections_result = $conn->query($elections_query);
        
        if ($elections_result && $elections_result->num_rows > 0) {
            $exportData['elections'] = [];
            while ($election = $elections_result->fetch_assoc()) {
                // Get categories for this election
                $categories_query = "SELECT categoryID, name, description FROM categories WHERE electionID = ?";
                $categories_stmt = $conn->prepare($categories_query);
                $categories_stmt->bind_param("i", $election['electionID']);
                $categories_stmt->execute();
                $categories_result = $categories_stmt->get_result();
                
                $categories = [];
                if ($categories_result && $categories_result->num_rows > 0) {
                    while ($category = $categories_result->fetch_assoc()) {
                        $categories[] = [
                            'ID' => $category['categoryID'],
                            'Name' => $category['name'],
                            'Description' => $category['description']
                        ];
                    }
                }
                
                $exportData['elections'][] = [
                    'ID' => $election['electionID'],
                    'Name' => $election['name'],
                    'Description' => $election['description'],
                    'Start Date' => $election['startDate'],
                    'End Date' => $election['endDate'],
                    'Status' => $election['status'],
                    'Categories' => $categories
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching elections: " . $e->getMessage());
    }
}

// Export data based on format
switch ($format) {
    case 'csv':
        exportCSV($exportData);
        break;
    case 'excel':
        exportExcel($exportData);
        break;
    case 'pdf':
        exportPDF($exportData);
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Unsupported export format']);
        exit();
}

/**
 * Export data as CSV
 */
function exportCSV($data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="smartvote-dashboard.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write statistics
    if (isset($data['statistics'])) {
        fputcsv($output, ['Dashboard Statistics']);
        foreach ($data['statistics'] as $key => $value) {
            fputcsv($output, [$key, $value]);
        }
        fputcsv($output, []); // Empty line for separation
    }
    
    // Write students
    if (isset($data['students']) && !empty($data['students'])) {
        fputcsv($output, ['Students']);
        // Write headers
        fputcsv($output, array_keys($data['students'][0]));
        
        // Write data
        foreach ($data['students'] as $student) {
            fputcsv($output, $student);
        }
        fputcsv($output, []); // Empty line for separation
    }
    
    // Write elections
    if (isset($data['elections']) && !empty($data['elections'])) {
        fputcsv($output, ['Elections']);
        
        foreach ($data['elections'] as $election) {
            fputcsv($output, ['Election ID', $election['ID']]);
            fputcsv($output, ['Name', $election['Name']]);
            fputcsv($output, ['Description', $election['Description']]);
            fputcsv($output, ['Start Date', $election['Start Date']]);
            fputcsv($output, ['End Date', $election['End Date']]);
            fputcsv($output, ['Status', $election['Status']]);
            
            if (!empty($election['Categories'])) {
                fputcsv($output, ['Categories']);
                fputcsv($output, ['ID', 'Name', 'Description']);
                
                foreach ($election['Categories'] as $category) {
                    fputcsv($output, [$category['ID'], $category['Name'], $category['Description']]);
                }
            }
            
            fputcsv($output, []); // Empty line for separation
        }
    }
    
    fclose($output);
    exit();
}

/**
 * Export data as Excel
 * Note: This is a simplified version. For a production environment, you would use a library like PhpSpreadsheet
 */
function exportExcel($data) {
    // For simplicity, we'll just export as CSV with Excel MIME type
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="smartvote-dashboard.xls"');
    
    $output = fopen('php://output', 'w');
    
    // Write statistics
    if (isset($data['statistics'])) {
        fputcsv($output, ['Dashboard Statistics']);
        foreach ($data['statistics'] as $key => $value) {
            fputcsv($output, [$key, $value]);
        }
        fputcsv($output, []); // Empty line for separation
    }
    
    // Write students
    if (isset($data['students']) && !empty($data['students'])) {
        fputcsv($output, ['Students']);
        // Write headers
        fputcsv($output, array_keys($data['students'][0]));
        
        // Write data
        foreach ($data['students'] as $student) {
            fputcsv($output, $student);
        }
        fputcsv($output, []); // Empty line for separation
    }
    
    // Write elections
    if (isset($data['elections']) && !empty($data['elections'])) {
        fputcsv($output, ['Elections']);
        
        foreach ($data['elections'] as $election) {
            fputcsv($output, ['Election ID', $election['ID']]);
            fputcsv($output, ['Name', $election['Name']]);
            fputcsv($output, ['Description', $election['Description']]);
            fputcsv($output, ['Start Date', $election['Start Date']]);
            fputcsv($output, ['End Date', $election['End Date']]);
            fputcsv($output, ['Status', $election['Status']]);
            
            if (!empty($election['Categories'])) {
                fputcsv($output, ['Categories']);
                fputcsv($output, ['ID', 'Name', 'Description']);
                
                foreach ($election['Categories'] as $category) {
                    fputcsv($output, [$category['ID'], $category['Name'], $category['Description']]);
                }
            }
            
            fputcsv($output, []); // Empty line for separation
        }
    }
    
    fclose($output);
    exit();
}

/**
 * Export data as PDF
 */
function exportPDF($data) {
    try {
        // Check if TCPDF exists
        if (!file_exists('vendor/tecnickcom/tcpdf/tcpdf.php')) {
            throw new Exception('TCPDF library not found. Please run composer require tecnickcom/tcpdf');
        }

        require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Create new PDF document with explicit parameters
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

        // Set document information
        $pdf->SetCreator('SmartVote System');
        $pdf->SetAuthor('SmartVote Admin');
        $pdf->SetTitle('SmartVote Dashboard Export');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('courier');

        // Set margins - slightly larger to ensure content fits
        $pdf->SetMargins(20, 20, 20);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 20);

        // Set font - using core fonts to avoid encoding issues
        $pdf->SetFont('helvetica', '', 11);

        // Add a page
        $pdf->AddPage();

        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 10, 'SmartVote Dashboard Report', 0, 1, 'C');
        $pdf->Ln(10);

        // Statistics Section
        if (isset($data['statistics'])) {
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Dashboard Statistics', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(5);

            foreach ($data['statistics'] as $key => $value) {
                // Use MultiCell for better text wrapping
                $pdf->Cell(100, 8, $key . ':', 0, 0);
                $pdf->MultiCell(0, 8, $value, 0, 'L');
            }
            $pdf->Ln(10);
        }

        // Students Section
        if (isset($data['students']) && !empty($data['students'])) {
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Students', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(5);

            // Calculate column widths based on page width
            $pageWidth = $pdf->GetPageWidth() - 40; // Subtract margins
            $widths = array(
                $pageWidth * 0.1,  // ID
                $pageWidth * 0.2,  // Name
                $pageWidth * 0.25, // Email
                $pageWidth * 0.2,  // Department
                $pageWidth * 0.125, // Status
                $pageWidth * 0.125  // Role
            );

            // Table header with background
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetFont('helvetica', 'B', 11);
            $headers = array_keys($data['students'][0]);
            
            foreach ($headers as $index => $header) {
                $pdf->Cell($widths[$index], 8, $header, 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Table data
            $pdf->SetFont('helvetica', '', 10);
            foreach ($data['students'] as $student) {
                $pdf->SetFillColor(255, 255, 255);
                
                // Handle multi-line content
                $maxHeight = 8;
                foreach ($student as $value) {
                    $numLines = $pdf->getNumLines($value, $widths[0]);
                    $lineHeight = $numLines * 8;
                    $maxHeight = max($maxHeight, $lineHeight);
                }

                foreach ($student as $index => $value) {
                    $pdf->MultiCell($widths[$index], $maxHeight, $value, 1, 'L', false, 0);
                }
                $pdf->Ln($maxHeight);
            }
            $pdf->Ln(10);
        }

        // Elections Section
        if (isset($data['elections']) && !empty($data['elections'])) {
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Elections', 0, 1, 'L');
            
            foreach ($data['elections'] as $election) {
                $pdf->SetFont('helvetica', 'B', 13);
                $pdf->Ln(5);
                
                // Election details in a bordered box
                $pdf->SetFillColor(245, 245, 245);
                $pdf->Cell(0, 8, 'Election ID: ' . $election['ID'], 1, 1, 'L', true);
                
                $pdf->SetFont('helvetica', '', 11);
                $pdf->SetFillColor(255, 255, 255);
                
                // Use MultiCell for better text wrapping
                $labelWidth = 40;
                
                $pdf->Cell($labelWidth, 8, 'Name:', 1);
                $pdf->MultiCell(0, 8, $election['Name'], 1, 'L');
                
                $pdf->Cell($labelWidth, 8, 'Description:', 1);
                $pdf->MultiCell(0, 8, $election['Description'], 1, 'L');
                
                $pdf->Cell($labelWidth, 8, 'Start Date:', 1);
                $pdf->Cell(0, 8, $election['Start Date'], 1, 1, 'L');
                
                $pdf->Cell($labelWidth, 8, 'End Date:', 1);
                $pdf->Cell(0, 8, $election['End Date'], 1, 1, 'L');
                
                $pdf->Cell($labelWidth, 8, 'Status:', 1);
                $pdf->Cell(0, 8, $election['Status'], 1, 1, 'L');

                if (!empty($election['Categories'])) {
                    $pdf->Ln(5);
                    $pdf->SetFont('helvetica', 'B', 12);
                    $pdf->Cell(0, 8, 'Categories:', 0, 1);
                    
                    // Categories table
                    $pdf->SetFont('helvetica', 'B', 11);
                    $pdf->SetFillColor(240, 240, 240);
                    
                    // Calculate widths for categories table
                    $catWidths = array(
                        $pageWidth * 0.15,  // ID
                        $pageWidth * 0.35,  // Name
                        $pageWidth * 0.5   // Description
                    );
                    
                    $pdf->Cell($catWidths[0], 8, 'ID', 1, 0, 'C', true);
                    $pdf->Cell($catWidths[1], 8, 'Name', 1, 0, 'C', true);
                    $pdf->Cell($catWidths[2], 8, 'Description', 1, 1, 'C', true);

                    $pdf->SetFont('helvetica', '', 11);
                    foreach ($election['Categories'] as $category) {
                        $pdf->Cell($catWidths[0], 8, $category['ID'], 1, 0, 'L');
                        $pdf->Cell($catWidths[1], 8, $category['Name'], 1, 0, 'L');
                        $pdf->MultiCell($catWidths[2], 8, $category['Description'], 1, 'L');
                    }
                }
                $pdf->AddPage();
            }
        }

        // Output the PDF
        $pdf->Output('smartvote-dashboard.pdf', 'D');
        exit();
    } catch (Exception $e) {
        // Log the error
        error_log("PDF Generation Error: " . $e->getMessage());
        
        // Return error response
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate PDF: ' . $e->getMessage()
        ]);
        exit();
    }
}