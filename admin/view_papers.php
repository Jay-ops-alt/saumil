<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$papersStmt = $conn->prepare("SELECT qp.*, sc.code as scode, p.name as pname FROM question_papers qp
    LEFT JOIN subject_codes sc ON qp.subject_code_id = sc.id
    LEFT JOIN professors p ON qp.professor_id = p.id ORDER BY qp.created_at DESC");
$papersStmt->execute();
$papers = $papersStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Papers - AQPG</title>
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
            <h1 class="h4 mb-3">Question Papers</h1>
            <div class="table-responsive">
                <table class="table table-striped" id="paperTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Subject Code</th>
                            <th>Professor</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $papers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['scode']); ?></td>
                            <td><?php echo htmlspecialchars($row['pname']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    $('#paperTable').DataTable();
});
</script>
</body>
</html>
