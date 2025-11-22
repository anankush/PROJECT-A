<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? null;
    $status = $_POST['status'] ?? 'pending';
    $remarks = $_POST['remarks'] ?? '';

    if ($application_id) {
        // Begin transaction
        $conn->begin_transaction();
        try {
            // Update application status and remarks
            $stmt = $conn->prepare("UPDATE admission_forms SET status = ?, admin_remarks = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $remarks, $application_id);
            $stmt->execute();

            // Get student email for notification
            $email_stmt = $conn->prepare("SELECT s.email, s.name FROM admission_forms af 
                                        JOIN students s ON af.student_id = s.id 
                                        WHERE af.id = ?");
            $email_stmt->bind_param("i", $application_id);
            $email_stmt->execute();
            $student = $email_stmt->get_result()->fetch_assoc();

            // Send email notification
            if ($student) {
                $subject = "Application Status Update";
                $message = "Dear {$student['name']},\n\n";
                $message .= "Your application status has been updated to: " . ucfirst($status);
                if (!empty($remarks)) {
                    $message .= "\n\nRemarks: {$remarks}";
                }
                $message .= "\n\nRegards,\nCollege Administration";
                
                mail($student['email'], $subject, $message);
            }

            $conn->commit();
            $_SESSION['success'] = "Application status updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error updating application status!";
        }
    }
    
    header('Location: view_application.php?id=' . $application_id);
    exit();
}
?>
