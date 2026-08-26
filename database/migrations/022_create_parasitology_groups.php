<?php
/**
 * Parazitologické skupiny.
 *
 * Skupina = editovatelná množina zvířat v rámci jednoho pracoviště, na kterou
 * lze zadávat společné (skupinové) vzorky a aplikaci antiparazitik.
 *
 * Členství je řešeno samostatnou tabulkou s UNIQUE(animal_id) – zvíře smí být
 * nejvýše v JEDNÉ skupině (0 nebo 1). Individuální vyšetření zvířete je vždy
 * možné navíc, nezávisle na členství ve skupině.
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `parasitology_groups` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `workplace_id` int(11) NOT NULL,
            `name` varchar(150) NOT NULL,
            `notes` text DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_group_per_workplace` (`workplace_id`,`name`),
            KEY `workplace_id` (`workplace_id`),
            CONSTRAINT `fk_pg_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `parasitology_group_members` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `group_id` int(11) NOT NULL,
            `animal_id` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_animal` (`animal_id`),
            KEY `group_id` (`group_id`),
            CONSTRAINT `fk_pgm_group` FOREIGN KEY (`group_id`) REFERENCES `parasitology_groups` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pgm_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
