<?php
require_once '../config/db.php';
require_once '../includes/auth_student.php';

// Clear active session question
unset($_SESSION['active_question_id']);
unset($_SESSION['question_start_time']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard");
    exit();
}

$student_number = $_SESSION['student_number'];
$question_id = $_POST['question_id'] ?? null;
// user might send 'time_expired' as a flag
$raw_answer = $_POST['answer'] ?? null;
$selected_answer = !empty($raw_answer) ? $raw_answer : null;
$time_expired = $_POST['time_expired'] ?? false;

if (!$question_id) {
    header("Location: dashboard");
    exit();
}

// 1. Validate if we can actually submit (Cooldown check)
// This is a double check. The primary check is on dashboard.
// But we also need to check if they ALREADY answered this specific question (duplicate submission).
$stmt = $pdo->prepare("SELECT id FROM answers WHERE student_number = ? AND question_id = ?");
$stmt->execute([$student_number, $question_id]);
if ($stmt->fetch()) {
    // Already answered
    header("Location: dashboard");
    exit();
}

// 2. Insert Answer
// If time expired, we might want to record it as NULL or 'timeout'.
// If the schema for selected_answer is ENUM('a','b','c','d'), we can't insert 'timeout'.
// If the user didn't select anything, we probably shouldn't insert a row IF we want "unanswered" questions to remain available?
// BUT, the requirement is "next question they show every 30 minutes".
// This implies if you TRY and fail/timeout, you must wait.
// So we MUST insert a record to trigger the timestamp check.
// If null is not allowed in ENUM, we have a problem.
// Let's check schema again. `selected_answer ENUM('a', 'b', 'c', 'd')`. It does NOT say NOT NULL.
// So we can insert NULL.

try {
    $timestamp = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO answers (student_number, question_id, selected_answer, submitted_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_number, $question_id, $selected_answer, $timestamp]);
    
    // Automatic Time-Out removed as it is now fixed at login time 
    // per new attendance guidelines (12:45 PM and 05:45 PM)
    
} catch (Exception $e) {
    // Determine handling. If duplicate entry (shouldn't happen due to logic), ignore.
}

header("Location: dashboard");
exit();
?>
