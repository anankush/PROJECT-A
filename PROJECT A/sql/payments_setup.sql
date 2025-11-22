
-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    utr_number VARCHAR(100),
    transaction_id VARCHAR(100),
    course_name VARCHAR(100),
    status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
    admin_remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Add indexes for better performance
CREATE INDEX idx_student_payment ON payments(student_id);
CREATE INDEX idx_payment_status ON payments(status);
