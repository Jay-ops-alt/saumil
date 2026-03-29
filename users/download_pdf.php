<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['pro_id'])) {
    header('Location: login.php');
    exit;
}

$pid = (int)$_SESSION['pro_id'];
$paper_id = (int)($_GET['paper_id'] ?? 0);

$stmt = $conn->prepare('SELECT qp.*, sc.code, s.name AS subject_name FROM question_papers qp 
    LEFT JOIN subject_codes sc ON qp.subject_code_id=sc.id
    LEFT JOIN subjects s ON sc.subject_id=s.id
    WHERE qp.id=? AND qp.professor_id=?');
$stmt->bind_param('ii', $paper_id, $pid);
$stmt->execute();
$paper = $stmt->get_result()->fetch_assoc();
$stmt->close();

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

$html = '<h2 style="text-align:center;margin:0;">' . htmlspecialchars($paper['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2>';
$html .= '<p style="text-align:center;margin:4px 0;">' . htmlspecialchars($paper['subject_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' (' . htmlspecialchars($paper['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ')</p>';

$sectionIndex = 1;
foreach ($sections as $type => $questions) {
    if (!count($questions)) {
        continue;
    }
    $label = $type === 'MCQ' ? 'Multiple Choice Questions' : ($type === 'Fill' ? 'Fill in the Blanks' : ($type === 'Short' ? 'Short Questions' : 'Long Questions'));
    $html .= '<h4 style="margin-top:12px;">' . $sectionIndex . '. ' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h4><ol>';
    foreach ($questions as $q) {
        $html .= '<li><div>' . purify_html($q['question_text']) . '</div><div style="font-size:11px;color:#666;">Marks: ' . htmlspecialchars($q['marks'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' · CO: ' . htmlspecialchars($q['co'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' · Bloom: ' . htmlspecialchars($q['bloom_level'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
        if ($type === 'MCQ') {
            $choices = fetchChoices($conn, $q['id']);
            if ($choices) {
                $html .= '<ul>';
                foreach ($choices as $choice) {
                    $html .= '<li>' . htmlspecialchars($choice['choice_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
                }
                $html .= '</ul>';
            }
        }
        $html .= '</li>';
    }
    $html .= '</ol>';
    $sectionIndex++;
}

$dompdf = new Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$fileName = 'paper-' . (int)$paper_id . '.pdf';
$dompdf->stream($fileName, ['Attachment' => true]);
exit;
