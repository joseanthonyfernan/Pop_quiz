<?php
require_once '../config/db.php';
require_once '../includes/auth_student.php';
$base_url = "/Pop_quiz/";

$student_number = $_SESSION['student_number'];

// Get all answers from this student
// Join with questions table to get the question text and correct answer
// NEW: Also check if the 'selected_answer' matches 'correct_answer'
$sql = "SELECT a.*, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer 
        FROM answers a 
        JOIN questions q ON a.question_id = q.id 
        WHERE a.student_number = ? 
        ORDER BY a.submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$student_number]);
$history = $stmt->fetchAll();

// Calculate Stats
$total_answered = count($history);
$correct_count = 0;
foreach ($history as $h) {
    if ($h['selected_answer'] == $h['correct_answer']) {
        $correct_count++;
    }
}
$accuracy = ($total_answered > 0) ? round(($correct_count / $total_answered) * 100) : 0;

// Fetch Global Leaderboard (Top 10)
try {
    $leaderboard_sql = "SELECT s.fname, s.lname, SUM(r.score) as total_points, COUNT(r.id) as quizzes_taken
                        FROM results r
                        JOIN students s ON r.student_number = s.student_number
                        GROUP BY r.student_number, s.fname, s.lname
                        ORDER BY total_points DESC, quizzes_taken ASC
                        LIMIT 5";
    $leaderboard = $pdo->query($leaderboard_sql)->fetchAll();
} catch (Exception $e) {
    $leaderboard = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - PopQuiz</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <script src="<?php echo $base_url; ?>assets/js/devtools-blocker.js"></script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f8f9fa;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .history-item {
            border-left: 5px solid transparent;
            transition: all 0.2s;
        }
        .history-item.correct {
            border-left-color: #1cc88a;
            background-color: rgba(28, 200, 138, 0.05);
        }
        .history-item.wrong {
            border-left-color: #e74a3b;
            background-color: rgba(231, 74, 59, 0.05);
        }
        .leaderboard-card {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .rank-item {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }
        .rank-item:last-child { border-bottom: none; }
        .rank-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
        }
        .rank-1 { background: #ffd700; color: #fff; box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3); }
        .rank-2 { background: #c0c0c0; color: #fff; }
        .rank-3 { background: #cd7f32; color: #fff; }
        .rank-other { background: #f1f5f9; color: #64748b; }
    </style>
</head>
<body class="bg-light">

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">My Progress</h2>
        <a href="dashboard" class="btn btn-outline-primary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Back to Quiz</a>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card bg-white p-4 h-100">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-pencil-alt fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Total Answered</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_answered; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-white p-4 h-100">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-check fa-2x text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Correct Answers</h6>
                        <h2 class="fw-bold mb-0"><?php echo $correct_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-white p-4 h-100">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-chart-pie fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Accuracy</h6>
                        <h2 class="fw-bold mb-0"><?php echo $accuracy; ?>%</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Leaderboard Column -->
        <div class="col-lg-12">
            <h4 class="fw-bold mb-4 text-center">Top Performers</h4>
            <div class="card leaderboard-card bg-white">
                <div class="card-header bg-primary text-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-trophy me-2"></i> Leaderboard</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($leaderboard)): ?>
                        <div class="p-4 text-center text-muted">
                            <small>No records available</small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($leaderboard as $index => $player): ?>
                            <?php 
                                $rank = $index + 1;
                                $rank_class = $rank <= 3 ? "rank-$rank" : "rank-other";
                            ?>
                            <div class="rank-item">
                                <div class="rank-number <?php echo $rank_class; ?>">
                                    <?php echo $rank; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($player['fname'] . ' ' . $player['lname']); ?>
                                        <?php if ($player['fname'] . $player['lname'] === ($_SESSION['student_name'] ?? '')): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.6rem;">YOU</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo $player['quizzes_taken']; ?> Quizzes
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary"><?php echo number_format($player['total_points']); ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;">POINTS</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light border-0 py-3 text-center">
                    <small class="text-muted italic">Updated in real-time</small>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
