<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}
$pid = (int)$_SESSION['pro_id'];
$paper_id = isset($_GET['paper_id']) ? (int)$_GET['paper_id'] : 0;
$message = '';

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
        if ($text && in_array($type, ['MCQ','Fill','Short','Long'], true)) {
            $stmt = $conn->prepare('INSERT INTO questions (paper_id, question_text, type, marks, co, bloom_level) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('ississ', $paper_id, $text, $type, $marks, $co, $bloom);
            if ($stmt->execute()) {
                $qid = $stmt->insert_id;
                if ($type === 'MCQ' && !empty($_POST['choices']) && isset($_POST['correct'])) {
                    foreach ($_POST['choices'] as $idx => $choiceText) {
                        $ctext = trim($choiceText);
                        if ($ctext === '') continue;
                        $isCorrect = ((int)$_POST['correct'] === $idx) ? 1 : 0;
                        $cstmt = $conn->prepare('INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?,?,?)');
                        $cstmt->bind_param('isi', $qid, $ctext, $isCorrect);
                        $cstmt->execute();
                        $cstmt->close();
                    }
                }
                $message = 'Question added';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questions - AQPG</title>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4">Manage Questions</h1>
                <div>
                    <a href="papers.php" class="btn btn-secondary">Back to Papers</a>
                    <?php if ($paper_id): ?><a href="generate_paper.php?paper_id=<?php echo $paper_id; ?>" class="btn btn-info">Generate</a><?php endif; ?>
                </div>
            </div>
            <form method="get" class="row g-2 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Select Paper</label>
                    <select name="paper_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Choose...</option>
                        <?php while ($p = $paperList->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo $paper_id==$p['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($p['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>

            <?php if ($paper_id): ?>
            <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <div class="card mb-4">
                <div class="card-header">Add Question</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea name="question_text" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" id="question_type" class="form-select" required>
                                    <option value="MCQ">MCQ</option>
                                    <option value="Fill">Fill</option>
                                    <option value="Short">Short</option>
                                    <option value="Long">Long</option>
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
                        <div id="choices-container" class="mt-3">
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
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>Question</th><th>Type</th><th>Marks</th><th>CO</th><th>Bloom</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($q = $questions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars($q['question_text']); ?></td>
                            <td><?php echo $q['type']; ?></td>
                            <td><?php echo $q['marks']; ?></td>
                            <td><?php echo htmlspecialchars($q['co']); ?></td>
                            <td><?php echo htmlspecialchars($q['bloom_level']); ?></td>
                            <td>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this question?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-info">Select a paper to manage questions.</div>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
