<?php
require_once 'config/db.php';
session_start();

if (isset($_SESSION['student_number'])) {
    $stmt = $pdo->prepare("DELETE FROM active_sessions WHERE student_number = ?");
    $stmt->execute([$_SESSION['student_number']]);
}

session_unset();
session_destroy();
header("Location: index");
exit();
?>
