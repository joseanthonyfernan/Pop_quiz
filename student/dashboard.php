<?php
require_once '../config/db.php';
require_once '../includes/auth_student.php';
$base_url = "/Pop_quiz/";

$student_number = $_SESSION['student_number'];

// Check today's attendance status
$today = date('Y-m-d');
$attendance_check = $pdo->prepare("SELECT * FROM daily_attendance WHERE student_number = ? AND attendance_date = ?");
$attendance_check->execute([$student_number, $today]);
$today_attendance = $attendance_check->fetch();

// Fetch full student details for the update form
$student_stmt = $pdo->prepare("SELECT * FROM students WHERE student_number = ?");
$student_stmt->execute([$student_number]);
$student_data = $student_stmt->fetch();

// Profile Update Handler
$update_success = null;
$update_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $mname = trim($_POST['mname']);
    $section = trim($_POST['section']);
    $year_level = trim($_POST['year_level']);
    $department = trim($_POST['department']);

    try {
        $update_stmt = $pdo->prepare("UPDATE students SET fname = ?, lname = ?, mname = ?, section = ?, year_level = ?, department = ? WHERE student_number = ?");
        $update_stmt->execute([$fname, $lname, $mname, $section, $year_level, $department, $student_number]);
        
        // Update session name just in case it changed
        $_SESSION['student_name'] = $fname . ' ' . $lname;
        $update_success = "Profile updated successfully!";
        
        // Refresh student data
        $student_stmt->execute([$student_number]);
        $student_data = $student_stmt->fetch();
    } catch (Exception $e) {
        $update_error = "Error updating profile: " . $e->getMessage();
    }
}

// Check if current time is past cutoff (5:30 PM)
$current_time = date('H:i:s');
$cutoff_time = '17:30:00'; // 5:30 PM
$is_past_cutoff = ($current_time > $cutoff_time);
$late_cutoff = '09:30:00'; // 9:30 AM

// Cooldown logic removed per user request
$question = null;
$remaining_time = 15;

// 2. Get Active Question or Pick New One
if (isset($_SESSION['active_question_id'])) {
    $q_id = $_SESSION['active_question_id'];
    $stmt = $pdo->prepare("
        SELECT q.*, z.schedule_date 
        FROM questions q
        JOIN quizzes z ON q.quiz_id = z.id
        WHERE q.id = ? AND z.schedule_date = CURRENT_DATE
    ");
    $stmt->execute([$q_id]);
    $question = $stmt->fetch();
    
    // If question deleted or not found, unset session
    if (!$question) {
        unset($_SESSION['active_question_id']);
        unset($_SESSION['question_start_time']);
    } else {
        // Check if question is still unlocked
        $current_time = date('H:i:s');
        if ($question['unlock_time'] && $current_time < $question['unlock_time']) {
            // Question is now locked
            $question = null;
            unset($_SESSION['active_question_id']);
            unset($_SESSION['question_start_time']);
        }
    }
}

// Pick new if needed
if (!$question) {
    // Strict Mode: Only the MOST RECENTLY unlocked question is available.
    // Previous questions are effectively "expired".
    $current_time = date('H:i:s');
    
    // Find the latest unlocked question
    // Find the latest unlocked question FOR TODAY'S QUIZ
    $sql = "SELECT q.* 
            FROM questions q
            JOIN quizzes z ON q.quiz_id = z.id
            WHERE q.unlock_time IS NOT NULL 
            AND q.unlock_time <= ? 
            AND z.schedule_date = CURRENT_DATE
            ORDER BY q.unlock_time DESC 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_time]);
    $latest_question = $stmt->fetch();
    
    if ($latest_question) {
        // STRICT TIMING: Check if student is "late"
        // Valid window: Student must enter within 10 minutes of unlock time
        $valid_window_seconds = 10 * 60; 
        
        $unlock_ts = strtotime($latest_question['unlock_time']);
        $current_ts = strtotime($current_time);
        
        // Check if current time is within window
        if (($current_ts - $unlock_ts) <= $valid_window_seconds) {
            // Student is on time (within 10 mins)
            
            // Check if student has answered this specific question
            $check_stmt = $pdo->prepare("SELECT id FROM answers WHERE student_number = ? AND question_id = ?");
            $check_stmt->execute([$student_number, $latest_question['id']]);
            
            if (!$check_stmt->fetch()) {
                // Not answered yet - this is the active question!
                $question = $latest_question;
                $_SESSION['active_question_id'] = $question['id'];
                $_SESSION['question_start_time'] = time();
            }
        }
    }
}

// Calculate Remaining Time for Current Question Check
if ($question) {
    $elapsed = time() - $_SESSION['question_start_time'];
    $remaining_time = 15 - $elapsed;
    
    if ($remaining_time <= 0) {
        // Server-Side Timeout Detection
        echo "<script>window.location.href='submit_single_answer?question_id=" . $question['id'] . "&time_expired=1';</script>";
        exit(); 
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily PopQuiz</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <script src="<?php echo $base_url; ?>assets/js/devtools-blocker.js"></script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #2d3748;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.4);
            overflow: hidden;
            width: 100%;
            max-width: 700px;
        }

        .welcome-header {
            text-align: center;
            padding: 30px;
            background: rgba(255,255,255,0.5);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #764ba2;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

        /* Countdown View */
        .cooldown-container {
            text-align: center;
            padding: 50px 30px;
        }

        .countdown-timer {
            font-size: 4rem;
            font-weight: 800;
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 20px 0;
            font-variant-numeric: tabular-nums;
        }

        /* Question View */
        .question-card-body {
            padding: 40px;
        }

        .timer-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid #764ba2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #764ba2;
            margin: 0 auto 30px;
            position: relative;
        }
        
        .timer-circle::after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            border: 4px solid transparent; /* Highlight */
            border-top-color: #ff6b6b;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }

        .option-btn {
            display: block;
            width: 100%;
            text-align: left;
            padding: 15px 20px;
            margin-bottom: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            transition: all 0.2s;
            font-size: 1.1rem;
            position: relative;
            cursor: pointer;
        }

        .option-btn:hover {
            border-color: #a3bffa;
            background: #f7fafc;
            transform: translateX(5px);
        }

        .option-btn.selected {
            border-color: #667eea;
            background: #ebf4ff;
            color: #5a67d8;
            font-weight: 600;
        }

        .option-letter {
            display: inline-block;
            width: 30px;
            font-weight: 700;
            color: #a0aec0;
        }

        .option-btn.selected .option-letter {
            color: #5a67d8;
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
                /* Allow container to expand rather than being centered if content is tall */
                height: auto; 
                min-height: 100vh;
                display: block; 
            }
            
            .glass-card {
                margin: 20px auto;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }

            .header-content {
                flex-direction: column;
                gap: 20px;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
                width: 100%;
            }

            .user-avatar {
                margin-right: 0 !important;
                margin-bottom: 10px;
                width: 70px;
                height: 70px;
                font-size: 28px;
            }

            .user-details {
                text-align: center !important;
            }

            .action-buttons {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .action-buttons .btn {
                flex: 1 1 auto;
                font-size: 0.8rem;
                padding: 6px 12px;
                white-space: nowrap;
            }

            .welcome-header {
                padding: 20px 15px;
            }

            .cooldown-container {
                padding: 30px 15px;
            }

            .question-card-body {
                padding: 25px 15px;
            }

            .timer-circle {
                width: 60px;
                height: 60px;
                font-size: 1.2rem;
                margin: 0 auto 20px;
            }
            
            h4.fw-bold {
                font-size: 1.25rem; /* Smaller question text */
            }

            .countdown-timer {
                font-size: 2.5rem !important;
            }
            
            .option-btn {
                padding: 12px 15px;
                font-size: 1rem;
            }
            
            /* Modal adjustments */
            .modal-body {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="glass-card animate__animated animate__fadeInUp">
        
        <!-- HEADER -->
        <div class="welcome-header">
            <div class="d-flex align-items-center justify-content-between px-3 header-content">
                <div class="d-flex align-items-center user-info">
                    <div class="user-avatar shadow-sm me-3">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                    <div class="text-start user-details">
                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></h5>
                        <small class="text-muted">ID: <?php echo $_SESSION['student_number']; ?></small>
                    </div>
                </div>
                <div class="d-flex gap-2 action-buttons">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-user-edit me-1"></i> Profile
                    </button>
                    <a href="view_result" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Results</a>
                    <a href="../logout" class="btn btn-sm btn-outline-danger rounded-pill px-3">Leave</a>
                </div>
            </div>
            
            <?php if ($update_success): ?>
                <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 rounded-4 mb-0" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $update_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($update_error): ?>
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 rounded-4 mb-0" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $update_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Attendance Status Bar -->
            <div class="mt-3 px-3">
                <?php if ($today_attendance): ?>
                    <div class="alert alert-<?php echo $today_attendance['status'] == 'PRESENT' ? 'success' : ($today_attendance['status'] == 'LATE' ? 'warning' : 'danger'); ?> mb-0 py-2">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Attendance Recorded:</strong> <?php echo $today_attendance['status']; ?> 
                        (Time-In: <?php echo date('h:i A', strtotime($today_attendance['time_in'])); ?>)
                        <?php if ($today_attendance['time_out']): ?>
                            <br>
                            <i class="fas fa-clock me-2"></i>
                            <strong>Time-Out:</strong> <?php echo date('h:i A', strtotime($today_attendance['time_out'])); ?> 
                            <span class="badge bg-info text-dark ms-1">Auto-Logged</span>
                        <?php endif; ?>
                    </div>
                <?php elseif ($is_past_cutoff): ?>
                    <div class="alert alert-danger mb-0 py-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attendance:</strong> ABSENT - You logged in after 05:30 PM.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0 py-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>No Attendance Record Today</strong> - Your attendance will be recorded automatically on your next login.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($question): ?>
            <!-- QUESTION VIEW -->
            <form action="submit_single_answer" method="POST" id="questionForm">
                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                <input type="hidden" name="answer" id="selectedAnswer" value="">
                
                <div class="question-card-body text-center">
                    
                    <?php if ($question['question_number']): ?>
                        <div class="mb-3">
                            <span class="badge bg-gradient text-white px-4 py-2" style="background: linear-gradient(135deg, #667eea, #764ba2); font-size: 0.9rem;">
                                <i class="fas fa-hashtag me-1"></i>Question <?php echo $question['question_number']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="timer-circle mb-4">
                        <span id="questionTimer"><?php echo $remaining_time; ?></span>
                    </div>

                    <h4 class="fw-bold mb-5"><?php echo $question['question']; ?></h4>

                    <div class="text-start mb-4">
                        <div class="option-btn" onclick="select(this, 'a')">
                            <span class="option-letter">A.</span> <?php echo $question['option_a']; ?>
                        </div>
                        <div class="option-btn" onclick="select(this, 'b')">
                            <span class="option-letter">B.</span> <?php echo $question['option_b']; ?>
                        </div>
                        <div class="option-btn" onclick="select(this, 'c')">
                            <span class="option-letter">C.</span> <?php echo $question['option_c']; ?>
                        </div>
                        <div class="option-btn" onclick="select(this, 'd')">
                            <span class="option-letter">D.</span> <?php echo $question['option_d']; ?>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 w-100 shadow" id="submitBtn" onclick="submitForm()" disabled>
                        Submit Answer <i class="fas fa-paper-plane ms-2"></i>
                    </button>
                </div>
            </form>

            <script>
                let qTime = <?php echo $remaining_time; ?>;
                const timerDisplay = document.getElementById('questionTimer');
                
                function select(el, val) {
                    document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                    el.classList.add('selected');
                    document.getElementById('selectedAnswer').value = val;
                    document.getElementById('submitBtn').disabled = false;
                }

                function submitForm() {
                    document.getElementById('questionForm').submit();
                }

                const timerIn = setInterval(() => {
                    qTime--;
                    timerDisplay.innerText = qTime;
                    
                    if (qTime <= 5) {
                        timerDisplay.style.color = '#dc3545';
                    }

                    if (qTime <= 0) {
                        clearInterval(timerIn);
                        // Auto submit (empty if nothing selected)
                        submitForm();
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <!-- NO AVAILABLE QUESTIONS (Either all done or waiting for unlock) -->
            <?php
            // Check if there are any unanswered questions (locked or otherwise)
            $unanswered_check = $pdo->prepare("SELECT COUNT(*) FROM questions q WHERE q.id NOT IN (SELECT question_id FROM answers WHERE student_number = ?)");
            $unanswered_check->execute([$student_number]);
            $unanswered_count = $unanswered_check->fetchColumn();
            
            if ($unanswered_count > 0) {
                // There are questions but they're locked - find next unlock time
                $current_time = date('H:i:s');
                $next_question = $pdo->prepare("
                    SELECT q.question_number, q.unlock_time 
                    FROM questions q 
                    JOIN quizzes z ON q.quiz_id = z.id
                    WHERE q.id NOT IN (SELECT question_id FROM answers WHERE student_number = ?)
                    AND q.unlock_time > ?
                    AND z.schedule_date = CURRENT_DATE
                    ORDER BY q.unlock_time ASC
                    LIMIT 1
                ");
                $next_question->execute([$student_number, $current_time]);
                $next = $next_question->fetch();
                
                if ($next): // Check if next question exists
            ?>
                <!-- WAITING FOR NEXT QUESTION -->
                <div class="cooldown-container">
                    <div class="mb-4">
                        <i class="fas fa-lock fa-4x text-warning"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Next Question Locked</h3>
                    <p class="text-muted">Question #<?php echo $next['question_number']; ?> will unlock soon!</p>
                    
                    <?php
                    // Calculate time until unlock
                    $current_timestamp = strtotime(date('H:i:s'));
                    $unlock_timestamp = strtotime($next['unlock_time']);
                    $seconds_until_unlock = $unlock_timestamp - $current_timestamp;
                    
                    // Handle case where unlock time is tomorrow (past midnight)
                    if ($seconds_until_unlock < 0) {
                        $seconds_until_unlock += 86400; // Add 24 hours
                    }
                    ?>
                    
                    <div class="countdown-timer mb-3" id="unlockCountdown" style="font-size: 3rem;">
                        --:--
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Unlocks at:</strong> <?php echo date('g:i A', strtotime($next['unlock_time'])); ?>
                    </div>
                    
                    <div class="mt-4">
                        <button onclick="location.reload()" class="btn btn-outline-primary rounded-pill">
                            <i class="fas fa-sync me-2"></i>Refresh Page
                        </button>
                    </div>
                </div>
                
                <script>
                    // Countdown timer for next question unlock
                    let secondsRemaining = <?php echo $seconds_until_unlock; ?>;
                    
                    function updateUnlockCountdown() {
                        if (secondsRemaining <= 0) {
                            location.reload();
                            return;
                        }
                        
                        const minutes = Math.floor(secondsRemaining / 60);
                        const seconds = secondsRemaining % 60;
                        
                        document.getElementById('unlockCountdown').innerText = 
                            (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                        
                        secondsRemaining--;
                        setTimeout(updateUnlockCountdown, 1000);
                    }
                    
                    updateUnlockCountdown();
                </script>
            
            <?php else: ?>
                <!-- NO UPCOMING QUESTIONS WITH UNLOCK TIME -->
                <div class="cooldown-container">
                    <div class="mb-4">
                        <i class="fas fa-calendar-times fa-4x text-muted"></i>
                    </div>
                    <?php 
                    // Check if there are any quizzes scheduled for today at all
                    $today_quiz_check = $pdo->query("SELECT COUNT(*) FROM quizzes WHERE schedule_date = CURRENT_DATE AND status = 'ON'")->fetchColumn();
                    if ($today_quiz_check > 0): 
                    ?>
                        <h3 class="fw-bold mb-2">No Active Questions</h3>
                        <p class="text-muted">There are no questions available right now. Please check back later.</p>
                    <?php else: ?>
                        <h3 class="fw-bold mb-2">No Quiz Scheduled Today</h3>
                        <p class="text-muted">There are no quizzes scheduled for <?php echo date('F j, Y'); ?>.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php } else { ?>
                <!-- ALL DONE -->
                <?php
                // Check if time_out was recorded today
                $check_timeout = $pdo->prepare("SELECT time_out FROM daily_attendance WHERE student_number = ? AND attendance_date = ?");
                $check_timeout->execute([$student_number, $today]);
                $attendance_record = $check_timeout->fetch();
                $timeout_recorded = $attendance_record && $attendance_record['time_out'];
                ?>
                <div class="cooldown-container">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <h3 class="fw-bold text-success">All Caught Up!</h3>
                    <p class="text-muted">You have answered all available questions.</p>
                    
                    <?php if ($timeout_recorded): ?>
                        <div class="alert alert-info mt-3 animate__animated animate__fadeIn">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Attendance Time-Out:</strong> Automatically recorded at 
                            <strong><?php echo date('h:i A', strtotime($attendance_record['time_out'])); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="view_result?quiz_id=0" class="btn btn-outline-primary rounded-pill">View History <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="" method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editProfileModalLabel">Update My Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted">First Name</label>
                            <input type="text" name="fname" class="form-control rounded-3" value="<?php echo htmlspecialchars($student_data['fname'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted">Last Name</label>
                            <input type="text" name="lname" class="form-control rounded-3" value="<?php echo htmlspecialchars($student_data['lname'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600 small text-muted">Middle Name (Optional)</label>
                            <input type="text" name="mname" class="form-control rounded-3" value="<?php echo htmlspecialchars($student_data['mname'] ?? ''); ?>">
                        </div>
                        <hr class="my-3 opacity-10">
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted">Section</label>
                            <input type="text" name="section" class="form-control rounded-3" value="<?php echo htmlspecialchars($student_data['section'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted">Year Level</label>
                            <select name="year_level" class="form-select rounded-3" required>
                                <option value="1st Year" <?php echo ($student_data['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($student_data['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($student_data['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo ($student_data['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                <option value="5th Year" <?php echo ($student_data['year_level'] == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600 small text-muted">Department</label>
                            <input type="text" name="department" class="form-control rounded-3" value="<?php echo htmlspecialchars($student_data['department'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        Save Changes <i class="fas fa-save ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
