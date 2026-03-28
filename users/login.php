<?php
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['pro_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $conn->prepare('SELECT id, password, status FROM professors WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($pid, $hash, $status);
        if ($stmt->fetch() && $status === 'active' && password_verify($password, $hash)) {
            $_SESSION['pro_id'] = $pid;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials or inactive account';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Professor Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="register.php">Need an account? Register</a>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
