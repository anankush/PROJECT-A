<?php
session_start();
require_once '../config/db_connection.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $admin_key = $_POST['admin_key'];

    // Validate admin key
    $key_result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_auth_key'")->fetch_assoc();
    
    if ($admin_key !== $key_result['setting_value']) {
        $error = "Invalid admin key!";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, is_temp_password FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['is_admin'] = true;
            
            // Check if using temporary password
            if ($user['is_temp_password'] == 1) {
                header("Location: change_password.php?temp=1");
                exit();
            }
            
            header("Location: dashboard.php");
            exit();
        }
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;    /* Dark slate */
            --secondary-color: #34495e;  /* Medium slate */
            --accent-color: #455a64;    /* Blue-grey */
            --highlight-color: #5c6bc0; /* Indigo */
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .form-control {
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            transform: translateY(-2px);
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-5">Admin Login</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="admin_key" class="form-label">Admin Authorization Key</label>
                                <input type="password" class="form-control" id="admin_key" name="admin_key" required>
                                <div class="form-text text-muted">Enter the admin authorization key</div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <button type="submit" class="btn btn-custom w-100 mb-3">Login</button>
                            <div class="text-center">
                                <p class="mb-2">Need an admin account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                                <p class="mb-0"><a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a></p>
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
