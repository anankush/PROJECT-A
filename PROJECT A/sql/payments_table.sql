-- First create students table if it doesn't exist
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Now create the payments table with proper foreign key reference
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(20) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending',
    utr_number VARCHAR(50),
    upi_id VARCHAR(100),
    card_number VARCHAR(255),
    card_expiry VARCHAR(5),
    transaction_ref VARCHAR(100),
    receipt_path VARCHAR(255),
    FOREIGN KEY (student_id) REFERENCES students(student_id)
) ENGINE=InnoDB;

-- Add indexes for better performance
CREATE INDEX idx_student_payment ON payments(student_id);
CREATE INDEX idx_payment_status ON payments(status);

-- Add receipt_path column if it doesn't exist
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS receipt_path VARCHAR(255) AFTER transaction_ref;

-- Add upi_id column if it doesn't exist
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS upi_id VARCHAR(100) AFTER utr_number;
