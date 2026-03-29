<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['pro_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if ($email) {
        $stmt = $conn->prepare('SELECT id FROM professors WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($pid);
        if ($stmt->fetch()) {
            $stmt->close();
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);

            $deleteStmt = $conn->prepare('DELETE FROM password_resets WHERE professor_id=?');
            $deleteStmt->bind_param('i', $pid);
            $deleteStmt->execute();
            $deleteStmt->close();

            $insert = $conn->prepare('INSERT INTO password_resets (professor_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $insert->bind_param('is', $pid, $hash);
            $insert->execute();
            $insert->close();

            $baseUrl = rtrim(app_env('APP_URL', 'http://localhost'), '/');
            $link = $baseUrl . '/users/reset_password.php?token=' . urlencode($token);
            $from = app_env('MAIL_FROM', 'no-reply@aqpg.local');
            $headers = "From: {$from}\r\n";
            @mail($email, 'AQPG password reset', "Use the link to reset your password:\n{$link}\nThis link expires in 1 hour.", $headers);
        } else {
            $stmt->close();
        }
        $message = 'If this email is registered, a reset link has been sent.';
    } else {
        $error = 'Please enter a valid email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - AQPG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicon-16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <span class="brand-mark"><span></span><span></span><span></span><span></span></span>
            <span>AQPG</span>
        </div>
        <div class="top-actions">
            <div class="nav-links d-none d-md-flex">
                <a href="../index.php">Home</a>
            </div>
            <span class="role-badge">PROFESSOR</span>
        </div>
    </header>
    <div class="hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card card-hover">
                        <div class="mb-3 text-center">
                            <p class="stat-label mb-1">Password reset</p>
                            <h1 class="page-title" style="font-size:28px;">Forgot your password?</h1>
                        </div>
                        <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <form method="post" class="d-grid gap-3">
                            <?php csrf_input(); ?>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send reset link</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="login.php" class="nav-link p-0">Back to login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
