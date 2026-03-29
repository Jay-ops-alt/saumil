<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$counts = ['subjects'=>0,'codes'=>0,'papers'=>0,'professors'=>0];

$stmt = $conn->prepare('SELECT COUNT(*) FROM subjects');
$stmt->execute();
$stmt->bind_result($counts['subjects']);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM subject_codes');
$stmt->execute();
$stmt->bind_result($counts['codes']);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM question_papers');
$stmt->execute();
$stmt->bind_result($counts['papers']);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM professors');
$stmt->execute();
$stmt->bind_result($counts['professors']);
$stmt->fetch();
$stmt->close();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - AQPG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicon-16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="topbar no-print">
        <div class="brand">
            <span class="brand-mark"><span></span><span></span><span></span><span></span></span>
            <span>AQPG</span>
        </div>
        <div class="top-actions">
            <div class="nav-links d-none d-md-flex">
                <a href="../index.php">Home</a>
                <a href="view_papers.php">Papers</a>
            </div>
            <span class="role-badge">ADMIN</span>
            <button class="sidebar-toggle d-lg-none" type="button" aria-label="Toggle sidebar">
                <span style="width:16px;height:2px;background:var(--ink);display:block;box-shadow:0 5px 0 var(--ink),0 -5px 0 var(--ink);"></span>
            </button>
        </div>
    </header>
    <div class="app-shell">
        <aside class="app-sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Navigation</div>
                <div class="sidebar-menu">
                    <a class="sidebar-link <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
                    <a class="sidebar-link" href="users.php">Users</a>
                    <a class="sidebar-link" href="subjects.php">Subjects</a>
                    <a class="sidebar-link" href="subject_codes.php">Subject Codes</a>
                    <a class="sidebar-link" href="view_papers.php">View Papers</a>
                    <a class="sidebar-link" href="logout.php">Logout</a>
                </div>
            </div>
            <div class="sidebar-bottom">
                <div class="sidebar-avatar">AD</div>
                <div class="sidebar-meta">
                    <small>Signed in</small>
                    <strong>Admin</strong>
                </div>
            </div>
        </aside>
        <main class="app-main">
            <div class="content-header">
                <div>
                    <p class="stat-label mb-1">Overview</p>
                    <h1 class="page-title">Dashboard</h1>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="card card-hover stat-card">
                        <div class="stat-label">Subjects</div>
                        <div class="stat-value"><?php echo $counts['subjects']; ?></div>
                        <div class="stat-subtext">Total subjects</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card card-hover stat-card">
                        <div class="stat-label">Subject Codes</div>
                        <div class="stat-value"><?php echo $counts['codes']; ?></div>
                        <div class="stat-subtext">Assigned codes</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card card-hover stat-card">
                        <div class="stat-label">Question Papers</div>
                        <div class="stat-value"><?php echo $counts['papers']; ?></div>
                        <div class="stat-subtext">Total papers</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card card-hover stat-card">
                        <div class="stat-label">Professors</div>
                        <div class="stat-value"><?php echo $counts['professors']; ?></div>
                        <div class="stat-subtext">Registered</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
