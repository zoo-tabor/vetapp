<?php
/**
 * Create ZooTrack tables: institutions, species, holdings
 * All tables use zootrack_ prefix to avoid conflicts with other VetApp tables.
 */
return function(PDO $pdo) {

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `zootrack_institutions` (
            `id`                  VARCHAR(20)   NOT NULL                         COMMENT 'Unikátní ID ve formátu B02-0057',
            `country`             VARCHAR(100)  NOT NULL                         COMMENT 'Stát anglicky',
            `subdivision`         VARCHAR(100)  DEFAULT NULL                     COMMENT 'Kraj / spolková země',
            `city`                VARCHAR(100)  DEFAULT NULL,
            `institution`         VARCHAR(255)  NOT NULL                         COMMENT 'Název instituce',
            `institution_aliases` TEXT          DEFAULT NULL                     COMMENT 'Alternativní názvy oddělené ;',
            `institution_type`    VARCHAR(100)  DEFAULT NULL,
            `website`             VARCHAR(500)  DEFAULT NULL,
            `eaza_status`         VARCHAR(100)  DEFAULT NULL                     COMMENT 'EAZA - Full member | EAZA - Candidate | non-EAZA',
            `other_memberships`   TEXT          DEFAULT NULL,
            `kea_verdict`         VARCHAR(100)  DEFAULT NULL                     COMMENT 'Výsledek kea průzkumu',
            `kea_confidence`      VARCHAR(50)   DEFAULT NULL,
            `kea_evidence`        TEXT          DEFAULT NULL,
            `notes`               TEXT          DEFAULT NULL,
            `created_at`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_zt_country`  (`country`),
            INDEX `idx_zt_eaza`     (`eaza_status`(30)),
            INDEX `idx_zt_city`     (`city`),
            FULLTEXT INDEX `ft_zt_inst` (`institution`, `institution_aliases`, `city`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='ZooTrack – seznam 2 240 evropských zoo institucí'
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `zootrack_species` (
            `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `scientific_name` VARCHAR(255)  NOT NULL                             COMMENT 'Vědecký název (unikátní)',
            `common_name_cs`  VARCHAR(255)  DEFAULT NULL,
            `common_name_en`  VARCHAR(255)  DEFAULT NULL,
            `common_name_de`  VARCHAR(255)  DEFAULT NULL,
            `taxon_class`     VARCHAR(100)  DEFAULT NULL                         COMMENT 'Aves | Mammalia | ...',
            `taxon_order`     VARCHAR(100)  DEFAULT NULL,
            `taxon_family`    VARCHAR(100)  DEFAULT NULL,
            `iucn_status`     VARCHAR(10)   DEFAULT NULL                         COMMENT 'EX | EW | CR | EN | VU | NT | LC',
            `cites_appendix`  VARCHAR(10)   DEFAULT NULL                         COMMENT 'I | II | III | I/II | I/NC | II/NC | III/NC',
            `eep`             TINYINT(1)    NOT NULL DEFAULT 0                   COMMENT '1 = EEP/ESB druh',
            `notes`           TEXT          DEFAULT NULL,
            `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_zt_scientific` (`scientific_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='ZooTrack – sledované druhy živočichů'
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `zootrack_holdings` (
            `id`                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `institution_id`      VARCHAR(20)   NOT NULL,
            `species_id`          INT UNSIGNED  NOT NULL,
            `holding_verdict`     VARCHAR(50)   NOT NULL DEFAULT 'unknown'       COMMENT 'confirmed_current | likely_current | historical | not_current | unclear | unknown',
            `sex_ratio`           VARCHAR(50)   DEFAULT NULL                     COMMENT 'samci.samice.neurčení, např. 2.3.0',
            `count_note`          VARCHAR(255)  DEFAULT NULL,
            `breeding_verdict`    VARCHAR(50)   NOT NULL DEFAULT 'unknown'       COMMENT 'confirmed | likely | no_evidence | historical | unknown',
            `last_offspring_year` VARCHAR(10)   DEFAULT NULL,
            `confidence`          VARCHAR(20)   NOT NULL DEFAULT 'medium'        COMMENT 'high | medium | low',
            `source_type`         VARCHAR(50)   DEFAULT NULL                     COMMENT 'website | facebook | instagram | zims | zootierliste | zoochat | visitor_report | direct_contact | other',
            `source_url`          VARCHAR(2000) DEFAULT NULL,
            `evidence_date`       DATE          DEFAULT NULL,
            `evidence_summary`    TEXT          DEFAULT NULL,
            `notes`               TEXT          DEFAULT NULL,
            `created_at`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_zt_inst_species` (`institution_id`, `species_id`),
            INDEX `idx_zt_h_inst`    (`institution_id`),
            INDEX `idx_zt_h_species` (`species_id`),
            INDEX `idx_zt_h_verdict` (`holding_verdict`),
            INDEX `idx_zt_h_breed`   (`breeding_verdict`),
            CONSTRAINT `fk_zt_h_inst`    FOREIGN KEY (`institution_id`) REFERENCES `zootrack_institutions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_zt_h_species` FOREIGN KEY (`species_id`)     REFERENCES `zootrack_species`      (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='ZooTrack – záznamy o druzích v institucích'
    ");

};
