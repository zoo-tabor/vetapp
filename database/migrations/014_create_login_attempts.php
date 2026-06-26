<?php
/**
 * Track failed login attempts for brute-force throttling (SEC-10).
 * One row per failed attempt; rows for a username are cleared on successful login.
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NOT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_username_time` (`username`, `attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
