<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if we're in edit/reapply mode
$edit_mode = isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'reapply';
$form_id = isset($_POST['form_id']) ? mysqli_real_escape_string($conn, $_POST['form_id']) : null;

// Fetch existing data if in edit mode
$form_data = null;
if ($edit_mode && $form_id) {
    $query = "SELECT * FROM admission_forms WHERE id = ? AND student_id = ? AND status = 'rejected' AND (reapplied = 0 OR reapplied IS NULL)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $form_id, $_SESSION['student_id']);
    $stmt->execute();
    $form_data = $stmt->get_result()->fetch_assoc();

    if (!$form_data) {
        $_SESSION['error_msg'] = "Invalid access or form already reapplied.";
        header("Location: dashboard.php");
        exit();
    }
}

// Get courses with error handling
try {
    $courses = $conn->query("SELECT name FROM courses")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $courses = [];
    error_log("Error fetching courses: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form - College Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background: linear-gradient(120deg, #2980b9, #8e44ad);
        }
        .form-section {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .form-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../index.php">College Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admission_form.php">Admission Form</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="form-section">
        <div class="container">
            <div class="card form-card">
                <div class="card-body">
                    <h2 class="text-center mb-4">Admission Form</h2>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="process_admission.php" method="POST" enctype="multipart/form-data">
                        <!-- Add max file size directive -->
                        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
                        
                        <!-- Personal Information -->
                        <h4 class="mb-3">Personal Information</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_mode ? htmlspecialchars($form_data['name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $edit_mode ? htmlspecialchars($form_data['email']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $edit_mode ? htmlspecialchars($form_data['phone']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $edit_mode ? htmlspecialchars($form_data['dob']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="father_name" class="form-label">Father's Name</label>
                                <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo $edit_mode ? htmlspecialchars($form_data['father_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mother_name" class="form-label">Mother's Name</label>
                                <input type="text" class="form-control" id="mother_name" name="mother_name" value="<?php echo $edit_mode ? htmlspecialchars($form_data['mother_name']) : ''; ?>" required>
                            </div>
                        </div>

                        <!-- Other Details -->
                        <h4 class="mt-4 mb-3">Other Details</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $edit_mode && $form_data['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $edit_mode && $form_data['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $edit_mode && $form_data['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="gender_other" name="gender_other" placeholder="Please specify" style="display:none;" value="<?php echo $edit_mode ? htmlspecialchars($form_data['gender_other']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="ST/SC" <?php echo $edit_mode && $form_data['category'] == 'ST/SC' ? 'selected' : ''; ?>>ST/SC</option>
                                    <option value="OBC" <?php echo $edit_mode && $form_data['category'] == 'OBC' ? 'selected' : ''; ?>>OBC</option>
                                    <option value="General" <?php echo $edit_mode && $form_data['category'] == 'General' ? 'selected' : ''; ?>>General</option>
                                    <option value="Others" <?php echo $edit_mode && $form_data['category'] == 'Others' ? 'selected' : ''; ?>>Others</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="category_other" name="category_other" placeholder="If other, please specify" style="display:none;" value="<?php echo $edit_mode ? htmlspecialchars($form_data['category_other']) : ''; ?>">
                                <input type="file" class="form-control mt-2" id="caste_certificate" name="caste_certificate" style="display:none;">
                            </div>
                        </div>

                        <!-- Address -->
                        <h4 class="mt-4 mb-3">Address Details</h4>
                        <div class="row g-3">
                            <!-- Address field - fixed to properly fetch address -->
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php 
                                    if ($edit_mode && isset($form_data['address'])) {
                                        echo htmlspecialchars($form_data['address']);
                                    } elseif ($edit_mode) {
                                        // Fetch address specifically if it's not in the initial query
                                        $addr_query = "SELECT address FROM admission_forms WHERE id = ?";
                                        $addr_stmt = $conn->prepare($addr_query);
                                        $addr_stmt->bind_param("s", $form_id);
                                        $addr_stmt->execute();
                                        $addr_result = $addr_stmt->get_result();
                                        if ($addr_data = $addr_result->fetch_assoc()) {
                                            echo htmlspecialchars($addr_data['address']);
                                        }
                                    }
                                ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="" disabled selected>Select your state</option>
                                    <option value="Andhra Pradesh" <?php echo $edit_mode && $form_data['state'] == 'Andhra Pradesh' ? 'selected' : ''; ?>>Andhra Pradesh</option>
                                    <option value="Arunachal Pradesh" <?php echo $edit_mode && $form_data['state'] == 'Arunachal Pradesh' ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                                    <option value="Assam" <?php echo $edit_mode && $form_data['state'] == 'Assam' ? 'selected' : ''; ?>>Assam</option>
                                    <option value="Bihar" <?php echo $edit_mode && $form_data['state'] == 'Bihar' ? 'selected' : ''; ?>>Bihar</option>
                                    <option value="Chhattisgarh" <?php echo $edit_mode && $form_data['state'] == 'Chhattisgarh' ? 'selected' : ''; ?>>Chhattisgarh</option>
                                    <option value="Goa" <?php echo $edit_mode && $form_data['state'] == 'Goa' ? 'selected' : ''; ?>>Goa</option>
                                    <option value="Gujarat" <?php echo $edit_mode && $form_data['state'] == 'Gujarat' ? 'selected' : ''; ?>>Gujarat</option>
                                    <option value="Haryana" <?php echo $edit_mode && $form_data['state'] == 'Haryana' ? 'selected' : ''; ?>>Haryana</option>
                                    <option value="Himachal Pradesh" <?php echo $edit_mode && $form_data['state'] == 'Himachal Pradesh' ? 'selected' : ''; ?>>Himachal Pradesh</option>
                                    <option value="Jharkhand" <?php echo $edit_mode && $form_data['state'] == 'Jharkhand' ? 'selected' : ''; ?>>Jharkhand</option>
                                    <option value="Karnataka" <?php echo $edit_mode && $form_data['state'] == 'Karnataka' ? 'selected' : ''; ?>>Karnataka</option>
                                    <option value="Kerala" <?php echo $edit_mode && $form_data['state'] == 'Kerala' ? 'selected' : ''; ?>>Kerala</option>
                                    <option value="Madhya Pradesh" <?php echo $edit_mode && $form_data['state'] == 'Madhya Pradesh' ? 'selected' : ''; ?>>Madhya Pradesh</option>
                                    <option value="Maharashtra" <?php echo $edit_mode && $form_data['state'] == 'Maharashtra' ? 'selected' : ''; ?>>Maharashtra</option>
                                    <option value="Manipur" <?php echo $edit_mode && $form_data['state'] == 'Manipur' ? 'selected' : ''; ?>>Manipur</option>
                                    <option value="Meghalaya" <?php echo $edit_mode && $form_data['state'] == 'Meghalaya' ? 'selected' : ''; ?>>Meghalaya</option>
                                    <option value="Mizoram" <?php echo $edit_mode && $form_data['state'] == 'Mizoram' ? 'selected' : ''; ?>>Mizoram</option>
                                    <option value="Nagaland" <?php echo $edit_mode && $form_data['state'] == 'Nagaland' ? 'selected' : ''; ?>>Nagaland</option>
                                    <option value="Odisha" <?php echo $edit_mode && $form_data['state'] == 'Odisha' ? 'selected' : ''; ?>>Odisha</option>
                                    <option value="Punjab" <?php echo $edit_mode && $form_data['state'] == 'Punjab' ? 'selected' : ''; ?>>Punjab</option>
                                    <option value="Rajasthan" <?php echo $edit_mode && $form_data['state'] == 'Rajasthan' ? 'selected' : ''; ?>>Rajasthan</option>
                                    <option value="Sikkim" <?php echo $edit_mode && $form_data['state'] == 'Sikkim' ? 'selected' : ''; ?>>Sikkim</option>
                                    <option value="Tamil Nadu" <?php echo $edit_mode && $form_data['state'] == 'Tamil Nadu' ? 'selected' : ''; ?>>Tamil Nadu</option>
                                    <option value="Telangana" <?php echo $edit_mode && $form_data['state'] == 'Telangana' ? 'selected' : ''; ?>>Telangana</option>
                                    <option value="Tripura" <?php echo $edit_mode && $form_data['state'] == 'Tripura' ? 'selected' : ''; ?>>Tripura</option>
                                    <option value="Uttar Pradesh" <?php echo $edit_mode && $form_data['state'] == 'Uttar Pradesh' ? 'selected' : ''; ?>>Uttar Pradesh</option>
                                    <option value="Uttarakhand" <?php echo $edit_mode && $form_data['state'] == 'Uttarakhand' ? 'selected' : ''; ?>>Uttarakhand</option>
                                    <option value="West Bengal" <?php echo $edit_mode && $form_data['state'] == 'West Bengal' ? 'selected' : ''; ?>>West Bengal</option>
                                    <option value="Andaman and Nicobar Islands" <?php echo $edit_mode && $form_data['state'] == 'Andaman and Nicobar Islands' ? 'selected' : ''; ?>>Andaman and Nicobar Islands</option>
                                    <option value="Chandigarh" <?php echo $edit_mode && $form_data['state'] == 'Chandigarh' ? 'selected' : ''; ?>>Chandigarh</option>
                                    <option value="Dadra and Nagar Haveli and Daman and Diu" <?php echo $edit_mode && $form_data['state'] == 'Dadra and Nagar Haveli and Daman and Diu' ? 'selected' : ''; ?>>Dadra and Nagar Haveli and Daman and Diu</option>
                                    <option value="Delhi" <?php echo $edit_mode && $form_data['state'] == 'Delhi' ? 'selected' : ''; ?>>Delhi</option>
                                    <option value="Jammu and Kashmir" <?php echo $edit_mode && $form_data['state'] == 'Jammu and Kashmir' ? 'selected' : ''; ?>>Jammu and Kashmir</option>
                                    <option value="Ladakh" <?php echo $edit_mode && $form_data['state'] == 'Ladakh' ? 'selected' : ''; ?>>Ladakh</option>
                                    <option value="Lakshadweep" <?php echo $edit_mode && $form_data['state'] == 'Lakshadweep' ? 'selected' : ''; ?>>Lakshadweep</option>
                                    <option value="Puducherry" <?php echo $edit_mode && $form_data['state'] == 'Puducherry' ? 'selected' : ''; ?>>Puducherry</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo $edit_mode ? htmlspecialchars($form_data['pincode']) : ''; ?>" required>
                            </div>
                        </div>

                        <!-- Course Details -->
                        <h4 class="mt-4 mb-3">Course Details</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="course" class="form-label">Course</label>
                                <select class="form-select" id="course" name="course" required>
                                    <option value="">Select Course</option>
                                    <?php if (!empty($courses)): ?>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo htmlspecialchars($course['name']); ?>" <?php echo $edit_mode && $form_data['course'] == $course['name'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($course['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="Computer Science & Engineering" <?php echo $edit_mode && $form_data['course'] == 'Computer Science & Engineering' ? 'selected' : ''; ?>>Computer Science & Engineering</option>
                                        <option value="Civil Engineering" <?php echo $edit_mode && $form_data['course'] == 'Civil Engineering' ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="Mechanical Engineering" <?php echo $edit_mode && $form_data['course'] == 'Mechanical Engineering' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="Electrical Engineering" <?php echo $edit_mode && $form_data['course'] == 'Electrical Engineering' ? 'selected' : ''; ?>>Electrical Engineering</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Course Duration</label>
                                <select class="form-select" id="duration" name="duration" required>
                                    <option value="">Select Duration</option>
                                    <option value="2 Years" <?php echo $edit_mode && $form_data['duration'] == '2 Years' ? 'selected' : ''; ?>>2 Years</option>
                                    <option value="3 Years" <?php echo $edit_mode && $form_data['duration'] == '3 Years' ? 'selected' : ''; ?>>3 Years</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="hs_iti_upload" style="display:none;">
                                <label for="hs_iti_document" class="form-label">HS/ITI Document (JPG, PNG, PDF only, max 5MB)</label>
                                <input type="file" class="form-control" id="hs_iti_document" name="hs_iti_document" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>

                        <!-- Document Upload -->
                        <h4 class="mt-4 mb-3">Document Upload</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="madhyamik_admit" class="form-label">Madhyamik Admit Card (JPG, PNG, PDF only, max 5MB)</label>
                                <?php if ($edit_mode && !empty($form_data['madhyamik_admit_path'])): ?>
                                    <div class="mb-2">
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Currently uploaded: <?php echo basename($form_data['madhyamik_admit_path']); ?></small>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="madhyamik_admit" name="madhyamik_admit" accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <div class="col-md-6">
                                <label for="aadhar_number" class="form-label">Aadhar Number/VID</label>
                                <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" pattern="[0-9]{12}" maxlength="12" value="<?php echo $edit_mode ? htmlspecialchars($form_data['aadhar_number']) : ''; ?>" required>
                                <div class="form-text">Enter 12-digit Aadhar number without spaces</div>
                                <?php if ($edit_mode && !empty($form_data['aadhar_doc_path'])): ?>
                                    <div class="mb-2">
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Currently uploaded: <?php echo basename($form_data['aadhar_doc_path']); ?></small>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control mt-2" id="aadhar" name="aadhar" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="photo" class="form-label">Photo (JPG, PNG only, max 5MB)</label>
                                <?php if ($edit_mode && !empty($form_data['photo_path'])): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/photos/<?php echo htmlspecialchars($form_data['photo_path']); ?>" class="img-thumbnail mb-2" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="signature" class="form-label">Signature (JPG, PNG only, max 5MB)</label>
                                <?php if ($edit_mode && !empty($form_data['signature_path'])): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/signatures/<?php echo htmlspecialchars($form_data['signature_path']); ?>" class="img-thumbnail mb-2" style="max-height: 50px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="signature" name="signature" accept=".jpg,.jpeg,.png">
                            </div>

                            <?php if ($edit_mode && $form_data['duration'] == '2 Years' && !empty($form_data['hs_iti_document_path'])): ?>
                                <div class="col-md-6" id="hs_iti_upload">
                                    <label for="hs_iti_document" class="form-label">HS/ITI Document</label>
                                    <div class="mb-2">
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Currently uploaded: <?php echo basename($form_data['hs_iti_document_path']); ?></small>
                                    </div>
                                    <input type="file" class="form-control" id="hs_iti_document" name="hs_iti_document" accept=".jpg,.jpeg,.png,.pdf">
                                </div>
                            <?php endif; ?>

                            <?php if ($edit_mode && in_array($form_data['category'], ['ST/SC', 'OBC']) && !empty($form_data['caste_certificate_path'])): ?>
                                <div class="col-md-6">
                                    <label for="caste_certificate" class="form-label">Caste Certificate</label>
                                    <div class="mb-2">
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Currently uploaded: <?php echo basename($form_data['caste_certificate_path']); ?></small>
                                    </div>
                                    <input type="file" class="form-control" id="caste_certificate" name="caste_certificate" accept=".jpg,.jpeg,.png,.pdf">
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Add hidden fields to track existing files -->
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="existing_madhyamik_admit" value="<?php echo htmlspecialchars($form_data['madhyamik_admit_path']); ?>">
                            <input type="hidden" name="existing_aadhar_doc" value="<?php echo htmlspecialchars($form_data['aadhar_doc_path']); ?>">
                            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($form_data['photo_path']); ?>">
                            <input type="hidden" name="existing_signature" value="<?php echo htmlspecialchars($form_data['signature_path']); ?>">
                            <?php if (!empty($form_data['hs_iti_document_path'])): ?>
                                <input type="hidden" name="existing_hs_iti" value="<?php echo htmlspecialchars($form_data['hs_iti_document_path']); ?>">
                            <?php endif; ?>
                            <?php if (!empty($form_data['caste_certificate_path'])): ?>
                                <input type="hidden" name="existing_caste_cert" value="<?php echo htmlspecialchars($form_data['caste_certificate_path']); ?>">
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Add hidden fields for edit mode -->
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="form_id" value="<?php echo htmlspecialchars($form_id); ?>">
                            <input type="hidden" name="edit_mode" value="reapply">
                        <?php endif; ?>

                        <div class="mt-4">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <?php echo $edit_mode ? 'Update and Resubmit Application' : 'Submit Application'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide caste certificate upload based on category
        document.getElementById('category').addEventListener('change', function() {
            const casteCertificateDiv = document.getElementById('caste_certificate');
            const categoryOtherDiv = document.getElementById('category_other');
            if (this.value === 'ST/SC' || this.value === 'OBC') {
                casteCertificateDiv.style.display = 'block';
                document.getElementById('caste_certificate').required = true;
                categoryOtherDiv.style.display = 'none';
                document.getElementById('category_other').required = false;
            } else if (this.value === 'Others') {
                casteCertificateDiv.style.display = 'none';
                document.getElementById('caste_certificate').required = false;
                categoryOtherDiv.style.display = 'block';
                document.getElementById('category_other').required = true;
            } else {
                casteCertificateDiv.style.display = 'none';
                document.getElementById('caste_certificate').required = false;
                categoryOtherDiv.style.display = 'none';
                document.getElementById('category_other').required = false;
            }
        });

        // Show/hide gender other input based on gender
        document.getElementById('gender').addEventListener('change', function() {
            const genderOtherDiv = document.getElementById('gender_other');
            if (this.value === 'Other') {
                genderOtherDiv.style.display = 'block';
                document.getElementById('gender_other').required = true;
            } else {
                genderOtherDiv.style.display = 'none';
                document.getElementById('gender_other').required = false;
            }
        });

        // Show/hide HS/ITI upload based on course duration
        document.getElementById('duration').addEventListener('change', function() {
            const hsItiUploadDiv = document.getElementById('hs_iti_upload');
            const hsItiDocument = document.getElementById('hs_iti_document');
            
            if (this.value === '2 Years') {
                hsItiUploadDiv.style.display = 'block';
                hsItiDocument.required = true;
            } else {
                hsItiUploadDiv.style.display = 'none';
                hsItiDocument.required = false;
                hsItiDocument.value = ''; // Clear any selected file
            }
        });
    </script>
</body>
</html>
