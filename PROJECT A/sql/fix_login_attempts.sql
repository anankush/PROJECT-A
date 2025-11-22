
-- Fix the column name from last_login_attempt to last_attempt
ALTER TABLE students 
CHANGE COLUMN last_login_attempt last_attempt TIMESTAMP NULL;

-- If the column doesn't exist, add it
ALTER TABLE students 
ADD COLUMN last_attempt TIMESTAMP NULL 
AFTER login_attempts;
