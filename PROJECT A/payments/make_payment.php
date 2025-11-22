<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

$payment_amount = 5000.00; // Fixed amount
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $utr_number = $_POST['utr_number'] ?? '';
    $upi_id = $_POST['upi_id'] ?? '';  // Add this line
    
    // Handle file upload
    $receipt_path = '';
    if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] == 0) {
        $upload_dir = '../uploads/receipts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('receipt_') . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $target_file)) {
            $receipt_path = 'uploads/receipts/' . $file_name;
        }
    }

    // Add card details
    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    if (empty($student_name)) {
        $error = "Student name is required!";
    } else if ($payment_method === 'card' && (empty($card_number) || empty($card_expiry) || empty($card_cvv))) {
        $error = "All card details are required!";
    } else {
        // For card payments, store card details
        if ($payment_method === 'card') {
            // Hash sensitive card data before storing
            $hashed_card = password_hash($card_number, PASSWORD_DEFAULT);
            $sql = "INSERT INTO payments (student_id, amount, payment_method, card_number, card_expiry, status, receipt_path) 
                    VALUES (?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idssss", $_SESSION['student_id'], $payment_amount, $payment_method, $hashed_card, $card_expiry, $receipt_path);
        } else if ($payment_method === 'upi') {
            $sql = "INSERT INTO payments (student_id, amount, payment_method, upi_id, utr_number, status, receipt_path) 
                    VALUES (?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idssss", $_SESSION['student_id'], $payment_amount, $payment_method, $upi_id, $utr_number, $receipt_path);
        } else {
            // Existing payment methods logic
            $sql = "INSERT INTO payments (student_id, amount, payment_method, utr_number, status, receipt_path) 
                    VALUES (?, ?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idsss", $_SESSION['student_id'], $payment_amount, $payment_method, $utr_number, $receipt_path);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Payment processed successfully!";
            header('Location: ../student/dashboard.php');
            exit();
        } else {
            $error = "Payment processing failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - Seat Booking Fee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #34495e;
            --success-color: #0f9d58;
            --warning-color: #f4b400;
            --danger-color: #db4437;
            --light-bg: #f8fafb;
            --card-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .navbar {
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .payment-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .amount-section {
            background: linear-gradient(120deg, #f8f9fa, #ffffff);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 2px solid #e3e6e8;
            margin-bottom: 30px;
        }

        .amount-display {
            font-size: 36px;
            font-weight: 700;
            color: var(--success-color);
            margin: 15px 0;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
        }

        .payment-option {
            background: white;
            border: 2px solid #e3e6e8;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .payment-option:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .payment-option.selected {
            border-color: var(--primary-color);
            background: linear-gradient(120deg, #f8f9fa, #ffffff);
            box-shadow: 0 8px 20px rgba(26, 115, 232, 0.15);
        }

        .payment-option.selected::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
        }

        .payment-details {
            display: none;
            animation: slideDown 0.3s ease;
        }

        .payment-details.active {
            display: block;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid #e3e6e8;
        }

        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e3e6e8;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
        }

        .btn-primary {
            background: linear-gradient(120deg, var(--primary-color), #1557b0);
            border: none;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.3);
        }

        .qr-code {
            max-width: 250px;
            margin: 20px auto;
            padding: 15px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .payment-icon {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(26, 115, 232, 0.1);
            border-radius: 10px;
            margin-right: 15px;
            color: var(--primary-color);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        /* Card input styles */
        .card-input-group {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .input-label {
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* Footer Styles */
        footer {
            position: relative;
            bottom: 0;
            width: 100%;
            background: linear-gradient(120deg, #2c3e50, #34495e);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        footer p {
            font-size: 1.1rem;
            font-weight: 500;
        }

        footer small {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Fix Complete Payment Section */
        .d-grid.gap-2 {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        .btn-outline-secondary {
            border-width: 2px;
        }

        /* Improved form layout */
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .mb-3 {
            margin-bottom: 1.5rem !important;
        }
    </style>
</head>
<body>
    <!-- Add Navbar -->
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="make_payment.php">
                <i class="fas fa-university me-2"></i>TPD Payment Gateway
            </a>
            <div class="navbar-text">
                <a href="../student/dashboard.php" class="text-white text-decoration-none me-4">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <span>
                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container payment-container">
        <div class="main-card">
            <h2 class="text-center mb-4">
                <i class="fas fa-credit-card me-2 text-primary"></i>
                Secure Payment Gateway
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="amount-section">
                <h5 class="text-muted mb-2">Seat Booking Fee</h5>
                <div class="amount-display">₹<?php echo number_format($payment_amount, 2); ?></div>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    This amount is non-refundable
                </small>
            </div>

            <form method="POST" id="paymentForm" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label">Select Payment Method</label>
                    
                    <!-- UPI Option -->
                    <div class="payment-option" onclick="selectPayment('upi')">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method" value="upi" class="form-check-input me-2" required>
                            <i class="fas fa-mobile-alt me-2 payment-icon"></i>
                            <span>UPI Payment</span>
                        </div>
                        <div class="payment-details" id="upiDetails">
                            <div class="mt-3">
                                <input type="text" class="form-control" placeholder="Enter UPI ID" name="upi_id">
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Option -->
                    <div class="payment-option" onclick="selectPayment('qr')">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method" value="qr" class="form-check-input me-2" required>
                            <i class="fas fa-qrcode me-2 payment-icon"></i>
                            <span>Scan QR Code</span>
                        </div>
                        <div class="payment-details" id="qrDetails">
                            <div class="text-center">
                                <img src="../images/QR.jpg" alt="QR Code" class="qr-code">
                                <p class="text-muted">Scan this QR code to pay</p>
                            </div>
                        </div>
                    </div>

                    <!-- Debit Card Option -->
                    <div class="payment-option" onclick="selectPayment('card')">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method" value="card" class="form-check-input me-2" required>
                            <i class="fas fa-credit-card me-2 payment-icon"></i>
                            <span>Debit Card</span>
                        </div>
                        <div class="payment-details" id="cardDetails">
                            <div class="mt-3">
                                <input type="text" class="form-control mb-2" name="card_number" placeholder="Card Number" pattern="[0-9]{16}">
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="card_expiry" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/([0-9]{2})">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="card_cvv" placeholder="CVV" pattern="[0-9]{3}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NEFT Option -->
                    <div class="payment-option" onclick="selectPayment('neft')">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method" value="neft" class="form-check-input me-2" required>
                            <i class="fas fa-university me-2 payment-icon"></i>
                            <span>NEFT Transfer</span>
                        </div>
                        <div class="payment-details" id="neftDetails">
                            <div class="mt-3">
                                <div class="bank-details p-3 bg-light rounded">
                                    <h6 class="mb-3">Bank Account Details:</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Bank Name:</strong></td>
                                            <td>State Bank of India</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Name:</strong></td>
                                            <td>Techno Polytechnic Durgapur</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Number:</strong></td>
                                            <td>
                                                <span id="accountNumber">36169158651</span>
                                                <button class="btn btn-sm btn-outline-primary ms-2" 
                                                        onclick="copyToClipboard('accountNumber')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>IFSC Code:</strong></td>
                                            <td>
                                                <span id="ifscCode">SBIN0006795</span>
                                                <button class="btn btn-sm btn-outline-primary ms-2" 
                                                        onclick="copyToClipboard('ifscCode')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Branch:</strong></td>
                                            <td>Main Branch, City Name</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Amount:</strong></td>
                                            <td>₹<?php echo number_format($payment_amount, 2); ?></td>
                                        </tr>
                                    </table>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Please make the NEFT transfer then enter the UTR number & upload Receipt below for verification.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mb-3">
                    <label class="form-label">Student Name (for verification)</label>
                    <input type="text" class="form-control" name="student_name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">UPI Reference/UTR Number *</label>
                    <input type="text" class="form-control" name="utr_number" required>
                    <small class="text-muted">This is mandatory for payment confirmation</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Payment Receipt</label>
                    <input type="file" class="form-control" name="payment_receipt" accept="image/*,.pdf" required>
                    <small class="text-muted">Upload your payment receipt/screenshot (JPG, PNG, or PDF)</small>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-lock me-2"></i>Complete Payment
                    </button>
                    <a href="../student/dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Secure Payment Gateway
                    </p>
                    <small class="text-white">
                    &copy; Powered by PROJECT A  <?php echo date('Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function selectPayment(method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Hide all payment details
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.classList.remove('active');
            });
            
            // Select clicked option and show its details
            const selectedOption = document.querySelector(`.payment-option:has(input[value="${method}"])`);
            const details = document.getElementById(`${method}Details`);
            
            selectedOption.classList.add('selected');
            details.classList.add('active');
            selectedOption.querySelector('input[type="radio"]').checked = true;
        }

        // Add this new function for copying text
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(() => {
                // Show temporary success message
                const button = event.target.closest('button');
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.add('btn-success');
                button.classList.remove('btn-outline-primary');
                
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-primary');
                }, 1500);
            });
            event.stopPropagation(); // Prevent payment option selection when clicking copy button
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
