<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index");
    exit();
}
?>
