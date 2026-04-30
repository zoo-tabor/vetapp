<?php
return function(PDO $pdo) {
    // commercial_exception was previously a TINYINT boolean (0/1).
    // After migration 004 changed it to VARCHAR, any rows that had boolean
    // values now show literal '0' or '1'. Reset them to NULL.
    $pdo->exec("
        UPDATE animals
        SET commercial_exception = NULL
        WHERE commercial_exception IN ('0', '1')
    ");
};
