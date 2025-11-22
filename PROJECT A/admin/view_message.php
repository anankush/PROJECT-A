<?php
require_once '../config/db_connection.php';
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$message_id = $_GET['id'];

// Get message details
$query = "SELECT * FROM contact_messages WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Message not found.";
    header('Location: dashboard.php');
    exit();
}

$message = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .message-content {
            white-space: pre-wrap;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        /* Add new responsive styles */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .action-buttons .btn {
            flex: 1;
            min-width: 200px;
            transition: all 0.3s ease;
        }
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
            }
            .action-buttons .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
            <a href="dashboard.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </nav>

    <div class="container mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Message Details</h3>
                    <?php 
                        $statusClass = $message['status'] === 'unread' ? 'bg-primary' : 
                                    ($message['status'] === 'seen' ? 'bg-secondary' : 'bg-info');
                        $statusIcon = $message['status'] === 'unread' ? 'fa-envelope' : 
                                    ($message['status'] === 'seen' ? 'fa-check-double' : 'fa-envelope-open');
                    ?>
                    <span class="badge <?php echo $statusClass; ?> p-2">
                        <i class="fas <?php echo $statusIcon; ?> me-1"></i>
                        <?php echo $message['status'] === 'seen' ? 'Read' : 'Unread'; ?>
                    </span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($message['phone']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y H:i', strtotime($message['created_at'])); ?></p>
                    </div>
                </div>

                <h5>Message:</h5>
                <div class="message-content mb-4">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>

                <div class="mt-4 action-buttons">
                    <?php if($message['status'] === 'unread'): ?>
                        <button onclick="markAsRead(<?php echo $message['id']; ?>)" class="btn btn-success">
                            <i class="fas fa-check-double"></i> Mark as Read
                        </button>
                    <?php else: ?>
                        <button onclick="markAsUnread(<?php echo $message['id']; ?>)" class="btn btn-info">
                            <i class="fas fa-envelope"></i> Mark as Unread
                        </button>
                    <?php endif; ?>
                    <button onclick="deleteMessage(<?php echo $message['id']; ?>)" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Message
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function markAsRead(id) {
            updateMessageStatus(id, 'seen');
        }

        function markAsUnread(id) {
            updateMessageStatus(id, 'unread');
        }

        function updateMessageStatus(id, status) {
            if (confirm(`Mark this message as ${status}?`)) {
                fetch('update_message.php?id=' + id + '&action=' + status)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to update message status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the message');
                    });
            }
        }

        function deleteMessage(id) {
            if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                fetch('update_message.php?id=' + id + '&action=delete')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            alert('Failed to delete message');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the message');
                    });
            }
        }
    </script>
</body>
</html>
