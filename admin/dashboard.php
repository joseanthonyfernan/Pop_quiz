<?php
require_once '../config/db.php';
require_once '../includes/auth_admin.php';

// Stats
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_quizzes = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$total_questions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();

// Today's Attendance Stats
$today = date('Y-m-d');
$present_today = $pdo->prepare("SELECT COUNT(*) FROM daily_attendance WHERE attendance_date = ? AND status = 'PRESENT'");
$present_today->execute([$today]);
$present_count = $present_today->fetchColumn();

$late_today = $pdo->prepare("SELECT COUNT(*) FROM daily_attendance WHERE attendance_date = ? AND status = 'LATE'");
$late_today->execute([$today]);
$late_count = $late_today->fetchColumn();

// Get Top Performers
$top_performers = $pdo->query("
    SELECT r.student_number, SUM(r.score) as total_score, s.fname, s.lname 
    FROM results r
    JOIN students s ON r.student_number = s.student_number
    GROUP BY r.student_number 
    ORDER BY total_score DESC 
    LIMIT 5
")->fetchAll();

// Recent Activity (Latest 5 submissions)
$recent_submissions = $pdo->query("
    SELECT r.*, s.fname, s.lname, q.title as quiz_title
    FROM results r
    JOIN students s ON r.student_number = s.student_number
    JOIN quizzes q ON r.quiz_id = q.id
    ORDER BY r.submitted_at DESC
    LIMIT 5
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | PopQuiz</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="../assets/js/devtools-blocker.js"></script>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
            --success-gradient: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            --info-gradient: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f7fafc;
            color: #2d3748;
            padding-top: 60px;
        }

        .navbar {
            background: #fff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1050;
            height: 60px;
        }

        .navbar-brand {
            font-weight: 700;
            color: #4a5568 !important;
        }

        .glass-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
        }

        .stat-card {
            padding: 25px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .bg-primary-soft { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .bg-success-soft { background: rgba(28, 200, 138, 0.1); color: #1cc88a; }
        .bg-warning-soft { background: rgba(246, 173, 85, 0.1); color: #f6ad55; }
        .bg-info-soft { background: rgba(54, 185, 204, 0.1); color: #36b9cc; }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #4a5568;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            margin-bottom: 8px;
        }

        .sidebar-link:hover {
            background: #edf2f7;
            color: #764ba2;
        }

        .sidebar-link i {
            width: 30px;
            font-size: 18px;
        }

        .sidebar-link.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
        }

        .performer-item {
            padding: 15px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .performer-item:last-child {
            border-bottom: none;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background: #edf2f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #4a5568;
        }

        .badge-pill {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
        }

        .main-content {
            padding: 40px 20px;
            margin-left: 16.666667%; /* Offset for fixed sidebar (col-lg-2) */
            width: 83.333333%;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            z-index: 1000;
            overflow-y: auto;
            background: #fff;
            border-right: 1px solid #edf2f7;
            padding-top: 20px;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .welcome-section {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 40px;
            color: #fff;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::after {
            content: '\f19d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>PopQuiz Admin
        </a>
        <div class="ms-auto d-flex align-items-center">
            <span class="text-muted me-3 d-none d-md-block">
                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('F d, Y'); ?>
            </span>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" id="userMenu" data-bs-toggle="dropdown">
                    <div class="avatar-circle me-2 bg-primary text-white">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                    <span class="fw-600"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3" aria-labelledby="userMenu">
                    <li><a class="dropdown-item py-2" href="../logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="sidebar col-lg-2 py-4 px-3 bg-white border-end d-none d-lg-block">
            <div class="mb-4 px-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 11px;">Main Menu</small>
            </div>
            <a href="dashboard" class="sidebar-link active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="manage_quizzes" class="sidebar-link">
                <i class="fas fa-book"></i> Quizzes
            </a>
            <a href="manage_questions" class="sidebar-link">
                <i class="fas fa-question-circle"></i> Questions
            </a>
            <a href="attendance" class="sidebar-link">
                <i class="fas fa-clipboard-check"></i> Attendance
            </a>
            <a href="results" class="sidebar-link">
                <i class="fas fa-chart-line"></i> Results
            </a>
            
            <div class="mt-5 mb-4 px-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 11px;">System</small>
            </div>
            <a href="browse_db" class="sidebar-link">
                <i class="fas fa-database"></i> Database
            </a>
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 main-content">
            <div class="welcome-section animate__animated animate__fadeIn">
                <h1 class="fw-bold mb-2">Welcome Back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
                <p class="mb-0 opacity-75">Here's what's happening today in your quiz portal.</p>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="glass-card stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                        <div class="stat-icon bg-primary-soft">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h6 class="text-muted mb-1">Total Students</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_students; ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <div class="stat-icon bg-success-soft">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h6 class="text-muted mb-1">Total Quizzes</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_quizzes; ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                        <div class="stat-icon bg-warning-soft">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h6 class="text-muted mb-1">Present Today</h6>
                        <h2 class="fw-bold mb-0"><?php echo $present_count; ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                        <div class="stat-icon bg-info-soft">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="text-muted mb-1">Late Today</h6>
                        <h2 class="fw-bold mb-0"><?php echo $late_count; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Top Performers -->
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 animate__animated animate__fadeInLeft">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Top Performers</h5>
                            <i class="fas fa-trophy text-warning"></i>
                        </div>
                        <div class="performer-list">
                            <?php if ($top_performers): ?>
                                <?php foreach ($top_performers as $i => $student): ?>
                                    <div class="performer-item d-flex align-items-center">
                                        <div class="avatar-circle me-3 <?php echo $i === 0 ? 'bg-primary text-white' : ''; ?>">
                                            <?php echo strtoupper(substr($student['fname'], 0, 1)); ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></h6>
                                            <small class="text-muted">ID: <?php echo $student['student_number']; ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary-soft text-primary rounded-pill"><?php echo $student['total_score']; ?> pts</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <p class="text-muted small">No data yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-md-8">
                    <div class="glass-card p-4 h-100 animate__animated animate__fadeInRight">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Recent Submissions</h5>
                            <a href="results" class="btn btn-sm btn-link text-primary fw-bold text-decoration-none">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light border-0">
                                    <tr>
                                        <th class="border-0">Student</th>
                                        <th class="border-0">Quiz</th>
                                        <th class="border-0">Score</th>
                                        <th class="border-0">Time</th>
                                        <th class="border-0">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_submissions): ?>
                                        <?php foreach ($recent_submissions as $res): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($res['fname'] . ' ' . $res['lname']); ?></div>
                                                    <small class="text-muted"><?php echo $res['student_number']; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($res['quiz_title']); ?></td>
                                                <td>
                                                    <span class="fw-bold text-primary"><?php echo $res['score']; ?></span>
                                                </td>
                                                <td><small><?php echo date('h:i A', strtotime($res['submitted_at'])); ?></small></td>
                                                <td>
                                                    <span class="badge bg-success-soft text-success rounded-pill px-3">Completed</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No recent submissions</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4 mt-4">
                <div class="col-12">
                    <div class="glass-card p-4 animate__animated animate__fadeInUp">
                        <h5 class="fw-bold mb-4">Quick Management</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="manage_quizzes" class="btn btn-light w-100 py-3 border rounded-4 d-flex align-items-center justify-content-center text-decoration-none text-dark">
                                    <i class="fas fa-plus-circle text-primary me-2"></i> Create New Quiz
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="attendance" class="btn btn-light w-100 py-3 border rounded-4 d-flex align-items-center justify-content-center text-decoration-none text-dark">
                                    <i class="fas fa-user-check text-success me-2"></i> Mark Attendance
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="results" class="btn btn-light w-100 py-3 border rounded-4 d-flex align-items-center justify-content-center text-decoration-none text-dark">
                                    <i class="fas fa-file-export text-warning me-2"></i> Export Reports
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button onclick="window.location.reload()" class="btn btn-light w-100 py-3 border rounded-4 d-flex align-items-center justify-content-center text-decoration-none text-dark">
                                    <i class="fas fa-sync text-info me-2"></i> Refresh Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
