<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
requireRole('patient');
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$stmt = $pdo->prepare("
    SELECT t.id, t.token_no, t.status, t.booked_at,
           d.name AS doctor_name, dep.name AS dept_name
    FROM tokens t
    JOIN doctors d ON t.doctor_id = d.id
    JOIN departments dep ON t.dept_id = dep.id
    WHERE t.user_id = ?
    ORDER BY t.booked_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h4 class="text-primary mb-4">My Bookings</h4>

<table class="table table-hover align-middle">
  <thead class="table-primary">
    <tr>
      <th>Token #</th><th>Department</th><th>Doctor</th><th>Status</th><th>Date</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($tokens as $t): ?>
    <tr>
      <td><strong>#<?= $t['token_no'] ?></strong></td>
      <td><?= htmlspecialchars($t['dept_name']) ?></td>
      <td><?= htmlspecialchars($t['doctor_name']) ?></td>
      <td>
        <?php $badges = ['waiting'=>'warning','called'=>'info','done'=>'success','cancelled'=>'danger']; ?>
        <span class="badge bg-<?= $badges[$t['status']] ?? 'secondary' ?>"><?= ucfirst($t['status']) ?></span>
      </td>
      <td><?= date('d M Y, h:i A', strtotime($t['booked_at'])) ?></td>
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
    <?php if (empty($tokens)): ?>
      <tr><td colspan="6" class="text-center text-muted">No bookings yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>