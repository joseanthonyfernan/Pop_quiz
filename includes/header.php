<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_url = "/Pop_quiz/";
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

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base_url; ?>">
            <i class="fas fa-graduation-cap me-2"></i>PopQuiz
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['student_number'])): ?>
                    <li class="nav-item">
                        <span class="nav-link text-light opacity-75 d-none d-lg-block">
                            <i class="fas fa-user-circle me-1"></i> 
                            <?php echo $_SESSION['name'] ?? $_SESSION['student_name'] ?? 'User'; ?>
                        </span>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>admin/manage_quizzes">Quizzes</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>student/dashboard">My Quizzes</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger btn-sm text-white ms-lg-3 px-3 rounded-pill" href="<?php echo $base_url; ?>logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>index">Student Portal</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
