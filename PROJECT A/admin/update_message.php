<?php
require_once '../config/db_connection.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if required parameters are present
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$message_id = $_GET['id'];
$action = $_GET['action'];

try {
    if ($action === 'seen') {
        $query = "UPDATE contact_messages SET status = 'seen' WHERE id = ?";
    } elseif ($action === 'unread') {
        $query = "UPDATE contact_messages SET status = 'unread' WHERE id = ?";
    } elseif ($action === 'delete') {
        $query = "DELETE FROM contact_messages WHERE id = ?";
    } else {
        throw new Exception('Invalid action');
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Database error');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
