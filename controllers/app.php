<?php
ob_start();
session_start();

require_once '../controllers/classes.php';
require_once '../configs/dbconnection.php';

$auth = new Auth();
$admin = new Admin();

// Ensure action is provided and valid
$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = ["status" => "error", "message" => "Invalid action."];

if (!empty($action)) {
    switch ($action) {
        // **AUTHENTICATION METHODS**
        case 'register':
            $response = $auth->register();
            break;
        case 'login':
            $response = $auth->login();
            break;
        case 'logout':
            session_destroy();
            header("Location: ../login.php"); // Redirect to login page
            exit(); // Ensure no further code is executed
        case 'change_password':
            $response = $auth->change_password();
            break;
        case 'reset_password':
            $response = $auth->reset_password();
            break;

        // **ADMIN ACTIONS**
        case 'add_election':
            $response = $admin->add_election();
            break;
        case 'update_election':
            $response = $admin->update_election();
            break;
        case 'delete_election':
            $response = $admin->delete_election();
            break;
        case 'election_status':
            $response = $admin->election_status();
            break;
        case 'vote_status':
            $response = $admin->vote_status();
            break;
        case 'add_category':
            $response = $admin->add_category();
            break;
        case 'update_category':
            $response = $admin->update_category();
            break;
        case 'delete_category':
            $response = $admin->delete_category();
            break;
        case 'add_candidate':
            $response = $admin->add_candidate();
            break;
        case 'update_candidate':
            $response = $admin->update_candidate();
            break;
        case 'delete_candidate':
            $response = $admin->delete_candidate();
            break;
        case 'user_type':
            $response = $admin->user_type();
            break;

        // **ELECTION REPORTS**
        case 'download_report':
            $response = $admin->download_report();
            break;
        case 'delete_report':
            $response = $admin->delete_report();
            break;

        // **VOTING ACTIONS**
        case 'vote':
            $response = $admin->vote();
            break;
        case 'update_profile':
            $response = $admin->update_profile();
            break;
    }
}

// Return JSON response for all actions except logout
if ($action !== 'logout') {
    header('Content-Type: application/json');
    echo json_encode($response);
}

ob_end_flush();
?>