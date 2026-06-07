<?php
session_start();
require '../config/db.php';
include '../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === 'doctor') {
            header("Location: doctor_dashboard.php");
        } else {
            header("Location: book_token.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h4 class="mb-3 text-primary">Login</h4>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <p class="mt-3 text-center">New patient? <a href="register.php">Register here</a></p>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>