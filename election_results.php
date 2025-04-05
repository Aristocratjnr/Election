<?php
require_once 'configs/dbconnection.php';
require_once 'includes/auth_check.php';

$election_id = $_GET['election'] ?? null;
$action = $_GET['action'] ?? 'view';

if (!$election_id) {
    header('Location: ?page=elections');
    exit();
}

// Get election info
$election = $conn->query("SELECT * FROM elections WHERE electionID = $election_id")->fetch_assoc();

switch ($action) {
    case 'live':
        include 'results_live.php';
        break;
    case 'reports':
        include 'results_reports.php';
        break;
    case 'analytics':
        include 'results_analytics.php';
        break;
    case 'view':
    default:
        include 'results_view.php';
}
?>