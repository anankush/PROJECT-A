<?php
require_once '../config/db_connection.php';
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$payment_id = $_GET['id'];

$query = "DELETE FROM payments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $payment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Payment record deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting payment record.";
}

header('Location: dashboard.php');
exit();
