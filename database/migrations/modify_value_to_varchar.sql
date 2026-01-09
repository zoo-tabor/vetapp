-- Migration: Change value column from decimal to varchar in biochemistry_results and hematology_results
-- This allows storing text values like "neg.", "negativní", etc.

-- Backup note: Before running this migration, consider backing up your database

-- Modify biochemistry_results table
ALTER TABLE `biochemistry_results`
MODIFY COLUMN `value` VARCHAR(100) NULL;

-- Modify hematology_results table
ALTER TABLE `hematology_results`
MODIFY COLUMN `value` VARCHAR(100) NULL;

-- Note: Existing decimal values will be automatically converted to strings
-- Example: 45.2 becomes "45.2"
