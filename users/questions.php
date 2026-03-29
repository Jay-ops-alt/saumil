<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}
$pid = (int)$_SESSION['pro_id'];
$paper_id = isset($_GET['paper_id']) ? (int)$_GET['paper_id'] : 0;
$message = '';
$allowedTypes = ['MCQ','Fill','Short','Long'];

// Validate selected paper belongs to professor
if ($paper_id) {
    $check = $conn->prepare('SELECT COUNT(*) FROM question_papers WHERE id=? AND professor_id=?');
    $check->bind_param('ii', $paper_id, $pid);
    $check->execute();
    $check->bind_result($paperCount);
    $check->fetch();
    $check->close();
    if ($paperCount === 0) {
        $paper_id = 0;
    }
}

// Fetch papers for selection
$paperStmt = $conn->prepare('SELECT id, title FROM question_papers WHERE professor_id=? ORDER BY id DESC');
$paperStmt->bind_param('i', $pid);
$paperStmt->execute();
$paperList = $paperStmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $paper_id) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['type'] ?? 'MCQ';
        $marks = (int)($_POST['marks'] ?? 0);
        $co = trim($_POST['co'] ?? '');
        $bloom = trim($_POST['bloom_level'] ?? '');
        if ($text && in_array($type, $allowedTypes, true)) {
            $stmt = $conn->prepare('INSERT INTO questions (paper_id, question_text, type, marks, co, bloom_level) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('ississ', $paper_id, $text, $type, $marks, $co, $bloom);
            if ($stmt->execute()) {
                $qid = $stmt->insert_id;
                $questionSaved = true;
                if ($type === 'MCQ' && !empty($_POST['choices']) && isset($_POST['correct'])) {
                    $choiceData = [];
                    foreach ($_POST['choices'] as $idx => $choiceText) {
                        $ctext = trim($choiceText);
                        if ($ctext !== '') {
                            $choiceData[] = ['idx' => $idx, 'text' => $ctext];
                        }
                    }
                    if (count($choiceData) >= 2) {
                        foreach ($choiceData as $choiceRow) {
                            $isCorrect = ((int)$_POST['correct'] === $choiceRow['idx']) ? 1 : 0;
                            $cstmt = $conn->prepare('INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?,?,?)');
                            $cstmt->bind_param('isi', $qid, $choiceRow['text'], $isCorrect);
                            $cstmt->execute();
                            $cstmt->close();
                        }
                    } else {
                        $message = 'Please provide at least two choices.';
                        $cleanup = $conn->prepare('DELETE FROM questions WHERE id=?');
                        $cleanup->bind_param('i', $qid);
                        $cleanup->execute();
                        $cleanup->close();
                        $questionSaved = false;
                    }
                }
                if ($questionSaved) {
                    $message = 'Question added';
                }
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $qid = (int)($_POST['id'] ?? 0);
        if ($qid) {
            $delChoices = $conn->prepare('DELETE FROM choices WHERE question_id=?');
            $delChoices->bind_param('i', $qid);
            $delChoices->execute();
            $delChoices->close();

            $stmt = $conn->prepare('DELETE FROM questions WHERE id=? AND paper_id=?');
            $stmt->bind_param('ii', $qid, $paper_id);
            $stmt->execute();
            $stmt->close();
            $message = 'Question deleted';
        }
    }
}

$questions = [];
if ($paper_id) {
    $qstmt = $conn->prepare('SELECT * FROM questions WHERE paper_id=? ORDER BY id DESC');
    $qstmt->bind_param('i', $paper_id);
    $qstmt->execute();
    $questions = $qstmt->get_result();
    $qstmt->close();
}
$activePage = 'questions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questions - AQPG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a class="sidebar-link" href="papers.php">Question Papers</a>
                    <a class="sidebar-link <?php echo $activePage === 'questions' ? 'active' : ''; ?>" href="questions.php">Questions</a>
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
                    <p class="stat-label mb-1">Questions</p>
                    <h1 class="page-title">Manage Questions</h1>
                </div>
                <div class="d-flex gap-2">
                    <a href="papers.php" class="btn btn-outline-primary">Back to Papers</a>
                    <?php if ($paper_id): ?><a href="generate_paper.php?paper_id=<?php echo $paper_id; ?>" class="btn btn-primary">Generate</a><?php endif; ?>
                </div>
            </div>
            <form method="get" class="row g-2 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Select Paper</label>
                    <select name="paper_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Choose...</option>
                        <?php while ($p = $paperList->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo $paper_id==$p['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($p['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>

            <?php if ($paper_id): ?>
            <?php if ($message): ?><div class="alert alert-info mb-3"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
            <div class="card card-hover mb-4">
                <div class="section-title">Add Question</div>
                <form method="post" class="d-grid gap-3">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label class="form-label">Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" id="question_type" class="form-select" required>
                                <?php foreach ($allowedTypes as $typeOpt): ?>
                                    <option value="<?php echo $typeOpt; ?>"><?php echo $typeOpt; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marks</label>
                            <input type="number" name="marks" class="form-control" min="1" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CO</label>
                            <input type="text" name="co" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bloom Level</label>
                            <input type="text" name="bloom_level" class="form-control">
                        </div>
                    </div>
                    <div id="choices-container" class="mt-2">
                        <label class="form-label">Choices (for MCQ)</label>
                        <?php for ($i=0;$i<4;$i++): ?>
                        <div class="input-group mb-2">
                            <span class="input-group-text">
                                <input type="radio" name="correct" value="<?php echo $i; ?>" <?php echo $i===0?'checked':''; ?>>
                            </span>
                            <input type="text" name="choices[]" class="form-control" placeholder="Choice <?php echo $i+1; ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>

            <div class="card card-hover">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>ID</th><th>Question</th><th>Type</th><th>Marks</th><th>CO</th><th>Bloom</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <?php while ($q = $questions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $q['id']; ?></td>
                                <td><?php echo purify_html($q['question_text']); ?></td>
                                <td><?php echo $q['type']; ?></td>
                                <td><?php echo $q['marks']; ?></td>
                                <td><?php echo htmlspecialchars($q['co'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($q['bloom_level'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this question?');">
                                        <?php csrf_input(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="btn btn-icon" aria-label="Delete question">×</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info">Select a paper to manage questions.</div>
            <?php endif; ?>
        </main>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
