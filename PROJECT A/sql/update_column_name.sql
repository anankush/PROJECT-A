-- Check if permanent_address column exists and address doesn't
SELECT IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
           WHERE TABLE_NAME = 'admission_forms' 
           AND COLUMN_NAME = 'permanent_address'
           AND TABLE_SCHEMA = DATABASE())
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME = 'admission_forms' 
                  AND COLUMN_NAME = 'address'
                  AND TABLE_SCHEMA = DATABASE()),
    'ALTER TABLE admission_forms CHANGE permanent_address address TEXT',
    'SELECT "No change needed"'
) INTO @sql;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
