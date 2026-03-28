<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}
$pid = (int)$_SESSION['pro_id'];
$paper_id = (int)($_GET['paper_id'] ?? 0);

$paper = null;
if ($paper_id) {
    $stmt = $conn->prepare('SELECT qp.*, sc.code, s.name AS subject_name FROM question_papers qp 
        LEFT JOIN subject_codes sc ON qp.subject_code_id=sc.id
        LEFT JOIN subjects s ON sc.subject_id=s.id
        WHERE qp.id=? AND qp.professor_id=?');
    $stmt->bind_param('ii', $paper_id, $pid);
    $stmt->execute();
    $paper = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (!$paper) {
    header('Location: papers.php');
    exit;
}

function fetchQuestions($conn, $paper_id, $type) {
    $stmt = $conn->prepare('SELECT * FROM questions WHERE paper_id=? AND type=?');
    $stmt->bind_param('is', $paper_id, $type);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

function fetchChoices($conn, $qid) {
    $stmt = $conn->prepare('SELECT * FROM choices WHERE question_id=?');
    $stmt->bind_param('i', $qid);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

$sections = [
    'MCQ' => fetchQuestions($conn, $paper_id, 'MCQ'),
    'Fill' => fetchQuestions($conn, $paper_id, 'Fill'),
    'Short' => fetchQuestions($conn, $paper_id, 'Short'),
    'Long' => fetchQuestions($conn, $paper_id, 'Long'),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Printable Paper - AQPG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center no-print mb-3">
        <a href="papers.php" class="btn btn-secondary">Back</a>
        <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div>
    <div class="print-area border p-4 rounded">
        <div class="text-center mb-4">
            <h3>University Examination</h3>
            <p class="mb-0">Subject: <?php echo htmlspecialchars($paper['subject_name']); ?> (<?php echo htmlspecialchars($paper['code']); ?>)</p>
            <p class="mb-0">Paper Title: <?php echo htmlspecialchars($paper['title']); ?></p>
            <p class="mb-0">Date: <?php echo date('d-m-Y'); ?></p>
        </div>
        <?php if ($paper['instructions']): ?>
            <div class="mb-3">
                <strong>Instructions:</strong>
                <p><?php echo nl2br(htmlspecialchars($paper['instructions'])); ?></p>
            </div>
        <?php endif; ?>

        <?php foreach ($sections as $type => $questions): ?>
            <?php if (count($questions)): ?>
                <h5 class="mt-4"><?php echo $type === 'MCQ' ? 'Multiple Choice Questions' : ($type === 'Fill' ? 'Fill in the Blanks' : ($type === 'Short' ? 'Short Questions' : 'Long Questions')); ?></h5>
                <ol class="mt-2">
                    <?php foreach ($questions as $q): ?>
                        <li class="mb-2">
                            <div><strong><?php echo htmlspecialchars($q['question_text']); ?></strong> (<?php echo $q['marks']; ?> marks, CO: <?php echo htmlspecialchars($q['co']); ?>, Bloom: <?php echo htmlspecialchars($q['bloom_level']); ?>)</div>
                            <?php if ($type === 'MCQ'): ?>
                                <ul class="mt-1">
                                    <?php foreach (fetchChoices($conn, $q['id']) as $choice): ?>
                                        <li><?php echo htmlspecialchars($choice['choice_text']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
