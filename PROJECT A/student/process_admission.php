<?php
session_start();
require_once '../config/db_connection.php';
require_once '../includes/FileHandler.php';

// Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("POST data: " . print_r($_POST, true));

if (!isset($_SESSION['student_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admission_form.php');
    exit();
}

try {
    $fileHandler = new FileHandler('../uploads/');
    
    // Check if this is a reapplication
    $is_reapply = isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'reapply';
    
    // Validate aadhar number format first
    if (!isset($_POST['aadhar_number']) || !preg_match('/^[0-9]{12}$/', $_POST['aadhar_number'])) {
        throw new Exception("Invalid Aadhar number format. Must be 12 digits.");
    }
    
    // Required fields validation
    $requiredFields = ['name', 'email', 'phone', 'dob', 'father_name', 'mother_name', 
                      'gender', 'category', 'address', 'state', 'pincode', 'course', 
                      'duration', 'aadhar_number'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Required files with their existing file paths
    $requiredFiles = [
        'madhyamik_admit' => [
            'dir' => 'madhyamik_admits/',
            'existing' => isset($_POST['existing_madhyamik_admit']) ? $_POST['existing_madhyamik_admit'] : null
        ],
        'aadhar' => [
            'dir' => 'aadhar_docs/',
            'existing' => isset($_POST['existing_aadhar_doc']) ? $_POST['existing_aadhar_doc'] : null
        ],
        'photo' => [
            'dir' => 'photos/',
            'existing' => isset($_POST['existing_photo']) ? $_POST['existing_photo'] : null
        ],
        'signature' => [
            'dir' => 'signatures/',
            'existing' => isset($_POST['existing_signature']) ? $_POST['existing_signature'] : null
        ]
    ];

    $uploadedFiles = [];
    foreach ($requiredFiles as $fileKey => $info) {
        if (!empty($_FILES[$fileKey]['name'])) {
            // New file uploaded
            $fileHandler->validateFile($_FILES[$fileKey]);
            $uploadedFiles[$fileKey] = $fileHandler->uploadFile($_FILES[$fileKey], $info['dir']);
        } elseif ($is_reapply && !empty($info['existing'])) {
            // Use existing file path for reapplication
            $uploadedFiles[$fileKey] = $info['existing'];
        } elseif (!$is_reapply) {
            // New application requires all files
            throw new Exception("Missing required file: $fileKey");
        }
    }

    // Handle optional files
    if ($_POST['duration'] === '2 Years') {
        if (!empty($_FILES['hs_iti_document']['name'])) {
            $fileHandler->validateFile($_FILES['hs_iti_document']);
            $uploadedFiles['hs_iti'] = $fileHandler->uploadFile($_FILES['hs_iti_document'], 'hs_iti_documents/');
        } elseif ($is_reapply && !empty($_POST['existing_hs_iti'])) {
            $uploadedFiles['hs_iti'] = $_POST['existing_hs_iti'];
        }
    }

    if (in_array($_POST['category'], ['ST/SC', 'OBC'])) {
        if (!empty($_FILES['caste_certificate']['name'])) {
            $fileHandler->validateFile($_FILES['caste_certificate']);
            $uploadedFiles['caste'] = $fileHandler->uploadFile($_FILES['caste_certificate'], 'caste_certificates/');
        } elseif ($is_reapply && !empty($_POST['existing_caste_cert'])) {
            $uploadedFiles['caste'] = $_POST['existing_caste_cert'];
        }
    }

    if ($is_reapply) {
        // Validate form_id
        if (!isset($_POST['form_id']) || empty($_POST['form_id'])) {
            throw new Exception("Invalid form ID for reapplication");
        }

        // Update existing application with ALL fields
        $update_query = "UPDATE admission_forms SET 
                        name = ?, email = ?, phone = ?, dob = ?, 
                        father_name = ?, mother_name = ?, gender = ?,
                        gender_other = ?, category = ?, category_other = ?,
                        address = ?, state = ?, pincode = ?, 
                        course = ?, duration = ?, aadhar_number = ?,
                        madhyamik_admit_path = ?, aadhar_doc_path = ?,
                        photo_path = ?, signature_path = ?, 
                        caste_certificate_path = ?, hs_iti_document_path = ?,
                        status = 'pending', reapplied = 1,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = ? AND student_id = ?";
        
        $gender_other = !empty($_POST['gender_other']) ? $_POST['gender_other'] : null;
        $category_other = !empty($_POST['category_other']) ? $_POST['category_other'] : null;
        $caste_certificate = isset($uploadedFiles['caste']) ? $uploadedFiles['caste'] : $_POST['existing_caste_cert'];
        $hs_iti_doc = isset($uploadedFiles['hs_iti']) ? $uploadedFiles['hs_iti'] : $_POST['existing_hs_iti'];

        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssssssssssssssssssii",
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['dob'],
            $_POST['father_name'],
            $_POST['mother_name'],
            $_POST['gender'],
            $gender_other,
            $_POST['category'],
            $category_other,
            $_POST['address'],
            $_POST['state'],
            $_POST['pincode'],
            $_POST['course'],
            $_POST['duration'],
            $_POST['aadhar_number'],
            $uploadedFiles['madhyamik_admit'],
            $uploadedFiles['aadhar'],
            $uploadedFiles['photo'],
            $uploadedFiles['signature'],
            $caste_certificate,
            $hs_iti_doc,
            $_POST['form_id'],
            $_SESSION['student_id']
        );
    } else {
        // Fixed INSERT query with correct column name 'duration' instead of 'course_duration'
        $stmt = $conn->prepare("INSERT INTO admission_forms (
            student_id, name, email, phone, dob, father_name, mother_name, 
            gender, gender_other, category, category_other, caste_certificate_path,
            address, state, pincode, course, duration, 
            hs_iti_document_path, madhyamik_admit_path, aadhar_number,
            aadhar_doc_path, photo_path, signature_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        // Convert student_id to integer and prepare values
        $student_id = (int)$_SESSION['student_id'];
        $gender_other = !empty($_POST['gender_other']) ? $_POST['gender_other'] : null;
        $category_other = !empty($_POST['category_other']) ? $_POST['category_other'] : null;
        $caste_certificate = isset($uploadedFiles['caste']) ? $uploadedFiles['caste'] : null;
        $hs_iti_doc = isset($uploadedFiles['hs_iti']) ? $uploadedFiles['hs_iti'] : null;

        // Store all values in variables first
        $values = [
            $student_id,           // 1. integer
            $_POST['name'],        // 2. string
            $_POST['email'],       // 3. string
            $_POST['phone'],       // 4. string
            $_POST['dob'],         // 5. string
            $_POST['father_name'], // 6. string
            $_POST['mother_name'], // 7. string
            $_POST['gender'],      // 8. string
            $gender_other,         // 9. string
            $_POST['category'],    // 10. string
            $category_other,       // 11. string
            $caste_certificate,    // 12. string
            $_POST['address'],     // 13. string
            $_POST['state'],       // 14. string
            $_POST['pincode'],     // 15. string
            $_POST['course'],      // 16. string
            $_POST['duration'],    // 17. string
            $hs_iti_doc,          // 18. string
            $uploadedFiles['madhyamik_admit'], // 19. string
            $_POST['aadhar_number'],           // 20. string
            $uploadedFiles['aadhar'],          // 21. string
            $uploadedFiles['photo'],           // 22. string
            $uploadedFiles['signature']        // 23. string
        ];

        // Create correct types string - 1 integer + 22 strings = 23 parameters
        $types = 'i' . str_repeat('s', 22);

        // Bind parameters using spread operator
        $stmt->bind_param($types, ...$values);
    }

    // Start transaction and execute
    $conn->begin_transaction();
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to save application: " . $stmt->error);
    }

    $conn->commit();
    $_SESSION['success_msg'] = $is_reapply ? "Application updated and resubmitted successfully!" : "Application submitted successfully!";
    header('Location: dashboard.php');
    exit();

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    if (isset($fileHandler)) $fileHandler->cleanup();
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: ' . ($is_reapply ? 'admission_form.php' : 'dashboard.php'));
    exit();
}
?>
