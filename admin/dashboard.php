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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - AQPG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block sidebar p-3">
            <h5 class="text-white">Admin</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="users.php">Users</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="subjects.php">Subjects</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="subject_codes.php">Subject Codes</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="view_papers.php">View Papers</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="col-md-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h4">Dashboard</h1>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <div class="card-title">Subjects</div>
                            <h3><?php echo $counts['subjects']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <div class="card-title">Subject Codes</div>
                            <h3><?php echo $counts['codes']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-info">
                        <div class="card-body">
                            <div class="card-title">Question Papers</div>
                            <h3><?php echo $counts['papers']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-secondary">
                        <div class="card-body">
                            <div class="card-title">Professors</div>
                            <h3><?php echo $counts['professors']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
