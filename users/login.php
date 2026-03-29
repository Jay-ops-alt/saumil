<?php
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['pro_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $rateKey = 'prof_login';
    if (login_rate_limited($rateKey)) {
        $error = 'Too many login attempts. Please try again later.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $conn->prepare('SELECT id, password, status FROM professors WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($pid, $hash, $status);
        if ($stmt->fetch() && $status === 'active' && password_verify($password, $hash)) {
            session_regenerate_id(true);
            $_SESSION['pro_id'] = $pid;
            clear_login_attempts($rateKey);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials or inactive/pending account';
            record_login_attempt($rateKey);
        }
        $stmt->close();
    } else {
        $error = 'Email and password are required';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Professor Login - AQPG</title>
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
                <div class="col-lg-5 col-md-8">
                    <div class="card card-hover">
                        <div class="mb-3 text-center">
                            <p class="stat-label mb-1">Welcome back</p>
                            <h1 class="page-title" style="font-size:28px;">Professor Login</h1>
                        </div>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <form method="post" class="d-grid gap-3">
                            <?php csrf_input(); ?>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="register.php" class="nav-link p-0">Need an account? Register</a>
                            <br>
                            <a href="forgot_password.php" class="nav-link p-0">Forgot password?</a>
                        </div>
                        <div class="text-center mt-2">
                            <a href="../index.php" class="nav-link p-0">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
