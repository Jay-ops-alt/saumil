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
        $title = trim($_POST['title'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $code_id = (int)($_POST['subject_code_id'] ?? 0);
        $allow = $conn->prepare('SELECT COUNT(*) FROM subject_codes WHERE id=? AND professor_id=?');
        $allow->bind_param('ii', $code_id, $pid);
        $allow->execute();
        $allow->bind_result($cnt);
        $allow->fetch();
        $allow->close();
        if ($title && $code_id && $cnt > 0) {
            $stmt = $conn->prepare('INSERT INTO question_papers (title, instructions, subject_code_id, professor_id, created_at) VALUES (?,?,?,?,NOW())');
            $stmt->bind_param('ssii', $title, $instructions, $code_id, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Paper created';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $code_id = (int)($_POST['subject_code_id'] ?? 0);
        $allow = $conn->prepare('SELECT COUNT(*) FROM subject_codes WHERE id=? AND professor_id=?');
        $allow->bind_param('ii', $code_id, $pid);
        $allow->execute();
        $allow->bind_result($cnt);
        $allow->fetch();
        $allow->close();
        if ($id && $title && $code_id && $cnt > 0) {
            $stmt = $conn->prepare('UPDATE question_papers SET title=?, instructions=?, subject_code_id=? WHERE id=? AND professor_id=?');
            $stmt->bind_param('ssiii', $title, $instructions, $code_id, $id, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Paper updated';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare('DELETE FROM question_papers WHERE id=? AND professor_id=?');
            $stmt->bind_param('ii', $id, $pid);
            $stmt->execute();
            $stmt->close();
            $message = 'Paper deleted';
        }
    }
}

$codes = $conn->prepare('SELECT sc.id, sc.code, s.name FROM subject_codes sc LEFT JOIN subjects s ON sc.subject_id=s.id WHERE sc.professor_id=?');
$codes->bind_param('i', $pid);
$codes->execute();
$codeList = $codes->get_result();

$papers = $conn->prepare('SELECT qp.*, sc.code FROM question_papers qp LEFT JOIN subject_codes sc ON qp.subject_code_id=sc.id WHERE qp.professor_id=? ORDER BY qp.id DESC');
$papers->bind_param('i', $pid);
$papers->execute();
$paperResult = $papers->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Papers - AQPG</title>
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
                <h1 class="h4">Question Papers</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Create Paper</button>
            </div>
            <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <div class="table-responsive">
                <table class="table table-striped" id="paperTable">
                    <thead><tr><th>ID</th><th>Title</th><th>Subject Code</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($row = $paperResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['code']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this paper?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <a href="questions.php?paper_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">Questions</a>
                                <a href="generate_paper.php?paper_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Generate</a>
                            </td>
                        </tr>
                        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="post" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Paper</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Instructions</label>
                                            <textarea name="instructions" class="form-control" rows="4"><?php echo htmlspecialchars($row['instructions']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Subject Code</label>
                                            <select name="subject_code_id" class="form-select" required>
                                                <?php
                                                $codes->execute();
                                                $codeList2 = $codes->get_result();
                                                while ($c = $codeList2->fetch_assoc()):
                                                ?>
                                                    <option value="<?php echo $c['id']; ?>" <?php echo $c['id']==$row['subject_code_id']?'selected':''; ?>>
                                                        <?php echo htmlspecialchars($c['code'].' - '.$c['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
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
                <h5 class="modal-title">Create Paper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Instructions</label>
                    <textarea name="instructions" class="form-control" rows="4"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject Code</label>
                    <select name="subject_code_id" class="form-select" required>
                        <?php while ($c = $codeList->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['code'].' - '.$c['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>$(function(){ $('#paperTable').DataTable(); });</script>
</body>
</html>
