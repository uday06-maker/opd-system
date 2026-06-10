<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
requireRole('patient');
include '../includes/header.php';

$stmt = $pdo->prepare("
    SELECT t.id, t.token_no, t.status, t.booked_at, t.called_at,
           d.name AS doctor_name, dep.name AS dept_name
    FROM tokens t
    JOIN doctors d ON t.doctor_id = d.id
    JOIN departments dep ON t.dept_id = dep.id
    WHERE t.user_id = ?
    ORDER BY t.booked_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count stats
$waiting  = array_filter($tokens, fn($t) => $t['status'] === 'waiting');
$done     = array_filter($tokens, fn($t) => $t['status'] === 'done');
$cancel   = array_filter($tokens, fn($t) => $t['status'] === 'cancelled');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary mb-0">My Bookings</h4>
    <a href="book_token.php" class="btn btn-primary btn-sm">+ Book New Token</a>
</div>

<!-- Mini stats -->
<div class="row g-3 mb-4">
    <?php
    $ms = [
        ['label'=>'Total Bookings','val'=>count($tokens),    'cls'=>'blue'],
        ['label'=>'Waiting',       'val'=>count($waiting),   'cls'=>'amber'],
        ['label'=>'Completed',     'val'=>count($done),      'cls'=>'green'],
        ['label'=>'Cancelled',     'val'=>count($cancel),    'cls'=>'red'],
    ];
    foreach ($ms as $m): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?= $m['val'] ?></div>
                <div class="stat-lbl"><?= $m['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="section-title">Booking History</div>
<div class="card">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-primary">
        <tr>
            <th>Token #</th>
            <th>Department</th>
            <th>Doctor</th>
            <th>Status</th>
            <th>Booked At</th>
            <th>Called At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($tokens)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    No bookings yet.
                    <a href="book_token.php" class="btn btn-primary btn-sm ms-2">Book Now</a>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($tokens as $t):
                $badges = [
                    'waiting'   => 'warning',
                    'called'    => 'info',
                    'done'      => 'success',
                    'cancelled' => 'danger'
                ];
                $b = $badges[$t['status']] ?? 'secondary';
            ?>
            <tr>
                <td><span class="token-num">#<?= $t['token_no'] ?></span></td>
                <td><?= htmlspecialchars($t['dept_name']) ?></td>
                <td><?= htmlspecialchars($t['doctor_name']) ?></td>
                <td><span class="badge bg-<?= $b ?>"><?= ucfirst($t['status']) ?></span></td>
                <td><?= date('d M Y, h:i A', strtotime($t['booked_at'])) ?></td>
                <td>
                    <?= $t['called_at']
                        ? date('h:i A', strtotime($t['called_at']))
                        : '<span class="text-muted">—</span>' ?>
                </td>
                <td>
                    <?php if ($t['status'] === 'waiting'): ?>
                        <a href="cancel_token.php?id=<?= $t['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Cancel this token?')">Cancel</a>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>
</div>

<?php include '../includes/footer.php'; ?>