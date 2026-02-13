<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- ADVANCED SECURITY: HEADERS ---
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// --- ADVANCED SECURITY: SESSION BINDING ---
if (isset($_SESSION['student_number'])) {
    if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header("Location: index?error=session_invalid");
        exit();
    }
}

// --- ADVANCED SECURITY: CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security validation failed. Possible CSRF attempt.");
    }
}

$base_url = "/Pop_quiz/";

$error = '';

// Block access if already logged in (unless logging in as same user)
if (isset($_SESSION['student_number'])) {
    if (isset($_POST['check_student']) && isset($_POST['student_number'])) {
        // If trying to log in as a DIFFERENT student while session is active
        if ($_POST['student_number'] !== $_SESSION['student_number']) {
            $error = "This device is already in use. You cannot log in as another student.";
            // Prevent the login logic from running
            unset($_POST['check_student']);
        } else {
            // Same user - let them go to dashboard
            header("Location: student/dashboard");
            exit();
        }
    } else {
        // Normal access - redirect to dashboard
        header("Location: student/dashboard");
        exit();
    }
}

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard");
    exit();
}
$show_registration = false;
$temp_student_number = '';

// Step 1: Check Student Number
if (isset($_POST['check_student'])) {
    verify_csrf();

    // RATE LIMITING: Prevent probing student numbers (10 attempts, then 15 min lock)
    $attempts = $_SESSION['student_check_attempts'] ?? 0;
    $lock_time = $_SESSION['student_check_lockout'] ?? 0;

    if (time() < $lock_time) {
        $wait = ceil(($lock_time - time()) / 60);
        $error = "Too many attempts. Please try again in $wait minutes.";
    } else {
        $student_number = trim(filter_var($_POST['student_number'], FILTER_SANITIZE_STRING));
        
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_number = ?");
        $stmt->execute([$student_number]);
        $student = $stmt->fetch();

    if ($student) {
        // STRICT ONE-DEVICE POLICY
        // Check active_sessions table instead
        $check_sess = $pdo->prepare("SELECT * FROM active_sessions WHERE student_number = ?");
        $check_sess->execute([$student_number]);
        $active_session = $check_sess->fetch();
        
        $is_blocked = false;
        
        if ($active_session && !empty($active_session['session_token'])) {
            $last_active_time = strtotime($active_session['last_activity']);
            $current_time_ts = time();
            $time_diff = $current_time_ts - $last_active_time;
            
            // If active within 5 minutes
            if ($time_diff < (5 * 60)) {
                // Check if it's the SAME device (User Agent match)
                $stored_ua = $active_session['user_agent'] ?? '';
                $current_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                // If UA is different, it means ANOTHER device is trying to access
                if ($stored_ua !== $current_ua) {
                    $is_blocked = true;
                    $error = "This student number is already logged in on another device.";
                }
            }
        }
        
        if (!$is_blocked) {
            // Success
            unset($_SESSION['student_check_attempts'], $_SESSION['student_check_lockout']);

            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            // Set session
            $_SESSION['student_number'] = $student['student_number'];
            $_SESSION['student_name'] = $student['fname'] . ' ' . $student['lname'];
            $_SESSION['role'] = 'student';
            
            // BIND SESSION
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            // SINGLE DEVICE LOGIN ENFORCEMENT
            $session_token = bin2hex(random_bytes(32));
            $current_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ip_addr = $_SERVER['REMOTE_ADDR'] ?? '';
            
            // USE active_sessions table
            $token_stmt = $pdo->prepare("
                INSERT INTO active_sessions (student_number, session_token, user_agent, ip_address, last_activity) 
                VALUES (:sn, :st, :ua, :ip, NOW())
                ON DUPLICATE KEY UPDATE 
                session_token = :st, user_agent = :ua, ip_address = :ip, last_activity = NOW() 
            ");
            $token_stmt->execute([
                ':sn' => $student_number, 
                ':st' => $session_token, 
                ':ua' => $current_ua,
                ':ip' => $ip_addr
            ]);
            $_SESSION['session_token'] = $session_token;
            
            // AUTOMATIC ATTENDANCE RECORDING
            $today = date('Y-m-d');
            $current_time = date('H:i:s');
            $late_cutoff = '09:30:00'; // PRESENT if <= 9:30 AM
            $late_end = '17:30:00';    // LATE if <= 5:30 PM, else ABSENT
            
            // Check if already recorded attendance today
            $check_attendance = $pdo->prepare("SELECT id, device_fingerprint FROM daily_attendance WHERE student_number = ? AND attendance_date = ?");
            $check_attendance->execute([$student_number, $today]);
            $attendance_record = $check_attendance->fetch();
            
            if ($attendance_record) {
                // Attendance exists - Check if device matches
                $stored_fingerprint = $attendance_record['device_fingerprint'];
                
                // If fingerprint exists (not null) and doesn't match current UA
                if (!empty($stored_fingerprint) && $stored_fingerprint !== $current_ua) {
                    // BLOCK LOGIN - Attendance locked to another device
                    session_unset();
                    session_destroy();
                    $error = "Attendance Locked: You have already clocked in using another device today. You must use that device.";
                    $show_registration = false; // Ensure login form shows
                    // Prevent redirect header
                    // We must exit this block but display the error.
                    // The easiest way is to NOT redirect and let the page render with $error.
                    // However, we are deep in logic.
                    // We need to stop execution here.
                } else {
                    // Device matches or no fingerprint stored (legacy) - Allow login
                    header("Location: student/dashboard");
                    exit();
                }
            } else {
                // No attendance yet - NEW CHECK: Has this device been used by ANOTHER student today?
                $check_device = $pdo->prepare("SELECT student_number FROM daily_attendance WHERE attendance_date = ? AND device_fingerprint = ? AND student_number != ?");
                $check_device->execute([$today, $current_ua, $student_number]);
                if ($check_device->fetch()) {
                    session_unset();
                    session_destroy();
                    $error = "This device is already used. You cannot mark attendance on this device.";
                    $show_registration = false;
                } else {
                    // Device clean - Record attendance
                    // Determine status based on login time
                    if ($current_time <= $late_cutoff) {
                        $status = 'PRESENT';
                    } elseif ($current_time <= $late_end) {
                        $status = 'LATE';
                    } else {
                        $status = 'ABSENT';
                    }
    
                    // Fixed Log-outs
                    if ($current_time <= '12:45:00') {
                        $time_out = '12:45:00';
                    } else {
                        $time_out = '17:45:00'; // 5:45 PM
                    }
                    
                    // Record attendance automatically with DEVICE FINGERPRINT
                    try {
                        $stmt_att = $pdo->prepare("INSERT INTO daily_attendance (student_number, attendance_date, time_in, time_out, status, device_fingerprint) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt_att->execute([$student_number, $today, $current_time, $time_out, $status, $current_ua]);
                    } catch (Exception $e) {
                        // Silent fail
                    }
                    
                    header("Location: student/dashboard");
                    exit();
                }
            }
        }
    } else {
        // Record failed attempt
        $_SESSION['student_check_attempts'] = ($_SESSION['student_check_attempts'] ?? 0) + 1;
        if ($_SESSION['student_check_attempts'] >= 10) {
            $_SESSION['student_check_lockout'] = time() + (15 * 60);
            $error = "Too many attempts. Account lookup locked for 15 minutes.";
        }
        
        $show_registration = true;
        $temp_student_number = filter_var($student_number, FILTER_SANITIZE_STRING);
    }
}
}

// Step 2: Register New Student
if (isset($_POST['register_student'])) {
    verify_csrf();

    $student_number = filter_var($_POST['student_number'], FILTER_SANITIZE_STRING);
    $fname = trim(filter_var($_POST['fname'], FILTER_SANITIZE_STRING));
    $lname = trim(filter_var($_POST['lname'], FILTER_SANITIZE_STRING));
    $mname = trim(filter_var($_POST['mname'], FILTER_SANITIZE_STRING));
    $section = trim(filter_var($_POST['section'], FILTER_SANITIZE_STRING));
    $year_level = trim(filter_var($_POST['year_level'], FILTER_SANITIZE_STRING));
    $department = trim(filter_var($_POST['department'], FILTER_SANITIZE_STRING));

    try {
        $stmt = $pdo->prepare("INSERT INTO students (student_number, fname, lname, mname, section, year_level, department) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_number, $fname, $lname, $mname, $section, $year_level, $department]);
        
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        // Set session
        $_SESSION['student_number'] = $student_number;
        $_SESSION['student_name'] = $fname . ' ' . $lname;
        $_SESSION['role'] = 'student';
        
        // BIND SESSION
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // SINGLE DEVICE LOGIN ENFORCEMENT
        $session_token = bin2hex(random_bytes(32));
        $current_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_addr = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Store session in dedicated table
        $token_stmt = $pdo->prepare("
            INSERT INTO active_sessions (student_number, session_token, user_agent, ip_address, last_activity) 
            VALUES (:sn, :st, :ua, :ip, NOW())
            ON DUPLICATE KEY UPDATE 
            session_token = :st, user_agent = :ua, ip_address = :ip, last_activity = NOW()
        ");
        $token_stmt->execute([
            ':sn' => $student_number,
            ':st' => $session_token,
            ':ua' => $current_ua,
            ':ip' => $ip_addr
        ]);
        $_SESSION['session_token'] = $session_token;
        
        // AUTOMATIC ATTENDANCE RECORDING
        $today = date('Y-m-d');
        $current_time = date('H:i:s');
        $late_cutoff = '09:30:00';
        $late_end = '17:30:00';
        
        // NEW CHECK: Has this device been used by ANOTHER student today?
        $check_device = $pdo->prepare("SELECT student_number FROM daily_attendance WHERE attendance_date = ? AND device_fingerprint = ? AND student_number != ?");
        $check_device->execute([$today, $current_ua, $student_number]);
        $is_blocked_device = false;
        
        if ($check_device->fetch()) {
             $is_blocked_device = true;
             session_unset();
             session_destroy();
             $error = "This device is already used. You cannot mark attendance on this device.";
             $show_registration = false;
        }
        
        if (!$is_blocked_device) { 
            if ($current_time <= $late_cutoff) {
                $status = 'PRESENT';
            } elseif ($current_time <= $late_end) {
                $status = 'LATE';
            } else {
                $status = 'ABSENT';
            }

            // Fixed Log-outs
            if ($current_time <= '12:45:00') {
                $time_out = '12:45:00';
            } else {
                $time_out = '17:45:00'; // 5:45 PM
            }
            
            try {
                $stmt_att = $pdo->prepare("INSERT INTO daily_attendance (student_number, attendance_date, time_in, time_out, status, device_fingerprint) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_att->execute([$student_number, $today, $current_time, $time_out, $status, $current_ua]);
            } catch (Exception $e) {
                // Silent fail
            }
        }
        
        header("Location: student/dashboard");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to register. Please try again.";
        $show_registration = true;
        $temp_student_number = $student_number;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pop Quiz System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <script src="<?php echo $base_url; ?>assets/js/devtools-blocker.js"></script>
</head>
<body class="bg-light">

<main class="container py-4">
<br>
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-12 col-md-7 col-lg-5">
        <div class="text-center mb-4 animate__animated animate__fadeIn">
            <h1 class="display-5 fw-bold text-primary">PopQuiz</h1>
            <p class="text-muted">Fastest way to test your knowledge</p>
        </div>
        
        <?php if (!$show_registration): ?>  
            <!-- Student Entrance -->
            <div class="card shadow-lg p-3 p-md-4 login-card border-0 mb-4 animate__animated animate__zoomIn">
                <h4 class="text-center mb-4 fw-600">Student Entrance</h4>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Enter Student Number</label>
                        <div class="input-group input-group-lg border rounded">
                            <span class="input-group-text bg-white border-0"><i class="fas fa-id-card text-muted"></i></span>
                            <input type="text" name="student_number" class="form-control border-0" placeholder="e.g. 2024-0001" required autofocus>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" name="check_student" class="btn btn-primary btn-lg py-3 mt-2 shadow-sm rounded-pill fw-bold">
                            Get Started <i class="fas fa-rocket ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
                        
        <?php else: ?>
            <!-- Student Detailed Registration -->
            <div class="card shadow-lg p-3 p-md-4 login-card border-0 mb-4 animate__animated animate__fadeInRight">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Complete Profile</h4>
                    <span class="badge bg-info text-dark px-3 rounded-pill"><?php echo $temp_student_number; ?></span>
                </div>
                <p class="text-muted small mb-4">First time here? Please provide your details to join the quiz.</p>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="student_number" value="<?php echo htmlspecialchars($temp_student_number); ?>">
                    
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">First Name</label>
                            <input type="text" name="fname" class="form-control" placeholder="John" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Last Name</label>
                            <input type="text" name="lname" class="form-control" placeholder="Doe" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Middle Name (Optional)</label>
                        <input type="text" name="mname" class="form-control" placeholder="Smith">
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Year Level</label>
                            <select name="year_level" class="form-select" required>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Section</label>
                            <input type="text" name="section" class="form-control" placeholder="A, B, CS101" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="College of Engineering" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="register_student" class="btn btn-success btn-lg py-3 shadow-sm rounded-pill fw-bold">
                            Save & Start Quiz
                        </button>
                        <a href="index" class="btn btn-link text-muted btn-sm">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>