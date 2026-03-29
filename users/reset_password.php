<?php
require_once __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$message = '';
$valid = false;
$tokenHash = '';
$profId = null;

if ($token) {
    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare('SELECT pr.professor_id FROM password_resets pr WHERE pr.token_hash=? AND pr.used=0 AND pr.expires_at > NOW() LIMIT 1');
    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $stmt->bind_result($profId);
    if ($stmt->fetch()) {
        $valid = true;
    } else {
        $error = 'Reset link is invalid or expired.';
    }
    $stmt->close();
} else {
    $error = 'Invalid reset link.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $newPassword = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($newPassword && $newPassword === $confirm && $profId) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $conn->prepare('UPDATE professors SET password=? WHERE id=?');
        $update->bind_param('si', $hash, $profId);
        if ($update->execute()) {
            $flag = $conn->prepare('UPDATE password_resets SET used=1 WHERE token_hash=?');
            $flag->bind_param('s', $tokenHash);
            $flag->execute();
            $flag->close();
            $message = 'Password updated. You can now log in.';
            $valid = false;
        } else {
            $error = 'Could not update password.';
        }
        $update->close();
    } else {
        $error = 'Passwords must match.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - AQPG</title>
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
                            <p class="stat-label mb-1">Reset password</p>
                            <h1 class="page-title" style="font-size:28px;">Choose a new password</h1>
                        </div>
                        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <?php if ($valid): ?>
                        <form method="post" class="d-grid gap-3">
                            <?php csrf_input(); ?>
                            <div>
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Password</button>
                        </form>
                        <?php else: ?>
                            <p class="muted">If your link is invalid or expired, request a new one.</p>
                        <?php endif; ?>
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
