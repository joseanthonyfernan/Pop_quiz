<?php
require_once '../config/db.php';
require_once '../includes/auth_student.php';

// Check if student can still time-in (before 5:30 PM)
$current_time = date('H:i:s');
$cutoff_time = '17:30:00'; // 5:30 PM
$late_cutoff = '09:30:00'; // 9:30 AM

// Even if after cutoff, we record it as ABSENT rather than blocking (per index.php logic)
// But manually clicking "Time In" usually implies they want to record presence.
// Let's keep the block for manual if it's past 5:30 PM or just allow it as ABSENT.
// The user said "5:30pm above is absent", so let's allow recording as ABSENT.


$student_number = $_SESSION['student_number'];
$today = date('Y-m-d');

// Check if already timed in today
$check = $pdo->prepare("SELECT * FROM daily_attendance WHERE student_number = ? AND attendance_date = ?");
$check->execute([$student_number, $today]);
$existing = $check->fetch();

if ($existing) {
    $_SESSION['error'] = "You have already timed in today at " . date('h:i A', strtotime($existing['time_in']));
    header("Location: dashboard");
    exit();
}

// Determine status based on current time
if ($current_time <= $late_cutoff) {
    $status = 'PRESENT';
} elseif ($current_time <= $cutoff_time) {
    $status = 'LATE';
} else {
    $status = 'ABSENT';
}

// Record time-in
try {
    $stmt = $pdo->prepare("INSERT INTO daily_attendance (student_number, attendance_date, time_in, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_number, $today, $current_time, $status]);
    
    $_SESSION['success'] = "Time-in recorded successfully! Status: " . $status;
} catch (Exception $e) {
    $_SESSION['error'] = "Error recording time-in: " . $e->getMessage();
}

header("Location: dashboard");
exit();
?>
