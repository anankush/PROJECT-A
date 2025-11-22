<?php
function checkLoginAttempts($conn, $email) {
    $stmt = $conn->prepare("SELECT login_attempts, last_attempt FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['login_attempts'] >= 3) {
            $last_attempt = strtotime($row['last_attempt']);
            $current_time = time();
            $time_diff = $current_time - $last_attempt;
            
            if ($time_diff < 900) { // 15 minutes = 900 seconds
                return false;
            } else {
                // Reset attempts after 15 minutes
                $reset_stmt = $conn->prepare("UPDATE students SET login_attempts = 0, last_attempt = NULL WHERE email = ?");
                $reset_stmt->bind_param("s", $email);
                $reset_stmt->execute();
            }
        }
    }
    return true;
}

function updateLoginAttempts($conn, $email, $success = false) {
    if ($success) {
        $stmt = $conn->prepare("UPDATE students SET login_attempts = 0, last_attempt = NULL WHERE email = ?");
    } else {
        $stmt = $conn->prepare("UPDATE students SET login_attempts = login_attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE email = ?");
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
}

function generateResetToken($conn, $email) {
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $token, $expiry);
    $stmt->execute();
    
    return $token;
}
?>
