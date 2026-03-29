<?php
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['pro_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$name || !$email || !$password || !$confirm) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    } else {
        $stmt = $conn->prepare('SELECT id FROM professors WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // New accounts require admin approval before login
            $status = 'pending';
            $stmt2 = $conn->prepare('INSERT INTO professors (name, email, password, status, created_at) VALUES (?,?,?,?,NOW())');
            $stmt2->bind_param('ssss', $name, $email, $hash, $status);
            if ($stmt2->execute()) {
                $success = 'Registration submitted. Please wait for admin approval.';
            } else {
                $error = 'Registration failed';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Professor Register - AQPG</title>
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
                            <p class="stat-label mb-1">Create your account</p>
                            <h1 class="page-title" style="font-size:28px;">Professor Registration</h1>
                        </div>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div><?php endif; ?>
                        <form method="post" class="d-grid gap-3">
                            <?php csrf_input(); ?>
                            <div>
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="login.php" class="nav-link p-0">Already have an account? Login</a>
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
