DROP TABLE IF EXISTS courses;

CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

-- Insert default courses
INSERT INTO courses (name) VALUES
('Computer Science & Engineering'),
('Civil Engineering'),
('Mechanical Engineering'),
('Electrical Engineering');
