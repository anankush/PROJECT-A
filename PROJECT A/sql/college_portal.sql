CREATE DATABASE college_portal;
USE college_portal;

CREATE TABLE admission_forms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    dob DATE,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    gender VARCHAR(20),
    gender_other VARCHAR(50),
    category VARCHAR(20),
    caste_certificate_path VARCHAR(255),
    permanent_address TEXT,
    state VARCHAR(50),
    pincode VARCHAR(10),
    course VARCHAR(100),
    course_duration VARCHAR(20),
    hs_iti_document_path VARCHAR(255),
    madhyamik_admit_path VARCHAR(255),
    aadhar_number VARCHAR(20),
    aadhar_doc_path VARCHAR(255),
    photo VARCHAR(255),
    signature_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admission_id INT,
    amount DECIMAL(10,2),
    transaction_id VARCHAR(100),
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admission_id) REFERENCES admission_forms(id)
);

-- Contact Messages Table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread'
);

-- Courses Table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    specialization VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default courses
INSERT INTO courses (name, description, duration, specialization) VALUES
('Computer Science & Engineering', 'Learn about computer systems, software development, and cutting-edge technologies.', '2-3 Years', 'Specializations Available'),
('Civil Engineering', 'Study structural engineering, construction management, and environmental systems.', '2-3 Years', 'Specializations Available'),
('Mechanical Engineering', 'Master the principles of mechanics, thermodynamics, and machine design.', '2-3 Years', 'Specializations Available'),
('Electrical Engineering', 'Study power systems, electronics, and control systems.', '2-3 Years', 'Specializations Available');
