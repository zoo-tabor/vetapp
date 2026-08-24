<?php
/**
 * Add contact tracking and animal exchange fields to zootrack_institutions.
 *
 * Idempotent: each column is added only if it does not already exist. These
 * columns exist in production but were never recorded in the `migrations`
 * tracking table, so this migration WILL be (re-)run there — it must no-op
 * cleanly instead of failing on a duplicate column. MySQL/MariaDB has no
 * portable `ADD COLUMN IF NOT EXISTS`, so we probe information_schema per column.
 */
return function(PDO $pdo) {
    $table = 'zootrack_institutions';

    $columns = [
        'last_contact_date' => "`last_contact_date` DATE       DEFAULT NULL       COMMENT 'Datum posledního kontaktu'",
        'contact_notes'     => "`contact_notes`     TEXT       DEFAULT NULL       COMMENT 'Poznámka ke kontaktu'",
        'animals_from_them' => "`animals_from_them` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = máme od nich zvířata'",
        'animals_at_them'   => "`animals_at_them`   TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = mají od nás zvířata'",
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
