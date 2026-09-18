<?php
/**
 * Odstranění sloupce enclosures.sample_type (individual/mixed).
 *
 * Vzorkování po jedincích/výbězích se v systému už nepoužívá – parazitologické
 * testování probíhá přes parazitologické skupiny. Sloupec proto rušíme.
 * (examinations.sample_type ZŮSTÁVÁ – nese info, zda šlo o hromadný odběr.)
 *
 * Pojistka: sloupec zahodíme jen pokud existuje, aby migrace nespadla při
 * případném opakovaném spuštění (viz migrační gotcha v CLAUDE.md).
 */
return function(PDO $pdo) {
    $exists = $pdo->query("
        SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'enclosures'
          AND COLUMN_NAME = 'sample_type'
    ")->fetchColumn();

    if ($exists > 0) {
        $pdo->exec("ALTER TABLE `enclosures` DROP COLUMN `sample_type`");
    }
};
