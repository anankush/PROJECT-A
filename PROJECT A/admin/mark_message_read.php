<?php
require_once '../config/db_connection.php';
require_once 'check_session.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "UPDATE contact_messages SET status='read' WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    $response = ['success' => false];
    
    if($stmt->execute()) {
        $response['success'] = true;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
