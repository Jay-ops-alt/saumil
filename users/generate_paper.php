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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicon-16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container my-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 no-print mb-3 print-actions">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="headerText" class="form-control form-control-sm" style="min-width:200px;" value="UNIVERSITY EXAMINATION" placeholder="Header / Institution name">
                    <input type="text" id="watermarkText" class="form-control form-control-sm" style="min-width:180px;" value="CONFIDENTIAL" placeholder="Watermark text">
                    <input type="text" id="candidateNameInput" class="form-control form-control-sm" style="min-width:180px;" placeholder="Candidate name (optional)">
                </div>
                <div class="d-flex gap-2">
                    <a href="papers.php" class="btn btn-outline-primary">Back</a>
                    <a href="download_pdf.php?paper_id=<?php echo $paper_id; ?>" class="btn btn-outline-secondary">Download PDF</a>
                    <button class="btn btn-primary" onclick="window.print()">Print</button>
                </div>
            </div>
        <div class="print-wrapper">
            <div class="watermark" aria-hidden="true"></div>
            <div class="print-header">
                <div class="print-meta" id="headerDisplay" data-default="UNIVERSITY EXAMINATION">UNIVERSITY EXAMINATION</div>
                <div class="paper-title"><?php echo htmlspecialchars($paper['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                <div class="stat-subtext text-white">
                    <?php echo htmlspecialchars($paper['subject_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> (<?php echo htmlspecialchars($paper['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>) · Date: <?php echo $paper['created_at'] ? date('d-m-Y', strtotime($paper['created_at'])) : date('d-m-Y'); ?>
                </div>
            </div>
            <div class="print-body">
                <div class="exam-meta">
                    <div class="meta-line"><span>Subject:</span><span><?php echo htmlspecialchars($paper['subject_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span></div>
                    <div class="meta-line"><span>Code:</span><span><?php echo htmlspecialchars($paper['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span></div>
                    <div class="meta-line"><span>Candidate:</span><span id="candidateDisplay">________________</span></div>
                </div>
                <?php if ($paper['instructions']): ?>
                    <div class="instruction-bar mb-3">
                        <?php echo nl2br(htmlspecialchars($paper['instructions'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')); ?>
                    </div>
                <?php endif; ?>

                <?php
                $sectionIndex = 1;
                foreach ($sections as $type => $questions):
                    if (count($questions)):
                        $label = $type === 'MCQ' ? 'Multiple Choice Questions' : ($type === 'Fill' ? 'Fill in the Blanks' : ($type === 'Short' ? 'Short Questions' : 'Long Questions'));
                ?>
                    <div class="print-section">
                        <div class="section-heading">
                            <span class="circle"><?php echo $sectionIndex; ?></span>
                            <span><?php echo $label; ?></span>
                            <span class="stat-subtext">Marks varied</span>
                        </div>
                        <div class="d-grid gap-2">
                            <?php foreach ($questions as $idx => $q): ?>
                                <div class="question-item">
                                    <span class="q-number"><?php echo $idx + 1; ?>.</span>
                                    <div class="flex-grow-1">
                                        <div><?php echo purify_html($q['question_text']); ?></div>
                                         <div class="stat-subtext">Marks: <?php echo htmlspecialchars($q['marks'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> · CO: <?php echo htmlspecialchars($q['co'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> · Bloom: <?php echo htmlspecialchars($q['bloom_level'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                                        <?php if ($type === 'MCQ'): ?>
                                            <div class="mcq-choices">
                                                <?php foreach (fetchChoices($conn, $q['id']) as $choice): ?>
                                                     <span><?php echo htmlspecialchars($choice['choice_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php
                        $sectionIndex++;
                    endif;
                endforeach;
                ?>
            </div>
            <div class="print-footer">
                <span>Professor ID: <?php echo htmlspecialchars($pid, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                <span>AQPG</span>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
