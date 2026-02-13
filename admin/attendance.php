<?php
require_once '../config/db.php';
require_once '../includes/auth_admin.php';

// Get filters
$selected_date = $_GET['date'] ?? date('Y-m-d');
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

// Pagination settings
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Fetch all students and their attendance for the selected date
$query = "
    SELECT 
        s.student_number,
        s.fname,
        s.lname,
        s.mname,
        da.time_in,
        da.time_out,
        da.status,
        da.created_at
    FROM students s

    LEFT JOIN daily_attendance da ON s.student_number = da.student_number AND da.attendance_date = :date
    WHERE 1=1
";

$params = ['date' => $selected_date];

if ($selected_year) {
    $query .= " AND s.year_level = :year";
    $params['year'] = $selected_year;
}

if ($selected_section) {
    $query .= " AND s.section = :section";
    $params['section'] = $selected_section;
}

$query .= "
    ORDER BY 
        CASE 
            WHEN da.status = 'PRESENT' THEN 1
            WHEN da.status = 'LATE' THEN 2
            WHEN da.status = 'ABSENT' OR da.status IS NULL THEN 3
        END,
        da.time_in ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$all_attendance_records = $stmt->fetchAll();

// Calculate statistics
$total_students = count($all_attendance_records);
$present_count = 0;
$late_count = 0;
$absent_count = 0;

foreach ($all_attendance_records as $record) {
    if ($record['status'] == 'PRESENT') {
        $present_count++;
    } elseif ($record['status'] == 'LATE') {
        $late_count++;
    } else {
        $absent_count++;
    }
}

// Paginate results for display
$total_records = count($all_attendance_records);
$total_pages = ceil($total_records / $limit);
$attendance_records = array_slice($all_attendance_records, $offset, $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Monitoring | PopQuiz Admin</title>
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
            --success-gradient: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            --warning-gradient: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
            --danger-gradient: linear-gradient(135deg, #e74a3b 0%, #be2e21 100%);
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

        .stat-card-mini {
            padding: 20px;
            border-radius: 18px;
            color: #fff;
            height: 100%;
        }

        .bg-primary-grad { background: var(--primary-gradient); }
        .bg-success-grad { background: var(--success-gradient); }
        .bg-warning-grad { background: var(--warning-gradient); }
        .bg-danger-grad { background: var(--danger-gradient); }

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
        .bg-warning-soft { background: rgba(246, 173, 85, 0.1); color: #f6ad55; }
        .bg-danger-soft { background: rgba(231, 74, 59, 0.1); color: #e74a3b; }
        
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
            .sidebar, .navbar, .date-filter-box, .stat-card-mini, .sidebar-link, .btn, .glass-card > h6, .glass-card > p, .row.mt-4, .card-footer, .d-print-none {
                display: none !important;
            }
            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .d-print-table {
                display: table !important;
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
            }
            .table th {
                background-color: #f8f9fa !important;
                color: #000 !important;
            }
            /* Show Date in Print */
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
        <div class="sidebar col-lg-2 py-4 px-3 bg-white border-end d-lg-block" style="min-height: calc(100vh - 60px);">
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
            <a href="attendance" class="sidebar-link active">
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
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h2 class="fw-bold mb-1">Attendance Monitoring</h2>
                    <p class="text-muted mb-0">Tracking student presence and participation.</p>
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

                        <?php if ($selected_date != date('Y-m-d') || $selected_year || $selected_section): ?>
                            <a href="attendance" class="btn btn-sm btn-light rounded-pill" title="Clear Filters">
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
                <h3>Attendance Report</h3>
                <p class="mb-0">Date: <?php echo date('F j, Y', strtotime($selected_date)); ?></p>
                <?php if($selected_year) echo "<p class='mb-0'>Year Level: $selected_year</p>"; ?>
                <?php if($selected_section) echo "<p class='mb-0'>Section: $selected_section</p>"; ?>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5 animate__animated animate__fadeIn">
                <div class="col-md-3">
                    <div class="stat-card-mini bg-primary-grad shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="opacity-75 mb-1">Total Enrolled</h6>
                                <h2 class="fw-bold mb-0"><?php echo $total_students; ?></h2>
                            </div>
                            <i class="fas fa-users fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card-mini bg-success-grad shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="opacity-75 mb-1">Present</h6>
                                <h2 class="fw-bold mb-0"><?php echo $present_count; ?></h2>
                            </div>
                            <i class="fas fa-check-circle fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card-mini bg-warning-grad shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="opacity-75 mb-1">Late</h6>
                                <h2 class="fw-bold mb-0"><?php echo $late_count; ?></h2>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card-mini bg-danger-grad shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="opacity-75 mb-1">Absent</h6>
                                <h2 class="fw-bold mb-0"><?php echo $absent_count; ?></h2>
                            </div>
                            <i class="fas fa-times-circle fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="glass-card animate__animated animate__fadeInUp">
                <!-- Screen Version (Paginated) -->
                <div class="table-responsive d-print-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="py-3">Student</th>
                                <th class="py-3 text-center">Time-In</th>
                                <th class="py-3 text-center">Time-Out (AM)</th>
                                <th class="py-3 text-center">Time-Out (PM)</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="text-end px-4 py-3">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_records as $index => $record): 
                                $row_number = $offset + $index + 1;
                            ?>
                            <tr>
                                <td class="px-4 text-muted small"><?php echo $row_number; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-light text-primary fw-bold">
                                            <?php echo $record['fname'] ? strtoupper(substr($record['fname'], 0, 1)) : '?'; ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?php echo $record['fname'] ? htmlspecialchars($record['lname'] . ', ' . $record['fname']) : 'Unknown Student'; ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($record['student_number']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($record['time_in']): ?>
                                        <span class="fw-600 text-dark"><?php echo date('h:i A', strtotime($record['time_in'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">--:--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($record['time_out'] && strtotime($record['time_out']) <= strtotime('12:45:00')): ?>
                                        <span class="badge badge-pill bg-info-soft text-info">
                                            <i class="fas fa-flag-checkered me-1"></i> <?php echo date('h:i A', strtotime($record['time_out'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">--:--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($record['time_out'] && strtotime($record['time_out']) > strtotime('12:45:00')): ?>
                                        <span class="badge badge-pill bg-info-soft text-info">
                                            <i class="fas fa-flag-checkered me-1"></i> <?php echo date('h:i A', strtotime($record['time_out'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">--:--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $status = $record['status'] ?? 'ABSENT';
                                    $badge_class = '';
                                    switch($status) {
                                        case 'PRESENT': $badge_class = 'bg-success-soft'; break;
                                        case 'LATE': $badge_class = 'bg-warning-soft'; break;
                                        default: $badge_class = 'bg-danger-soft';
                                    }
                                    ?>
                                    <span class="badge badge-pill <?php echo $badge_class; ?> px-3 py-2">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <button class="btn btn-sm btn-light border" title="View Logs">
                                        <i class="fas fa-search text-muted"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($attendance_records)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p>No student records found in the database.</p>
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
                                <th class="px-2 py-2" style="width: 50px;">#</th>
                                <th class="py-2">Student Name</th>
                                <th class="py-2">Student ID</th>
                                <th class="py-2 text-center">Time-In</th>
                                <th class="py-2 text-center">Time-Out</th>
                                <th class="py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_attendance_records as $index => $record): ?>
                            <tr>
                                <td class="px-2 py-2 text-center small"><?php echo $index + 1; ?></td>
                                <td class="px-2 py-2 fw-bold"><?php echo htmlspecialchars($record['lname'] . ', ' . $record['fname']); ?></td>
                                <td class="px-2 py-2 small"><?php echo htmlspecialchars($record['student_number']); ?></td>
                                <td class="px-2 py-2 text-center small"><?php echo $record['time_in'] ? date('h:i A', strtotime($record['time_in'])) : '--:--'; ?></td>
                                <td class="px-2 py-2 text-center small"><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '--:--'; ?></td>
                                <td class="px-2 py-2 text-center small fw-bold"><?php echo $record['status'] ?? 'ABSENT'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white border-top p-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing <span class="fw-600 text-dark"><?php echo $offset + 1; ?></span> to <span class="fw-600 text-dark"><?php echo min($offset + $limit, $total_records); ?></span> of <span class="fw-600 text-dark"><?php echo $total_records; ?></span> records
                        </div>
                        <nav>
                            <?php
                            // Build query string for pagination links
                            $query_params = $_GET;
                            ?>
                            <ul class="pagination pagination-sm mb-0 gap-2">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <?php 
                                    $query_params['page'] = $page - 1;
                                    $prev_url = '?' . http_build_query($query_params);
                                    ?>
                                    <a class="page-link rounded-pill px-3 border-0 bg-light text-dark" href="<?php echo $prev_url; ?>">
                                        <i class="fas fa-chevron-left me-1"></i> Prev
                                    </a>
                                </li>
                                <li class="page-item disabled">
                                    <span class="page-link rounded-pill px-3 border-0 bg-white">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                                </li>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <?php 
                                    $query_params['page'] = $page + 1;
                                    $next_url = '?' . http_build_query($query_params);
                                    ?>
                                    <a class="page-link rounded-pill px-3 border-0 bg-light text-dark" href="<?php echo $next_url; ?>">
                                        Next <i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="row mt-4 animate__animated animate__fadeIn">
                <div class="col-md-6">
                    <div class="glass-card p-4 h-100">
                        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Attendance Policy</h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2"><i class="fas fa-circle-check text-success me-2"></i><strong>Present:</strong> Logins on or before 09:30 AM</li>
                            <li class="mb-2"><i class="fas fa-circle-right text-warning me-2"></i><strong>Late:</strong> Logins after 09:31 AM and 05:30 PM</li>
                            <li><i class="fas fa-circle-xmark text-danger me-2"></i><strong>Absent:</strong> No logins at all</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card p-4 h-100">
                        <h6 class="fw-bold mb-3"><i class="fas fa-clock me-2 text-primary"></i>Automation Note</h6>
                        <p class="small text-muted mb-0">
                            Attendance is automatically captured upon student login. <strong>Time-Out</strong> is automatically fixed at <strong>12:45 PM</strong> (for morning logins) or <strong>05:45 PM</strong> (for afternoon logins).
                        </p>
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
