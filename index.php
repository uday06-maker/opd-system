<?php
session_start();
require 'config/db.php';
include 'includes/header.php';

$total_today = $pdo->query("SELECT COUNT(*) FROM tokens WHERE DATE(booked_at) = CURDATE()")->fetchColumn();
$waiting     = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='waiting' AND DATE(booked_at) = CURDATE()")->fetchColumn();
$done        = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='done' AND DATE(booked_at) = CURDATE()")->fetchColumn();
$dept_count  = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
?>

<!-- Hero -->
<div class="opd-hero mb-4">
    <h1>Hospital OPD Queue System</h1>
    <p>Book your token online. Track your queue in real time. Skip the wait.</p>
    <div class="d-flex gap-3 flex-wrap">
        <a href="pages/book_token.php" class="btn-white">Book a Token</a>
        <a href="pages/view_queue.php" class="btn-ghost">View Live Queue</a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['icon'=>'🏥','label'=>'Departments',      'val'=>$dept_count,  'cls'=>'blue'],
        ['icon'=>'🎫','label'=>'Tokens Today',     'val'=>$total_today, 'cls'=>'amber'],
        ['icon'=>'⏳','label'=>'Waiting Now',      'val'=>$waiting,     'cls'=>'red'],
        ['icon'=>'✅','label'=>'Consultations Done','val'=>$done,        'cls'=>'green'],
    ];
    foreach ($stats as $s): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-<?= $s['cls'] ?>"><?= $s['icon'] ?></div>
            <div>
                <div class="stat-val"><?= $s['val'] ?></div>
                <div class="stat-lbl"><?= $s['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- How it works -->
<div class="section-title">How it works</div>
<div class="row g-3 mb-2">
    <?php
    $steps = [
        ['n'=>'1','title'=>'Register',    'desc'=>'Create a free patient account in seconds.'],
        ['n'=>'2','title'=>'Book Token',  'desc'=>'Choose your department and preferred doctor.'],
        ['n'=>'3','title'=>'Track Queue', 'desc'=>'Watch the live queue from anywhere in real time.'],
        ['n'=>'4','title'=>'Get Called',  'desc'=>'Visit the doctor when your number is called.'],
    ];
    foreach ($steps as $s): ?>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center h-100">
            <div class="step-circle"><?= $s['n'] ?></div>
            <h6 class="fw-bold mb-1"><?= $s['title'] ?></h6>
            <p class="text-muted small mb-0"><?= $s['desc'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>