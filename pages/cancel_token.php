<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE tokens SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}

header("Location: my_tokens.php");
exit;
?>