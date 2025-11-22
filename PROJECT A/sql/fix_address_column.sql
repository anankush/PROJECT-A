-- Check if permanent_address exists and convert it to address
SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
           WHERE TABLE_NAME = 'admission_forms' 
           AND COLUMN_NAME = 'permanent_address'
           AND TABLE_SCHEMA = DATABASE()),
    'ALTER TABLE admission_forms CHANGE permanent_address address TEXT',
    'ALTER TABLE admission_forms ADD COLUMN IF NOT EXISTS address TEXT'
) INTO @sql;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
