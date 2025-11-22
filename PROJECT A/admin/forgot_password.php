<?php
session_start();
require_once '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $temp_password = bin2hex(random_bytes(8));
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE admin SET password = ?, is_temp_password = 1 WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            $success = "Your temporary password is: " . $temp_password . "<br>Please change it immediately after logging in.";
        }
    } else {
        $success = "If your email exists, you will receive reset instructions.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #2c3e50, #3498db);
            height: 100vh;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background: linear-gradient(120deg, #2c3e50, #3498db);
            border: none;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-5">Forgot Password</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if(isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <hr>
                                <p class="mb-0">Please <a href="login.php">login</a> with your temporary password and change it immediately.</p>
                            </div>
                        <?php endif; ?>

                        <?php if(!isset($success)): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-custom w-100 mb-3">Reset Password</button>
                                <div class="text-center">
                                    <p class="mb-0">Remember your password? <a href="login.php" class="text-decoration-none">Login here</a></p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../index.php" class="btn btn-light">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
