<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'] ?? null;
    // Always set default status to pending for new payments
    if (!isset($_POST['status']) || empty($_POST['status'])) {
        $status = 'pending';
    } else {
        $status = $_POST['status'];
    }
    $remarks = $_POST['remarks'] ?? '';

    if ($payment_id) {
        // Begin transaction
        $conn->begin_transaction();
        try {
            // Update payment status and remarks
            $stmt = $conn->prepare("UPDATE payments SET status = ?, admin_remarks = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $remarks, $payment_id);
            $stmt->execute();

            // Get student email for notification
            $email_stmt = $conn->prepare("SELECT s.email, s.name FROM payments p 
                                        JOIN students s ON p.student_id = s.id 
                                        WHERE p.id = ?");
            $email_stmt->bind_param("i", $payment_id);
            $email_stmt->execute();
            $student = $email_stmt->get_result()->fetch_assoc();

            // Send email notification
            if ($student) {
                $subject = "Payment Status Update";
                $message = "Dear {$student['name']},\n\n";
                $message .= "Your payment status has been updated to: " . ucfirst($status);
                if (!empty($remarks)) {
                    $message .= "\n\nRemarks: {$remarks}";
                }
                $message .= "\n\nRegards,\nCollege Administration";
                
                mail($student['email'], $subject, $message);
            }

            $conn->commit();
            $_SESSION['success'] = "Payment status updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error updating payment status!";
        }
    }
    
    header('Location: view_payment.php?id=' . $payment_id);
    exit();
}
?>
