<?php
/**
 * Napojení vyšetření a odčervení na parazitologické skupiny (Část 3).
 *
 * Model: vyšetření/odčervení může být buď individuální (animal_id), nebo
 * skupinové (group_id). Aby čtení historie bylo jednotné a exportovatelné,
 * zavádíme spojovací tabulky examination_animals / deworming_animals, do
 * kterých se zapisují VŠECHNA napojená zvířata (individuální = 1 řádek,
 * skupinové = N řádků = snímek členů skupiny k datu záznamu). Historie zvířete
 * se tak čte jednotně přes tyto junction tabulky.
 */
return function(PDO $pdo) {
    // --- Vyšetření: junction + snímek stávajících záznamů -------------------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `examination_animals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `examination_id` int(11) NOT NULL,
            `animal_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_exam_animal` (`examination_id`,`animal_id`),
            KEY `examination_id` (`examination_id`),
            KEY `animal_id` (`animal_id`),
            CONSTRAINT `fk_ea_exam` FOREIGN KEY (`examination_id`) REFERENCES `examinations` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ea_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Backfill: každé stávající vyšetření má animal_id → jeden řádek v junction.
    $pdo->exec("
        INSERT IGNORE INTO examination_animals (examination_id, animal_id)
        SELECT id, animal_id FROM examinations WHERE animal_id IS NOT NULL
    ");

    // Skupinové napojení + zvolnění animal_id (skupinové záznamy mají animal_id NULL).
    $pdo->exec("ALTER TABLE `examinations` ADD COLUMN `group_id` int(11) DEFAULT NULL");
    $pdo->exec("ALTER TABLE `examinations` ADD KEY `group_id` (`group_id`)");
    $pdo->exec("ALTER TABLE `examinations` MODIFY COLUMN `animal_id` int(11) DEFAULT NULL");
    $pdo->exec("
        ALTER TABLE `examinations`
        ADD CONSTRAINT `fk_exam_group` FOREIGN KEY (`group_id`)
        REFERENCES `parasitology_groups` (`id`) ON DELETE SET NULL
    ");

    // --- Odčervení: junction + snímek stávajících záznamů -------------------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `deworming_animals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `deworming_id` int(11) NOT NULL,
            `animal_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_dew_animal` (`deworming_id`,`animal_id`),
            KEY `deworming_id` (`deworming_id`),
            KEY `animal_id` (`animal_id`),
            CONSTRAINT `fk_da_dew` FOREIGN KEY (`deworming_id`) REFERENCES `dewormings` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_da_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        INSERT IGNORE INTO deworming_animals (deworming_id, animal_id)
        SELECT id, animal_id FROM dewormings WHERE animal_id IS NOT NULL
    ");

    $pdo->exec("ALTER TABLE `dewormings` ADD COLUMN `group_id` int(11) DEFAULT NULL");
    $pdo->exec("ALTER TABLE `dewormings` ADD KEY `group_id` (`group_id`)");
    $pdo->exec("ALTER TABLE `dewormings` MODIFY COLUMN `animal_id` int(11) DEFAULT NULL");
    $pdo->exec("
        ALTER TABLE `dewormings`
        ADD CONSTRAINT `fk_dew_group` FOREIGN KEY (`group_id`)
        REFERENCES `parasitology_groups` (`id`) ON DELETE SET NULL
    ");
};
