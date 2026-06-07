<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';

// Handle add doctor form
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_doctor') {
        $name    = sanitize($_POST['name']    ?? '');
        $dept_id = intval($_POST['dept_id']   ?? 0);
        $email   = sanitize($_POST['email']   ?? '');
        $pass    = $_POST['password']         ?? '';

        if (empty($name) || empty($email) || empty($pass) || $dept_id === 0) {
            $errors[] = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        } elseif (strlen($pass) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        } else {
            try {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                // Create user account
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, email, password, role)
                     VALUES (?, ?, ?, 'doctor')"
                );
                $stmt->execute([$name, $email, $hashed]);
                $user_id = $pdo->lastInsertId();

                // Create doctor record
                $stmt = $pdo->prepare(
                    "INSERT INTO doctors (user_id, name, dept_id) VALUES (?, ?, ?)"
                );
                $stmt->execute([$user_id, $name, $dept_id]);
                $success = "Doctor added successfully!";
            } catch (PDOException $e) {
                $errors[] = "Email already exists.";
            }
        }
    }

    if ($_POST['action'] === 'toggle_doctor') {
        $doc_id    = intval($_POST['doctor_id']);
        $available = intval($_POST['available']);
        $stmt      = $pdo->prepare("UPDATE doctors SET available = ? WHERE id = ?");
        $stmt->execute([$available, $doc_id]);
        header("Location: admin_dashboard.php"); exit;
    }
}

// Fetch stats
$total_patients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$total_doctors  = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_today    = $pdo->query("SELECT COUNT(*) FROM tokens WHERE DATE(booked_at)=CURDATE()")->fetchColumn();
$total_done     = $pdo->query("SELECT COUNT(*) FROM tokens WHERE status='done' AND DATE(booked_at)=CURDATE()")->fetchColumn();

// Fetch all doctors
$doctors = $pdo->query("
    SELECT d.*, dep.name AS dept_name, u.email
    FROM doctors d
    JOIN departments dep ON d.dept_id = dep.id
    LEFT JOIN users u ON d.user_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch departments for form
$depts = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
?>

<h4 class="text-primary mb-4">Admin Dashboard</h4>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'Total Patients', 'value' => $total_patients, 'color' => 'primary'],
        ['label' => 'Total Doctors',  'value' => $total_doctors,  'color' => 'info'],
        ['label' => 'Tokens Today',   'value' => $total_today,    'color' => 'warning'],
        ['label' => 'Done Today',     'value' => $total_done,     'color' => 'success'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fs-2 fw-bold text-<?= $c['color'] ?>"><?= $c['value'] ?></div>
            <div class="text-muted small"><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">

    <!-- Add Doctor Form -->
    <div class="col-md-5">
        <div class="card p-4">
            <h5 class="mb-3 text-primary">Add New Doctor</h5>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="add_doctor">
                <div class="mb-3">
                    <label class="form-label">Doctor Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select name="dept_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($depts as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Login Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-100">Add Doctor</button>
            </form>
        </div>
    </div>

    <!-- Doctors List -->
    <div class="col-md-7">
        <div class="card p-4">
            <h5 class="mb-3 text-primary">All Doctors</h5>
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctors as $doc): ?>
                    <tr>
                        <td><?= htmlspecialchars($doc['name']) ?></td>
                        <td><?= htmlspecialchars($doc['dept_name']) ?></td>
                        <td><?= htmlspecialchars($doc['email'] ?? '—') ?></td>
                        <td>
                            <span class="badge bg-<?= $doc['available'] ? 'success' : 'secondary' ?>">
                                <?= $doc['available'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle_doctor">
                                <input type="hidden" name="doctor_id" value="<?= $doc['id'] ?>">
                                <input type="hidden" name="available"
                                       value="<?= $doc['available'] ? 0 : 1 ?>">
                                <button class="btn btn-sm btn-<?= $doc['available'] ? 'warning' : 'success' ?>">
                                    <?= $doc['available'] ? 'Disable' : 'Enable' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>