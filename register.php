<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        $success = "Registration successful! You can now login.";
    } catch (PDOException $e) {
        $error = "Registration failed. Email might already exist.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-lg p-3 p-md-4 login-card mt-5">
            <h3 class="text-center mb-4">Create Account</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create password" required>
                </div>
                <input type="hidden" name="role" value="admin">
                <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Register Admin</button>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login" class="text-decoration-none text-primary fw-bold">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
