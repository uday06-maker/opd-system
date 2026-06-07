<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
include '../includes/header.php';

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password']          ?? '';
    $old      = ['email' => $email];

    // Server-side validation
    if (empty($email))
        $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Enter a valid email address.";

    if (empty($password))
        $errors[] = "Password is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

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
            // Small delay to slow brute force
            sleep(1);
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h4 class="mb-3 text-primary">Login</h4>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= $e ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="email"
                 class="form-control" required
                 value="<?= $old['email'] ?? '' ?>">
          <div class="invalid-feedback">Enter a valid email.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" id="password"
                 class="form-control" required>
          <div class="invalid-feedback">Password is required.</div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <p class="mt-3 text-center">
          New patient? <a href="register.php">Register here</a>
        </p>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let valid = true;
    const email    = document.getElementById('email');
    const password = document.getElementById('password');

    [email, password].forEach(f => f.classList.remove('is-invalid'));

    if (!email.value.includes('@')) {
        email.classList.add('is-invalid'); valid = false;
    }
    if (password.value.trim() === '') {
        password.classList.add('is-invalid'); valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>

<?php include '../includes/footer.php'; ?>