<?php
require_once '../config/db_connection.php';
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$application_id = $_GET['id'];

// Delete the application
$query = "DELETE FROM admission_forms WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $application_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Application deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting application.";
}

header('Location: dashboard.php');
exit();
