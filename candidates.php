<?php
require_once 'configs/dbconnection.php';
require_once 'includes/auth_check.php';

$position_id = $_GET['position'] ?? null;
$action = $_GET['action'] ?? 'manage';

if (!$position_id) {
    header('Location: ?page=elections');
    exit();
}

// Get position and election info
$position = $conn->query("SELECT p.*, e.name as election_name 
                         FROM positions p
                         JOIN elections e ON p.electionID = e.electionID
                         WHERE p.positionID = $position_id")->fetch_assoc();

switch ($action) {
    case 'create':
        include 'candidates_create.php';
        break;
    case 'edit':
        include 'candidates_edit.php';
        break;
    case 'delete':
        include 'candidates_delete.php';
        break;
    case 'import':
        include 'candidates_import.php';
        break;
    case 'manage':
    default:
        include 'candidates_manage.php';
}
?>