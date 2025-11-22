<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!checkLoginAttempts($conn, $email)) {
        $error = "Too many failed attempts. Please try again after 15 minutes.";
    } else {
        $sql = "SELECT id, name, email, password FROM students WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                updateLoginAttempts($conn, $email, true);
                $_SESSION['student_id'] = $user['id'];
                $_SESSION['student_name'] = $user['name'];
                $_SESSION['student_email'] = $user['email'];
                header("Location: dashboard.php");
                exit();
            } else {
                updateLoginAttempts($conn, $email);
                $error = "Invalid password!";
            }
        } else {
            $error = "Email not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #455a64;
            --highlight-color: #5c6bc0;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            height: 100vh;
        }
        
        .btn-custom {
            background: linear-gradient(120deg, var(--highlight-color), var(--accent-color));
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(92, 107, 192, 0.4);
        }
        
        .card {
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            animation: fadeIn 0.8s ease;
        }
        
        .form-control:focus {
            border-color: var(--highlight-color);
            box-shadow: 0 0 0 0.2rem rgba(92, 107, 192, 0.25);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-5">Student Login</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn btn-custom w-100 mb-3">Login</button>
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                            </div>
                        </form>
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
