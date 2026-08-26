<?php
/**
 * Nepovinné označení vzorku u vyšetření (např. "vz. 1" / "vz. 2").
 *
 * Velké výběhy (např. vodní svět) mají někdy více vzorků na stejné datum;
 * tenhle štítek je od sebe odliší v historii i tisku. Je NULLABLE – u drtivé
 * většiny záznamů zůstává prázdný a nikde se pak nezobrazuje.
 */
return function(PDO $pdo) {
    $pdo->exec("ALTER TABLE `examinations` ADD COLUMN `sample_label` varchar(20) DEFAULT NULL");
};
