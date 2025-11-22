<?php
include('../config/db_connection.php');
session_start();

if(isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "SELECT * FROM admin WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(32));
        $update_query = "UPDATE admin SET reset_token = '$token', reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = '$email'";
        mysqli_query($conn, $update_query);
        
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
        
        // Send email
        $to = $email;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: " . $reset_link;
        $headers = "From: noreply@yourwebsite.com";
        
        mail($to, $subject, $message, $headers);
        
        $success = "Password reset instructions have been sent to your email.";
    } else {
        $error = "Email address not found.";
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
        :root {
            --primary-color: #1a237e;    /* Dark blue */
            --secondary-color: #283593;  /* Mid blue */
            --accent-color: #3949ab;    /* Light blue */
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), var(--accent-color));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
        }
        .btn-custom {
            background: linear-gradient(120deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white;
            padding: 10px 0;
            font-weight: 500;
        }
        .btn-custom:hover {
            color: white;
            opacity: 0.9;
        }
        .form-control {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-5">Admin Password Recovery</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if(isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <hr>
                                <p class="mb-0">Please check your email for the reset link.</p>
                            </div>
                        <?php endif; ?>

                        <?php if(!isset($success)): ?>
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <button type="submit" name="submit" class="btn btn-custom w-100 mb-4">Send Reset Link</button>
                                <div class="text-center">
                                    <p class="mb-0">Remember your password? <a href="login.php" class="text-primary text-decoration-none">Login here</a></p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="../index.php" class="btn btn-light px-4">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
