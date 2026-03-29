<?php
require_once __DIR__ . '/config/db.php';

$contactStatus = '';
$contactError = '';
$stats = ['questions' => 0, 'papers' => 0];

if ($countStmt = $conn->prepare('SELECT COUNT(*) FROM questions')) {
    $countStmt->execute();
    $countStmt->bind_result($stats['questions']);
    $countStmt->fetch();
    $countStmt->close();
}
if ($paperStmt = $conn->prepare('SELECT COUNT(*) FROM question_papers')) {
    $paperStmt->execute();
    $paperStmt->bind_result($stats['papers']);
    $paperStmt->fetch();
    $paperStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypot = trim($_POST['website'] ?? '');
    if ($honeypot !== '') {
        $contactError = 'Unable to submit request right now.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $msg = trim($_POST['message'] ?? '');
        if ($name && $email && $msg) {
            $to = app_env('CONTACT_EMAIL', '');
            if ($to) {
                $from = app_env('MAIL_FROM', $email);
                $subject = 'AQPG contact form';
                $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$msg}";
                $headers = "From: {$from}\r\nReply-To: {$email}";
                if (mail($to, $subject, $body, $headers)) {
                    $contactStatus = 'Thank you for reaching out. We will get back to you soon.';
                } else {
                    $contactError = 'Message could not be sent right now. Please try again later.';
                }
            } else {
                $contactError = 'Contact email is not configured.';
            }
        } else {
            $contactError = 'Please provide valid details.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Automatic Question Paper Generator (AQPG)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <span class="brand-mark"><span></span><span></span><span></span><span></span></span>
            <span>AQPG</span>
        </div>
        <div class="top-actions">
            <div class="nav-links">
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="admin/login.php" class="btn btn-outline-primary">Admin</a>
                <a href="users/login.php" class="btn btn-primary">Professor</a>
            </div>
        </div>
    </header>

    <main class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="d-grid gap-3">
                    <span class="eyebrow">Smart exam builder</span>
                    <h1 class="hero-title">Automatic <em>Question Paper</em> Generator</h1>
                    <p class="hero-subtitle">Create balanced, print-ready papers with structured sections, Bloom’s levels, and reusable question banks—built for administrators and professors.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="users/login.php" class="btn btn-primary">Start generating</a>
                        <a href="admin/login.php" class="btn btn-outline-primary">Admin console</a>
                    </div>
                </div>
                <div class="pattern-surface">
                    <svg viewBox="0 0 200 200" preserveAspectRatio="none">
                        <circle cx="40" cy="40" r="30" stroke="#fff" stroke-width="3" fill="none"/>
                        <circle cx="160" cy="60" r="18" stroke="#fff" stroke-width="2" fill="none"/>
                        <line x1="20" y1="140" x2="180" y2="160" stroke="#fff" stroke-width="2" stroke-dasharray="6 6"/>
                    </svg>
                    <div class="hero-card position-relative">
                        <h3 class="section-title mb-2">Sample Paper</h3>
                        <div class="d-grid gap-2">
                            <div class="d-flex justify-content-between">
                                <span class="stat-label">Course</span>
                                <span class="stat-subtext">CS401</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="stat-label">Title</span>
                                <span class="stat-subtext">Advanced Algorithms</span>
                            </div>
                            <div class="d-grid gap-1">
                                <span class="stat-label">Sections</span>
                                <div class="d-grid gap-1">
                                    <div class="badge badge-secondary">MCQ · 20 marks</div>
                                    <div class="badge badge-secondary">Short · 30 marks</div>
                                    <div class="badge badge-secondary">Long · 50 marks</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-strip">
                <div class="card card-hover stat-card">
                    <div class="stat-label">Questions curated</div>
                    <div class="stat-value"><?php echo number_format((int)$stats['questions']); ?></div>
                    <div class="stat-subtext">Across subjects & outcomes</div>
                </div>
                <div class="card card-hover stat-card">
                    <div class="stat-label">Papers generated</div>
                    <div class="stat-value"><?php echo number_format((int)$stats['papers']); ?></div>
                    <div class="stat-subtext">Printable, balanced layouts</div>
                </div>
                <div class="card card-hover stat-card">
                    <div class="stat-label">Avg. time saved</div>
                    <div class="stat-value">68%</div>
                    <div class="stat-subtext">vs manual formatting</div>
                </div>
            </div>

            <section id="about" class="section-block">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <h2 class="page-title" style="font-size:32px;">Built for modern assessment teams</h2>
                        <p class="hero-subtitle">AQPG streamlines exam creation with role-based workflows, reusable banks, and automated formatting.</p>
                        <ul class="list-unstyled d-grid gap-2">
                            <li>— Role-based portals for admins and professors</li>
                            <li>— Bloom’s taxonomy tagging and CO mapping</li>
                            <li>— Ready-to-print layouts with clear sections</li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-hover">
                            <h3 class="section-title">What you get</h3>
                            <div class="d-grid gap-2">
                                <div class="d-flex justify-content-between">
                                    <span class="stat-label">MCQ balance</span><span class="stat-subtext">Weights & options</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="stat-label">Print clean</span><span class="stat-subtext">No extra clutter</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="stat-label">Metadata</span><span class="stat-subtext">Bloom level & CO</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="section-block">
                <h2 class="section-title">Key features</h2>
                <div class="feature-grid">
                    <div class="card card-hover feature-card">
                        <div class="icon-box">⧉</div>
                        <div>
                            <div class="stat-label" style="letter-spacing:0;">Structured sections</div>
                            <p class="muted mb-0">MCQ, short, and long answers with marks and outcomes organized for you.</p>
                        </div>
                    </div>
                    <div class="card card-hover feature-card">
                        <div class="icon-box">✓</div>
                        <div>
                            <div class="stat-label" style="letter-spacing:0;">Reusable banks</div>
                            <p class="muted mb-0">Maintain subject codes, question banks, and tags for fast future papers.</p>
                        </div>
                    </div>
                    <div class="card card-hover feature-card">
                        <div class="icon-box">☰</div>
                        <div>
                            <div class="stat-label" style="letter-spacing:0;">Print ready</div>
                            <p class="muted mb-0">Clean printable view with hidden controls and branded paper headers.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="section-block">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card card-hover">
                            <h3 class="section-title">Contact us</h3>
                            <form method="post" class="d-grid gap-3">
                                <?php csrf_input(); ?>
                                <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                    <label>Website</label>
                                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Send Message</button>
                                <?php if ($contactStatus): ?>
                                    <div class="alert alert-info mt-2"><?php echo htmlspecialchars($contactStatus, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if ($contactError): ?>
                                    <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($contactError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-hover h-100">
                            <h3 class="section-title">Reach us</h3>
                            <p class="muted mb-2">Email: support@aqpg.local</p>
                            <p class="muted mb-2">Phone: +1 (555) 123-4567</p>
                            <p class="muted mb-0">Address: 123 Academic Lane, Knowledge City</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer text-center">
        <div class="container">
            &copy; <?php echo date('Y'); ?> AQPG. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
