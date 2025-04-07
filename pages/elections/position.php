<?php
require_once 'configs/dbconnection.php';
require_once 'includes/auth_check.php';

$election_id = $_GET['election'] ?? null;
$action = $_GET['action'] ?? 'manage';

if (!$election_id) {
    header('Location: ?page=elections');
    exit();
}

// Get election info
$election = $conn->query("SELECT * FROM elections WHERE electionID = $election_id")->fetch_assoc();

switch ($action) {
    case 'create':
        include 'positions_create.php';
        break;
    case 'edit':
        include 'positions_edit.php';
        break;
    case 'delete':
        include 'positions_delete.php';
        break;
    case 'manage':
    default:
        include 'positions_manage.php';
}
?>