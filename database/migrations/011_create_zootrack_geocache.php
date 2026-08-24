<?php
/**
 * Geocoding cache for ZooTrack map.
 * Stores Nominatim results for city+country pairs so geocoding runs only once.
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `zootrack_geocache` (
            `city`       VARCHAR(100) NOT NULL,
            `country`    VARCHAR(100) NOT NULL,
            `lat`        DECIMAL(9,6) DEFAULT NULL,
            `lng`        DECIMAL(9,6) DEFAULT NULL,
            `not_found`  TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = Nominatim vrátil prázdný výsledek',
            `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`city`, `country`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
};
