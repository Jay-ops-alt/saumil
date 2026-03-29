<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $statusInput = $_POST['status'] ?? 'pending';
        $status = in_array($statusInput, ['active','inactive','pending'], true) ? $statusInput : 'pending';
        if ($name && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO professors (name, email, password, status, created_at) VALUES (?,?,?,?,NOW())');
            $stmt->bind_param('ssss', $name, $email, $hash, $status);
            if ($stmt->execute()) {
                $message = 'Professor added';
            }
            $stmt->close();
        } else {
            $message = 'Please provide valid data';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $statusInput = $_POST['status'] ?? 'pending';
        $status = in_array($statusInput, ['active','inactive','pending'], true) ? $statusInput : 'pending';
        $password = $_POST['password'] ?? '';
        if ($id && $name && $email) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE professors SET name=?, email=?, password=?, status=? WHERE id=?');
                $stmt->bind_param('ssssi', $name, $email, $hash, $status, $id);
            } else {
                $stmt = $conn->prepare('UPDATE professors SET name=?, email=?, status=? WHERE id=?');
                $stmt->bind_param('sssi', $name, $email, $status, $id);
            }
            if ($stmt->execute()) {
                $message = 'Professor updated';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare('DELETE FROM professors WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $message = 'Professor deleted';
        }
    }
}

$professorsStmt = $conn->prepare('SELECT * FROM professors ORDER BY created_at DESC');
$professorsStmt->execute();
$professors = $professorsStmt->get_result();
$activePage = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Professors - AQPG</title>
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
                    <a class="sidebar-link" href="dashboard.php">Dashboard</a>
                    <a class="sidebar-link <?php echo $activePage === 'users' ? 'active' : ''; ?>" href="users.php">Users</a>
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
                    <p class="stat-label mb-1">Administration</p>
                    <h1 class="page-title">Professors</h1>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Professor</button>
                </div>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info mb-3"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
            <?php endif; ?>
            <div class="card card-hover">
                <div class="table-responsive">
                    <table class="table align-middle" id="profTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $modalHtml = ''; ?>
                            <?php while ($row = $professors->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                    <td><span class="badge <?php echo $row['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td class="text-end d-flex gap-1 justify-content-end">
                                        <button class="btn btn-icon" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>" aria-label="Edit professor">✎</button>
                                         <form method="post" onsubmit="return confirm('Delete this professor?');">
                                             <?php csrf_input(); ?>
                                             <input type="hidden" name="action" value="delete">
                                             <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                             <button type="submit" class="btn btn-icon" aria-label="Delete professor">×</button>
                                         </form>
                                     </td>
                                </tr>
                                <?php
                                ob_start();
                                ?>
                                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="post" class="modal-content">
                                            <?php csrf_input(); ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Professor</h5>
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
                                                    <label class="form-label">Email</label>
                                                     <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password (leave blank to keep)</label>
                                                    <input type="password" name="password" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?php echo $row['status']==='pending'?'selected':''; ?>>Pending</option>
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
                                <?php
                                $modalHtml .= ob_get_clean();
                                ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php echo $modalHtml; ?>
        </main>
    </div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <?php csrf_input(); ?>
            <div class="modal-header">
                <h5 class="modal-title">Add Professor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
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
<script src="../assets/js/main.js"></script>
<script>
$(function() {
    $('#profTable').DataTable();
});
</script>
</body>
</html>
