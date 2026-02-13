<?php
require_once '../config/db.php';
require_once '../includes/auth_student.php';
$base_url = "/Pop_quiz/";

$student_number = $_SESSION['student_number'];

// Cooldown logic removed per user request
$question = null;
$remaining_time = 15;

// 2. Get Active Question or Pick New One
// First, check if we have a valid active question in session
$has_valid_session = false;

if (isset($_SESSION['active_question_id']) && isset($_SESSION['question_start_time'])) {
    $q_id = $_SESSION['active_question_id'];
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$q_id]);
    $question = $stmt->fetch();
    
    // Check if this question was already answered (shouldn't happen, but safety check)
    if ($question) {
        $check = $pdo->prepare("SELECT id FROM answers WHERE student_number = ? AND question_id = ?");
        $check->execute([$student_number, $q_id]);
        if ($check->fetch()) {
            // Already answered, clear session
            $question = null;
            unset($_SESSION['active_question_id']);
            unset($_SESSION['question_start_time']);
        } else {
            // Check if question is still unlocked
            $current_time = date('H:i:s');
            if ($question['unlock_time'] && $current_time < $question['unlock_time']) {
                // Question is now locked (shouldn't happen but safety check)
                $question = null;
                unset($_SESSION['active_question_id']);
                unset($_SESSION['question_start_time']);
            } else {
                $has_valid_session = true;
            }
        }
    } else {
        // Question deleted, clear session
        unset($_SESSION['active_question_id']);
        unset($_SESSION['question_start_time']);
    }
}

// Pick new if needed
if (!$question) {
    // Strict Mode: Only the MOST RECENTLY unlocked question is available.
    // Previous questions are effectively "expired".
    $current_time = date('H:i:s');
    
    // Find the latest unlocked question
    $sql = "SELECT * FROM questions 
            WHERE unlock_time IS NOT NULL 
            AND unlock_time <= ? 
            ORDER BY unlock_time DESC 
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
        // If late (> 10 mins) OR already answered:
        // $question remains null.
        // Falls through to "Next Question Locked" screen.
    }
}

// Calculate Remaining Time for Current Question
if ($question && isset($_SESSION['question_start_time'])) {
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
    <title>PopQuiz Challenge</title>
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
            border: 4px solid transparent;
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

    </style>
</head>
<body>

<div class="main-container">
    <div class="glass-card animate__animated animate__fadeInUp">
        
        <!-- HEADER -->
        <div class="welcome-header">
            <div class="d-flex align-items-center justify-content-between px-3">
                <div class="d-flex align-items-center">
                    <div class="user-avatar shadow-sm me-3">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                    <div class="text-start">
                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></h5>
                        <small class="text-muted">ID: <?php echo $_SESSION['student_number']; ?></small>
                    </div>
                </div>
                <a href="../logout" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
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
                    WHERE q.id NOT IN (SELECT question_id FROM answers WHERE student_number = ?)
                    AND q.unlock_time > ?
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
                    
                    <?php
                    // Get all upcoming questions
                    $upcoming = $pdo->prepare("
                        SELECT q.question_number, q.unlock_time 
                        FROM questions q 
                        WHERE q.id NOT IN (SELECT question_id FROM answers WHERE student_number = ?)
                        AND q.unlock_time > ?
                        ORDER BY q.unlock_time ASC
                        LIMIT 5
                    ");
                    $upcoming->execute([$student_number, $current_time]);
                    $upcoming_questions = $upcoming->fetchAll();
                    
                    if (count($upcoming_questions) > 0):
                    ?>
                    <div class="mt-4 text-start" style="max-width: 400px; margin: 0 auto;">
                        <h6 class="fw-bold mb-3">Upcoming Questions:</h6>
                        <div class="list-group">
                            <?php foreach ($upcoming_questions as $uq): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-question-circle me-2"></i>Question #<?php echo $uq['question_number']; ?></span>
                                    <span class="badge bg-primary rounded-pill"><?php echo date('g:i A', strtotime($uq['unlock_time'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
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
                        <i class="fas fa-clock fa-4x text-muted"></i>
                    </div>
                    <h3 class="fw-bold mb-2">No More Questions Today</h3>
                    <p class="text-muted">All scheduled questions for today have been completed.</p>
                    
                    <div class="mt-4">
                        <a href="view_result" class="btn btn-outline-primary rounded-pill">View History <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php } else { ?>
                <!-- ALL DONE -->
                <?php
                // Check if time_out was recorded today
                $today = date('Y-m-d');
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
                        <a href="view_result" class="btn btn-outline-primary rounded-pill">View History <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
