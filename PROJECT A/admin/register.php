<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $admin_key = trim($_POST['admin_key']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password strength
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // First, verify the admin key
        $key_sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'admin_auth_key'";
        $key_result = $conn->query($key_sql);
        
        if (!$key_result || $key_result->num_rows === 0) {
            $error = "System configuration error. Please contact administrator.";
        } else {
            $key_row = $key_result->fetch_assoc();
            $valid_key = $key_row['setting_value'];

            if ($admin_key !== $valid_key) {
                $error = "Invalid admin key!";
            } else if ($password !== $confirm_password) {
                $error = "Passwords do not match!";
            } else {
                // Check if email already exists
                $check_sql = "SELECT id FROM admin WHERE email = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error = "Email already registered!";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $sql = "INSERT INTO admin (name, email, password) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $name, $email, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Registration successful! Please login.";
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Registration failed! Please try again.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #2c3e50, #3498db);
            height: 100vh;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            animation: slideUp 0.8s ease;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .btn-custom {
            background: linear-gradient(120deg, #2c3e50, #3498db);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        .form-control {
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            transform: translateY(-2px);
        }
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        .alert {
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-5">Admin Registration</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="admin_key" class="form-label">Admin Authorization Key</label>
                                <input type="password" class="form-control" id="admin_key" name="admin_key" required>
                                <div class="form-text text-muted">Enter the admin authorization key</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-custom w-100 mb-3">Register</button>
                            <div class="text-center">
                                <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
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
