<?php
/**
 * Paměť párování pro import parazitologie (.xlsx).
 *
 * Uloží, na jaké zvíře / skupinu byl v daném pracovišti napárován textový popis
 * vzorku (např. "vodní svět vz. 1"), aby se při dalším importu předvyplnil.
 * Párování se ale VŽDY zobrazí k ruční kontrole – alias je jen návrh.
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `parasitology_import_aliases` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `workplace_id` int(11) NOT NULL,
            `sample_text_norm` varchar(255) NOT NULL,
            `target_type` enum('animal','group') NOT NULL,
            `target_id` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_wp_text` (`workplace_id`,`sample_text_norm`),
            KEY `workplace_id` (`workplace_id`),
            CONSTRAINT `fk_pia_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
