<?php
/**
 * Create ZooTrack audit log table.
 * Logs inserts, updates and deletes on species, holdings and institutions.
 */
return function(PDO $pdo) {

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `zootrack_changes` (
            `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `entity_type` VARCHAR(30)   NOT NULL COMMENT 'species | holding | institution',
            `entity_id`   VARCHAR(50)   NOT NULL COMMENT 'PK of the changed row (int or string)',
            `action`      VARCHAR(10)   NOT NULL COMMENT 'create | update | delete',
            `changes`     JSON          DEFAULT NULL COMMENT 'JSON object: {field: [old, new]}',
            `label`       VARCHAR(255)  DEFAULT NULL COMMENT 'Human-readable identifier at time of change',
            `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_entity` (`entity_type`, `entity_id`),
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
};
