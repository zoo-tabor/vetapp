<?php
/**
 * Add weight column to animals table
 */
return function(PDO $pdo) {
    $pdo->exec("
        ALTER TABLE `animals`
        ADD COLUMN `weight` DECIMAL(8,2) DEFAULT NULL COMMENT 'Hmotnost v kg'
        AFTER `gender`
    ");
};
