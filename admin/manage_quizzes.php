<?php
require_once '../config/db.php';
require_once '../includes/auth_admin.php';

// 1. Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = "Quiz deleted successfully!";
    header("Location: manage_quizzes");
    exit();
}

// 2. Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    $stmt = $pdo->prepare("SELECT status FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    
    $new_status = ($current == 'ON') ? 'OFF' : 'ON';
    $stmt = $pdo->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    
    $_SESSION['message'] = "Quiz status successfully changed to $new_status!";
    header("Location: manage_quizzes");
    exit();
}

// 3. Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quiz_action'])) {
    $title = $_POST['title'];
    $schedule_date = $_POST['schedule_date'];
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, schedule_date = ? WHERE id = ?");
        $stmt->execute([$title, $schedule_date, $_POST['id']]);
        $_SESSION['message'] = "Quiz updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO quizzes (title, schedule_date, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $schedule_date, $user_id]);
        $_SESSION['message'] = "Quiz created successfully!";
    }
    header("Location: manage_quizzes");
    exit();
}

// Prepare data for view
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$quizzes = $pdo->query("SELECT * FROM quizzes ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes | PopQuiz Admin</title>
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
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            color: #718096;
            background: #f8fafc;
            border-bottom: 2px solid #edf2f7;
        }

        .badge-pill {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 11px;
        }

        .bg-success-soft { background: rgba(28, 200, 138, 0.1); color: #1cc88a; }
        .bg-danger-soft { background: rgba(231, 74, 59, 0.1); color: #e74a3b; }
        .bg-info-soft { background: rgba(54, 185, 204, 0.1); color: #36b9cc; }
        .bg-warning-soft { background: rgba(246, 173, 85, 0.1); color: #f6ad55; }
        
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            border-bottom: 1px solid #edf2f7;
            padding: 25px;
        }

        .modal-footer {
            border-top: 1px solid #edf2f7;
            padding: 20px 25px;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 15px;
            border-color: #e2e8f0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>PopQuiz Admin
        </a>
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" id="userMenu" data-bs-toggle="dropdown">
                    <div class="avatar-circle me-2 bg-primary text-white">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                    <span class="fw-600 d-none d-md-block"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3" aria-labelledby="userMenu">
                    <li><a class="dropdown-item py-2 text-danger" href="../logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
            <a href="dashboard" class="sidebar-link">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="manage_quizzes" class="sidebar-link active">
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
            <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h2 class="fw-bold mb-1">Manage Quizzes</h2>
                    <p class="text-muted mb-0">Create and organize your quiz assessments.</p>
                </div>
                <button class="btn btn-primary btn-action shadow-sm" data-bs-toggle="modal" data-bs-target="#quizModal">
                    <i class="fas fa-plus me-2"></i>Create New Quiz
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 animate__animated animate__fadeInDown" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="glass-card animate__animated animate__fadeIn">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Title</th>
                                <th class="py-3">Schedule Date</th>
                                <th class="py-3">Questions</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Created Date</th>
                                <th class="text-end px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $quiz): 
                                $q_count = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE quiz_id = ?");
                                $q_count->execute([$quiz['id']]);
                                $count = $q_count->fetchColumn();
                            ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($quiz['title']); ?></div>
                                    <small class="text-muted">ID: #<?php echo $quiz['id']; ?></small>
                                </td>
                                <td>
                                    <?php if ($quiz['schedule_date']): ?>
                                        <span class="badge badge-pill bg-info-soft">
                                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('M d, Y', strtotime($quiz['schedule_date'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Not Scheduled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-pill bg-warning-soft text-warning">
                                        <i class="fas fa-list-ul me-1"></i> <?php echo $count; ?> Items
                                    </span>
                                </td>
                                <td>
                                    <a href="?toggle_status=<?php echo $quiz['id']; ?>" class="text-decoration-none">
                                        <?php if ($quiz['status'] == 'ON'): ?>
                                            <span class="badge badge-pill bg-success-soft">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="badge badge-pill bg-danger-soft">INACTIVE</span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="text-muted"><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                                <td class="text-end px-4">
                                    <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                        <a href="manage_questions?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-white border-end" title="Manage Questions">
                                            <i class="fas fa-tasks text-primary"></i>
                                        </a>
                                        <button class="btn btn-sm btn-white border-end" onclick="editQuiz(<?php echo $quiz['id']; ?>, '<?php echo addslashes($quiz['title']); ?>', '<?php echo $quiz['schedule_date']; ?>')" title="Edit">
                                            <i class="fas fa-edit text-warning"></i>
                                        </button>
                                        <a href="?delete=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-white" onclick="return confirm('Are you sure you want to delete this quiz?')" title="Delete">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($quizzes)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-book-open fa-3x mb-3 opacity-25"></i>
                                    <p>No quizzes found. Create your first one to get started!</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quiz Modal -->
<div class="modal fade" id="quizModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <input type="hidden" name="quiz_action" value="1">
            <div class="modal-header">
                <h5 class="fw-bold mb-0" id="modalTitle">Create New Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="quiz_id">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Quiz Title</label>
                    <input type="text" name="title" id="quiz_title" class="form-control" placeholder="e.g. Mathematics Basics" required>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold small text-muted text-uppercase">Schedule Date</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-day text-muted"></i></span>
                        <input type="date" name="schedule_date" id="quiz_date" class="form-control border-start-0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editQuiz(id, title, date) {
    document.getElementById('modalTitle').innerText = 'Edit Quiz Details';
    document.getElementById('quiz_id').value = id;
    document.getElementById('quiz_title').value = title;
    document.getElementById('quiz_date').value = date;
    var myModal = new bootstrap.Modal(document.getElementById('quizModal'));
    myModal.show();
}

document.getElementById('quizModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').innerText = 'Create New Quiz';
    document.getElementById('quiz_id').value = '';
    document.getElementById('quiz_title').value = '';
    document.getElementById('quiz_date').value = '';
});
</script>

</body>
</html>
