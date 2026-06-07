<?php
session_start();
require '../config/db.php';
include '../includes/header.php';
?>

<h4 class="text-primary mb-4">Live OPD Queue</h4>

<?php
$tokens = $pdo->query("
    SELECT t.token_no, t.status, t.booked_at,
           u.username, d.name AS doctor_name, dep.name AS dept_name
    FROM tokens t
    JOIN users u ON t.user_id = u.id
    JOIN doctors d ON t.doctor_id = d.id
    JOIN departments dep ON t.dept_id = dep.id
    WHERE DATE(t.booked_at) = CURDATE()
    ORDER BY t.token_no ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="table-responsive">
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
    <?php foreach ($tokens as $t): ?>
    <tr>
      <td><strong>#<?= $t['token_no'] ?></strong></td>
      <td><?= htmlspecialchars($t['username']) ?></td>
      <td><?= htmlspecialchars($t['dept_name']) ?></td>
      <td><?= htmlspecialchars($t['doctor_name']) ?></td>
      <td>
        <?php
          $badges = [
            'waiting'   => 'warning',
            'called'    => 'info',
            'done'      => 'success',
            'cancelled' => 'danger'
          ];
          $b = $badges[$t['status']] ?? 'secondary';
        ?>
        <span class="badge bg-<?= $b ?>"><?= ucfirst($t['status']) ?></span>
      </td>
      <td><?= date('h:i A', strtotime($t['booked_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($tokens)): ?>
      <tr><td colspan="6" class="text-center text-muted">No tokens booked today.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php include '../includes/footer.php'; ?>