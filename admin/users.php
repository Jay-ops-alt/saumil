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
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
        if ($name && $email && $password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
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
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
        $password = $_POST['password'] ?? '';
        if ($id && $name && $email) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Professors - AQPG</title>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4">Professors</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Professor</button>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-striped" id="profTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $modalHtml = ''; ?>
                        <?php while ($row = $professors->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this professor?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                            ob_start();
                            ?>
                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="post" class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Professor</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password (leave blank to keep)</label>
                                                <input type="password" name="password" class="form-control">
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
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
            <?php echo $modalHtml; ?>
        </main>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
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
<script>
$(function() {
    $('#profTable').DataTable();
});
</script>
</body>
</html>
