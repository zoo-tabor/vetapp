-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Počítač: md392.wedos.net:3306
-- Vygenerováno: Pát 09. led 2026, 09:38
-- Verze serveru: 10.4.34-MariaDB-log
-- Verze PHP: 5.4.23

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `d328675_vetapp`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `animals`
--

CREATE TABLE IF NOT EXISTS `animals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workplace_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `species` varchar(100) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `identifier` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','unknown') DEFAULT 'unknown',
  `current_status` enum('active','transferred','deceased','removed') DEFAULT 'active',
  `current_enclosure_id` int(11) DEFAULT NULL,
  `assigned_user` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `next_check_date` varchar(50) DEFAULT NULL,
  `transfer_location` varchar(100) DEFAULT NULL,
  `animal_category` varchar(100) DEFAULT NULL COMMENT 'User-adjustable category (Šelmy Kočkovité, etc.)',
  PRIMARY KEY (`id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `current_enclosure_id` (`current_enclosure_id`),
  KEY `idx_assigned_user` (`assigned_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `animal_status_history`
--

CREATE TABLE IF NOT EXISTS `animal_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `status` enum('born','received','active','transferred','deceased','removed') NOT NULL,
  `status_date` date NOT NULL,
  `from_workplace_id` int(11) DEFAULT NULL,
  `to_workplace_id` int(11) DEFAULT NULL,
  `from_enclosure_id` int(11) DEFAULT NULL,
  `to_enclosure_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `audit_log`
--

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `biochemistry_results`
--

CREATE TABLE IF NOT EXISTS `biochemistry_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `biochemistry_tests`
--

CREATE TABLE IF NOT EXISTS `biochemistry_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `test_location` varchar(200) DEFAULT NULL,
  `reference_source` enum('Idexx','Laboklin','Synlab','ZIMS') DEFAULT 'Idexx',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `dewormings`
--

CREATE TABLE IF NOT EXISTS `dewormings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `workplace_id` int(11) NOT NULL,
  `deworming_date` date NOT NULL,
  `medication` varchar(100) DEFAULT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `administration_route` varchar(10) DEFAULT NULL COMMENT 'p.o., s.c., i.m.',
  `reason` text DEFAULT NULL,
  `related_examination_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `enclosures`
--

CREATE TABLE IF NOT EXISTS `enclosures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workplace_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `sample_type` enum('individual','mixed') DEFAULT 'individual',
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `workplace_id` (`workplace_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `examinations`
--

CREATE TABLE IF NOT EXISTS `examinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `workplace_id` int(11) NOT NULL,
  `enclosure_id` int(11) DEFAULT NULL,
  `examination_date` date NOT NULL,
  `sample_type` varchar(50) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `parasite_found` varchar(255) DEFAULT NULL,
  `finding_status` enum('negative','positive') NOT NULL,
  `intensity` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `next_check_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `enclosure_id` (`enclosure_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `examination_parasites`
--

CREATE TABLE IF NOT EXISTS `examination_parasites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `examination_id` int(11) NOT NULL,
  `parasite_id` int(11) NOT NULL,
  `intensity` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `examination_id` (`examination_id`),
  KEY `parasite_id` (`parasite_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `hematology_results`
--

CREATE TABLE IF NOT EXISTS `hematology_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `hematology_tests`
--

CREATE TABLE IF NOT EXISTS `hematology_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `test_location` varchar(200) DEFAULT NULL,
  `reference_source` enum('Idexx','Laboklin','Synlab','ZIMS') DEFAULT 'Idexx',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `parasites`
--

CREATE TABLE IF NOT EXISTS `parasites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scientific_name` varchar(100) NOT NULL,
  `common_name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `reference_ranges`
--

CREATE TABLE IF NOT EXISTS `reference_ranges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_type` enum('biochemistry','hematology') NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `species` varchar(100) NOT NULL,
  `source` enum('Idexx','Laboklin','Synlab','ZIMS') NOT NULL,
  `min_value` decimal(10,2) DEFAULT NULL,
  `max_value` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reference` (`test_type`,`parameter_name`,`species`,`source`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `reference_sources`
--

CREATE TABLE IF NOT EXISTS `reference_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_name` (`source_name`),
  KEY `idx_source_name` (`source_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `scheduled_checks`
--

CREATE TABLE IF NOT EXISTS `scheduled_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `workplace_id` int(11) NOT NULL,
  `scheduled_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `related_examination_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `urine_reference_ranges`
--

CREATE TABLE IF NOT EXISTS `urine_reference_ranges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `species` varchar(100) NOT NULL,
  `reference_source` enum('Idexx','Laboklin','Synlab','ZIMS') NOT NULL DEFAULT 'Synlab',
  `parameter_name` varchar(100) NOT NULL,
  `min_value` decimal(10,3) DEFAULT NULL,
  `max_value` decimal(10,3) DEFAULT NULL,
  `reference_text` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reference` (`species`,`reference_source`,`parameter_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=98 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `urine_results`
--

CREATE TABLE IF NOT EXISTS `urine_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `parameter_name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_urine_results_test` (`test_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `urine_tests`
--

CREATE TABLE IF NOT EXISTS `urine_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `test_location` varchar(255) DEFAULT NULL,
  `reference_source` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_urine_tests_animal` (`animal_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user_read','user_edit') NOT NULL DEFAULT 'user_read',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `user_workplace_permissions`
--

CREATE TABLE IF NOT EXISTS `user_workplace_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `workplace_id` int(11) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission` (`user_id`,`workplace_id`),
  KEY `user_id` (`user_id`),
  KEY `workplace_id` (`workplace_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `vaccination_cost_estimates`
--

CREATE TABLE IF NOT EXISTS `vaccination_cost_estimates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `workplace_id` int(11) DEFAULT NULL,
  `vaccine_id` int(11) DEFAULT NULL COMMENT 'Links to warehouse_items (where category = Vakcíny)',
  `animal_category` varchar(100) DEFAULT NULL,
  `estimated_doses` int(11) NOT NULL DEFAULT 0,
  `cost_per_dose` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `idx_year_workplace` (`year`,`workplace_id`),
  KEY `idx_vaccine_id` (`vaccine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `vaccination_history`
--

CREATE TABLE IF NOT EXISTS `vaccination_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `vaccination_date` date NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `administered_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `administered_by` (`administered_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_animal_id` (`animal_id`),
  KEY `idx_vaccination_date` (`vaccination_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `vaccination_plans`
--

CREATE TABLE IF NOT EXISTS `vaccination_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `vaccine_id` int(11) DEFAULT NULL COMMENT 'Links to warehouse_items (where category = Vakcíny)',
  `vaccine_name` varchar(255) NOT NULL,
  `planned_date` date NOT NULL,
  `month_planned` tinyint(4) DEFAULT NULL COMMENT 'Month (1-12) for planning grid',
  `vaccination_interval_days` int(11) DEFAULT NULL COMMENT 'User-defined interval (365, 730, 1095, etc.)',
  `requires_booster` tinyint(1) DEFAULT 0 COMMENT 'Primary vaccination needs booster',
  `booster_days` int(11) DEFAULT NULL COMMENT 'Days until booster (typically 14)',
  `booster_plan_id` int(11) DEFAULT NULL COMMENT 'Links to the booster vaccination plan',
  `animal_category` varchar(100) DEFAULT NULL COMMENT 'Šelmy Kočkovité, Psovité, Kopytníci, etc.',
  `status` enum('planned','completed','overdue','cancelled') DEFAULT 'planned',
  `administered_date` date DEFAULT NULL,
  `administered_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `notification_sent_7days` tinyint(1) DEFAULT 0,
  `notification_sent_1day` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `administered_by` (`administered_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_animal_id` (`animal_id`),
  KEY `idx_planned_date` (`planned_date`),
  KEY `idx_status` (`status`),
  KEY `idx_vaccine_id` (`vaccine_id`),
  KEY `idx_animal_category` (`animal_category`),
  KEY `fk_vaccination_plans_booster` (`booster_plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `vaccination_schedule_5year`
--

CREATE TABLE IF NOT EXISTS `vaccination_schedule_5year` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `animal_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `vaccine_id` int(11) DEFAULT NULL COMMENT 'Links to warehouse_items (where category = Vakcíny)',
  `planned_month` tinyint(4) DEFAULT NULL COMMENT '1-12 for month',
  `vaccination_type` varchar(100) DEFAULT NULL COMMENT 'RCP, DHPPi+L4, Covexin, etc.',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_year` (`year`),
  KEY `idx_animal_year` (`animal_id`,`year`),
  KEY `idx_vaccine_id` (`vaccine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `vaccine_templates`
--

CREATE TABLE IF NOT EXISTS `vaccine_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `frequency_days` int(11) DEFAULT NULL COMMENT 'Recommended frequency in days',
  `species` varchar(100) DEFAULT NULL COMMENT 'Recommended for species',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vaccine_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `vaccine_type_colors`
--

CREATE TABLE IF NOT EXISTS `vaccine_type_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vaccine_type` varchar(100) NOT NULL,
  `color_hex` varchar(7) NOT NULL COMMENT 'e.g. #e74c3c',
  `abbreviation` varchar(20) NOT NULL COMMENT 'Short name for grid display',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vaccine_type` (`vaccine_type`),
  UNIQUE KEY `unique_vaccine_type` (`vaccine_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `warehouse_batches`
--

CREATE TABLE IF NOT EXISTS `warehouse_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL COMMENT 'Batch/lot number for traceability',
  `quantity` decimal(10,2) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `received_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `expiration_date` (`expiration_date`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `warehouse_consumption`
--

CREATE TABLE IF NOT EXISTS `warehouse_consumption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `weekly_consumption` decimal(10,2) NOT NULL COMMENT 'Manually entered estimated weekly usage',
  `desired_weeks_stock` int(11) NOT NULL DEFAULT 4 COMMENT 'How many weeks of stock to maintain',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `warehouse_items`
--

CREATE TABLE IF NOT EXISTS `warehouse_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) DEFAULT NULL COMMENT 'User-assignable item code/number (e.g., 1, 2, 3...)',
  `workplace_id` int(11) DEFAULT NULL COMMENT 'NULL = Central warehouse (Centrální sklad)',
  `name` varchar(200) NOT NULL,
  `category` enum('food','medicament') NOT NULL COMMENT 'food = Krmiva, medicament = Léčiva',
  `unit` varchar(50) NOT NULL COMMENT 'kg, L, ks (pieces), balení (packages), etc.',
  `current_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock_level` decimal(10,2) DEFAULT NULL COMMENT 'Minimum stock level for low stock alerts',
  `max_stock_level` decimal(10,2) DEFAULT NULL COMMENT 'Maximum desired stock level',
  `supplier` varchar(200) DEFAULT NULL COMMENT 'Supplier name',
  `storage_location` varchar(200) DEFAULT NULL COMMENT 'Where stored in warehouse (shelf, room, etc.)',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `category` (`category`),
  KEY `created_by` (`created_by`),
  KEY `idx_warehouse_items_workplace_category` (`workplace_id`,`category`),
  KEY `idx_warehouse_items_item_code` (`item_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `warehouse_movements`
--

CREATE TABLE IF NOT EXISTS `warehouse_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL COMMENT 'Which batch this movement affects',
  `movement_type` enum('in','out','adjustment') NOT NULL COMMENT 'in = příjem, out = výdej, adjustment = inventura/oprava',
  `quantity` decimal(10,2) NOT NULL COMMENT 'Positive for in, negative for out',
  `movement_date` date NOT NULL,
  `reference_document` varchar(200) DEFAULT NULL COMMENT 'Invoice number, delivery note, etc.',
  `notes` text DEFAULT NULL COMMENT 'Reason for movement (e.g., Weekly feeding, Delivery from supplier)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  KEY `movement_date` (`movement_date`),
  KEY `movement_type` (`movement_type`),
  KEY `created_by` (`created_by`),
  KEY `idx_warehouse_movements_date_type` (`movement_date`,`movement_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `workplaces`
--

CREATE TABLE IF NOT EXISTS `workplaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=5 ;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `animals`
--
ALTER TABLE `animals`
  ADD CONSTRAINT `fk_animal_enclosure` FOREIGN KEY (`current_enclosure_id`) REFERENCES `enclosures` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_animal_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`);

--
-- Omezení pro tabulku `animal_status_history`
--
ALTER TABLE `animal_status_history`
  ADD CONSTRAINT `fk_ash_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ash_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `biochemistry_results`
--
ALTER TABLE `biochemistry_results`
  ADD CONSTRAINT `biochemistry_results_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `biochemistry_tests` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `biochemistry_tests`
--
ALTER TABLE `biochemistry_tests`
  ADD CONSTRAINT `biochemistry_tests_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `biochemistry_tests_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `dewormings`
--
ALTER TABLE `dewormings`
  ADD CONSTRAINT `fk_dew_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dew_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_dew_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`);

--
-- Omezení pro tabulku `enclosures`
--
ALTER TABLE `enclosures`
  ADD CONSTRAINT `fk_enclosure_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `examinations`
--
ALTER TABLE `examinations`
  ADD CONSTRAINT `fk_exam_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_enclosure` FOREIGN KEY (`enclosure_id`) REFERENCES `enclosures` (`id`),
  ADD CONSTRAINT `fk_exam_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_exam_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`);

--
-- Omezení pro tabulku `examination_parasites`
--
ALTER TABLE `examination_parasites`
  ADD CONSTRAINT `fk_ep_examination` FOREIGN KEY (`examination_id`) REFERENCES `examinations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ep_parasite` FOREIGN KEY (`parasite_id`) REFERENCES `parasites` (`id`);

--
-- Omezení pro tabulku `hematology_results`
--
ALTER TABLE `hematology_results`
  ADD CONSTRAINT `hematology_results_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `hematology_tests` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `hematology_tests`
--
ALTER TABLE `hematology_tests`
  ADD CONSTRAINT `hematology_tests_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hematology_tests_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `scheduled_checks`
--
ALTER TABLE `scheduled_checks`
  ADD CONSTRAINT `fk_sc_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sc_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_sc_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`);

--
-- Omezení pro tabulku `urine_results`
--
ALTER TABLE `urine_results`
  ADD CONSTRAINT `urine_results_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `urine_tests` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `urine_tests`
--
ALTER TABLE `urine_tests`
  ADD CONSTRAINT `urine_tests_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `urine_tests_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `user_workplace_permissions`
--
ALTER TABLE `user_workplace_permissions`
  ADD CONSTRAINT `fk_uwp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_uwp_workplace` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `vaccination_cost_estimates`
--
ALTER TABLE `vaccination_cost_estimates`
  ADD CONSTRAINT `vaccination_cost_estimates_ibfk_1` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `vaccination_history`
--
ALTER TABLE `vaccination_history`
  ADD CONSTRAINT `vaccination_history_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccination_history_ibfk_2` FOREIGN KEY (`administered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vaccination_history_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `vaccination_plans`
--
ALTER TABLE `vaccination_plans`
  ADD CONSTRAINT `fk_vaccination_plans_booster` FOREIGN KEY (`booster_plan_id`) REFERENCES `vaccination_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vaccination_plans_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccination_plans_ibfk_2` FOREIGN KEY (`administered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vaccination_plans_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `vaccination_schedule_5year`
--
ALTER TABLE `vaccination_schedule_5year`
  ADD CONSTRAINT `vaccination_schedule_5year_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `warehouse_batches`
--
ALTER TABLE `warehouse_batches`
  ADD CONSTRAINT `warehouse_batches_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `warehouse_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_batches_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `warehouse_consumption`
--
ALTER TABLE `warehouse_consumption`
  ADD CONSTRAINT `warehouse_consumption_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `warehouse_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_consumption_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `warehouse_items`
--
ALTER TABLE `warehouse_items`
  ADD CONSTRAINT `warehouse_items_ibfk_1` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_items_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `warehouse_movements`
--
ALTER TABLE `warehouse_movements`
  ADD CONSTRAINT `warehouse_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `warehouse_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_movements_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `warehouse_batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warehouse_movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
