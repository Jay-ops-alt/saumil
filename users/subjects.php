<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}
$pid = (int)$_SESSION['pro_id'];
$message = '';
$modalHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
        if ($name) {
            $stmt = $conn->prepare('INSERT INTO subjects (name, description, status, professor_id) VALUES (?,?,?,?)');
            $stmt->bind_param('sssi', $name, $desc, $status, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Subject added';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
        if ($id && $name) {
            $stmt = $conn->prepare('UPDATE subjects SET name=?, description=?, status=? WHERE id=? AND professor_id=?');
            $stmt->bind_param('sssii', $name, $desc, $status, $id, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Subject updated';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare('DELETE FROM subjects WHERE id=? AND professor_id=?');
            $stmt->bind_param('ii', $id, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Subject deleted';
        }
    }
}

$subjects = $conn->prepare('SELECT * FROM subjects WHERE professor_id=? ORDER BY id DESC');
$subjects->bind_param('i', $pid);
$subjects->execute();
$result = $subjects->get_result();
$activePage = 'subjects';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Subjects - AQPG</title>
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
            </div>
            <span class="role-badge">PROFESSOR</span>
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
                    <a class="sidebar-link <?php echo $activePage === 'subjects' ? 'active' : ''; ?>" href="subjects.php">My Subjects</a>
                    <a class="sidebar-link" href="subject_codes.php">My Subject Codes</a>
                    <a class="sidebar-link" href="papers.php">Question Papers</a>
                    <a class="sidebar-link" href="questions.php">Questions</a>
                    <a class="sidebar-link" href="logout.php">Logout</a>
                </div>
            </div>
            <div class="sidebar-bottom">
                <div class="sidebar-avatar">PR</div>
                <div class="sidebar-meta">
                    <small>Signed in</small>
                    <strong>Professor</strong>
                </div>
            </div>
        </aside>
        <main class="app-main">
            <div class="content-header">
                <div>
                    <p class="stat-label mb-1">Subjects</p>
                    <h1 class="page-title">My Subjects</h1>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Subject</button>
                </div>
            </div>
            <?php if ($message): ?><div class="alert alert-info mb-3"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
            <div class="card card-hover">
                <div class="table-responsive">
                    <table class="table align-middle" id="subjectTable">
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php $modalHtml = ''; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><span class="badge <?php echo $row['status']==='active'?'badge-success':'badge-secondary'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td class="text-end d-flex gap-1 justify-content-end">
                                    <button class="btn btn-icon" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>" aria-label="Edit subject">✎</button>
                                    <form method="post" onsubmit="return confirm('Delete this subject?');">
                                        <?php csrf_input(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-icon" aria-label="Delete subject">×</button>
                                    </form>
                                </td>
                            </tr>
                            <?php ob_start(); ?>
                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="post" class="modal-content">
                                        <?php csrf_input(); ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Subject</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($row['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" <?php echo $row['status']==='active'?'selected':''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $row['status']==='inactive'?'selected':''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php $modalHtml .= ob_get_clean(); ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php echo $modalHtml; ?>
        </main>
    </div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <?php csrf_input(); ?>
            <div class="modal-header">
                <h5 class="modal-title">Add Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
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
$(function() { $('#subjectTable').DataTable(); });
</script>
</body>
</html>
