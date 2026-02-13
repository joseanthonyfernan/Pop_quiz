<?php
require_once '../config/db.php';
require_once '../includes/auth_admin.php';

$tables = [];
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $error = $e->getMessage();
}

$selected_table = $_GET['table'] ?? null;
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$table_data = [];
$columns = [];
$total_records = 0;
$total_pages = 0;

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && isset($_POST['action']) && $_POST['action'] === 'import_csv') {
    $table = $_POST['table'] ?? '';
    $import_mode = $_POST['import_mode'] ?? 'upsert';
    if ($table === 'students' && !empty($_FILES['csv_file']['tmp_name'])) {
        try {
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
            $header = fgetcsv($handle); // Skip header row
            
            $success_count = 0;
            $error_count = 0;
            $pdo->beginTransaction();
            
            if ($import_mode === 'clear_all') {
                $pdo->exec("DELETE FROM students");
            }
            
            // REPLACE INTO handles "Update or Replace" behavior automatically
            $stmt = $pdo->prepare("REPLACE INTO students (student_number, fname, lname, mname, section, year_level, department) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");

            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) >= 7) {
                    $row = array_slice($data, 0, 7);
                    $stmt->execute($row);
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
            $pdo->commit();
            fclose($handle);
            $success = "Successfully imported/updated $success_count records. Errors: $error_count.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Error importing CSV: " . $e->getMessage();
        }
    } else {
        $error = "Please select a valid CSV file.";
    }
}

if ($selected_table && in_array($selected_table, $tables)) {
    try {
        // Get columns first
        $stmt = $pdo->query("DESCRIBE `$selected_table`");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $where_clause = "";
        $params = [];
        
        // Search by firstname (fname or firstname column)
        if (!empty($search)) {
            $search_col = null;
            if (in_array('fname', $columns)) $search_col = 'fname';
            else if (in_array('firstname', $columns)) $search_col = 'firstname';
            
            if ($search_col) {
                $where_clause = " WHERE `$search_col` LIKE :search ";
                $params[':search'] = "%$search%";
            }
        }

        // Total count for pagination
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM `$selected_table` $where_clause");
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Fetch data with limit/offset
        $stmt = $pdo->prepare("SELECT * FROM `$selected_table` $where_clause LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = "Error fetching table data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Browser | PopQuiz Admin</title>
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
            --dark-gradient: linear-gradient(135deg, #2d3436 0%, #000000 100%);
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

        .table-list-container {
            position: sticky;
            top: 20px;
        }

        .table-item {
            padding: 12px 20px;
            border-radius: 12px;
            color: #4a5568;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
            margin-bottom: 5px;
            border: 1px solid transparent;
        }

        .table-item:hover {
            background: #f8fafc;
            border-color: #edf2f7;
            color: #764ba2;
        }

        .table-item.active {
            background: rgba(118, 75, 162, 0.05);
            border-color: rgba(118, 75, 162, 0.2);
            color: #764ba2;
            font-weight: 600;
        }

        .table-item-count {
            font-size: 11px;
            padding: 4px 8px;
            background: #f1f5f9;
            border-radius: 50px;
            color: #64748b;
        }

        .active .table-item-count {
            background: #764ba2;
            color: #fff;
        }

        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            color: #718096;
            background: #f8fafc;
            border-bottom: 2px solid #edf2f7;
            white-space: nowrap;
        }

        .db-cell {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 12px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .empty-state {
            padding: 100px 20px;
            text-align: center;
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
        <div class="sidebar col-lg-2 py-4 px-3 bg-white border-end d-none d-lg-block" style="min-height: calc(100vh - 60px);">
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
            <a href="results" class="sidebar-link">
                <i class="fas fa-chart-line"></i> Results
            </a>
            
            <div class="mt-5 mb-4 px-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 11px;">System</small>
            </div>
            <a href="browse_db" class="sidebar-link active">
                <i class="fas fa-database"></i> Database
            </a>
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4 rounded-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="table-list-container animate__animated animate__fadeIn">
                        <div class="d-flex align-items-center mb-4 px-2">
                            <i class="fas fa-database text-primary me-2 shadow-sm"></i>
                            <h5 class="fw-bold mb-0">Tables</h5>
                        </div>
                        <div class="glass-card p-3">
                            <div class="table-list">
                                <?php foreach ($tables as $table): ?>
                                    <a href="?table=<?php echo $table; ?>" 
                                       class="table-item <?php echo $selected_table === $table ? 'active' : ''; ?>">
                                        <span><i class="fas fa-table me-2 opacity-50"></i> <?php echo $table; ?></span>
                                        <?php
                                        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                                        ?>
                                        <span class="table-item-count"><?php echo $count; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="animate__animated animate__fadeIn" style="animation-delay: 0.1s;">
                        <?php if ($selected_table): ?>
                            <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                                <div>
                                    <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($selected_table); ?></h2>
                                    <p class="text-muted mb-0 small"><i class="fas fa-eye me-1"></i> Browsing raw records</p>
                                </div>

                                <?php 
                                // Only show search if fname or firstname column exists
                                if (in_array('fname', $columns) || in_array('firstname', $columns)): ?>
                                <div class="flex-grow-1 mx-4">
                                    <form action="" method="GET" class="d-flex">
                                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>">
                                        <div class="input-group input-group-sm bg-white rounded-pill shadow-sm overflow-hidden" style="border: 1px solid #edf2f7;">
                                            <span class="input-group-text bg-white border-0 px-3"><i class="fas fa-search text-muted"></i></span>
                                            <input type="text" name="search" class="form-control border-0 px-0" placeholder="Search firstname..." value="<?php echo htmlspecialchars($search); ?>">
                                            <?php if ($search): ?>
                                                <a href="?table=<?php echo urlencode($selected_table); ?>" class="btn bg-white border-0 text-muted"><i class="fas fa-times"></i></a>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-primary px-4 rounded-pill m-1">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex gap-2">
                                    <?php if ($selected_table === 'students'): ?>
                                        <button class="btn btn-sm btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                                            <i class="fas fa-file-import me-1"></i> Import CSV
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-4" onclick="location.reload()">
                                        <i class="fas fa-sync me-1"></i> Refresh
                                    </button>
                                </div>
                            </div>

                            <div class="glass-card">
                                <?php if (!empty($table_data)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <?php foreach ($columns as $col): ?>
                                                        <th class="px-3 py-3"><?php echo $col; ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($table_data as $row): ?>
                                                    <tr>
                                                        <?php foreach ($row as $val): ?>
                                                            <td class="px-3 py-2">
                                                                <div class="db-cell" title="<?php echo htmlspecialchars($val ?? 'NULL'); ?>">
                                                                    <?php echo htmlspecialchars($val ?? 'NULL'); ?>
                                                                </div>
                                                            </td>
                                                        <?php endforeach; ?>
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
                                                <ul class="pagination pagination-sm mb-0 gap-2">
                                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                        <a class="page-link rounded-pill px-3 border-0 bg-light text-dark" href="?table=<?php echo urlencode($selected_table); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">
                                                            <i class="fas fa-chevron-left me-1"></i> Prev
                                                        </a>
                                                    </li>
                                                    <li class="page-item disabled">
                                                        <span class="page-link rounded-pill px-3 border-0 bg-white">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                                                    </li>
                                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                        <a class="page-link rounded-pill px-3 border-0 bg-light text-dark" href="?table=<?php echo urlencode($selected_table); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">
                                                            Next <i class="fas fa-chevron-right ms-1"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-inbox fa-4x mb-3 text-muted opacity-25"></i>
                                        <h5 class="text-muted">No records found</h5>
                                        <p class="text-muted small">This table doesn't have any data yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="glass-card empty-state">
                                <i class="fas fa-search fa-4x mb-4 text-primary opacity-25"></i>
                                <h3 class="fw-bold">Database Browser</h3>
                                <p class="text-muted mx-auto" style="max-width: 400px;">Select a table from the sidebar to inspect its raw data. This is a system administration tool meant for debugging and auditing.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Import CSV Modal -->
<?php if ($selected_table === 'students'): ?>
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_csv">
                <input type="hidden" name="table" value="students">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="importCsvModalLabel">Import Students CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-info rounded-3 mb-4" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Format Requirement:</strong> CSV should have the following columns in order:
                        <code class="d-block mt-2 px-2 py-1 bg-white rounded border">student_number, fname, lname, mname, section, year_level, department</code>
                        <small class="text-muted mt-2 d-block">The first line will be skipped as the header.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="csv_file" class="form-label fw-600">Choose CSV File</label>
                        <input type="file" class="form-control rounded-3" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-600 mb-2">Import Strategy</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="import_mode" id="mode_upsert" value="upsert" checked>
                            <label class="form-check-label small" for="mode_upsert">
                                <strong>Update or Replace:</strong> Updates existing records and adds new ones.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_mode" id="mode_clear" value="clear_all">
                            <label class="form-check-label small text-danger" for="mode_clear">
                                <strong>Clear All & Replace:</strong> Deletes ALL existing student records before importing.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-toggle="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-upload me-1"></i> Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>
