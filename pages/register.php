<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
include '../includes/header.php';

$errors  = [];
$success = '';
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password']          ?? '';
    $confirm  = $_POST['confirm']           ?? '';
    $old      = compact('username', 'email');

    // Server-side validation
    if (empty($username))
        $errors[] = "Username is required.";
    elseif (strlen($username) < 3)
        $errors[] = "Username must be at least 3 characters.";
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username))
        $errors[] = "Username can only contain letters, numbers and underscores.";

    if (empty($email))
        $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Enter a valid email address.";

    if (empty($password))
        $errors[] = "Password is required.";
    elseif (strlen($password) < 6)
        $errors[] = "Password must be at least 6 characters.";

    if ($password !== $confirm)
        $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare(
                "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
            );
            $stmt->execute([$username, $email, $hashed]);
            $success = "Registered successfully! <a href='login.php'>Login here</a>";
            $old     = [];
        } catch (PDOException $e) {
            $errors[] = "Username or email already exists.";
        }
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h4 class="mb-3 text-primary">Patient Registration</h4>

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

      <form method="POST" id="registerForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" id="username"
                 class="form-control" required minlength="3"
                 pattern="^[a-zA-Z0-9_]+$"
                 value="<?= $old['username'] ?? '' ?>">
          <div class="invalid-feedback">
            At least 3 characters, letters/numbers/underscore only.
          </div>
        </div>
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
                 class="form-control" required minlength="6">
          <div class="invalid-feedback">Minimum 6 characters.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm" id="confirm"
                 class="form-control" required>
          <div class="invalid-feedback">Passwords must match.</div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="mt-3 text-center">
          Already have an account? <a href="login.php">Login</a>
        </p>
      </form>
    </div>
  </div>
</div>

<script>
// Client-side validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;

    const username = document.getElementById('username');
    const email    = document.getElementById('email');
    const password = document.getElementById('password');
    const confirm  = document.getElementById('confirm');

    // Reset
    [username, email, password, confirm].forEach(f => f.classList.remove('is-invalid'));

    if (username.value.trim().length < 3 || !/^[a-zA-Z0-9_]+$/.test(username.value)) {
        username.classList.add('is-invalid'); valid = false;
    }
    if (!email.value.includes('@') || !email.value.includes('.')) {
        email.classList.add('is-invalid'); valid = false;
    }
    if (password.value.length < 6) {
        password.classList.add('is-invalid'); valid = false;
    }
    if (confirm.value !== password.value) {
        confirm.classList.add('is-invalid'); valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>

<?php include '../includes/footer.php'; ?>