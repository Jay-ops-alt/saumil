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

$codesStmt = $conn->prepare('SELECT sc.id, sc.code, s.name FROM subject_codes sc LEFT JOIN subjects s ON sc.subject_id=s.id WHERE sc.professor_id=?');
$codesStmt->bind_param('i', $pid);
$codesStmt->execute();
$codeOptions = $codesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$papers = $conn->prepare('SELECT qp.*, sc.code FROM question_papers qp LEFT JOIN subject_codes sc ON qp.subject_code_id=sc.id WHERE qp.professor_id=? ORDER BY qp.id DESC');
$papers->bind_param('i', $pid);
$papers->execute();
$paperResult = $papers->get_result();
$activePage = 'papers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Papers - AQPG</title>
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
                    <a class="sidebar-link" href="subjects.php">My Subjects</a>
                    <a class="sidebar-link" href="subject_codes.php">My Subject Codes</a>
                    <a class="sidebar-link <?php echo $activePage === 'papers' ? 'active' : ''; ?>" href="papers.php">Question Papers</a>
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
                    <p class="stat-label mb-1">Papers</p>
                    <h1 class="page-title">Question Papers</h1>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Create Paper</button>
                </div>
            </div>
            <?php if ($message): ?><div class="alert alert-info mb-3"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
            <div class="card card-hover">
                <div class="table-responsive">
                    <table class="table align-middle" id="paperTable">
                        <thead><tr><th>ID</th><th>Title</th><th>Subject Code</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <?php $modalHtml = ''; ?>
                            <?php while ($row = $paperResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td class="text-end d-flex gap-1 justify-content-end flex-wrap">
                                    <button class="btn btn-icon" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>" aria-label="Edit paper">✎</button>
                                    <form method="post" onsubmit="return confirm('Delete this paper?');">
                                        <?php csrf_input(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-icon" aria-label="Delete paper">×</button>
                                    </form>
                                    <a href="questions.php?paper_id=<?php echo $row['id']; ?>" class="btn btn-icon" aria-label="Manage questions">?</a>
                                    <a href="generate_paper.php?paper_id=<?php echo $row['id']; ?>" class="btn btn-icon" aria-label="Generate paper">⇢</a>
                                </td>
                            </tr>
                            <?php ob_start(); ?>
                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="post" class="modal-content">
                                        <?php csrf_input(); ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Paper</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($row['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Instructions</label>
                                                <textarea name="instructions" class="form-control" rows="4"><?php echo htmlspecialchars($row['instructions'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Subject Code</label>
                                                <select name="subject_code_id" class="form-select" required>
                                                    <?php foreach ($codeOptions as $c): ?>
                                                        <option value="<?php echo $c['id']; ?>" <?php echo $c['id']==$row['subject_code_id']?'selected':''; ?>>
                                                             <?php echo htmlspecialchars($c['code'].' - '.$c['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
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
                        <?php foreach ($codeOptions as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['code'].' - '.$c['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/js/main.js"></script>
<script>$(function(){ $('#paperTable').DataTable(); });</script>
</body>
</html>
