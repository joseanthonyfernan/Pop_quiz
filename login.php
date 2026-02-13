<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/includes/PHPMailer/Exception.php';
require __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/SMTP.php';

require_once 'config/db.php';

// --- ADVANCED SECURITY: HEADERS ---
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// --- ADVANCED SECURITY: SESSION BINDING ---
// Bind session to IP and User-Agent to prevent session hijacking
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=session_bound_violation");
        exit();
    }
}

// --- ADVANCED SECURITY: CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed. Possible Cross-Site Request Forgery detected.");
    }
}

// Redirect if already logged in - MUST BE BEFORE ANY OUTPUT
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard");
    exit();
}
if (isset($_SESSION['student_number'])) {
    header("Location: student/dashboard");
    exit();
}

$error = '';
$success = '';

// Function to send OTP via PHPMailer
function sendOTP($to, $otp) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'hapersounds@gmail.com';               // SMTP username
        $mail->Password   = 'pwcd nvch mzhc vflo';                  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        // Recipients
        $mail->setFrom('hapersounds@gmail.com', 'PopQuiz Admin');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Admin Login OTP';
        $mail->Body    = "<h3>Admin Login Verification</h3><p>Your OTP code is: <strong>$otp</strong></p><p>This code expires in 5 minutes.</p>";
        $mail->AltBody = "Your OTP code is: $otp. This code expires in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Return error message for debugging if needed, or just false
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle OTP Cancellation
if (isset($_GET['cancel_otp'])) {
    // Note: We don't verify CSRF on GET for simplicity here, but the session is wiped anyway
    unset($_SESSION['verify_otp'], $_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['temp_user'], $_SESSION['otp_attempts']);
    header("Location: login.php");
    exit();
}

// Handle OTP Resend
if (isset($_GET['resend_otp']) && isset($_SESSION['verify_otp'])) {
    if (isset($_SESSION['temp_user'])) {
        // RATE LIMIT: Resend Cooldown (e.g., 60 seconds)
        $last_sent = $_SESSION['last_otp_resend'] ?? 0;
        if (time() - $last_sent < 60) {
            $error = "Please wait a moment before requesting another code.";
        } else {
            $_SESSION['otp'] = random_int(100000, 999999);
            $_SESSION['otp_expiry'] = time() + 300;
            $_SESSION['last_otp_resend'] = time();
            $_SESSION['otp_attempts'] = 0; // Reset attempts on resend
            
            if (sendOTP($_SESSION['temp_user']['email'], $_SESSION['otp'])) {
                $success = "A new OTP has been sent to your email.";
            } else {
                $error = "Failed to send OTP. Please check your email configuration.";
            }
        }
    }
}

// Handle OTP Verification
if (isset($_POST['otp_code']) && isset($_SESSION['verify_otp'])) {
    verify_csrf(); // Protect against CSRF

    if (isset($_SESSION['otp'])) {
        $user_otp = trim($_POST['otp_code']);
        $stored_otp = (string)$_SESSION['otp'];
        
        // RATE LIMIT: Check attempts
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        
        if ($_SESSION['otp_attempts'] > 3) {
            $error = "Too many failed attempts. Please login again to request a new code.";
            unset($_SESSION['verify_otp'], $_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['temp_user'], $_SESSION['otp_attempts']);
        } elseif (time() > $_SESSION['otp_expiry']) {
            $error = "OTP has expired. Please login again.";
            unset($_SESSION['verify_otp'], $_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['temp_user'], $_SESSION['otp_attempts']);
        } elseif (hash_equals($stored_otp, $user_otp)) {
            // OTP Correct - Login User
            if (isset($_SESSION['temp_user']) && !empty($_SESSION['temp_user'])) {
                $user = $_SESSION['temp_user'];
                
                // IMPORTANT: Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = 'admin';
                
                // BIND SESSION TO DEVICE
                $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                
                // Determine redirect
                $redirect_url = "admin/dashboard";
                if (isset($_GET['redirect'])) {
                    $redirect_url = $_GET['redirect'];
                }
                
                // Cleanup
                unset($_SESSION['temp_user'], $_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['verify_otp'], $_SESSION['otp_attempts'], $_SESSION['last_otp_resend'], $_SESSION['failed_login_attempts']);
                
                header("Location: " . $redirect_url);
                exit();
            } else {
                $error = "Session data lost. Please login again.";
                unset($_SESSION['verify_otp'], $_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['temp_user'], $_SESSION['otp_attempts']);
            }
        } else {
            $remaining = 3 - $_SESSION['otp_attempts'];
            $error = "Invalid OTP. You have $remaining attempts left.";
        }
    } else {
        header("Location: login.php");
        exit();
    }
}

// Handle Initial Login
if (isset($_POST['admin_login'])) {
    verify_csrf(); // Protect against CSRF

    // RATE LIMIT: Account Lockout (5 attempts, then 15 min wait)
    $attempts = $_SESSION['failed_login_attempts'] ?? 0;
    $lockout_time = $_SESSION['lockout_time'] ?? 0;
    
    if (time() < $lockout_time) {
        $remaining_lock = ceil(($lockout_time - time()) / 60);
        $error = "Too many failed login attempts. Please wait $remaining_lock minutes.";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Reset failed attempts on password success
            unset($_SESSION['failed_login_attempts'], $_SESSION['lockout_time']);

            // Start OTP process
            $_SESSION['temp_user'] = $user;
            $_SESSION['otp'] = random_int(100000, 999999);
            $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
            $_SESSION['verify_otp'] = true;
            $_SESSION['otp_attempts'] = 0;
            $_SESSION['last_otp_resend'] = time();
            
            // Send OTP via PHPMailer
            if (sendOTP($user['email'], $_SESSION['otp'])) {
                // Success
            } else {
                $error = "Warning: Could not send OTP email. Please check configuration.";
            }
            
            header("Location: login.php");
            exit();
        } else {
            // Record failed attempt
            $_SESSION['failed_login_attempts'] = ($_SESSION['failed_login_attempts'] ?? 0) + 1;
            if ($_SESSION['failed_login_attempts'] >= 5) {
                $_SESSION['lockout_time'] = time() + (15 * 60); // 15 Minute Lockout
                $error = "Too many failed login attempts. Account locked for 15 minutes.";
            } else {
                $left = 5 - $_SESSION['failed_login_attempts'];
                $error = "Invalid admin credentials. $left attempts remaining.";
            }
        }
    }
}

// NOW WE CAN START OUTPUT
require_once 'includes/header.php';
?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-12 col-md-5 col-lg-4">
        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-user-shield fa-2x"></i>
            </div>
            <h2 class="fw-bold">Teacher Login</h2>
            <p class="text-muted">Access your management panel</p>
        </div>

        <div class="card shadow-lg p-3 p-md-4 border-0 animate__animated animate__fadeInUp">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small shadow-sm"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success py-2 small shadow-sm"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['verify_otp']) && $_SESSION['verify_otp']): ?>
                <!-- OTP Verification Form -->
                <div class="text-center mb-4">
                    <p class="mb-0 text-muted">We sent a 6-digit code to</p>
                    <strong class="text-dark"><?php echo isset($_SESSION['temp_user']['email']) ? htmlspecialchars($_SESSION['temp_user']['email']) : 'your email'; ?></strong>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted uppercase">Enter Verification Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                            <input type="text" name="otp_code" class="form-control border-start-0 text-center fw-bold letter-spacing-2" placeholder="xxxxxx" required pattern="[0-9]{6}" maxlength="6" style="letter-spacing: 5px; font-size: 1.2rem;">
                        </div>
                    </div>
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            Verify & Login <i class="fas fa-check-circle ms-2"></i>
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="login.php?cancel_otp=1" class="text-decoration-none text-muted small me-2"><i class="fas fa-arrow-left me-1"></i> Cancel</a>
                        <span class="text-muted">|</span>
                        <a href="login.php?resend_otp=1" class="text-decoration-none text-primary small ms-2">Resend Code</a>
                    </div>
                </form>
            <?php else: ?>
                <!-- Standard Login Form -->
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="admin_login" value="1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted uppercase">Admin Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="admin@popquiz.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            Sign In <i class="fas fa-sign-in-alt ms-2"></i>
                        </button>
                    </div>
                    <div class="text-center">
                        <a href="index" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Student Portal</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
