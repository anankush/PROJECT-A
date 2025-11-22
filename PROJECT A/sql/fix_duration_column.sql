-- Check and fix the duration column name
SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
           WHERE TABLE_NAME = 'admission_forms' 
           AND COLUMN_NAME = 'course_duration'
           AND TABLE_SCHEMA = DATABASE()),
    'ALTER TABLE admission_forms CHANGE course_duration duration VARCHAR(255)',
    'ALTER TABLE admission_forms MODIFY COLUMN duration VARCHAR(255)'
) INTO @sql;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
