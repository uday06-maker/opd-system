<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';

// Overall stats
$total_patients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$total_doctors  = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_tokens   = $pdo->query("SELECT COUNT(*) FROM tokens")->fetchColumn();
$total_done     = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='done'")->fetchColumn();

// Today's stats
$today_tokens   = $pdo->query("SELECT COUNT(*) FROM tokens WHERE DATE(booked_at)=CURDATE()")->fetchColumn();
$today_waiting  = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='waiting' AND DATE(booked_at)=CURDATE()")->fetchColumn();
$today_done     = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='done' AND DATE(booked_at)=CURDATE()")->fetchColumn();
$today_cancel   = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='cancelled' AND DATE(booked_at)=CURDATE()")->fetchColumn();

// Department wise stats
$dept_stats = $pdo->query("
    SELECT dep.name, COUNT(t.id) AS total,
           SUM(t.status='done') AS done,
           SUM(t.status='waiting') AS waiting,
           SUM(t.status='cancelled') AS cancelled
    FROM departments dep
    LEFT JOIN tokens t ON t.dept_id = dep.id AND DATE(t.booked_at) = CURDATE()
    GROUP BY dep.id, dep.name
")->fetchAll(PDO::FETCH_ASSOC);

// Doctor wise stats
$doc_stats = $pdo->query("
    SELECT d.name AS doctor, dep.name AS dept,
           COUNT(t.id) AS total,
           SUM(t.status='done') AS done,
           SUM(t.status='waiting') AS waiting,
           SUM(t.status='cancelled') AS cancelled
    FROM doctors d
    JOIN departments dep ON d.dept_id = dep.id
    LEFT JOIN tokens t ON t.doctor_id = d.id AND DATE(t.booked_at) = CURDATE()
    GROUP BY d.id
")->fetchAll(PDO::FETCH_ASSOC);

// Last 7 days trend
$trend = $pdo->query("
    SELECT DATE(booked_at) AS day, COUNT(*) AS total,
           SUM(status='done') AS done
    FROM tokens
    WHERE booked_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(booked_at)
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary mb-0">Reports & Analytics</h4>
    <span class="badge bg-info">Today: <?= date('d M Y') ?></span>
</div>

<!-- Overall Stats -->
<div class="section-title">Overall Statistics</div>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['icon'=>'👥','label'=>'Total Patients', 'val'=>$total_patients,'cls'=>'blue'],
        ['icon'=>'🩺','label'=>'Total Doctors',  'val'=>$total_doctors, 'cls'=>'green'],
        ['icon'=>'🎫','label'=>'All Time Tokens','val'=>$total_tokens,  'cls'=>'amber'],
        ['icon'=>'✅','label'=>'Completed',       'val'=>$total_done,   'cls'=>'green'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-<?= $c['cls'] ?>"><?= $c['icon'] ?></div>
            <div>
                <div class="stat-val"><?= $c['val'] ?></div>
                <div class="stat-lbl"><?= $c['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Today's Stats -->
<div class="section-title">Today's Summary</div>
<div class="row g-3 mb-4">
    <?php
    $today_cards = [
        ['label'=>'Total Today',  'val'=>$today_tokens,  'color'=>'primary'],
        ['label'=>'Waiting',      'val'=>$today_waiting, 'color'=>'warning'],
        ['label'=>'Done',         'val'=>$today_done,    'color'=>'success'],
        ['label'=>'Cancelled',    'val'=>$today_cancel,  'color'=>'danger'],
    ];
    foreach ($today_cards as $tc): ?>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 border-<?= $tc['color'] ?>">
            <div class="fs-2 fw-bold text-<?= $tc['color'] ?>"><?= $tc['val'] ?></div>
            <div class="text-muted small"><?= $tc['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- 7 Day Trend -->
<div class="section-title">Last 7 Days Trend</div>
<div class="card p-4 mb-4">
    <canvas id="trendChart" height="100"></canvas>
</div>

<!-- Department Stats -->
<div class="section-title">Department-wise (Today)</div>
<div class="card mb-4">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
            <tr>
                <th>Department</th>
                <th>Total</th>
                <th>Done</th>
                <th>Waiting</th>
                <th>Cancelled</th>
                <th>Completion %</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dept_stats as $d):
                $pct = $d['total'] > 0 ? round(($d['done'] / $d['total']) * 100) : 0;
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                <td><?= $d['total'] ?></td>
                <td><span class="badge bg-success"><?= $d['done'] ?></span></td>
                <td><span class="badge bg-warning"><?= $d['waiting'] ?></span></td>
                <td><span class="badge bg-danger"><?= $d['cancelled'] ?></span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:8px;border-radius:10px;">
                            <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="small fw-bold"><?= $pct ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Doctor Stats -->
<div class="section-title">Doctor-wise (Today)</div>
<div class="card mb-4">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
            <tr>
                <th>Doctor</th>
                <th>Department</th>
                <th>Total</th>
                <th>Done</th>
                <th>Waiting</th>
                <th>Cancelled</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doc_stats as $d): ?>
            <tr>
                <td><strong><?= htmlspecialchars($d['doctor']) ?></strong></td>
                <td><?= htmlspecialchars($d['dept']) ?></td>
                <td><?= $d['total'] ?></td>
                <td><span class="badge bg-success"><?= $d['done'] ?></span></td>
                <td><span class="badge bg-warning"><?= $d['waiting'] ?></span></td>
                <td><span class="badge bg-danger"><?= $d['cancelled'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode(array_map(fn($r) => date('d M', strtotime($r['day'])), $trend)) ?>;
const totals  = <?= json_encode(array_column($trend, 'total')) ?>;
const dones   = <?= json_encode(array_column($trend, 'done')) ?>;

new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Total Tokens',
                data: totals,
                backgroundColor: 'rgba(10,110,189,0.15)',
                borderColor: '#0a6ebd',
                borderWidth: 2,
                borderRadius: 6,
            },
            {
                label: 'Completed',
                data: dones,
                backgroundColor: 'rgba(0,184,148,0.15)',
                borderColor: '#00b894',
                borderWidth: 2,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>