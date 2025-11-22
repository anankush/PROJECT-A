-- Drop existing students table if it exists
DROP TABLE IF EXISTS students;

-- Create students table with all required columns
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    login_attempts INT DEFAULT 0,
    last_attempt TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Drop existing admission_forms table if it exists
DROP TABLE IF EXISTS admission_forms;

-- Create admission_forms table with all required columns
CREATE TABLE admission_forms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    mother_name VARCHAR(100) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    gender_other VARCHAR(50),
    category VARCHAR(20) NOT NULL,
    category_other VARCHAR(50),
    caste_certificate_path VARCHAR(255),
    permanent_address TEXT NOT NULL,
    state VARCHAR(50) NOT NULL,
    pincode VARCHAR(6) NOT NULL,
    course VARCHAR(100) NOT NULL,
    course_duration VARCHAR(20) NOT NULL,
    hs_iti_document_path VARCHAR(255),
    madhyamik_admit_path VARCHAR(255) NOT NULL,
    aadhar_number VARCHAR(12) NOT NULL,
    aadhar_doc_path VARCHAR(255) NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    signature_path VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Create reset_tokens table for password resets
CREATE TABLE reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255),
    token VARCHAR(255),
    expiry TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
