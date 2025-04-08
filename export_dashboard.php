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
 * Note: This is a simplified version. For a production environment, you would use a library like TCPDF or FPDF
 */
function exportPDF($data) {
    // For simplicity, we'll just export as CSV with PDF MIME type
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="smartvote-dashboard.pdf"');
    
    // In a real implementation, you would generate a PDF here
    // For now, we'll just output a simple text representation
    
    $output = fopen('php://output', 'w');
    
    fwrite($output, "SMARTVOTE DASHBOARD EXPORT\n\n");
    
    // Write statistics
    if (isset($data['statistics'])) {
        fwrite($output, "DASHBOARD STATISTICS\n");
        foreach ($data['statistics'] as $key => $value) {
            fwrite($output, "$key: $value\n");
        }
        fwrite($output, "\n");
    }
    
    // Write students
    if (isset($data['students']) && !empty($data['students'])) {
        fwrite($output, "STUDENTS\n");
        // Write headers
        fwrite($output, implode("\t", array_keys($data['students'][0])) . "\n");
        
        // Write data
        foreach ($data['students'] as $student) {
            fwrite($output, implode("\t", $student) . "\n");
        }
        fwrite($output, "\n");
    }
    
    // Write elections
    if (isset($data['elections']) && !empty($data['elections'])) {
        fwrite($output, "ELECTIONS\n");
        
        foreach ($data['elections'] as $election) {
            fwrite($output, "Election ID: " . $election['ID'] . "\n");
            fwrite($output, "Name: " . $election['Name'] . "\n");
            fwrite($output, "Description: " . $election['Description'] . "\n");
            fwrite($output, "Start Date: " . $election['Start Date'] . "\n");
            fwrite($output, "End Date: " . $election['End Date'] . "\n");
            fwrite($output, "Status: " . $election['Status'] . "\n");
            
            if (!empty($election['Categories'])) {
                fwrite($output, "Categories:\n");
                fwrite($output, "ID\tName\tDescription\n");
                
                foreach ($election['Categories'] as $category) {
                    fwrite($output, $category['ID'] . "\t" . $category['Name'] . "\t" . $category['Description'] . "\n");
                }
            }
            
            fwrite($output, "\n");
        }
    }
    
    fclose($output);
    exit();
} 