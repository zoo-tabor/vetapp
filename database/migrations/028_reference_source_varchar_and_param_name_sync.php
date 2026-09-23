<?php
/**
 * 1) Zdroj laboratoře (reference_source / source) byl ENUM s pevným seznamem
 *    ('Idexx','Laboklin','Synlab','ZIMS','Fuji'). Nově přidané laboratoře se
 *    ukládají do tabulky reference_sources (např. "Exotic Animal Formulary"),
 *    ale kvůli ENUMu je pak nešlo použít u odběrů ani referenčních mezí – uložení
 *    spadlo, resp. hodnota se ořízla na prázdný řetězec. Převedeme na VARCHAR,
 *    aby fungoval libovolný zdroj z reference_sources.
 *
 * 2) Denormalizovaný parameter_name se místy rozešel s kanonickým názvem v
 *    číselníku lab_parameters – lišil se jen velikostí písmen (např.
 *    "retikulocyty" vs "Retikulocyty"). Kvůli tomu se referenční meze nepárovaly
 *    s výsledky (vyhodnocení se klíčuje podle parameter_name) ani ve správě
 *    referenčních hodnot. Sjednotíme název podle lab_parameters (jediný zdroj
 *    pravdy) všude, kde známe parameter_id.
 *
 * Obojí je idempotentní – ENUM se převádí jen pokud sloupec ENUM ještě je,
 * UPDATE se dotkne jen řádků, které se skutečně liší (binární porovnání).
 */
return function(PDO $pdo) {
    // --- 1) ENUM -> VARCHAR --------------------------------------------------
    $enumToVarchar = [
        // tabulka => [sloupec, definice cílového VARCHAR sloupce]
        ['reference_ranges',   'source',           "VARCHAR(100) NOT NULL"],
        ['biochemistry_tests', 'reference_source', "VARCHAR(100) DEFAULT 'Idexx'"],
        ['hematology_tests',   'reference_source', "VARCHAR(100) DEFAULT 'Idexx'"],
    ];

    foreach ($enumToVarchar as [$table, $column, $definition]) {
        $stmt = $pdo->prepare("
            SELECT DATA_TYPE FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        $dataType = $stmt->fetchColumn();

        // Převádíme jen když je sloupec pořád ENUM (jinak už je hotovo).
        if (strtolower((string)$dataType) === 'enum') {
            $pdo->exec("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$definition}");
        }
    }

    // --- 2) Sjednocení názvů parametrů podle číselníku -----------------------
    // Binární porovnání (utf8mb4_bin) na obou stranách, aby se lišila i velikost
    // písmen – výchozí utf8mb4_unicode_ci je case-insensitive a "retikulocyty"
    // i "Retikulocyty" by považovala za shodné, takže by se nic neaktualizovalo.
    foreach (['reference_ranges', 'biochemistry_results', 'hematology_results'] as $table) {
        $pdo->exec("
            UPDATE `{$table}` t
            JOIN lab_parameters p ON p.id = t.parameter_id
            SET t.parameter_name = p.name
            WHERE t.parameter_id IS NOT NULL
              AND t.parameter_name COLLATE utf8mb4_bin <> p.name COLLATE utf8mb4_bin
        ");
    }
};
