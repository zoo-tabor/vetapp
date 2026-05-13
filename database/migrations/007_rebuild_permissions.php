<?php
return function(PDO $pdo) {
    // 1. Create new user_permissions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_permissions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `workplace_id` int(11) NOT NULL,
            `section` enum('animals','biochemistry','urine','vaccination','warehouse','parasitology','lexikon') NOT NULL,
            `can_view` tinyint(1) NOT NULL DEFAULT 0,
            `can_edit` tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_workplace_section` (`user_id`,`workplace_id`,`section`),
            KEY `user_id` (`user_id`),
            KEY `workplace_id` (`workplace_id`),
            CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_up_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // 2. Fan existing user_workplace_permissions out to all sections
    $pdo->exec("
        INSERT IGNORE INTO user_permissions (user_id, workplace_id, section, can_view, can_edit)
        SELECT uwp.user_id, uwp.workplace_id, s.section, uwp.can_view, uwp.can_edit
        FROM user_workplace_permissions uwp
        CROSS JOIN (
            SELECT 'animals'       AS section UNION ALL
            SELECT 'biochemistry'             UNION ALL
            SELECT 'urine'                    UNION ALL
            SELECT 'vaccination'              UNION ALL
            SELECT 'warehouse'                UNION ALL
            SELECT 'parasitology'             UNION ALL
            SELECT 'lexikon'
        ) s
    ");

    // 3. Collapse user_edit / user_read → user
    $pdo->exec("UPDATE users SET role = 'user' WHERE role IN ('user_read', 'user_edit')");

    // 4. Alter role enum (drop old values)
    $pdo->exec("
        ALTER TABLE users
        MODIFY COLUMN `role` enum('admin','user') NOT NULL DEFAULT 'user'
    ");

    // 5. Drop old permissions table
    $pdo->exec("DROP TABLE IF EXISTS `user_workplace_permissions`");
};
