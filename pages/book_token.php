<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
requireRole('patient');
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php"); exit;
}

$success = '';

// Fetch departments
$depts = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);

// Fetch doctors based on selected dept
$doctors = [];
if (!empty($_GET['dept_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE dept_id = ? AND available = 1");
    $stmt->execute([$_GET['dept_id']]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $dept_id   = $_POST['dept_id'];
    $user_id   = $_SESSION['user_id'];

    // Get next token number for this doctor
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tokens WHERE doctor_id = ? AND DATE(booked_at) = CURDATE()");
    $stmt->execute([$doctor_id]);
    $token_no = $stmt->fetchColumn() + 1;

    $stmt = $pdo->prepare("INSERT INTO tokens (user_id, doctor_id, dept_id, token_no) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $doctor_id, $dept_id, $token_no]);

    $success = "Token booked! Your token number is <strong>#$token_no</strong>";
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h4 class="mb-3 text-primary">Book Your Token</h4>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>

      <form method="GET" class="mb-3">
        <label class="form-label">Select Department</label>
        <select name="dept_id" class="form-select" onchange="this.form.submit()">
          <option value="">-- Choose Department --</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($_GET['dept_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if (!empty($doctors)): ?>
      <form method="POST">
        <input type="hidden" name="dept_id" value="<?= $_GET['dept_id'] ?>">
        <div class="mb-3">
          <label class="form-label">Select Doctor</label>
          <select name="doctor_id" class="form-select" required>
            <option value="">-- Choose Doctor --</option>
            <?php foreach ($doctors as $doc): ?>
              <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-success w-100">Confirm Booking</button>
      </form>
      <?php elseif (!empty($_GET['dept_id'])): ?>
        <div class="alert alert-warning">No doctors available in this department right now.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>