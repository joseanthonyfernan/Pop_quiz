<?php
require_once '../config/db.php';
require_once '../includes/auth_admin.php';

$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id) {
    header("Location: manage_quizzes");
    exit();
}

// Get quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header("Location: manage_quizzes");
    exit();
}

// Handle Delete Question
if (isset($_GET['delete'])) {
    $q_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?");
    $stmt->execute([$q_id, $quiz_id]);
    $_SESSION['message'] = "Question removed successfully.";
    header("Location: manage_questions?quiz_id=$quiz_id");
    exit();
}

// Handle Add/Edit Question
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = $_POST['question'];
    $question_num = $_POST['question_number'] ?? null;
    $unlock_time = $_POST['unlock_time'] ?? null;
    $opt_a = $_POST['option_a'];
    $opt_b = $_POST['option_b'];
    $opt_c = $_POST['option_c'];
    $opt_d = $_POST['option_d'];
    $correct = $_POST['correct_answer'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE questions SET question = ?, question_number = ?, unlock_time = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ? WHERE id = ?");
        $stmt->execute([$question_text, $question_num, $unlock_time, $opt_a, $opt_b, $opt_c, $opt_d, $correct, $_POST['id']]);
        $_SESSION['message'] = "Question updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question, question_number, unlock_time, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$quiz_id, $question_text, $question_num, $unlock_time, $opt_a, $opt_b, $opt_c, $opt_d, $correct]);
        $_SESSION['message'] = "Question added successfully!";
    }
    header("Location: manage_questions?quiz_id=$quiz_id");
    exit();
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$questions_stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number ASC, id ASC");
$questions_stmt->execute([$quiz_id]);
$questions = $questions_stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions | PopQuiz Admin</title>
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
            overflow: hidden;
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

        .question-card {
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.2s;
        }

        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        .question-header {
            background: #f8fafc;
            padding: 20px 30px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .question-body {
            padding: 30px;
        }

        .option-box {
            padding: 15px 20px;
            border-radius: 12px;
            border: 2px solid #edf2f7;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .option-box.correct {
            border-color: #1cc88a;
            background: rgba(28, 200, 138, 0.05);
        }

        .option-letter {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            color: #64748b;
        }

        .correct .option-letter {
            background: #1cc88a;
            color: #fff;
        }

        .badge-pill {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 11px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 15px;
        }

        .modal-content {
            border-radius: 24px;
            border: none;
        }

        .breadcrumb-item a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 600;
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
            <nav aria-label="breadcrumb" class="mb-4 animate__animated animate__fadeIn">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="manage_quizzes">Quizzes</a></li>
                    <li class="breadcrumb-item active text-muted"><?php echo htmlspecialchars($quiz['title']); ?> Questions</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h2 class="fw-bold mb-1">Manage Questions</h2>
                    <p class="text-muted mb-0">Quiz: <strong><?php echo htmlspecialchars($quiz['title']); ?></strong></p>
                </div>
                <button class="btn btn-primary btn-action shadow-sm" data-bs-toggle="modal" data-bs-target="#questionModal">
                    <i class="fas fa-plus me-2"></i>Add Question
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInDown" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <?php foreach ($questions as $index => $q): ?>
                    <div class="question-card shadow-sm animate__animated animate__fadeInUp" style="animation-delay: <?php echo $index * 0.05; ?>s">
                        <div class="question-header">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">#<?php echo $q['question_number'] ?? ($index + 1); ?></span>
                                <h6 class="fw-bold mb-0 text-muted">Question ID: <?php echo $q['id']; ?></h6>
                                <?php if ($q['unlock_time']): ?>
                                    <span class="badge bg-info-soft text-info ms-3">
                                        <i class="fas fa-clock me-1"></i> Unlocks: <?php echo date('h:i A', strtotime($q['unlock_time'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light border" onclick='editQuestion(<?php echo json_encode($q); ?>)'>
                                    <i class="fas fa-edit text-warning"></i>
                                </button>
                                <a href="?quiz_id=<?php echo $quiz_id; ?>&delete=<?php echo $q['id']; ?>" class="btn btn-sm btn-light border" onclick="return confirm('Delete this question?')">
                                    <i class="fas fa-trash text-danger"></i>
                                </a>
                            </div>
                        </div>
                        <div class="question-body">
                            <h4 class="fw-bold mb-4"><?php echo htmlspecialchars($q['question']); ?></h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="option-box <?php echo $q['correct_answer'] == 'a' ? 'correct' : ''; ?>">
                                        <div class="option-letter">A</div>
                                        <div class="option-text"><?php echo htmlspecialchars($q['option_a']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="option-box <?php echo $q['correct_answer'] == 'b' ? 'correct' : ''; ?>">
                                        <div class="option-letter">B</div>
                                        <div class="option-text"><?php echo htmlspecialchars($q['option_b']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="option-box <?php echo $q['correct_answer'] == 'c' ? 'correct' : ''; ?>">
                                        <div class="option-letter">C</div>
                                        <div class="option-text"><?php echo htmlspecialchars($q['option_c']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="option-box <?php echo $q['correct_answer'] == 'd' ? 'correct' : ''; ?>">
                                        <div class="option-letter">D</div>
                                        <div class="option-text"><?php echo htmlspecialchars($q['option_d']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($questions)): ?>
                        <div class="glass-card text-center py-5">
                            <i class="fas fa-question-circle fa-4x mb-3 opacity-25"></i>
                            <h5 class="text-muted">No questions yet.</h5>
                            <p class="text-muted mb-4">Add some questions to make this quiz live!</p>
                            <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#questionModal">
                                <i class="fas fa-plus me-2"></i>Add First Question
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="fw-bold mb-0" id="qModalTitle">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="q_id">
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Question Order #</label>
                        <input type="number" name="question_number" id="q_num" class="form-control" placeholder="e.g. 1">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small text-muted text-uppercase">Unlock Time (Optional)</label>
                        <input type="time" name="unlock_time" id="q_unlock" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Question Content</label>
                    <textarea name="question" id="q_text" class="form-control" rows="3" placeholder="Type your question here..." required></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6 text-start">
                        <label class="form-label fw-bold small text-muted text-uppercase">Option A</label>
                        <input type="text" name="option_a" id="q_a" class="form-control" required>
                    </div>
                    <div class="col-md-6 text-start">
                        <label class="form-label fw-bold small text-muted text-uppercase">Option B</label>
                        <input type="text" name="option_b" id="q_b" class="form-control" required>
                    </div>
                    <div class="col-md-6 text-start">
                        <label class="form-label fw-bold small text-muted text-uppercase">Option C</label>
                        <input type="text" name="option_c" id="q_c" class="form-control" required>
                    </div>
                    <div class="col-md-6 text-start">
                        <label class="form-label fw-bold small text-muted text-uppercase">Option D</label>
                        <input type="text" name="option_d" id="q_d" class="form-control" required>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold small text-muted text-uppercase">Correct Answer</label>
                    <select name="correct_answer" id="q_correct" class="form-select" required>
                        <option value="a">Option A</option>
                        <option value="b">Option B</option>
                        <option value="c">Option C</option>
                        <option value="d">Option D</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Save Question</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editQuestion(q) {
    document.getElementById('qModalTitle').innerText = 'Edit Question';
    document.getElementById('q_id').value = q.id;
    document.getElementById('q_num').value = q.question_number;
    document.getElementById('q_unlock').value = q.unlock_time;
    document.getElementById('q_text').value = q.question;
    document.getElementById('q_a').value = q.option_a;
    document.getElementById('q_b').value = q.option_b;
    document.getElementById('q_c').value = q.option_c;
    document.getElementById('q_d').value = q.option_d;
    document.getElementById('q_correct').value = q.correct_answer;
    var myModal = new bootstrap.Modal(document.getElementById('questionModal'));
    myModal.show();
}

document.getElementById('questionModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('qModalTitle').innerText = 'Add New Question';
    document.getElementById('q_id').value = '';
    document.getElementById('q_num').value = '';
    document.getElementById('q_unlock').value = '';
    document.getElementById('q_text').value = '';
    document.getElementById('q_a').value = '';
    document.getElementById('q_b').value = '';
    document.getElementById('q_c').value = '';
    document.getElementById('q_d').value = '';
    document.getElementById('q_correct').value = 'a';
});
</script>

</body>
</html>
