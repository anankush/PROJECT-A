<?php
require_once '../config/db_connection.php';
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

// Enhanced query to get all payment and student details
$query = "SELECT p.*, s.name as student_name, s.email as student_email, 
          af.id as application_id, af.course
          FROM payments p 
          LEFT JOIN students s ON p.student_id = s.id
          LEFT JOIN admission_forms af ON p.student_id = af.student_id
          WHERE p.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Payment not found.";
    header('Location: dashboard.php');
    exit();
}

$payment = $result->fetch_assoc();

// Set default status to pending if not set
if (!isset($payment['status']) || empty($payment['status'])) {
    $update_stmt = $conn->prepare("UPDATE payments SET status = 'pending' WHERE id = ?");
    $update_stmt->bind_param("i", $_GET['id']);
    $update_stmt->execute();
    $payment['status'] = 'pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card { box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 15px; }
        .document-preview { border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-pending { background: #ffeeba; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Payment #<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <span class="status-badge status-<?php echo $payment['status']; ?>">
                        <?php echo ucfirst($payment['status']); ?>
                    </span>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Student Information</h5>
                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($payment['application_id']); ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($payment['student_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($payment['student_email']); ?></p>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($payment['course']); ?></p>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="mb-3">Payment Information</h5>
                        <p><strong>Amount:</strong> ₹<?php echo number_format($payment['amount'], 2); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></p>
                        <p><strong>UTR/Reference:</strong> <?php echo htmlspecialchars($payment['utr_number']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?></p>
                        
                        <?php if ($payment['payment_method'] === 'upi'): ?>
                            <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($payment['upi_id']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($payment['payment_method'] === 'card'): ?>
                            <p><strong>Card Number:</strong> XXXX-XXXX-XXXX-<?php echo substr($payment['card_number'], -4); ?></p>
                            <p><strong>Card Expiry:</strong> <?php echo htmlspecialchars($payment['card_expiry']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($payment['receipt_path'])): ?>
                <div class="mt-4">
                    <h5 class="mb-3">Payment Receipt</h5>
                    <div class="document-preview">
                        <?php
                        // Fix receipt path
                        $file_extension = strtolower(pathinfo($payment['receipt_path'], PATHINFO_EXTENSION));
                        $receipt_path = '../uploads/receipts/' . basename($payment['receipt_path']);  // Use basename to get just the filename
                        
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png'])): ?>
                            <img src="<?php echo $receipt_path; ?>" 
                                 class="img-fluid" style="max-height: 300px;" alt="Payment Receipt">
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" onclick="openReceiptModal()">
                                    <i class="fas fa-search-plus me-2"></i>View Full Size
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                <p class="mt-2">PDF Document</p>
                                <a href="<?php echo $receipt_path; ?>" 
                                   class="btn btn-primary mt-2" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>View PDF
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Add Receipt Modal -->
                <div class="modal fade" id="receiptModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Payment Receipt</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="<?php echo $receipt_path; ?>" 
                                     class="img-fluid" alt="Full Size Receipt">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Section -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Payment Review</h5>
                        </div>
                        <div class="card-body">
                            <!-- Current Status -->
                            <div class="mb-4">
                                <h6>Current Status:</h6>
                                <span class="status-badge status-<?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </div>

                            <?php if ($payment['status'] === 'pending'): ?>
                                <!-- Status Update Form -->
                                <form action="update_payment_status.php" method="POST">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Admin Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3" 
                                                placeholder="Enter any remarks or reasons"></textarea>
                                    </div>

                                    <div class="btn-group">
                                        <button type="submit" name="status" value="completed" 
                                                class="btn btn-success me-2" 
                                                onclick="return confirm('Are you sure you want to approve this payment?')">
                                            <i class="fas fa-check me-2"></i>Approve Payment
                                        </button>
                                        <button type="submit" name="status" value="rejected" 
                                                class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to reject this payment?')">
                                            <i class="fas fa-times me-2"></i>Reject Payment
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <!-- Show Admin Remarks if any -->
                                <?php if (!empty($payment['admin_remarks'])): ?>
                                    <div class="mb-3">
                                        <h6>Admin Remarks:</h6>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($payment['admin_remarks'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Status Message -->
                                <div class="alert alert-<?php echo $payment['status'] === 'completed' ? 'success' : 'danger'; ?>">
                                    <i class="fas fa-<?php echo $payment['status'] === 'completed' ? 'check-circle' : 'times-circle'; ?> me-2"></i>
                                    <?php echo $payment['status'] === 'completed' ? 
                                        'This payment has been approved. Student can now print their admission form.' : 
                                        'This payment has been rejected.'; ?>
                                </div>

                                <!-- Option to Change Status -->
                                <form action="update_payment_status.php" method="POST" class="mt-3">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Change Status</label>
                                        <select name="status" class="form-select" required>
                                            <?php if($payment['status'] === 'pending'): ?>
                                                <option value="completed">Approve Payment</option>
                                                <option value="rejected">Reject Payment</option>
                                            <?php else: ?>
                                                <option value="completed" <?php echo $payment['status'] === 'completed' ? 'selected' : ''; ?>>Approve Payment</option>
                                                <option value="rejected" <?php echo $payment['status'] === 'rejected' ? 'selected' : ''; ?>>Reject Payment</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3" 
                                                placeholder="Enter new remarks"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-2"></i>Update Status
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updatePaymentStatus(status) {
        if (confirm(`Are you sure you want to ${status} this payment?`)) {
            window.location.href = `update_payment_status.php?id=<?php echo $payment['id']; ?>&status=${status}`;
        }
    }

    function openReceiptModal() {
        var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        receiptModal.show();
    }
    </script>
</body>
</html>
