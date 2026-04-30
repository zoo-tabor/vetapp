<?php
return function(PDO $pdo) {
    $pdo->exec("
        ALTER TABLE `animals`
          ADD COLUMN `registration_number` VARCHAR(100) DEFAULT NULL,
          ADD COLUMN `cites_category` VARCHAR(5) DEFAULT NULL,
          ADD COLUMN `eu_regulation` VARCHAR(5) DEFAULT NULL,
          ADD COLUMN `law_critically_endangered` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `law_strongly_endangered` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `law_endangered` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `commercial_exception` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `requires_ku_registration` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `ku_registration_done` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `exception_required` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `exception_granted` ENUM('UDĚLENA','NAHRAZENA') DEFAULT NULL,
          ADD COLUMN `deviation_required` TINYINT(1) NOT NULL DEFAULT 0,
          ADD COLUMN `deviation_set` ENUM('UDĚLENA','NAHRAZENA') DEFAULT NULL
    ");
};
