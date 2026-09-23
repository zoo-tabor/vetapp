<?php
/**
 * Analýza moči: urine_reference_ranges.reference_source byl ENUM s pevným
 * seznamem ('Idexx','Laboklin','Synlab','ZIMS'). Stejně jako u biochemie
 * (migrace 028) to bránilo použití jakékoli jiné laboratoře. Převedeme na
 * VARCHAR, aby šel uložit libovolný zdroj.
 *
 * (urine_tests.reference_source už VARCHAR je, mění se jen tabulka mezí.)
 *
 * Idempotentní – převádí jen dokud je sloupec ENUM.
 */
return function(PDO $pdo) {
    $stmt = $pdo->prepare("
        SELECT DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'urine_reference_ranges'
          AND COLUMN_NAME = 'reference_source'
    ");
    $stmt->execute();
    $dataType = $stmt->fetchColumn();

    if (strtolower((string)$dataType) === 'enum') {
        $pdo->exec("ALTER TABLE `urine_reference_ranges`
            MODIFY COLUMN `reference_source` VARCHAR(100) NOT NULL DEFAULT 'Synlab'");
    }
};
