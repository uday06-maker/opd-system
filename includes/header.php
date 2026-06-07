<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital OPD System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/opd-system/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark px-4 d-flex justify-content-between">
    <a class="navbar-brand fw-bold text-white text-decoration-none" href="/opd-system/index.php">
        Hospital OPD System
    </a>
    <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="text-white me-2" style="font-size:13px;opacity:0.85">
                Hi, <?= htmlspecialchars($_SESSION['username']) ?>
            </span>
            <?php if ($_SESSION['role'] === 'patient'): ?>
                <a href="/opd-system/pages/book_token.php" class="btn btn-light btn-sm">Book Token</a>
                <a href="/opd-system/pages/my_tokens.php" class="btn btn-light btn-sm">My Tokens</a>
                <a href="/opd-system/pages/view_queue.php" class="btn btn-outline-light btn-sm">Live Queue</a>
            <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                <a href="/opd-system/pages/doctor_dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                <a href="/opd-system/pages/admin_dashboard.php" class="btn btn-light btn-sm">Admin Panel</a>
                <a href="/opd-system/pages/view_queue.php" class="btn btn-outline-light btn-sm">Live Queue</a>
            <?php endif; ?>
            <a href="/opd-system/pages/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        <?php else: ?>
            <a href="/opd-system/pages/login.php" class="btn btn-light btn-sm">Login</a>
            <a href="/opd-system/pages/register.php" class="btn btn-outline-light btn-sm">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container mt-4">