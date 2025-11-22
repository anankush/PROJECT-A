CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    utr_number VARCHAR(50),    -- Added UTR/UPI reference number field
    status VARCHAR(20) DEFAULT 'pending',
    course_name VARCHAR(100),
    academic_year VARCHAR(20),
    FOREIGN KEY (student_id) REFERENCES students(id)
);
