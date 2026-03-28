<?php require_once __DIR__ . '/config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Automatic Question Paper Generator (AQPG)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">AQPG</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">Team</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reviews">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div class="ms-lg-3 d-flex gap-2">
                    <a href="admin/login.php" class="btn btn-primary btn-sm">Admin Login</a>
                    <a href="users/login.php" class="btn btn-success btn-sm">Professor Login</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="py-5 bg-primary text-white hero-section">
        <div class="container py-5 text-center">
            <h1 class="display-5 fw-bold">Automatic Question Paper Generator</h1>
            <p class="lead">Generate balanced, printable question papers in minutes.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="admin/login.php" class="btn btn-light btn-lg">Admin Login</a>
                <a href="users/login.php" class="btn btn-outline-light btn-lg">Professor Login</a>
            </div>
        </div>
    </header>

    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>About AQPG</h2>
                    <p>AQPG streamlines the creation of academic examination papers with structured workflows, reusable question banks, and fast printing.</p>
                    <ul>
                        <li>Role-based access for admins and professors</li>
                        <li>Rich question types with Bloom’s taxonomy tagging</li>
                        <li>Printable papers with sections and instructions</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=900&q=80" class="img-fluid rounded shadow" alt="Classroom">
                </div>
            </div>
        </div>
    </section>

    <section id="team" class="py-5 bg-light">
        <div class="container text-center">
            <h2>Our Team</h2>
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Avani Shah</h5>
                            <p class="card-text">Lead Architect</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Jay Ops</h5>
                            <p class="card-text">Backend Engineer</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Saumil Patel</h5>
                            <p class="card-text">UI/UX Specialist</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="reviews" class="py-5">
        <div class="container text-center">
            <h2>Reviews</h2>
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <blockquote class="blockquote">
                        <p>“Saved hours of manual formatting.”</p>
                        <footer class="blockquote-footer">Prof. Mehta</footer>
                    </blockquote>
                </div>
                <div class="col-md-4">
                    <blockquote class="blockquote">
                        <p>“Simple and reliable for last-minute papers.”</p>
                        <footer class="blockquote-footer">Prof. Rao</footer>
                    </blockquote>
                </div>
                <div class="col-md-4">
                    <blockquote class="blockquote">
                        <p>“Great structure for Bloom’s levels.”</p>
                        <footer class="blockquote-footer">Prof. Desai</footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2>Contact Us</h2>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
                            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
                            $msg = htmlspecialchars(trim($_POST['message'] ?? ''));
                            if ($name && $email && $msg) {
                                echo '<div class="alert alert-info mt-3">Demo form only. Messages are not sent or stored.</div>';
                            } else {
                                echo '<div class="alert alert-danger mt-3">Please provide valid details.</div>';
                            }
                        }
                        ?>
                    </form>
                </div>
                <div class="col-md-6">
                    <h2>Reach Us</h2>
                    <p>Email: support@aqpg.local</p>
                    <p>Phone: +1 (555) 123-4567</p>
                    <p>Address: 123 Academic Lane, Knowledge City</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-3 text-center">
        <div class="container">
            &copy; <?php echo date('Y'); ?> AQPG. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
