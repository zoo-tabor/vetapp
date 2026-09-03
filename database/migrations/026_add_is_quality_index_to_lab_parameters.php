<?php
/**
 * Příznak "index kvality vzorku" na kanonickém parametru.
 *
 * Lipemický a hemolytický index vypovídají o KVALITĚ vzorku, ne o konkrétním
 * analytu, proto je chceme ve všech přehledech i v tisku vytáhnout do vlastní
 * skupiny "Kvalita vzorku", ne mezi biochemii/hematologii.
 *
 * test_type zůstává 'biochemistry' (kvůli uložení výsledků v biochemistry_results);
 * tenhle příznak je čistě zobrazovací kategorie.
 */
return function(PDO $pdo) {
    $pdo->exec("ALTER TABLE `lab_parameters` ADD COLUMN `is_quality_index` tinyint(1) NOT NULL DEFAULT 0");

    // Označit lipemický + hemolytický index (vč. anglických názvů z LDT).
    $pdo->exec("
        UPDATE `lab_parameters`
        SET `is_quality_index` = 1
        WHERE name LIKE '%ipaem%'   -- Lipaemia
           OR name LIKE '%ipem%'    -- Lipemický
           OR name LIKE '%aemolys%' -- Haemolysis
           OR name LIKE '%emolys%'  -- Hemolysis
           OR name LIKE '%emolyt%'  -- Hemolytický
    ");
};
