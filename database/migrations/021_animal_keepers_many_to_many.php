<?php
/**
 * Přechod přiřazení ošetřovatelů na vztah M:N.
 *
 * Dosud měl každé zvíře nejvýše jednoho ošetřovatele (animals.assigned_user,
 * text = username). Nově může mít zvíře více ošetřovatelů – vazby jsou v
 * samostatné tabulce animal_keepers (animal_id + user_id).
 *
 * 1. vytvořit animal_keepers (s FK a unikátním párem)
 * 2. přenést existující jednotlivé přiřazení z animals.assigned_user
 * 3. zahodit sloupec animals.assigned_user (i jeho index)
 *
 * Idempotentní: tabulka se vytváří přes IF NOT EXISTS, přenos i drop se dělají
 * jen dokud starý sloupec existuje.
 */
return function(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS animal_keepers (
            id INT(11) NOT NULL AUTO_INCREMENT,
            animal_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY uniq_animal_user (animal_id, user_id),
            KEY user_id (user_id),
            CONSTRAINT fk_ak_animal FOREIGN KEY (animal_id) REFERENCES animals (id) ON DELETE CASCADE,
            CONSTRAINT fk_ak_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    // Přenos původního jednotlivého přiřazení a odstranění starého sloupce.
    $hasCol = $pdo->query("SHOW COLUMNS FROM animals LIKE 'assigned_user'")->fetch();
    if ($hasCol !== false) {
        $pdo->exec("
            INSERT IGNORE INTO animal_keepers (animal_id, user_id)
            SELECT a.id, u.id
            FROM animals a
            JOIN users u ON u.username = a.assigned_user
            WHERE a.assigned_user IS NOT NULL AND a.assigned_user <> ''
        ");
        // Sloupec (a jeho index idx_assigned_user) už není potřeba.
        $pdo->exec("ALTER TABLE animals DROP COLUMN assigned_user");
    }
};
