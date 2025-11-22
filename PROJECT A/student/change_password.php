<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password = ?, is_temp_password = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['student_id']);
        
        if ($stmt->execute()) {
            header("Location: dashboard.php?msg=password_updated");
            exit();
        } else {
            $error = "Failed to update password";
        }
    } else {
        $error = "Passwords do not match";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2c3e50, #34495e); height: 100vh; }
        .card { border-radius: 1rem; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .btn-custom { background: linear-gradient(120deg, #5c6bc0, #455a64); border: none; color: white; }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Change Password</h2>
                        <?php if(isset($_GET['temp'])): ?>
                            <div class="alert alert-warning">
                                You must change your temporary password before continuing.
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-custom w-100">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
