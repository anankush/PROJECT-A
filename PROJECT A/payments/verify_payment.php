<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['student_id']) || !isset($_POST['payment_id'])) {
    header('Location: make_payment.php');
    exit();
}

$payment_id = $_POST['payment_id'];
$utr_number = $_POST['utr_number'] ?? '';

// Verify payment
$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? AND student_id = ?");
$stmt->bind_param("ii", $payment_id, $_SESSION['student_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if ($payment) {
    // Verify UTR number and update status
    if (!empty($utr_number) && $payment['status'] === 'pending') {
        $update = $conn->prepare("UPDATE payments SET status = 'completed' WHERE id = ?");
        $update->bind_param("i", $payment_id);
        
        if ($update->execute()) {
            // Log the verification
            $log = $conn->prepare("INSERT INTO payment_logs (payment_id, action, status, message) VALUES (?, 'verify', 'success', ?)");
            $message = "Payment verified with UTR: " . $utr_number;
            $log->bind_param("is", $payment_id, $message);
            $log->execute();
            
            header('Location: payment_success.php');
            exit();
        }
    }
}

header('Location: payment_failed.php');
exit();
