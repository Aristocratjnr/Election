<?php
require_once 'configs/dbconnection.php';
require_once 'includes/auth_check.php';

$election_id = $_GET['election'] ?? null;
$action = $_GET['action'] ?? 'design';

if (!$election_id) {
    header('Location: ?page=elections');
    exit();
}

// Get election info
$election = $conn->query("SELECT * FROM elections WHERE electionID = $election_id")->fetch_assoc();

switch ($action) {
    case 'preview':
        include 'ballots_preview.php';
        break;
    case 'templates':
        include 'ballots_templates.php';
        break;
    case 'design':
    default:
        include 'ballots_design.php';
}
?>