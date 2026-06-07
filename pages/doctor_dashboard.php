<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
requireRole('doctor');
include '../includes/header.php';
}

// Get doctor record linked to this user
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    echo "<div class='alert alert-danger'>Doctor profile not found.</div>";
    include '../includes/footer.php'; exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token_id'], $_POST['status'])) {
    $allowed = ['waiting', 'called', 'done', 'cancelled'];
    if (in_array($_POST['status'], $allowed)) {
        $called_at = $_POST['status'] === 'called' ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare("UPDATE tokens SET status = ?, called_at = ? WHERE id = ? AND doctor_id = ?");
        $stmt->execute([$_POST['status'], $called_at, $_POST['token_id'], $doctor['id']]);
    }
    header("Location: doctor_dashboard.php"); exit;
}

// Pagination
$per_page = 8;
$page     = max(1, intval($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$count_stmt = $pdo->prepare("
    SELECT COUNT(*) FROM tokens
    WHERE doctor_id = ? AND DATE(booked_at) = CURDATE()
");
$count_stmt->execute([$doctor['id']]);
$total       = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("
    SELECT t.id, t.token_no, t.status, t.booked_at, t.called_at,
           u.username, u.email
    FROM tokens t
    JOIN users u ON t.user_id = u.id
    WHERE t.doctor_id = ? AND DATE(t.booked_at) = CURDATE()
    ORDER BY t.token_no ASC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([$doctor['id']]);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats_stmt = $pdo->prepare("
    SELECT
        SUM(status = 'waiting')   AS waiting,
        SUM(status = 'called')    AS called,
        SUM(status = 'done')      AS done,
        SUM(status = 'cancelled') AS cancelled
    FROM tokens
    WHERE doctor_id = ? AND DATE(booked_at) = CURDATE()
");
$stats_stmt->execute([$doctor['id']]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary mb-0">Doctor Dashboard — <?= htmlspecialchars($doctor['name']) ?></h4>
    <span class="badge bg-success">Today's Queue</span>
</div>

<!-- Stats Bar -->
<div class="row g-3 mb-4">
    <?php
    $stat_cards = [
        ['label' => 'Waiting',   'value' => $stats['waiting']   ?? 0, 'color' => 'warning'],
        ['label' => 'Called',    'value' => $stats['called']    ?? 0, 'color' => 'info'],
        ['label' => 'Done',      'value' => $stats['done']      ?? 0, 'color' => 'success'],
        ['label' => 'Cancelled', 'value' => $stats['cancelled'] ?? 0, 'color' => 'danger'],
    ];
    foreach ($stat_cards as $sc):
    ?>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 border-<?= $sc['color'] ?>">
            <div class="fs-2 fw-bold text-<?= $sc['color'] ?>"><?= $sc['value'] ?></div>
            <div class="text-muted small"><?= $sc['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Token Queue Table -->
<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-primary">
        <tr>
            <th>Token #</th>
            <th>Patient</th>
            <th>Email</th>
            <th>Booked At</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($tokens)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No patients in queue today.</td></tr>
        <?php else: ?>
            <?php foreach ($tokens as $t):
                $badges = ['waiting'=>'warning','called'=>'info','done'=>'success','cancelled'=>'danger'];
                $b = $badges[$t['status']] ?? 'secondary';
            ?>
            <tr>
                <td><strong class="text-primary">#<?= $t['token_no'] ?></strong></td>
                <td><?= htmlspecialchars($t['username']) ?></td>
                <td><?= htmlspecialchars($t['email']) ?></td>
                <td><?= date('h:i A', strtotime($t['booked_at'])) ?></td>
                <td><span class="badge bg-<?= $b ?>"><?= ucfirst($t['status']) ?></span></td>
                <td>
                    <?php if ($t['status'] === 'waiting'): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="token_id" value="<?= $t['id'] ?>">
                        <input type="hidden" name="status" value="called">
                        <button class="btn btn-sm btn-info">Call</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($t['status'] === 'called'): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="token_id" value="<?= $t['id'] ?>">
                        <input type="hidden" name="status" value="done">
                        <button class="btn btn-sm btn-success">Done</button>
                    </form>
                    <?php endif; ?>
                    <?php if (in_array($t['status'], ['waiting', 'called'])): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="token_id" value="<?= $t['id'] ?>">
                        <input type="hidden" name="status" value="cancelled">
                        <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Cancel this token?')">Cancel</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($t['status'] === 'done'): ?>
                        <span class="text-muted">Completed</span>
                    <?php endif; ?>
                    <?php if ($t['status'] === 'cancelled'): ?>
                        <span class="text-muted">Cancelled</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
    setTimeout(function () { location.reload(); }, 20000);
</script>

<?php include '../includes/footer.php'; ?>