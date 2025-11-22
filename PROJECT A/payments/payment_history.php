<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch payment history
$stmt = $conn->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY transaction_date DESC");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$payments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-status-completed { color: #28a745; }
        .payment-status-pending { color: #ffc107; }
        .payment-status-failed { color: #dc3545; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,.075); }
        .receipt-btn { min-width: 120px; }
    </style>
</head>
<body class="bg-light">
    <?php include('../includes/navbar.php'); ?>

    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h4>
            </div>
            <div class="card-body">
                <?php if ($payments->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>UTR/Reference</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $payments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d M Y, h:i A', strtotime($payment['transaction_date'])); ?></td>
                                        <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                        <td><?php echo $payment['utr_number']; ?></td>
                                        <td>
                                            <span class="badge payment-status-<?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($payment['status'] === 'completed'): ?>
                                                <a href="download_receipt.php?id=<?php echo $payment['id']; ?>" 
                                                   class="btn btn-sm btn-success receipt-btn">
                                                    <i class="fas fa-download me-2"></i>Receipt
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="lead">No payment history found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
