<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-animation { animation: scaleIn 0.5s ease-in-out; }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm mx-auto" style="max-width: 500px;">
            <div class="card-body text-center p-5">
                <div class="success-animation">
                    <i class="fas fa-check-circle text-success fa-5x mb-4"></i>
                </div>
                <h2 class="mb-4">Payment Successful!</h2>
                <p class="text-muted mb-4">Your payment has been processed successfully.</p>
                <div class="d-grid gap-2">
                    <a href="payment_history.php" class="btn btn-primary">View Payment History</a>
                    <a href="../student/dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
