<?php
include('../includes/config.php');
session_start();

if(!isset($_GET['token'])) {
    header('Location: login.php');
    exit();
}

$token = $_GET['token'];

// Verify token and check if it's expired
$query = "SELECT * FROM admin WHERE reset_token = '$token' AND reset_token_expires > NOW()";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header('Location: login.php');
    exit();
}

if(isset($_POST['submit'])) {
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    if($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE admin SET password = '$hashed_password', reset_token = NULL, reset_token_expires = NULL WHERE reset_token = '$token'";
        mysqli_query($conn, $update_query);
        
        header('Location: login.php');
        exit();
    } else {
        $error = "Passwords do not match.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Admin</title>
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
                        <h2 class="text-center mb-5">Reset Admin Password</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="submit" class="btn btn-custom w-100 mb-3">Update Password</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-4"></div>
                    <a href="../index.php" class="btn btn-light px-4">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
