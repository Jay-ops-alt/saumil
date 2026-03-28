<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}
$pid = (int)$_SESSION['pro_id'];

$counts = ['subjects'=>0,'codes'=>0,'papers'=>0];

$stmt = $conn->prepare('SELECT COUNT(*) FROM subjects WHERE professor_id=?');
$stmt->bind_param('i', $pid);
$stmt->execute();
$stmt->bind_result($counts['subjects']);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM subject_codes WHERE professor_id=?');
$stmt->bind_param('i', $pid);
$stmt->execute();
$stmt->bind_result($counts['codes']);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM question_papers WHERE professor_id=?');
$stmt->bind_param('i', $pid);
$stmt->execute();
$stmt->bind_result($counts['papers']);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Professor Dashboard - AQPG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar p-3">
            <h5 class="text-white">Professor</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="subjects.php">My Subjects</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="subject_codes.php">My Subject Codes</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="papers.php">Question Papers</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="questions.php">Questions</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="col-md-10 ms-sm-auto px-4 py-4">
            <h1 class="h4 mb-3">Dashboard</h1>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <div class="card-title">My Subjects</div>
                            <h3><?php echo $counts['subjects']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <div class="card-title">My Codes</div>
                            <h3><?php echo $counts['codes']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-info">
                        <div class="card-body">
                            <div class="card-title">My Papers</div>
                            <h3><?php echo $counts['papers']; ?></h3>
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
