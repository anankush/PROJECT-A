<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

// Debug logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    $_SESSION['error'] = "Missing parameters";
    header('Location: dashboard.php');
    exit();
}

$id = $_GET['id'];
$status = $_GET['status'];

// Validate status
if (!in_array($status, ['approved', 'rejected'])) {
    $_SESSION['error'] = "Invalid status";
    header('Location: dashboard.php');
    exit();
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Update application status
    $update_sql = "UPDATE admission_forms SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No application found with ID: " . $id);
    }

    // Get student email for notification
    $email_sql = "SELECT s.email, af.name, af.course 
                  FROM admission_forms af 
                  JOIN students s ON af.student_id = s.id 
                  WHERE af.id = ?";
    
    $stmt2 = $conn->prepare($email_sql);
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        // Send email notification
        $to = $student['email'];
        $subject = "Application Status Update";
        $message = "Dear {$student['name']},\n\n";
        $message .= "Your application for {$student['course']} has been {$status}.\n";
        $message .= "Thank you for your interest in our institution.\n\n";
        $message .= "Best regards,\nCollege Administration";
        
        mail($to, $subject, $message);
    }

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = "Application has been " . $status;

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log("Error in update_status.php: " . $e->getMessage());
    $_SESSION['error'] = "Error updating application: " . $e->getMessage();
}

header('Location: dashboard.php');
exit();
