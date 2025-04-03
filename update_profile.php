<?php
session_start();
include 'configs/dbconnection.php';
include 'configs/session.php';

header('Content-Type: application/json');

// Verify login
if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$studentId = (int)$_SESSION['login_id'];
$response = ['success' => false, 'message' => ''];

try {
    // Check if file was uploaded
    $newImageName = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "assets/img/profile/students/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        // Validate file
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['profileImage']['tmp_name']);
        finfo_close($fileInfo);
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG, and GIF files are allowed");
        }

        // Check file size (max 2MB)
        if ($_FILES['profileImage']['size'] > 2097152) {
            throw new Exception("File size must be less than 2MB");
        }

        // Generate unique filename
        $fileExt = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
        $newImageName = $studentId . '_' . uniqid() . '.' . strtolower($fileExt);
        $targetFile = $targetDir . $newImageName;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFile)) {
            throw new Exception("Failed to upload file");
        }

        // Delete old image if it exists
        $stmt = $conn->prepare("SELECT profilePicture FROM students WHERE studentID = ?");
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $oldImage = $result->fetch_assoc()['profilePicture'];
        $stmt->close();

        if ($oldImage && file_exists($targetDir . $oldImage) && $oldImage !== 'default.png') {
            unlink($targetDir . $oldImage);
        }
    }

    // Update database
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $contactNumber = $conn->real_escape_string($_POST['contactNumber'] ?? '');

    if ($newImageName) {
        $stmt = $conn->prepare("UPDATE students SET name = ?, contactNumber = ?, profilePicture = ? WHERE studentID = ?");
        $stmt->bind_param('sssi', $name, $contactNumber, $newImageName, $studentId);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name = ?, contactNumber = ? WHERE studentID = ?");
        $stmt->bind_param('ssi', $name, $contactNumber, $studentId);
    }

    if (!$stmt->execute()) {
        throw new Exception("Database update failed: " . $stmt->error);
    }

    $stmt->close();

    $response = [
        'success' => true,
        'message' => 'Profile updated successfully',
        'newImage' => $newImageName ?? null,
        'newName' => $name,
        'newContact' => $contactNumber
    ];

} catch (Exception $e) {
    // Delete the uploaded file if there was an error after uploading
    if (isset($targetFile)) {
        @unlink($targetFile);
    }
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>