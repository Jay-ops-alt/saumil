<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}
$pid = (int)$_SESSION['pro_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $allowed = $conn->prepare('SELECT COUNT(*) FROM subjects WHERE id=? AND professor_id=?');
        $allowed->bind_param('ii', $subject_id, $pid);
        $allowed->execute();
        $allowed->bind_result($countSub);
        $allowed->fetch();
        $allowed->close();
        if ($subject_id && $code && $countSub > 0) {
            $stmt = $conn->prepare('INSERT INTO subject_codes (subject_id, code, professor_id) VALUES (?,?,?)');
            $stmt->bind_param('isi', $subject_id, $code, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Code added';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare('DELETE FROM subject_codes WHERE id=? AND professor_id=?');
            $stmt->bind_param('ii', $id, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Code deleted';
        }
    }
}

$subjects = $conn->prepare('SELECT id, name FROM subjects WHERE professor_id=? AND status="active" ORDER BY name');
$subjects->bind_param('i', $pid);
$subjects->execute();
$subjectList = $subjects->get_result();

$codes = $conn->prepare('SELECT sc.*, s.name as subject_name FROM subject_codes sc 
    LEFT JOIN subjects s ON sc.subject_id=s.id WHERE sc.professor_id=? ORDER BY sc.id DESC');
$codes->bind_param('i', $pid);
$codes->execute();
$codeResult = $codes->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Subject Codes - AQPG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4">My Subject Codes</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Code</button>
            </div>
            <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <div class="table-responsive">
                <table class="table table-striped" id="codeTable">
                    <thead>
                        <tr><th>ID</th><th>Subject</th><th>Code</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $codeResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['code']); ?></td>
                            <td>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this code?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subject Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php while ($s = $subjectList->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function(){ $('#codeTable').DataTable(); });
</script>
</body>
</html>
