<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in as a student
if (!isset($_SESSION['student_number'])) {
    header("Location: ../index");
    exit();
}

// SINGLE DEVICE ENFORCEMENT
if (!isset($pdo)) {
    // Attempt to include db connection if not already present
    // Assuming this file is included from a file in /student/ or similar depth
    // Best effort to find db.php
    if (file_exists('../config/db.php')) {
        require_once '../config/db.php';
    } elseif (file_exists('../../config/db.php')) {
        require_once '../../config/db.php';
    } 
}

if (isset($pdo) && isset($_SESSION['student_number'])) {
    $check_token = $pdo->prepare("SELECT session_token, user_agent FROM active_sessions WHERE student_number = ?");
    $check_token->execute([$_SESSION['student_number']]);
    $result = $check_token->fetch();

    if ($result) {
        // STRICT SINGLE DEVICE ENFORCEMENT
        $db_token = $result['session_token'];
        $db_ua = $result['user_agent'];
        $session_token = $_SESSION['session_token'] ?? null;
        $current_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Check 1: Token Mismatch (logged in elsewhere)
        if ($db_token !== $session_token) {
            session_unset();
            session_destroy();
            header("Location: ../index?error=device_conflict");
            exit();
        } 
        // Check 2: User Agent Mismatch (session hijacking prevention)
        elseif ($db_ua !== $current_ua) {
            session_unset();
            session_destroy();
            header("Location: ../index?error=security_alert");
            exit();
        }
        else {
            // Valid session - update last activity
            $update_activity = $pdo->prepare("UPDATE active_sessions SET last_activity = NOW() WHERE student_number = ?");
            $update_activity->execute([$_SESSION['student_number']]);
        }
    } else {
        // No active session found in DB (maybe manually deleted or expired)
        // Force logout if student claims to be logged in
        if (isset($_SESSION['session_token'])) {
            session_unset();
            session_destroy();
            header("Location: ../index?error=session_expired");
            exit();
        }
    }
}
?>
