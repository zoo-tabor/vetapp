<?php
/**
 * Add email + phone contact columns to zootrack_institutions.
 *
 * Idempotent: these columns already exist in production (added out-of-band,
 * never recorded as a migration), so this must no-op there instead of failing
 * on a duplicate column. On a fresh rebuild it creates them. MySQL/MariaDB has
 * no portable `ADD COLUMN IF NOT EXISTS`, so we probe information_schema per
 * column. `AFTER website` keeps them grouped with the other contact fields when
 * the table is built from scratch (harmless if `website` ordering differs).
 */
return function(PDO $pdo) {
    $table = 'zootrack_institutions';

    $columns = [
        'email' => "`email` VARCHAR(255) DEFAULT NULL COMMENT 'Kontaktní e-mail instituce' AFTER `website`",
        'phone' => "`phone` VARCHAR(100) DEFAULT NULL COMMENT 'Kontaktní telefon instituce' AFTER `email`",
    ];

    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = :t
           AND COLUMN_NAME  = :c"
    );

    foreach ($columns as $name => $definition) {
        $check->execute([':t' => $table, ':c' => $name]);
        if ((int) $check->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$definition}");
        }
    }
};
