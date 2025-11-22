<?php
require_once '../config/db_connection.php';
require_once 'check_session.php';

// Add this near the top after session_start() to debug:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get application
$stmt = $conn->prepare("SELECT * FROM admission_forms WHERE student_id = ?");
$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

// Improved file verification
$missing_files = [];
if ($application && $application['status'] === 'approved') {
    $base_dir = '../uploads/';
    $required_files = [
        'photo' => ['dir' => 'photos', 'field' => 'photo_path'],
        'signature' => ['dir' => 'signatures', 'field' => 'signature_path']
    ];
    
    foreach ($required_files as $type => $info) {
        $file_path = $base_dir . $info['dir'] . '/' . $application[$info['field']];
        if (empty($application[$info['field']]) || !file_exists($file_path)) {
            $missing_files[] = ucfirst($type);
        }
    }

    if (!empty($missing_files)) {
        $_SESSION['warning_message'] = "The following files are missing or invalid: " . implode(", ", $missing_files);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2980b9;
            --secondary-color: #8e44ad;
            --accent-color: #9b59b6;
            --status-pending: #f4b400;    /* Yellow */
            --status-approved: #28a745;   /* Green */
            --status-rejected: #dc3545;   /* Red */
        }
        
        body {
            background: #ffffff;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(120deg, var(--primary-color), var(--accent-color));
            animation: slideDown 0.5s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .welcome-card {
            background: linear-gradient(120deg, var(--primary-color), var(--accent-color));
            color: white;
        }
        
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .list-group-item {
            transition: all 0.3s ease;
        }
        
        .list-group-item:hover {
            transform: translateX(5px);
            background-color: #f8f9fa;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
        
        .nav-link.logout-btn {
            background-color: #dc3545;
            color: white !important;
            padding: 8px 15px;
            border-radius: 5px;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link.logout-btn:hover {
            background-color: #bd2130;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .nav-link.logout-btn i {
            margin-right: 5px;
        }

        /* Add these new status-specific styles */
        .status-pending {
            color: var(--status-pending) !important;
        }
        
        .status-approved {
            color: var(--status-approved) !important;
        }
        
        .status-rejected {
            color: var(--status-rejected) !important;
        }

        .btn-status-pending {
            background-color: var(--status-pending);
            border-color: var(--status-pending);
            color: white;
        }

        .btn-status-approved {
            background-color: var(--status-approved);
            border-color: var(--status-approved);
            color: white;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            margin-left: 10px;
        }

        .status-badge.pending {
            background-color: var(--status-pending);
            color: white;
        }

        .status-badge.approved {
            background-color: var(--status-approved);
            color:white;
        }

        .status-badge.rejected {
            background-color: var(--status-rejected);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../index.php">College Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['student_name']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout-btn" href="#" onclick="confirmLogout()">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Welcome Card -->
        <div class="card welcome-card mb-4">
            <div class="card-body">
                <h4 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h4>
                <p class="card-text">Manage your admission application and track its status here.</p>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Application Status Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Application Status</h5>
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['warning_message'])): ?>
                            <div class="alert alert-warning alert-dismissible fade show">
                                <?php 
                                echo $_SESSION['warning_message'];
                                unset($_SESSION['warning_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($application): ?>
                            <div class="d-flex align-items-center mt-3">
                                <i class="fas fa-clipboard-check fa-2x me-3 
                                    <?php 
                                    echo match($application['status']) {
                                        'pending' => 'status-pending',
                                        'approved' => 'status-approved',
                                        'rejected' => 'status-rejected'
                                    };
                                    ?>">
                                </i>
                                <div>
                                    <h6 class="mb-0">Status: <?php echo ucfirst($application['status']); ?></h6>
                                    
                                    <?php if (!empty($application['admin_remarks'])): ?>
                                        <div class="alert alert-info mt-2 mb-2">
                                            <small><strong>Admin Remarks:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($application['admin_remarks'])); ?></small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($application['status'] === 'approved'): ?>
                                        <?php
                                        // Check if payment exists and is approved
                                        $payment_stmt = $conn->prepare("SELECT status FROM payments WHERE student_id = ? ORDER BY id DESC LIMIT 1");
                                        $payment_stmt->bind_param("i", $_SESSION['student_id']);
                                        $payment_stmt->execute();
                                        $payment_result = $payment_stmt->get_result();
                                        $payment = $payment_result->fetch_assoc();
                                        
                                        if($payment && $payment['status'] === 'completed'): ?>
                                            <a href="print_admission.php?id=<?php echo htmlspecialchars($application['id']); ?>" 
                                               class="btn btn-primary mt-3">
                                                <i class="fas fa-print me-2"></i>Print Application
                                            </a>
                                        <?php elseif($payment && $payment['status'] === 'pending'): ?>
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-clock me-2"></i>
                                                Your payment is under review. Please wait for admin approval.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Your application is approved. Please complete the payment to print your application.
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif($application['status'] === 'rejected'): ?>
                                        <?php if(!isset($application['reapplied']) || $application['reapplied'] == 0): ?>
                                            <div class="mt-3">
                                                <form method="POST" action="admission_form.php">
                                                    <input type="hidden" name="form_id" value="<?php echo htmlspecialchars($application['id']); ?>">
                                                    <input type="hidden" name="edit_mode" value="reapply">
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="fas fa-edit me-2"></i>Edit & Reapply Application
                                                    </button>
                                                </form>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="fas fa-info-circle"></i> You can edit and reapply only once
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info mt-3">
                                                <i class="fas fa-info-circle me-2"></i>You have already used your one-time reapplication option.
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You haven't submitted an application yet.</p>
                            <a href="admission_form.php" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Fill Admission Form
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Status Card -->
                <?php if ($application && $application['status'] === 'approved'): 
                    // Fetch payment details
                    $payment_stmt = $conn->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
                    $payment_stmt->bind_param("i", $_SESSION['student_id']);
                    $payment_stmt->execute();
                    $payment = $payment_stmt->get_result()->fetch_assoc();
                ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-credit-card me-2"></i>Payment Status
                            </h5>
                            <?php if ($payment): ?>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="mb-0"><strong>Amount:</strong> ₹<?php echo number_format($payment['amount'], 2); ?></p>
                                            <span class="status-badge status-<?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2">
                                            <small>Payment Date: <?php echo date('d M Y, h:i A', strtotime($payment['created_at'])); ?></small>
                                        </p>
                                        
                                        <?php if ($payment['status'] === 'completed'): ?>
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check-circle me-2"></i>Your payment has been approved. You can now print your admission form.
                                            </div>
                                        <?php elseif ($payment['status'] === 'rejected'): ?>
                                            <div class="alert alert-danger mb-0">
                                                <i class="fas fa-times-circle me-2"></i>Your payment was rejected.
                                                <?php if (!empty($payment['admin_remarks'])): ?>
                                                    <p class="mb-0 mt-2"><strong>Reason:</strong> <?php echo htmlspecialchars($payment['admin_remarks']); ?></p>
                                                <?php endif; ?>
                                                <div class="mt-3">
                                                    <a href="../payments/make_payment.php" class="btn btn-warning">
                                                        <i class="fas fa-redo me-2"></i>Try Payment Again
                                                    </a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-clock me-2"></i>Your payment is under review. Please wait for approval.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>No payment record found. Please make your payment.
                                    <div class="mt-3">
                                        <a href="../payments/make_payment.php" class="btn btn-primary">
                                            <i class="fas fa-credit-card me-2"></i>Make Payment
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Quick Links Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-link me-2"></i>Quick Links</h5>
                        <ul class="list-group list-group-flush">
                            <?php if ($application): ?>
                                <li class="list-group-item">
                                    <a href="view_application.php" class="text-decoration-none">
                                        <i class="fas fa-eye me-2"></i>View Application
                                    </a>
                                </li>
                                <?php if($application['status'] === 'approved'): ?>
                                    <li class="list-group-item">
                                        <a href="../payments/make_payment.php" class="text-decoration-none">
                                            <i class="fas fa-credit-card me-2"></i>Make Payment
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <li class="list-group-item">
                                <a href="../pages/courses.php" class="text-decoration-none">
                                    <i class="fas fa-book me-2"></i>View Courses
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="../pages/contact.php" class="text-decoration-none">
                                    <i class="fas fa-envelope me-2"></i>Contact Support
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
