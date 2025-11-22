<?php
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Simple timeout check (30 minutes)
if (time() - $_SESSION['last_activity'] > 1800) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time();
?>
