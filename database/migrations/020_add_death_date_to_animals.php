<?php
/**
 * Přidá sloupec animals.death_date (datum úmrtí).
 *
 * V produkci sloupec zpravidla už existuje – přidal ho ruční import zvířat
 * (ALTER TABLE animals ADD COLUMN IF NOT EXISTS death_date ...). Tato migrace
 * zajišťuje, že sloupec existuje i v prostředích, kam import nešel (např. nové
 * nasazení ze schématu), aby s ním UI mohlo počítat všude stejně.
 *
 * Idempotentní: sloupec se přidává jen když chybí; rozšíření identifieru na
 * varchar(100) (také součást importu) je obalené try/catch, aby opětovné
 * spuštění na už upravené DB nespadlo.
 */
return function(PDO $pdo) {
    $stmt = $pdo->query("SHOW COLUMNS FROM animals LIKE 'death_date'");
    if ($stmt->fetch() === false) {
        $pdo->exec("ALTER TABLE animals ADD COLUMN death_date date NULL DEFAULT NULL AFTER birth_date");
    }

    // Import zároveň rozšířil identifier na varchar(100); sjednotíme i tady.
    try {
        $pdo->exec("ALTER TABLE animals MODIFY COLUMN identifier varchar(100) DEFAULT NULL");
    } catch (Exception $e) {
        // Např. pokud je sloupec součástí indexu s jiným omezením – necháme být.
    }
};
