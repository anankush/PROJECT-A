<?php
require_once '../config/db_connection.php';
session_start();

// Check admin session
if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

// Update the SQL query to match exact column names from database schema
$query = "
    SELECT af.*, s.name as student_name, s.email as student_email
    FROM admission_forms af
    LEFT JOIN students s ON af.student_id = s.id
    WHERE af.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Application not found.";
    header('Location: dashboard.php');
    exit();
}

$application = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .section-title {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin: 20px 0 10px;
        }
        .document-preview {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fff;
        }
        .document-preview img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            margin: 10px auto;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-pending { background: #ffeeba; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        /* Personal Information: two-column layout */
        .personal-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .personal-details {
            flex: 1 1 65%;
        }
        .personal-media {
            flex: 1 1 30%;
            text-align: center;
        }
        .personal-media .media-box {
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 5px;
            background: #fff;
            max-width: 120px;
            margin-left: auto;
            margin-right: auto;
        }
        .personal-media .media-box img {
            width: 100%;
            height: auto;
        }
        /* A4 Print Settings */
        @media print {
            @page {
                size: A4;
                margin: 1.5cm;
            }
            body {
                background: white !important;
                font-size: 12pt;
            }
            .no-print {
                display: none !important;
            }
            .card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card-body {
                padding: 0 !important;
            }
            .document-preview {
                margin-bottom: 15px !important;
                page-break-inside: avoid;
            }
            /* Remove max-height limit for document images in print */
            .document-preview img {
                max-height: none !important;
                height: auto !important;
                margin: 10px auto !important;
            }
            .section-title {
                margin-top: 20px !important;
                font-size: 14pt !important;
            }
        }
    </style>
</head>
<body>
    <!-- Add print header -->
    <div class="print-header d-none d-print-block">
        <h2>Student Application Details</h2>
        <p>Application #<?php echo str_pad($application['id'], 6, '0', STR_PAD_LEFT); ?></p>
    </div>

    <nav class="navbar navbar-dark bg-primary mb-4 no-print">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
            <div>
                <button onclick="window.print()" class="btn btn-light me-2">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </nav>

    <div class="container mb-4">
        <div class="card">
            <div class="card-body">
                <!-- Status and Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Application #<?php echo str_pad($application['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <span class="status-badge status-<?php echo $application['status']; ?>">
                        <?php echo ucfirst($application['status']); ?>
                    </span>
                </div>

                <!-- Personal Information -->
                <h4 class="section-title">Personal Information</h4>
                <div class="personal-row mb-4">
                    <!-- Left: Personal Details -->
                    <div class="personal-details">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <strong>Name:</strong> <?php echo htmlspecialchars($application['name']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Phone:</strong> <?php echo htmlspecialchars($application['phone']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Date of Birth:</strong> <?php echo date('d/m/Y', strtotime($application['dob'])); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Gender:</strong> <?php echo htmlspecialchars($application['gender']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Category:</strong> <?php echo htmlspecialchars($application['category']); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Right: Photo and Signature -->
                    <div class="personal-media">
                        <div class="media-box">
                            <small class="d-block">Photo</small>
                            <?php if (!empty($application['photo_path'])): 
                                $photo_path = 'photos/' . basename($application['photo_path']);
                            ?>
                                <img src="../uploads/<?php echo htmlspecialchars($photo_path); ?>" alt="Student Photo">
                            <?php else: ?>
                                <div class="alert alert-warning p-1" style="font-size:9pt;">Not uploaded</div>
                            <?php endif; ?>
                        </div>
                        <div class="media-box">
                            <small class="d-block">Signature</small>
                            <?php if (!empty($application['signature_path'])): 
                                $signature_path = 'signatures/' . basename($application['signature_path']);
                            ?>
                                <img src="../uploads/<?php echo htmlspecialchars($signature_path); ?>" alt="Student Signature">
                            <?php else: ?>
                                <div class="alert alert-warning p-1" style="font-size:9pt;">Not uploaded</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Family & Address Information -->
                <h4 class="section-title mt-4">Family & Address Information</h4>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <strong>Father's Name:</strong> <?php echo htmlspecialchars($application['father_name']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Mother's Name:</strong> <?php echo htmlspecialchars($application['mother_name']); ?>
                    </div>
                    <div class="col-12">
                        <strong>Permanent Address:</strong><br>
                        <?php echo nl2br(htmlspecialchars($application['address'])); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>State:</strong> <?php echo htmlspecialchars($application['state']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Pincode:</strong> <?php echo htmlspecialchars($application['pincode']); ?>
                    </div>
                </div>

                <!-- Course Information -->
                <h4 class="section-title mt-4">Course Details</h4>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <strong>Course Applied:</strong> <?php echo htmlspecialchars($application['course']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Duration:</strong> <?php echo htmlspecialchars($application['duration']); ?>
                    </div>
                </div>

                <!-- Documents Section -->
                <h4 class="section-title mt-4 documents-section">Uploaded Documents</h4>
                <div class="row">
                    <?php
                    $documents = [
                        'Signature' => [
                            'path' => $application['signature_path'],
                            'title' => 'Student Signature',
                            'folder' => 'signatures'
                        ],
                        'Category Certificate' => [
                            'path' => $application['caste_certificate_path'],
                            'title' => 'Category Certificate',
                            'folder' => 'caste_certificates'
                        ],
                        'Madhyamik Admit' => [
                            'path' => $application['madhyamik_admit_path'],
                            'title' => 'Madhyamik Admit Card',
                            'folder' => 'madhyamik_admits'
                        ],
                        'Aadhar Card' => [
                            'path' => $application['aadhar_doc_path'],
                            'title' => 'Aadhar Document',
                            'folder' => 'aadhar_docs'
                        ],
                        'Additional Document' => [
                            'path' => $application['hs_iti_document_path'],
                            'title' => 'HS/ITI Document',
                            'folder' => 'hs_iti_documents'
                        ]
                    ];

                    // Optionally add photo into documents array (if desired)
                    if (!empty($application['photo_path'])) {
                        array_unshift($documents, [
                            'path' => $application['photo_path'],
                            'title' => 'Student Photo',
                            'folder' => 'photos'
                        ]);
                    }

                    foreach($documents as $key => $doc):
                        if(!empty($doc['path'])):
                            $filename = basename($doc['path']);
                            $full_path = $doc['folder'] . '/' . $filename;
                            $doc_url = "view_document.php?file=" . rawurlencode($full_path);
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="document-preview">
                            <h6 class="mb-2"><?php echo $doc['title']; ?></h6>
                            <?php
                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($full_path); ?>" 
                                     alt="<?php echo $doc['title']; ?>" class="img-fluid">
                            <?php elseif ($ext === 'pdf'): ?>
                                <div class="text-center p-3 bg-light">
                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                    <p class="mt-2">PDF Document</p>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-3 bg-light">
                                    <i class="fas fa-file fa-3x text-secondary"></i>
                                    <p class="mt-2">Document</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-2">
                                <a href="<?php echo htmlspecialchars($doc_url); ?>" 
                                   class="btn btn-sm btn-primary no-print" target="_blank">
                                    <i class="fas fa-search-plus me-1"></i>View Full Size
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>

                <!-- Admin Review Section -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Application Review</h5>
                        </div>
                        <div class="card-body">
                            <!-- Current Status -->
                            <div class="mb-4">
                                <h6>Current Status:</h6>
                                <span class="status-badge status-<?php echo $application['status']; ?>">
                                    <?php echo ucfirst($application['status']); ?>
                                </span>
                            </div>

                            <?php if ($application['status'] === 'pending'): ?>
                                <!-- Status Update Form -->
                                <form action="update_application_status.php" method="POST">
                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Admin Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3" 
                                                placeholder="Enter any remarks or reasons"></textarea>
                                    </div>

                                    <div class="btn-group">
                                        <button type="submit" name="status" value="approved" 
                                                class="btn btn-success me-2" 
                                                onclick="return confirm('Are you sure you want to approve this application?')">
                                            <i class="fas fa-check me-2"></i>Approve Application
                                        </button>
                                        <button type="submit" name="status" value="rejected" 
                                                class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to reject this application?')">
                                            <i class="fas fa-times me-2"></i>Reject Application
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <!-- Show Admin Remarks if any -->
                                <?php if (!empty($application['admin_remarks'])): ?>
                                    <div class="mb-3">
                                        <h6>Admin Remarks:</h6>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['admin_remarks'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Status Message -->
                                <div class="alert alert-<?php echo $application['status'] === 'approved' ? 'success' : 'danger'; ?>">
                                    <i class="fas fa-<?php echo $application['status'] === 'approved' ? 'check-circle' : 'times-circle'; ?> me-2"></i>
                                    <?php echo $application['status'] === 'approved' ? 
                                        'This application has been approved.' : 
                                        'This application has been rejected.'; ?>
                                </div>

                                <!-- Option to Change Status -->
                                <form action="update_application_status.php" method="POST" class="mt-3">
                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Change Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="approved" <?php echo $application['status'] === 'approved' ? 'selected' : ''; ?>>Approve Application</option>
                                            <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Reject Application</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3" 
                                                placeholder="Enter new remarks"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-2"></i>Update Status
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Remove existing admin actions as they're now part of the review system -->
                <div class="mt-4 text-center no-print">
                    <hr>
                    <a href="delete_application.php?id=<?php echo $application['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this application? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete Application
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
