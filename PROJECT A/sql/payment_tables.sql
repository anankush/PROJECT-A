
-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('upi', 'qr', 'card') NOT NULL,
    utr_number VARCHAR(100) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    student_name VARCHAR(100) NOT NULL,
    upi_id VARCHAR(100),
    card_number VARCHAR(16),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payment_logs table for tracking all payment activities
CREATE TABLE IF NOT EXISTS payment_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert example payment status (optional, for testing)
INSERT INTO payments (student_id, amount, payment_method, utr_number, status, student_name) 
VALUES 
(1, 5000.00, 'upi', 'UTR123456789', 'completed', 'John Doe'),
(2, 5000.00, 'card', 'UTR987654321', 'pending', 'Jane Smith');
