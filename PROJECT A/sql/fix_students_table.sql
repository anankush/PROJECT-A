
-- Option 1: If table exists, add the phone column
ALTER TABLE students
ADD COLUMN phone VARCHAR(20) AFTER email;

-- Option 2: If table doesn't exist, create it with all columns
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    is_temp_password TINYINT(1) DEFAULT 0,
    login_attempts INT DEFAULT 0,
    last_login_attempt TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
