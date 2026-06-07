<?php
session_start();
require '../config/db.php';
include '../includes/header.php';

// Search
$search = trim($_GET['search'] ?? '');

// Pagination
$per_page    = 5;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

// Build query with search
$where = "WHERE DATE(t.booked_at) = CURDATE()";
$params = [];

if ($search !== '') {
    $where .= " AND (u.username LIKE ? OR d.name LIKE ? OR dep.name LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
}

// Total count for pagination
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) FROM tokens t
    JOIN users u ON t.user_id = u.id
    JOIN doctors d ON t.doctor_id = d.id
    JOIN departments dep ON t.dept_id = dep.id
    $where
");
$count_stmt->execute($params);
$total      = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch paginated results
$stmt = $pdo->prepare("
    SELECT t.id, t.token_no, t.status, t.booked_at,
           u.username, d.name AS doctor_name, dep.name AS dept_name
    FROM tokens t
    JOIN users u ON t.user_id = u.id
    JOIN doctors d ON t.doctor_id = d.id
    JOIN departments dep ON t.dept_id = dep.id
    $where
    ORDER BY t.token_no ASC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="text-primary mb-0">Live OPD Queue</h4>
    <span class="badge bg-success" id="live-badge">Live</span>
</div>

<!-- Search Form -->
<form method="GET" class="mb-4">
    <div class="input-group">
        <input type="text" name="search" class="form-control"
               placeholder="Search by patient, doctor or department..."
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary" type="submit">Search</button>
        <?php if ($search): ?>
            <a href="view_queue.php" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($search): ?>
    <p class="text-muted mb-3">Showing results for "<strong><?= htmlspecialchars($search) ?></strong>" — <?= $total ?> found</p>
<?php endif; ?>

<!-- Queue Table -->
<div class="table-responsive" id="queue-table">
<table class="table table-hover align-middle">
    <thead class="table-primary">
        <tr>
            <th>Token #</th>
            <th>Patient</th>
            <th>Department</th>
            <th>Doctor</th>
            <th>Status</th>
            <th>Booked At</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($tokens)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <?= $search ? 'No results found.' : 'No tokens booked today.' ?>
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
                <td><strong class="text-primary">#<?= $t['token_no'] ?></strong></td>
                <td><?= htmlspecialchars($t['username']) ?></td>
                <td><?= htmlspecialchars($t['dept_name']) ?></td>
                <td><?= htmlspecialchars($t['doctor_name']) ?></td>
                <td><span class="badge bg-<?= $b ?>"><?= ucfirst($t['status']) ?></span></td>
                <td><?= date('h:i A', strtotime($t['booked_at'])) ?></td>
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
            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Live refresh every 15 seconds (only when no search active) -->
<?php if (!$search): ?>
<script>
    setTimeout(function () {
        location.reload();
    }, 15000);

    // Blink the live badge
    setInterval(function () {
        const badge = document.getElementById('live-badge');
        badge.style.opacity = badge.style.opacity === '0' ? '1' : '0';
    }, 800);
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>