<?php
session_start(); 

if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is admin (assuming type 0 is admin)
if (!isset($_SESSION['type']) || $_SESSION['type'] != 0) {
    header("Location: unauthorized.php"); // Create this page
    exit;
}