<?php
/**
 * Přidá příznak "ignorovat" ke kanonickým parametrům.
 *
 * Ignorovaný parametr slouží jako trvalá "přeskočit" značka pro LDT import:
 * když se surový název (např. "naklady za kuryra") napáruje na ignorovaný
 * parametr, import daný řádek přeskočí a nevytváří výsledek. Volba se – stejně
 * jako běžné párování – uloží jako alias, takže se příště přeskočí automaticky.
 *
 * Ignorované parametry se zároveň nenabízejí v číselníku (LabParameter::all()
 * je odfiltruje), aby nezaneřáďovaly výběr při ručním zadávání.
 */
return function(PDO $pdo) {
    $stmt = $pdo->query("SHOW COLUMNS FROM lab_parameters LIKE 'is_ignored'");
    if ($stmt->fetch() === false) {
        $pdo->exec("ALTER TABLE lab_parameters
                    ADD COLUMN is_ignored TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
    }
};
