-- Create animal_categories table
CREATE TABLE IF NOT EXISTS `animal_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workplace_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `workplace_id` (`workplace_id`),
  UNIQUE KEY `unique_category_per_workplace` (`workplace_id`, `name`),
  CONSTRAINT `fk_animal_categories_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add animal_category_id column to animals table
ALTER TABLE `animals`
ADD COLUMN `animal_category_id` int(11) DEFAULT NULL AFTER `animal_category`,
ADD KEY `animal_category_id` (`animal_category_id`),
ADD CONSTRAINT `fk_animals_category` FOREIGN KEY (`animal_category_id`) REFERENCES `animal_categories` (`id`) ON DELETE SET NULL;

-- Migrate existing data from animal_category (text) to animal_category_id
-- This will create categories and link them to animals
INSERT INTO `animal_categories` (`workplace_id`, `name`)
SELECT DISTINCT a.workplace_id, a.animal_category
FROM `animals` a
WHERE a.animal_category IS NOT NULL
  AND a.animal_category != ''
  AND a.animal_category NOT IN (
    SELECT name FROM animal_categories WHERE workplace_id = a.workplace_id
  );

-- Update animals to use the new category_id
UPDATE `animals` a
JOIN `animal_categories` ac ON ac.workplace_id = a.workplace_id AND ac.name = a.animal_category
SET a.animal_category_id = ac.id
WHERE a.animal_category IS NOT NULL AND a.animal_category != '';

-- Drop the old animal_category column (after confirming migration worked)
-- ALTER TABLE `animals` DROP COLUMN `animal_category`;
