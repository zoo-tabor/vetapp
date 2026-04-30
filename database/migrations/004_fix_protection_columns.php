<?php
return function(PDO $pdo) {
    $pdo->exec("
        ALTER TABLE `animals`
          DROP COLUMN `law_critically_endangered`,
          DROP COLUMN `law_strongly_endangered`,
          DROP COLUMN `law_endangered`,
          ADD COLUMN `law_114_1992` VARCHAR(50) DEFAULT NULL,
          MODIFY COLUMN `commercial_exception` VARCHAR(10) DEFAULT NULL,
          MODIFY COLUMN `exception_granted` VARCHAR(50) DEFAULT NULL,
          MODIFY COLUMN `deviation_set` VARCHAR(50) DEFAULT NULL
    ");
};
