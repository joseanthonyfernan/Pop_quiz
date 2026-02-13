<?php
require_once '../config/db.php';
require_once '../includes/auth_student.php';

$student_number = $_SESSION['student_number'];
$quiz_id = $_POST['quiz_id'];
$time_taken_seconds = $_POST['time_taken'];

// Convert seconds to HH:MM:SS
$completion_time = gmdate("H:i:s", $time_taken_seconds);

// Final safety check: Is the quiz still ON?
$q_check = $pdo->prepare("SELECT status FROM quizzes WHERE id = ?");
$q_check->execute([$quiz_id]);
$q_status = $q_check->fetchColumn();

if ($q_status != 'ON') {
    die("Error: This quiz has been closed by the teacher.");
}

// Check if already submitted (prevent refresh double-sub)
$check = $pdo->prepare("SELECT id FROM results WHERE student_number = ? AND quiz_id = ?");
$check->execute([$student_number, $quiz_id]);
if ($check->fetch()) {
    header("Location: view_result?quiz_id=$quiz_id");
    exit();
}

// Get all questions for this quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

$score = 0;
$total_questions = count($questions);
$answered_count = 0;

$pdo->beginTransaction();

try {
    foreach ($questions as $q) {
        $q_id = $q['id'];
        $selected_answer = $_POST['q_' . $q_id] ?? null;
        
        if ($selected_answer !== null) {
            $answered_count++;
            if ($selected_answer == $q['correct_answer']) {
                $score++;
            }
        }
        
        // Record answer
        $stmt = $pdo->prepare("INSERT INTO answers (student_number, question_id, selected_answer) VALUES (?, ?, ?)");
        $stmt->execute([$student_number, $q_id, $selected_answer]);
    }

    // Record Result
    $stmt = $pdo->prepare("INSERT INTO results (student_number, quiz_id, score, completion_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_number, $quiz_id, $score, $completion_time]);

    // Mark Attendance
    $status = ($answered_count == $total_questions) ? 'PRESENT' : 'ABSENT';
    $stmt = $pdo->prepare("INSERT INTO attendance (student_number, quiz_id, status) VALUES (?, ?, ?)");
    $stmt->execute([$student_number, $quiz_id, $status]);

    $pdo->commit();
    header("Location: view_result?quiz_id=$quiz_id");
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error submitting quiz: " . $e->getMessage());
}
?>
