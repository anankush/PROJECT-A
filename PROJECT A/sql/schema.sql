
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS college_portal;
USE college_portal;

-- Create admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    registration_number VARCHAR(50) UNIQUE,
    is_verified BOOLEAN DEFAULT FALSE,
    login_attempts INT DEFAULT 0,
    last_attempt TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create admission_forms table
CREATE TABLE IF NOT EXISTS admission_forms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    mother_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    gender VARCHAR(20) NOT NULL,
    category VARCHAR(50) NOT NULL,
    permanent_address TEXT NOT NULL,
    state VARCHAR(50) NOT NULL,
    pincode VARCHAR(6) NOT NULL,
    course VARCHAR(100) NOT NULL,
    course_duration VARCHAR(20) NOT NULL,
    aadhar_number VARCHAR(12) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    -- Document paths
    caste_certificate_path VARCHAR(255),
    hs_iti_document_path VARCHAR(255),
    madhyamik_admit_path VARCHAR(255) NOT NULL,
    aadhar_doc_path VARCHAR(255) NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    signature_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    admission_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('upi', 'qr', 'card', 'neft') NOT NULL,
    utr_number VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'verified') DEFAULT 'pending',
    verified_by INT,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verification_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (admission_id) REFERENCES admission_forms(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payment_logs table
CREATE TABLE IF NOT EXISTS payment_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create contact_messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create password_reset_tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    type ENUM('student', 'admin') NOT NULL,
    expiry TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (token),
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create system_settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create necessary indices
CREATE INDEX idx_student_email ON students(email);
CREATE INDEX idx_student_phone ON students(phone);
CREATE INDEX idx_payment_status ON payments(payment_status);
CREATE INDEX idx_payment_date ON payments(payment_date);
CREATE INDEX idx_utr_number ON payments(utr_number);

-- Insert default admin account
INSERT INTO admin (username, name, email, password) 
VALUES ('admin', 'Administrator', 'admin@example.com', '$2y$10$default_hashed_password')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value) VALUES 
('admin_auth_key', 'NAYAN@123'),
('payment_amount', '5000.00'),
('admission_open', 'true'),
('site_maintenance', 'false')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
