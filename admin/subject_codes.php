<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $professor_id = (int)($_POST['professor_id'] ?? 0);
        if ($subject_id && $code && $professor_id) {
            $stmt = $conn->prepare('INSERT INTO subject_codes (subject_id, code, professor_id) VALUES (?,?,?)');
            $stmt->bind_param('isi', $subject_id, $code, $professor_id);
            $stmt->execute();
            $stmt->close();
            $message = 'Subject code added';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare('DELETE FROM subject_codes WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Subject code deleted';
        }
    }
}

$subjectsStmt = $conn->prepare("SELECT id, name FROM subjects WHERE status='active' ORDER BY name");
$subjectsStmt->execute();
$subjects = $subjectsStmt->get_result();

$professorsStmt = $conn->prepare("SELECT id, name FROM professors WHERE status='active' ORDER BY name");
$professorsStmt->execute();
$professors = $professorsStmt->get_result();

$codesStmt = $conn->prepare("SELECT sc.*, s.name AS subject_name, p.name AS professor_name FROM subject_codes sc 
    LEFT JOIN subjects s ON sc.subject_id=s.id 
    LEFT JOIN professors p ON sc.professor_id=p.id ORDER BY sc.id DESC");
$codesStmt->execute();
$codes = $codesStmt->get_result();
$activePage = 'subject_codes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subject Codes - AQPG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                    <a class="sidebar-link" href="dashboard.php">Dashboard</a>
                    <a class="sidebar-link" href="users.php">Users</a>
                    <a class="sidebar-link" href="subjects.php">Subjects</a>
                    <a class="sidebar-link <?php echo $activePage === 'subject_codes' ? 'active' : ''; ?>" href="subject_codes.php">Subject Codes</a>
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
                    <p class="stat-label mb-1">Administration</p>
                    <h1 class="page-title">Subject Codes</h1>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Code</button>
                </div>
            </div>
            <?php if ($message): ?><div class="alert alert-info mb-3"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <div class="card card-hover">
                <div class="table-responsive">
                    <table class="table align-middle" id="codeTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Code</th>
                                <th>Professor</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $codes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['code']); ?></td>
                                <td><?php echo htmlspecialchars($row['professor_name']); ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this code?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-icon" aria-label="Delete code">×</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
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
                        <option value="">Select Subject</option>
                        <?php while ($s = $subjects->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Professor</label>
                    <select name="professor_id" class="form-select" required>
                        <option value="">Select Professor</option>
                        <?php while ($p = $professors->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/js/main.js"></script>
<script>
$(function() {
    $('#codeTable').DataTable();
});
</script>
</body>
</html>
