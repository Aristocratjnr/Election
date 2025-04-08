<?php
session_start();
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Function to validate color hex code
function isValidColor($color) {
    return preg_match('/^#[a-f0-9]{6}$/i', $color);
}

// Function to validate font family
function isValidFont($font) {
    $allowed_fonts = ['Poppins', 'Roboto', 'Open Sans', 'Montserrat', 'Lato'];
    return in_array($font, $allowed_fonts);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];

    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $header_color = trim($_POST['header_color'] ?? '#4361ee');
            $logo_position = $_POST['logo_position'] ?? 'center';
            $font_family = $_POST['font_family'] ?? 'Poppins';
            $show_footer = isset($_POST['show_footer']) ? 1 : 0;

            // Validate inputs
            if (empty($name)) {
                $response['message'] = 'Design name is required';
                break;
            }
            if (!isValidColor($header_color)) {
                $response['message'] = 'Invalid header color';
                break;
            }
            if (!in_array($logo_position, ['left', 'center', 'right'])) {
                $response['message'] = 'Invalid logo position';
                break;
            }
            if (!isValidFont($font_family)) {
                $response['message'] = 'Invalid font family';
                break;
            }

            try {
                $stmt = $conn->prepare("INSERT INTO ballot_designs (name, header_color, logo_position, font_family, show_footer) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $name, $header_color, $logo_position, $font_family, $show_footer);
                
                if ($stmt->execute()) {
                    $response = [
                        'success' => true,
                        'message' => 'Ballot design created successfully',
                        'id' => $stmt->insert_id
                    ];
                } else {
                    $response['message'] = 'Failed to create ballot design';
                }
            } catch (Exception $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $header_color = trim($_POST['header_color'] ?? '#4361ee');
            $logo_position = $_POST['logo_position'] ?? 'center';
            $font_family = $_POST['font_family'] ?? 'Poppins';
            $show_footer = isset($_POST['show_footer']) ? 1 : 0;

            // Validate inputs
            if (empty($id) || empty($name)) {
                $response['message'] = 'Invalid design ID or name';
                break;
            }
            if (!isValidColor($header_color)) {
                $response['message'] = 'Invalid header color';
                break;
            }
            if (!in_array($logo_position, ['left', 'center', 'right'])) {
                $response['message'] = 'Invalid logo position';
                break;
            }
            if (!isValidFont($font_family)) {
                $response['message'] = 'Invalid font family';
                break;
            }

            try {
                $stmt = $conn->prepare("UPDATE ballot_designs SET name = ?, header_color = ?, logo_position = ?, font_family = ?, show_footer = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $name, $header_color, $logo_position, $font_family, $show_footer, $id);
                
                if ($stmt->execute()) {
                    $response = [
                        'success' => true,
                        'message' => 'Ballot design updated successfully'
                    ];
                } else {
                    $response['message'] = 'Failed to update ballot design';
                }
            } catch (Exception $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                $response['message'] = 'Invalid design ID';
                break;
            }

            try {
                $stmt = $conn->prepare("DELETE FROM ballot_designs WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $response = [
                        'success' => true,
                        'message' => 'Ballot design deleted successfully'
                    ];
                } else {
                    $response['message'] = 'Failed to delete ballot design';
                }
            } catch (Exception $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];

    switch ($action) {
        case 'get':
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                $response['message'] = 'Invalid design ID';
                break;
            }

            try {
                $stmt = $conn->prepare("SELECT * FROM ballot_designs WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($design = $result->fetch_assoc()) {
                    $response = [
                        'success' => true,
                        'data' => $design
                    ];
                } else {
                    $response['message'] = 'Design not found';
                }
            } catch (Exception $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
            break;

        case 'list':
            try {
                $result = $conn->query("SELECT * FROM ballot_designs ORDER BY created_at DESC");
                $designs = [];
                
                while ($row = $result->fetch_assoc()) {
                    $designs[] = $row;
                }
                
                $response = [
                    'success' => true,
                    'data' => $designs
                ];
            } catch (Exception $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 