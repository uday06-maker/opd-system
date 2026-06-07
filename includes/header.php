<nav class="navbar navbar-dark bg-primary px-4 d-flex justify-content-between">
    <a class="navbar-brand fw-bold" href="/opd-system/index.php">Hospital OPD System</a>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="text-white me-3">Hi, <?= $_SESSION['username'] ?></span>
            <?php if ($_SESSION['role'] === 'patient'): ?>
                <a href="/opd-system/pages/book_token.php" class="btn btn-light btn-sm me-2">Book Token</a>
                <a href="/opd-system/pages/my_tokens.php" class="btn btn-light btn-sm me-2">My Tokens</a>
            <?php endif; ?>
            <a href="/opd-system/pages/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        <?php else: ?>
            <a href="/opd-system/pages/login.php" class="btn btn-light btn-sm me-2">Login</a>
            <a href="/opd-system/pages/register.php" class="btn btn-outline-light btn-sm">Register</a>
        <?php endif; ?>
    </div>
</nav>