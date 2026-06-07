<?php
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /opd-system/pages/login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header("Location: /opd-system/pages/unauthorized.php");
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>