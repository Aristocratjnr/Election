<?php
require_once 'configs/dbconnection.php';
require_once 'includes/auth_check.php';

$election_id = $_GET['election'] ?? null;
$action = $_GET['action'] ?? 'general';

if (!$election_id) {
    header('Location: ?page=elections');
    exit();
}

// Get election info
$election = $conn->query("SELECT * FROM elections WHERE electionID = $election_id")->fetch_assoc();

switch ($action) {
    case 'voting':
        include 'config_voting.php';
        break;
    case 'eligibility':
        include 'config_eligibility.php';
        break;
    case 'security':
        include 'config_security.php';
        break;
    case 'general':
    default:
        include 'config_general.php';
}
?>