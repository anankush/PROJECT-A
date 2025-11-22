<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_GET['id']) || !isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}

$admission_id = $_GET['id'];
$student_id = $_SESSION['student_id'];

// Fetch admission details
$sql = "SELECT af.*, s.email, s.phone 
        FROM admission_forms af 
        JOIN students s ON af.student_id = s.id 
        WHERE af.id = ? AND af.student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $admission_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$admission = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
            .certificate { box-shadow: none !important; }
        }

        body { background: #f0f2f5; }

        .certificate {
            position: relative;
            background: #fff;
            width: 210mm;
            margin: 20px auto;
            padding: 30px;
            box-shadow: 0 0 25px rgba(0,0,0,0.1);
        }

        .certificate-border {
            position: relative;
            padding: 20px;
            border: 2px solid #1a237e;
        }

        .certificate-border::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid #1a237e;
            z-index: -1;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px double #1a237e;
        }

        .college-logo {
            width: 90px;
            margin-bottom: 15px;
        }

        .form-number {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 14px;
            color: #1a237e;
            font-weight: bold;
        }

        .main-content {
            display: grid;
            grid-template-columns: auto 180px;
            gap: 30px;
        }

        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 15px;
            padding: 5px 0;
            border-bottom: 1px dotted #1a237e;
        }

        .photo-section {
            text-align: center;
        }

        .photo-box {
            border: 2px solid #1a237e;
            padding: 3px;
            margin-bottom: 20px;
        }

        .student-photo {
            width: 150px;
            height: 180px;
            object-fit: cover;
        }

        .signature-box {
            margin-top: 20px;
            text-align: center;
        }

        .signature-img {
            height: 50px;
            max-width: 150px;
            border-bottom: 1px solid #000;
        }

        .signature-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #1a237e;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #444;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            background: #1a237e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="certificate-border">
            <!-- Form Number -->
            <div class="form-number">Form No: <?php echo str_pad($admission['id'], 6, '0', STR_PAD_LEFT); ?></div>

            <!-- Header -->
            <div class="header">
                <img src="../images/logo.png" alt="College Logo" class="college-logo">
                <h1 style="font-size: 26px; color: #1a237e; margin: 0;">TECHNO POLYTECHNIC DURGAPUR</h1>
                <p style="color: #666; font-size: 14px; margin: 10px 0;">
                Behind Kanksa BDO Office, Panagarh , Paschim Bardhaman, West Bengal, City - Panagarh - PIN - 713148<br>
                    Phone: (123) 456-7890 | Email: info@college.edu
                </p>
                <h2 style="font-size: 20px; color: #1a237e; margin-top: 15px;">
                    ADMISSION FORM <?php echo date('Y'); ?>
                </h2>
                <span class="status-badge"><?php echo ucfirst($admission['status']); ?></span>
            </div>

            <div class="main-content">
                <!-- Student Information -->
                <div class="student-info">
                    <!-- Personal Details -->
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Applied</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['course']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Course Duration</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['duration']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Father's Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['father_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Mother's Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['mother_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($admission['dob'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['gender']); ?></div>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($admission['address']); ?></div>
                    </div>
                </div>

                <!-- Photo Section -->
                <div class="photo-section">
                    <?php if(!empty($admission['photo_path'])): ?>
                        <div class="photo-box">
                            <img src="<?php echo '../uploads/photos/' . htmlspecialchars($admission['photo_path']); ?>" 
                                 alt="Student Photo" class="student-photo">
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($admission['signature_path'])): ?>
                        <div class="signature-box">
                            <img src="<?php echo '../uploads/signatures/' . htmlspecialchars($admission['signature_path']); ?>" 
                                 alt="Student Signature" class="signature-img">
                            <div class="signature-label">Applicant's Signature</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div>Application Date: <?php echo date('d/m/Y', strtotime($admission['created_at'])); ?></div>
                <div>Generated on: <?php echo date('d/m/Y'); ?></div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-center my-4 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-4">
            <i class="fas fa-print"></i> Print Form
        </button>
        <a href="dashboard.php" class="btn btn-secondary btn-lg px-4 ms-2">Back</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>