<?php
/**
 * Create animal_weight_history table
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `animal_weight_history` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `animal_id` INT NOT NULL,
            `weight` DECIMAL(8,2) NOT NULL,
            `measured_date` DATE NOT NULL,
            `notes` VARCHAR(255) DEFAULT NULL,
            `created_by` INT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `animal_id` (`animal_id`),
            KEY `measured_date` (`measured_date`),
            CONSTRAINT `fk_awh_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_awh_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
