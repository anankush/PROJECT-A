<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/db_connection.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

// Check if using temporary password
$stmt = $conn->prepare("SELECT is_temp_password FROM admin WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if ($admin['is_temp_password'] == 1) {
    header("Location: change_password.php?temp=1");
    exit();
}

// Get unique years for the dropdown
$years_query = "SELECT DISTINCT YEAR(created_at) as year FROM admission_forms ORDER BY year DESC";
$years = $conn->query($years_query)->fetch_all(MYSQLI_ASSOC);

// Build the SQL query with search conditions
$sql = "SELECT af.*, s.email as email FROM admission_forms af 
        JOIN students s ON af.student_id = s.id";

$where_clauses = [];
$params = [];
$types = "";

if (isset($_GET['search_name']) && !empty($_GET['search_name'])) {
    $where_clauses[] = "af.name LIKE ?";
    $params[] = "%" . $_GET['search_name'] . "%";
    $types .= "s";
}

if (isset($_GET['search_year']) && !empty($_GET['search_year'])) {
    $where_clauses[] = "YEAR(af.created_at) = ?";
    $params[] = $_GET['search_year'];
    $types .= "i";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY af.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $applications = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Get messages
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Update the payments query to include course information
$payments_query = "SELECT p.*, s.email, s.name as student_name, 
                         COALESCE(p.course_name, af.course) as course_name
                  FROM payments p 
                  JOIN students s ON p.student_id = s.id
                  LEFT JOIN admission_forms af ON p.student_id = af.student_id";

// Try to get the date column name
$date_column = '';
$result = $conn->query("SHOW COLUMNS FROM payments");
$columns = $result->fetch_all(MYSQLI_ASSOC);
foreach ($columns as $column) {
    if (in_array($column['Field'], ['payment_date', 'created_at', 'date'])) {
        $date_column = $column['Field'];
        break;
    }
}

// Add the ORDER BY clause with the correct date column
if ($date_column) {
    $payments_query .= " ORDER BY p.$date_column DESC";
} else {
    $payments_query .= " ORDER BY p.id DESC"; // Fallback to ID if no date column found
}

$payments = $conn->query($payments_query)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;    /* Dark slate */
            --secondary-color: #34495e;  /* Medium slate */
            --accent-color: #455a64;    /* Blue-grey */
            --highlight-color: #5c6bc0; /* Indigo */
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(120deg, var(--primary-color), var(--accent-color));
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(120deg, var(--highlight-color), var(--accent-color));
            border: none;
        }

        .navbar {
            background: linear-gradient(120deg, #2c3e50, #3498db);
        }
        .card {
            background: rgba(255, 255, 255, 0.98);
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .navbar {
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
        
        .table tr {
            transition: all 0.3s ease;
        }
        
        .table tr:hover {
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-pending {
            color: #f39c12;
        }
        .status-approved {
            color: #27ae60;
        }
        .status-rejected {
            color: #c0392b;
        }
        .actions-hidden .action-btn {
            display: none;
        }
        .toggle-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .logout-btn {
            color: #dc3545 !important;
        }
        .logout-btn:hover {
            background-color: #dc3545 !important;
            color: white !important;
        }

        /* Updated logout button styles */
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

        /* Add these new styles */
        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1030;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .main-container {
            width: calc(100% - 280px);
            margin-left: 280px;
            min-height: 100vh;
            background: #f8f9fa;
            padding: 20px;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 15px;
        }

        .sidebar-menu {
            padding: 0;
            list-style: none;
        }

        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: #5c6bc0;
        }

        .content-section {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .content-section.active {
            display: block;
            opacity: 1;
        }

        .top-nav {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: white;
            padding: 15px 30px;
            margin-bottom: 20px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: slideDown 0.5s ease;
        }

        .logout-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1030;
            animation: fadeIn 0.5s ease;
        }

        .logout-btn {
            background: #dc3545;
            color: white !important;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
            background: #c82333;
        }

        /* Animation keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Add animation to content sections */
        .content-section {
            animation: fadeIn 0.5s ease;
        }

        .sidebar-menu .nav-item {
            animation: slideIn 0.5s ease;
            animation-fill-mode: both;
        }

        .sidebar-menu .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .sidebar-menu .nav-item:nth-child(2) { animation-delay: 0.2s; }
        .sidebar-menu .nav-item:nth-child(3) { animation-delay: 0.3s; }

        /* Update main container padding */
        .main-container {
            padding-top: 80px;
        }

        .user-info {
            color: var(--primary-color);
            animation: fadeIn 0.5s ease;
        }

        .btn-danger {
            background: linear-gradient(to right, #dc3545, #c82333);
            border: none;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h4 class="text-white mb-0">Admin Dashboard</h4>
            </div>
            <ul class="sidebar-menu nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#applications" onclick="showSection('applications')">
                        <i class="fas fa-file-alt me-2"></i>Applications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#messages" onclick="showSection('messages')">
                        <i class="fas fa-envelope me-2"></i>Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#payments" onclick="showSection('payments')">
                        <i class="fas fa-credit-card me-2"></i>Payments
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-container">
            <!-- Top Navigation -->
            <div class="top-nav d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-md-none me-3" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="user-info">
                        <span class="h5 mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                        </span>
                    </div>
                </div>
                <div>
                    <button class="btn btn-danger" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </button>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="content-section active" id="applications">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Student Applications</h4>
                        </div>
                        <form class="row g-3" method="GET">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search_name" 
                                       placeholder="Search by name" 
                                       value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="search_year">
                                    <option value="">Select Year</option>
                                    <?php foreach($years as $year): ?>
                                        <option value="<?php echo $year['year']; ?>" 
                                            <?php echo (isset($_GET['search_year']) && $_GET['search_year'] == $year['year']) ? 'selected' : ''; ?>>
                                            <?php echo $year['year']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">Search</button>
                                <a href="dashboard.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="collapse show" id="applicationsTable">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Status</th>
                                            <th>Applied On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($applications as $row): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['course']); ?></td>
                                            <td>
                                                <span class="status-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                            <td class="text-center">
                                                <a href="view_application.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-section" id="messages">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Contact Messages</h4>
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#messagesTable">
                            Toggle Messages
                        </button>
                    </div>
                    <div class="collapse show" id="messagesTable">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($messages as $msg):
                                            // Set default status if not set
                                            $status = isset($msg['status']) ? $msg['status'] : 'unread';
                                        ?>
                                        <tr class="<?php echo $status === 'unread' ? 'table-primary' : ''; ?>">
                                            <td><?php echo $msg['id']; ?></td>
                                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                            <td><?php echo htmlspecialchars($msg['phone']); ?></td>
                                            <td>
                                                <?php 
                                                // Show first 50 characters of message with ellipsis
                                                echo htmlspecialchars(substr($msg['message'], 0, 50)) . 
                                                     (strlen($msg['message']) > 50 ? '...' : ''); 
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = $msg['status'] === 'unread' ? 'bg-primary text-white' : 'bg-success text-white';
                                                    $statusIcon = $msg['status'] === 'unread' ? 'fa-envelope' : 'fa-check-double';
                                                    $statusText = $msg['status'] === 'unread' ? 'Unread' : 'Seen';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> p-2">
                                                    <i class="fas <?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($msg['created_at'])); ?></td>
                                            <td class="actions-column">
                                                <a href="view_message.php?id=<?php echo $msg['id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-section" id="payments">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Student Payments</h4>
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#paymentsTable">
                            Toggle Payments
                        </button>
                    </div>
                    <div class="collapse show" id="paymentsTable">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>Amount</th>
                                            <th>Course</th>
                                            <th>UTR/Reference No</th>  <!-- Changed text -->
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['id']; ?></td>
                                            <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['email']); ?></td>
                                            <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($payment['course_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($payment['transaction_id'] ?? $payment['utr_number'] ?? 'N/A'); ?></td>
                                            <td><?php 
                                                $date_field = isset($payment[$date_column]) ? $payment[$date_column] : 
                                                             (isset($payment['created_at']) ? $payment['created_at'] : 
                                                             (isset($payment['payment_date']) ? $payment['payment_date'] : 
                                                             (isset($payment['date']) ? $payment['date'] : null)));
                                                echo $date_field ? date('d M Y', strtotime($date_field)) : 'N/A';
                                            ?></td>
                                            <td>
                                                <span class="badge <?php echo $payment['status'] === 'completed' ? 'bg-success' : 
                                                    ($payment['status'] === 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td class="actions-column">
                                                <div class="action-btn">
                                                    <a href="view_payment.php?id=<?php echo $payment['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="message-subject fw-bold mb-3"></h6>
                    <div class="mb-3 border-bottom pb-2">
                        <strong>From:</strong> <span class="message-name"></span>
                        <br>
                        <strong>Email:</strong> <span class="message-email"></span>
                    </div>
                    <div class="message-content" style="white-space: pre-wrap;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary reply-email">Reply via Email</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Student:</strong> <span class="payment-name"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Amount:</strong> ₹<span class="payment-amount"></span>
                    </div>
                    <div class="mb-3">
                        <strong>UTR/Reference No:</strong> <span class="payment-reference"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Payment Method:</strong> <span class="payment-method"></span>
                    </div>
                </div>
                <div class="modal-footer"></div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Payment Modal -->
    <div class="modal fade" id="approvePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="update_payment_status.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="payment_id" id="approvePaymentId">
                        <input type="hidden" name="status" value="completed">
                        <p>Are you sure you want to approve this payment?</p>
                        <div class="mb-3">
                            <label class="form-label">Remarks (optional)</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Payment Modal -->
    <div class="modal fade" id="rejectPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="update_payment_status.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="payment_id" id="rejectPaymentId">
                        <input type="hidden" name="status" value="rejected">
                        <p>Are you sure you want to reject this payment?</p>
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason (required)</label>
                            <textarea name="remarks" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle message view
        const messageModal = document.getElementById('messageModal');
        messageModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const message = button.getAttribute('data-message');
            const subject = button.getAttribute('data-subject');
            const name = button.getAttribute('data-name');
            const email = button.getAttribute('data-email');

            // Update modal content
            messageModal.querySelector('.message-subject').textContent = 'Subject: ' + subject;
            messageModal.querySelector('.message-name').textContent = name;
            messageModal.querySelector('.message-email').textContent = email;
            messageModal.querySelector('.message-content').textContent = message;
            messageModal.querySelector('.reply-email').href = `mailto:${email}?subject=Re: ${subject}`;

            // Mark message as read
            fetch('mark_message_read.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        button.closest('tr').classList.remove('table-primary');
                        button.closest('tr').querySelector('.badge').classList.replace('bg-primary', 'bg-secondary');
                        button.closest('tr').querySelector('.badge').textContent = 'Read';
                    }
                });
        });

        // Handle payment view
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const name = button.getAttribute('data-name');
                const amount = button.getAttribute('data-amount');
                const transaction = button.getAttribute('data-transaction');
                const utr = button.getAttribute('data-utr');
                const method = button.getAttribute('data-method');

                // Update modal content
                paymentModal.querySelector('.payment-name').textContent = name;
                paymentModal.querySelector('.payment-amount').textContent = amount;
                paymentModal.querySelector('.payment-reference').textContent = transaction || utr || 'N/A';
                paymentModal.querySelector('.payment-method').textContent = method;
            });
        }

        // Handle approve payment modal
        const approvePaymentModal = document.getElementById('approvePaymentModal');
        if (approvePaymentModal) {
            approvePaymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const paymentId = button.getAttribute('data-id');
                this.querySelector('#approvePaymentId').value = paymentId;
            });
        }

        // Handle reject payment modal
        const rejectPaymentModal = document.getElementById('rejectPaymentModal');
        if (rejectPaymentModal) {
            rejectPaymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const paymentId = button.getAttribute('data-id');
                this.querySelector('#rejectPaymentId').value = paymentId;
            });
        }

        // Check if actions are hidden from localStorage
        const actionsHidden = localStorage.getItem('actionsHidden') === 'true';
        if (actionsHidden) {
            document.querySelectorAll('.actions-column').forEach(col => {
                col.classList.add('actions-hidden');
            });
            document.querySelector('.toggle-actions i').classList.replace('fa-eye', 'fa-eye-slash');
        }
    });

    function toggleActions() {
        const columns = document.querySelectorAll('.actions-column');
        const icon = document.querySelector('.toggle-actions i');
        
        columns.forEach(col => {
            col.classList.toggle('actions-hidden');
        });

        const isHidden = columns[0].classList.contains('actions-hidden');
        localStorage.setItem('actionsHidden', isHidden);
        
        // Toggle icon
        if (isHidden) {
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    // Add this new function
    function showSection(sectionId) {
        // Remove active class from all sections and nav links
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Add active class to selected section and nav link
        document.getElementById(sectionId).classList.add('active');
        document.querySelector(`.nav-link[href="#${sectionId}"]`).classList.add('active');
    }

    // Toggle sidebar on mobile
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    function deleteItem(type, id) {
        if (confirm('Are you sure you want to delete this ' + type + '?')) {
            fetch('delete_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `type=${type}&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete item');
                }
            });
        }
    }

    function updateStatus(type, id, status) {
        if (confirm(`Are you sure you want to ${status} this ${type}?`)) {
            fetch(`update_${type}_status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            });
        }
    }

    function updateMessageStatus(id, status) {
        fetch('update_message_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>
</body>
</html>
