<?php
require_once '../config/db.php';
require_once '../includes/auth_admin.php';

// Get filters
$selected_date = $_GET['date'] ?? '';
$selected_year = $_GET['year_level'] ?? '';
$selected_section = $_GET['section'] ?? '';

// Fetch filter options
$years = $pdo->query("SELECT DISTINCT year_level FROM students ORDER BY year_level")->fetchAll(PDO::FETCH_COLUMN);

// Fetch sections based on year level
$section_query = "SELECT DISTINCT section FROM students";
$section_params = [];
if ($selected_year) {
    $section_query .= " WHERE year_level = :year";
    $section_params['year'] = $selected_year;
}
$section_query .= " ORDER BY section";

$section_stmt = $pdo->prepare($section_query);
$section_stmt->execute($section_params);
$sections = $section_stmt->fetchAll(PDO::FETCH_COLUMN);

// Reset selected section if it's not in the filtered list
if ($selected_section && !in_array($selected_section, $sections)) {
    $selected_section = '';
}

// Build Global Leaderboard Query
$query = "
    SELECT 
        a.student_number, 
        s.fname, s.lname, s.mname,
        COUNT(a.id) as total_answered,
        SUM(CASE WHEN q.correct_answer = a.selected_answer THEN 1 ELSE 0 END) as correct_count,
        MAX(a.submitted_at) as last_active
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    LEFT JOIN students s ON a.student_number = s.student_number
    WHERE 1=1
";

$params = [];

if ($selected_date) {
    $query .= " AND DATE(a.submitted_at) = :date";
    $params['date'] = $selected_date;
}

if ($selected_year) {
    $query .= " AND s.year_level = :year";
    $params['year'] = $selected_year;
}

if ($selected_section) {
    $query .= " AND s.section = :section";
    $params['section'] = $selected_section;
}

$query .= "
    GROUP BY a.student_number
    ORDER BY correct_count DESC, total_answered ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$all_leaderboard = $stmt->fetchAll();

// Pagination settings
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$total_records = count($all_leaderboard);
$total_pages = ceil($total_records / $limit);

// Slice for display
$leaderboard = array_slice($all_leaderboard, $offset, $limit);

// Fetch total questions for context
$q_count_query = "SELECT COUNT(*) FROM questions q JOIN quizzes z ON q.quiz_id = z.id WHERE 1=1";
$q_params = [];
if ($selected_date) {
    $q_count_query .= " AND z.schedule_date = :date";
    $q_params['date'] = $selected_date;
}
$total_q_stmt = $pdo->prepare($q_count_query);
$total_q_stmt->execute($q_params);
$total_questions_count = $total_q_stmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results & Leaderboard | PopQuiz Admin</title>
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
            --gold-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            --silver-gradient: linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%);
            --bronze-gradient: linear-gradient(135deg, #d38312 0%, #a83279 100%);
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

        .rank-badge {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .rank-1 { background: var(--gold-gradient); color: #fff; }
        .rank-2 { background: var(--silver-gradient); color: #fff; }
        .rank-3 { background: var(--bronze-gradient); color: #fff; }
        .rank-default { background: #f1f5f9; color: #64748b; }

        .progress {
            height: 8px;
            border-radius: 10px;
            background: #edf2f7;
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

        .accuracy-pill {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 11px;
        }

        .date-filter-box {
            background: #fff;
            border-radius: 15px;
            padding: 15px 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 1px solid #edf2f7;
        }

        .form-control-date {
            border: none;
            background: transparent;
            font-weight: 600;
            color: #4a5568;
            outline: none;
        }

        .filter-select {
            border: none;
            background: #fff;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 14px;
            color: #4a5568;
            font-weight: 500;
            outline: none;
            cursor: pointer;
            min-width: 120px;
        }

        @media print {
            .sidebar, .navbar, .date-filter-box, .btn, .alert, .dropdown-menu, .card-footer, .d-print-none {
                display: none !important;
            }
            .sidebar-link, .mt-5, .px-3 {
                display: none !important;
            }
            .col-lg-2 {
                display: none !important;
            }
            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .col-lg-10 {
                width: 100% !important;
                flex: 0 0 100%;
                max-width: 100%;
            }
            .glass-card {
                box-shadow: none !important;
                border: none !important;
            }
            body {
                background: #fff !important;
                padding-top: 0 !important;
            }
            .table th {
                background-color: #f8f9fa !important;
                color: #000 !important;
            }
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
        }
        .print-header {
            display: none;
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
        <div class="sidebar col-lg-2 py-4 px-3 bg-white border-end d-lg-block">
            <div class="mb-4 px-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 11px;">Main Menu</small>
            </div>
            <a href="dashboard" class="sidebar-link">
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
            <a href="results" class="sidebar-link active">
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
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h2 class="fw-bold mb-1">Global Leaderboard</h2>
                    <p class="text-muted mb-0">Ranking students based on performance metrics.</p>
                </div>
                
                <div class="date-filter-box d-flex align-items-center mt-3 mt-md-0 gap-3">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <!-- Date Filter -->
                        <div class="d-flex align-items-center border-end pe-3">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            <input type="date" name="date" class="form-control-date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                        </div>

                        <!-- Year Filter -->
                        <select name="year_level" class="filter-select bg-light" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Section Filter -->
                        <select name="section" class="filter-select bg-light" onchange="this.form.submit()">
                            <option value="">All Sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo htmlspecialchars($section); ?>" <?php echo $selected_section == $section ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($selected_date || $selected_year || $selected_section): ?>
                            <a href="results" class="btn btn-sm btn-light rounded-pill" title="Clear Filters">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                    
                    <button onclick="window.print()" class="btn btn-primary rounded-pill px-3">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>
            </div>

            <!-- Print Header -->
            <div class="print-header mb-4">
                <h3>Global Leaderboard Report</h3>
                <?php if($selected_date) echo "<p class='mb-0'>Date: " . date('F j, Y', strtotime($selected_date)) . "</p>"; ?>
                <?php if($selected_year) echo "<p class='mb-0'>Year Level: $selected_year</p>"; ?>
                <?php if($selected_section) echo "<p class='mb-0'>Section: $selected_section</p>"; ?>
            </div>

            <div class="alert bg-white border-0 shadow-sm rounded-4 p-3 mb-4 animate__animated animate__fadeInDown">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                        <i class="fas fa-info-circle text-info"></i>
                    </div>
                    <p class="mb-0 small text-muted">Showing results for 
                        <strong><?php echo $selected_date ? date('M d, Y', strtotime($selected_date)) : 'All Time'; ?></strong>
                        <?php if($selected_year) echo " | Year: <strong>$selected_year</strong>"; ?>
                        <?php if($selected_section) echo " | Section: <strong>$selected_section</strong>"; ?>
                    </p>
                </div>
            </div>

            <div class="glass-card animate__animated animate__fadeIn">
                <!-- Screen Version (Paginated) -->
                <div class="table-responsive d-print-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Rank</th>
                                <th class="py-3">Student</th>
                                <th class="py-3 text-center">Correct / Total</th>
                                <th class="py-3">Performance</th>
                                <th class="py-3">Last Active</th>
                                <th class="text-end px-4 py-3">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $index => $r): ?>
                            <?php 
                                $total_q_display = $total_questions_count; 
                                $accuracy = ($total_q_display > 0) ? round(($r['correct_count'] / $total_q_display) * 100) : 0;
                                $rank_class = $index == 0 ? 'rank-1' : ($index == 1 ? 'rank-2' : ($index == 2 ? 'rank-3' : 'rank-default'));
                            ?>
                            <tr class="<?php echo $index < 3 ? 'bg-light bg-opacity-10' : ''; ?>">
                                <td class="px-4">
                                    <div class="rank-badge <?php echo $rank_class; ?>">
                                        <?php echo $index + 1; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-white border shadow-sm fw-bold">
                                            <?php echo $r['fname'] ? strtoupper(substr($r['fname'], 0, 1)) : '?'; ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?php echo $r['fname'] ? htmlspecialchars($r['lname'] . ', ' . $r['fname']) : 'Unknown Student'; ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($r['student_number']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary"><?php echo $r['correct_count']; ?></span>
                                    <span class="text-muted">/ <?php echo $total_q_display; ?></span>
                                </td>
                                <td style="width: 200px;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2">
                                            <div class="progress-bar <?php echo $accuracy >= 80 ? 'bg-success' : ($accuracy >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 role="progressbar" style="width: <?php echo $accuracy; ?>%"></div>
                                        </div>
                                        <span class="small fw-bold <?php echo $accuracy >= 80 ? 'text-success' : ($accuracy >= 50 ? 'text-warning' : 'text-danger'); ?>">
                                            <?php echo $accuracy; ?>%
                                        </span>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    <i class="far fa-clock me-1"></i> <?php echo date('M d, h:i A', strtotime($r['last_active'])); ?>
                                </td>
                                <td class="text-end px-4">
                                    <h5 class="fw-bold text-primary mb-0"><?php echo $r['correct_count'] * 1; ?> <small class="text-muted fw-normal" style="font-size: 10px;">pts</small></h5>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($leaderboard)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-trophy fa-4x mb-3 opacity-10"></i>
                                    <p>No results have been submitted yet for the selected filters.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Print Version (Full List) -->
                <div class="d-none d-print-block">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="px-2 py-2" style="width: 50px;">Rank</th>
                                <th class="py-2">Student Name</th>
                                <th class="py-2">Student ID</th>
                                <th class="py-2 text-center">Correct / Total</th>
                                <th class="py-2 text-center">Accuracy</th>
                                <th class="text-end px-2 py-2">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_leaderboard as $index => $r): 
                                $acc = ($total_questions_count > 0) ? round(($r['correct_count'] / $total_questions_count) * 100) : 0;
                            ?>
                            <tr>
                                <td class="px-2 py-2 text-center small"><?php echo $index + 1; ?></td>
                                <td class="px-2 py-2 fw-bold small"><?php echo htmlspecialchars($r['lname'] . ', ' . $r['fname']); ?></td>
                                <td class="px-2 py-2 small"><?php echo htmlspecialchars($r['student_number']); ?></td>
                                <td class="px-2 py-2 text-center small"><?php echo $r['correct_count']; ?> / <?php echo $total_questions_count; ?></td>
                                <td class="px-2 py-2 text-center small"><?php echo $acc; ?>%</td>
                                <td class="text-end px-2 py-2 small fw-bold"><?php echo $r['correct_count']; ?> pts</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white border-top p-3 px-4 d-print-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing <span class="fw-600 text-dark"><?php echo $offset + 1; ?></span> to <span class="fw-600 text-dark"><?php echo min($offset + $limit, $total_records); ?></span> of <span class="fw-600 text-dark"><?php echo $total_records; ?></span> records
                        </div>
                        <nav>
                            <?php
                            $query_params = $_GET;
                            ?>
                            <ul class="pagination pagination-sm mb-0 gap-2">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <?php 
                                    $query_params['page'] = $page - 1;
                                    $prev_url = '?' . http_build_query($query_params);
                                    ?>
                                    <a class="page-link rounded-pill px-3" href="<?php echo $prev_url; ?>"><i class="fas fa-chevron-left me-1"></i> Prev</a>
                                </li>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <?php 
                                    $query_params['page'] = $page + 1;
                                    $next_url = '?' . http_build_query($query_params);
                                    ?>
                                    <a class="page-link rounded-pill px-3" href="<?php echo $next_url; ?>">Next <i class="fas fa-chevron-right ms-1"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

