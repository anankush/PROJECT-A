<?php
session_start();

// Clear student-specific session variables
unset($_SESSION['student_id']);
unset($_SESSION['student_name']);
unset($_SESSION['student_email']);

// Destroy the session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirect to login
header('Location: login.php');
exit();
?>
