<?php
return function(PDO $pdo) {
    $pdo->exec("ALTER TABLE warehouse_items ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER notes");
};
