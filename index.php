<?php
session_start();
require 'config/db.php';
include 'includes/header.php';

// Today's stats for homepage
$total_today = $pdo->query("SELECT COUNT(*) FROM tokens WHERE DATE(booked_at) = CURDATE()")->fetchColumn();
$waiting     = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='waiting' AND DATE(booked_at) = CURDATE()")->fetchColumn();
$done        = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='done' AND DATE(booked_at) = CURDATE()")->fetchColumn();
$dept_count  = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
?>

<!-- Hero Section -->
<div class="text-center py-5 mb-4">
    <h1 class="display-5 fw-bold text-primary">Hospital OPD Queue System</h1>
    <p class="lead text-muted">Book your token online. Track your queue in real time. Skip the wait.</p>
    <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
        <a href="pages/book_token.php" class="btn btn-primary btn-lg px-4">Book a Token</a>
        <a href="pages/view_queue.php" class="btn btn-outline-primary btn-lg px-4">View Live Queue</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-5 justify-content-center">
    <?php
    $cards = [
        ['icon' => '🏥', 'label' => 'Departments',       'value' => $dept_count,  'color' => 'primary'],
        ['icon' => '🎫', 'label' => 'Tokens Today',      'value' => $total_today, 'color' => 'info'],
        ['icon' => '⏳', 'label' => 'Currently Waiting', 'value' => $waiting,     'color' => 'warning'],
        ['icon' => '✅', 'label' => 'Consultations Done','value' => $done,        'color' => 'success'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100">
            <div class="fs-1"><?= $c['icon'] ?></div>
            <div class="fs-3 fw-bold text-<?= $c['color'] ?>"><?= $c['value'] ?></div>
            <div class="text-muted small"><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- How it works -->
<div class="row g-4 mb-5">
    <div class="col-12"><h5 class="text-center text-muted mb-3">How it works</h5></div>
    <?php
    $steps = [
        ['step' => '1', 'title' => 'Register',     'desc' => 'Create a free patient account in seconds.'],
        ['step' => '2', 'title' => 'Book Token',   'desc' => 'Choose your department and doctor.'],
        ['step' => '3', 'title' => 'Track Queue',  'desc' => 'Watch the live queue from anywhere.'],
        ['step' => '4', 'title' => 'Get Called',   'desc' => 'Visit the doctor when your token is called.'],
    ];
    foreach ($steps as $s):
    ?>
    <div class="col-6 col-md-3 text-center">
        <div class="card p-3 h-100">
            <div class="rounded-circle bg-primary text-white fw-bold fs-5 mx-auto mb-2"
                 style="width:44px;height:44px;line-height:44px;"><?= $s['step'] ?></div>
            <h6 class="fw-bold"><?= $s['title'] ?></h6>
            <p class="text-muted small mb-0"><?= $s['desc'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>