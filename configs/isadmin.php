<?php
session_start(); 

if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is an admin (type 0)
if (!isset($_SESSION['type']) || $_SESSION['type'] != 0) {
    header("Location: unauthorized.php"); 
    exit;
}