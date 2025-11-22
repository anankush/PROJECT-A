<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['student_id']) || !isset($_POST['form_id'])) {
    header("Location: dashboard.php");
    exit();
}

$form_id = mysqli_real_escape_string($conn, $_POST['form_id']);
$student_id = $_SESSION['student_id'];

// Simplified query that doesn't depend on reapplied column
$query = "SELECT * FROM admission_forms WHERE id = ? AND student_id = ? AND status = 'rejected'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $form_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$form_data = $result->fetch_assoc();

if (!$form_data) {
    $_SESSION['error_msg'] = "Invalid access or form not found.";
    header("Location: dashboard.php");
    exit();
}

// Add check for reapplied status after fetching data
if (isset($form_data['reapplied']) && $form_data['reapplied'] == 1) {
    $_SESSION['error_msg'] = "You have already reapplied for this application.";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Process form submission
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Simplified update query without timestamp
    $update_query = "UPDATE admission_forms SET 
                    name = ?,
                    email = ?,
                    status = 'pending',
                    reapplied = 1
                    WHERE id = ? AND student_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssss", $name, $email, $form_id, $student_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Your application has been updated and resubmitted successfully.";
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Error updating application. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card">
            <div class="card-header">
                <h3>Edit Application Form</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="form_id" value="<?php echo htmlspecialchars($form_id); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                    </div>
                    
                    <!-- Add other form fields as needed -->
                    
                    <div class="mb-3">
                        <button type="submit" name="submit" class="btn btn-primary">Update and Resubmit</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
