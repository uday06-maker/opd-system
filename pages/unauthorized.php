<?php
session_start();
include '../includes/header.php';
?>

<div class="text-center py-5">
    <div class="display-1 text-danger">403</div>
    <h3 class="text-danger">Access Denied</h3>
    <p class="text-muted">You don't have permission to view this page.</p>
    <a href="/opd-system/index.php" class="btn btn-primary">Go Home</a>
</div>

<?php include '../includes/footer.php'; ?>