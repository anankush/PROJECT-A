<?php
require_once '../config/db_connection.php';
require_once 'check_session.php';

// Fetch application details
$stmt = $conn->prepare("SELECT af.*, s.email, s.phone, af.duration 
                       FROM admission_forms af 
                       JOIN students s ON af.student_id = s.id 
                       WHERE af.student_id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    $_SESSION['error_message'] = "No application found.";
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(120deg, #2980b9, #8e44ad); }
        .application-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .photo-box img {
            width: 150px;
            height: 180px;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        .signature-box img {
            max-height: 60px;
            max-width: 150px;
            border-bottom: 1px solid #000;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-pending { background-color: #ffeeba; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
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
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="card application-card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Application Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Application Status -->
                    <div class="col-12 mb-4 text-center">
                        <span class="status-badge status-<?php echo $application['status']; ?>">
                            Status: <?php echo ucfirst($application['status']); ?>
                        </span>
                    </div>

                    <!-- Left Column - Personal Information -->
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">Full Name</div>
                                <p><?php echo htmlspecialchars($application['name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Email</div>
                                <p><?php echo htmlspecialchars($application['email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Phone</div>
                                <p><?php echo htmlspecialchars($application['phone']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Date of Birth</div>
                                <p><?php echo date('d/m/Y', strtotime($application['dob'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Father's Name</div>
                                <p><?php echo htmlspecialchars($application['father_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Mother's Name</div>
                                <p><?php echo htmlspecialchars($application['mother_name']); ?></p>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Address</div>
                                <p><?php echo htmlspecialchars($application['address']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Course Applied</div>
                                <p><?php echo htmlspecialchars($application['course']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Course Duration</div>
                                <p><?php echo htmlspecialchars($application['duration']); ?> Years</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Photo and Signature -->
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="photo-box mb-3">
                                <img src="<?php echo '../uploads/photos/' . htmlspecialchars($application['photo_path']); ?>" 
                                     alt="Student Photo" class="img-fluid">
                            </div>
                            <div class="signature-box">
                                <img src="<?php echo '../uploads/signatures/' . htmlspecialchars($application['signature_path']); ?>" 
                                     alt="Signature" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Notice Section - Moved to bottom -->
                <?php if($application['status'] === 'approved'): ?>
                    <?php
                    // Check if payment exists and is approved
                    $payment_stmt = $conn->prepare("SELECT status FROM payments WHERE student_id = ? ORDER BY id DESC LIMIT 1");
                    $payment_stmt->bind_param("i", $_SESSION['student_id']);
                    $payment_stmt->execute();
                    $payment_result = $payment_stmt->get_result();
                    $payment = $payment_result->fetch_assoc();
                    ?>
                    
                    <div class="mt-4">
                        <?php if($payment && $payment['status'] === 'completed'): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Your application has been approved and payment has been confirmed.
                            </div>
                        <?php elseif($payment && $payment['status'] === 'pending'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>
                                Your payment is under review. Please wait for admin approval.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-info-circle me-2"></i>
                                    Your application is approved. Please complete the payment process.
                                </div>
                                <a href="../payments/make_payment.php" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>Make Payment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Application ID: <?php echo str_pad($application['id'], 6, '0', STR_PAD_LEFT); ?></small>
                    <small class="text-muted">Submitted on: <?php echo date('d/m/Y', strtotime($application['created_at'])); ?></small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
