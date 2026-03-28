<?php
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare('SELECT id, password FROM admin WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($adminId, $hash);
        if ($stmt->fetch() && password_verify($password, $hash)) {
            $_SESSION['admin_id'] = $adminId;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
        $stmt->close();
    } else {
        $error = 'Username and password are required';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - AQPG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <span class="role-badge">ADMIN</span>
        </div>
    </header>
    <div class="hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-8">
                    <div class="card card-hover">
                        <div class="mb-3 text-center">
                            <p class="stat-label mb-1">Secure Access</p>
                            <h1 class="page-title" style="font-size:28px;">Admin Sign In</h1>
                        </div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" class="d-grid gap-3">
                            <div>
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="nav-link p-0">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
