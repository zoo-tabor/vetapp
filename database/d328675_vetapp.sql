-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Počítač: md392.wedos.net:3306
-- Vygenerováno: Sob 03. led 2026, 14:00
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
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `next_check_date` varchar(50) DEFAULT NULL,
  `transfer_location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workplace_id` (`workplace_id`),
  KEY `current_enclosure_id` (`current_enclosure_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=10 ;

--
-- Vypisuji data pro tabulku `animals`
--

INSERT INTO `animals` (`id`, `workplace_id`, `name`, `species`, `breed`, `identifier`, `birth_date`, `gender`, `current_status`, `current_enclosure_id`, `notes`, `created_at`, `next_check_date`, `transfer_location`) VALUES
(2, 1, 'Max', 'Medvěd hnědý', NULL, 'ZOO-MH-1', '2025-12-01', 'male', 'active', 1, NULL, '2025-12-21 18:36:23', '2025-12-31', NULL),
(4, 1, 'John', 'Medvěd hnědý', NULL, 'ZOO-MH-2', NULL, 'male', 'active', 1, NULL, '2025-12-22 10:29:47', '12.01.2026', NULL),
(5, 2, 'Alice', 'Osel domácí', NULL, 'BAB-OD-1', '2017-04-17', 'female', 'active', 2, NULL, '2025-12-23 15:59:53', NULL, NULL),
(6, 1, 'Masotra', 'Fosa madagaskarská', NULL, 'ZOO-FM-001', '2012-05-27', 'male', 'active', NULL, NULL, '2025-12-28 12:58:30', 'aplikace antiparazitika', NULL),
(7, 3, 'Babeta', 'Pes domácí', NULL, 'LIP-PD-001', '2017-09-22', 'female', 'active', NULL, NULL, '2026-01-01 19:28:52', NULL, NULL),
(8, 1, 'Bubík', 'Medvěd hnědý', NULL, 'ZOO-MH-003', NULL, 'male', 'active', 4, NULL, '2026-01-02 12:23:20', NULL, NULL),
(9, 2, 'Husa domácí', 'Husa domácí', NULL, 'ZOO-HD-001', NULL, 'unknown', 'active', NULL, NULL, '2026-01-02 12:24:14', NULL, NULL);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=42 ;

--
-- Vypisuji data pro tabulku `animal_status_history`
--

INSERT INTO `animal_status_history` (`id`, `animal_id`, `status`, `status_date`, `from_workplace_id`, `to_workplace_id`, `from_enclosure_id`, `to_enclosure_id`, `reason`, `notes`, `created_by`, `created_at`) VALUES
(19, 2, 'received', '2025-12-01', NULL, 1, NULL, 1, NULL, NULL, 1, '2025-12-21 18:36:23'),
(20, 4, 'received', '0000-00-00', NULL, 1, NULL, NULL, NULL, NULL, 1, '2025-12-22 10:29:47'),
(21, 2, 'active', '2025-12-22', NULL, 1, NULL, 1, NULL, NULL, 1, '2025-12-22 14:14:27'),
(22, 2, 'transferred', '2025-12-22', NULL, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-22 14:45:03'),
(23, 2, 'transferred', '2025-12-22', NULL, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-22 14:45:16'),
(24, 2, 'active', '2025-12-22', NULL, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-22 14:45:23'),
(25, 4, 'active', '2025-12-23', NULL, 1, NULL, 1, NULL, NULL, 1, '2025-12-23 11:18:29'),
(26, 2, 'active', '2025-12-23', NULL, 1, NULL, 1, NULL, NULL, 1, '2025-12-23 11:18:48'),
(27, 4, 'active', '2025-12-23', NULL, 1, NULL, 1, NULL, NULL, 6, '2025-12-23 11:50:37'),
(28, 5, 'received', '2017-04-17', NULL, 2, NULL, NULL, NULL, NULL, 1, '2025-12-23 15:59:53'),
(29, 5, 'active', '2025-12-23', NULL, 2, NULL, NULL, NULL, NULL, 1, '2025-12-23 16:00:51'),
(30, 5, 'active', '2025-12-23', NULL, 2, NULL, 2, NULL, NULL, 1, '2025-12-23 16:01:09'),
(31, 5, 'active', '2025-12-23', NULL, 2, NULL, 2, NULL, NULL, 1, '2025-12-23 16:01:09'),
(32, 5, 'active', '2025-12-23', NULL, 2, NULL, 2, NULL, NULL, 1, '2025-12-23 16:01:09'),
(33, 6, 'received', '2012-05-27', NULL, 2, NULL, NULL, NULL, NULL, 1, '2025-12-28 12:58:30'),
(34, 6, 'transferred', '2025-12-30', NULL, 1, NULL, NULL, NULL, NULL, 1, '2025-12-30 12:22:02'),
(35, 6, 'active', '2025-12-30', NULL, 1, NULL, NULL, NULL, NULL, 1, '2025-12-30 12:22:15'),
(36, 7, 'received', '2017-09-22', NULL, 3, NULL, NULL, NULL, NULL, 1, '2026-01-01 19:28:52'),
(37, 8, 'received', '0000-00-00', NULL, 1, NULL, 4, NULL, NULL, 9, '2026-01-02 12:23:20'),
(38, 8, 'active', '2026-01-02', NULL, 1, NULL, 4, NULL, NULL, 9, '2026-01-02 12:23:38'),
(39, 9, 'received', '0000-00-00', NULL, 1, NULL, NULL, NULL, NULL, 9, '2026-01-02 12:24:14'),
(40, 9, 'transferred', '2026-01-02', NULL, 2, NULL, NULL, NULL, NULL, 9, '2026-01-02 12:24:19'),
(41, 9, 'active', '2026-01-02', NULL, 2, NULL, NULL, NULL, NULL, 9, '2026-01-02 12:24:30');

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

--
-- Vypisuji data pro tabulku `biochemistry_results`
--

INSERT INTO `biochemistry_results` (`id`, `test_id`, `parameter_name`, `value`, `unit`) VALUES
(1, 1, 'Triacylglyceridy', '0.14', 'mmol/L'),
(2, 1, 'Bilirubin celkový', '11.00', 'µmol/L'),
(3, 1, 'y-GT', '15.00', 'U/L'),
(4, 1, 'ALT', '121.78', 'U/L'),
(5, 1, 'AST', '243.98', 'U/L'),
(6, 1, 'CK (Kreatinkináza)', '154.77', 'U/L'),
(7, 1, 'Celková bílkovina', '54.00', 'g/L'),
(8, 1, 'Albumin', '28.80', 'g/L'),
(9, 1, 'Močovina', '4.30', 'mmol/L'),
(10, 1, 'Kreatinin', '116.30', 'µmol/L'),
(11, 2, 'Triacylglyceridy', '0.20', 'mmol/L'),
(12, 2, 'Močovina', '4.30', 'mmol/L'),
(13, 2, 'Kreatinin', '115.70', 'µmol/L'),
(14, 3, 'Amyláza', '569.00', 'U/l'),
(15, 3, 'Lipáza', '80.00', 'U/l'),
(16, 3, 'Glukóza', '4.00', 'mmol/l'),
(17, 3, 'Fruktozamin', '337.00', 'µmol/l'),
(18, 3, 'Triacylglyceridy', '0.00', 'mmol/l'),
(19, 3, 'Cholesterol', '6.00', 'mmol/l'),
(20, 3, 'Bilirubin celkový', '3.00', 'µmol/l'),
(21, 3, 'ALP', '33.00', 'U/l'),
(22, 3, 'GLDH', '3.00', 'U/l'),
(23, 3, 'y-GT', '4.00', 'U/l'),
(24, 3, 'ALT', '36.00', 'U/l'),
(25, 3, 'AST', '28.00', 'U/l'),
(26, 3, 'CK (Kreatinkináza)', '115.00', 'U/l'),
(27, 3, 'Celková bílkovina', '64.00', 'g/l'),
(28, 3, 'Albumin', '41.00', 'g/l'),
(29, 3, 'Globuliny', '22.00', 'g/l'),
(30, 3, 'A/G poměr', '1.00', 'ratio'),
(31, 3, 'SDMA', '0.00', 'µmol/l'),
(32, 3, 'Močovina', '5.00', 'mmol/l'),
(33, 3, 'Kreatinin', '76.00', 'µmol/l'),
(34, 3, 'Fosfor', '1.00', 'mmol/l'),
(35, 3, 'Hořčík', '0.00', 'mmol/l'),
(36, 3, 'Vápník', '2.00', 'mmol/l'),
(37, 3, 'Sodík', '147.00', 'mmol/l'),
(38, 3, 'Draslík', '4.00', 'mmol/l'),
(39, 3, 'Na-/K-kvocient', '31.00', '-'),
(40, 3, 'Železo', '24.00', 'µmol/l');

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

--
-- Vypisuji data pro tabulku `biochemistry_tests`
--

INSERT INTO `biochemistry_tests` (`id`, `animal_id`, `test_date`, `test_location`, `reference_source`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 5, '2025-11-27', 'VETUNI', 'Laboklin', 'Laboklin pro koně', 1, '2025-12-24 08:42:47', '2025-12-24 08:42:47'),
(2, 5, '2025-11-28', 'VETUNI', 'Laboklin', 'Laboklin referenční meze pro koně', 1, '2025-12-24 08:43:39', '2025-12-24 08:43:39'),
(3, 7, '2022-08-02', 'Laboklin', 'Laboklin', '', 1, '2026-01-01 19:43:19', '2026-01-01 19:43:19');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `dewormings`
--

INSERT INTO `dewormings` (`id`, `animal_id`, `workplace_id`, `deworming_date`, `medication`, `dosage`, `administration_route`, `reason`, `related_examination_id`, `notes`, `created_by`, `created_at`) VALUES
(1, 2, 1, '2025-12-21', 'Ivermectin', '3,4 ml', 's.c.', 'Odčervení', 1, NULL, 1, '2025-12-21 18:37:14');

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

--
-- Vypisuji data pro tabulku `enclosures`
--

INSERT INTO `enclosures` (`id`, `workplace_id`, `name`, `code`, `sample_type`, `notes`, `is_active`, `created_at`) VALUES
(1, 1, 'Medvěd hnědý', NULL, 'individual', '', 1, '2025-12-21 18:35:58'),
(2, 2, 'Osel domácí', NULL, 'individual', '', 1, '2025-12-23 16:00:59'),
(3, 1, 'Vodní svět', NULL, 'individual', '', 1, '2026-01-02 12:20:48'),
(4, 1, 'Medvěd hnědý 2', NULL, 'individual', '', 1, '2026-01-02 12:22:50');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=8 ;

--
-- Vypisuji data pro tabulku `examinations`
--

INSERT INTO `examinations` (`id`, `animal_id`, `workplace_id`, `enclosure_id`, `examination_date`, `sample_type`, `institution`, `parasite_found`, `finding_status`, `intensity`, `notes`, `next_check_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2025-12-21', 'Flotace', 'SVÚ Jihlava', 'Eimeria spp.', 'positive', '++', NULL, NULL, 1, '2025-12-21 18:36:44', '2025-12-22 10:30:55'),
(2, 4, 1, NULL, '2025-12-22', 'Flotace', 'SVÚ Praha', NULL, 'negative', 'neg.', NULL, NULL, 1, '2025-12-22 10:47:41', '2025-12-22 10:47:41'),
(3, 4, 1, NULL, '2025-12-22', 'Larvoskopie', 'SVÚ Praha', NULL, 'negative', 'neg.', NULL, NULL, 1, '2025-12-22 10:47:41', '2025-12-22 10:47:41'),
(4, 4, 1, NULL, '2025-12-22', 'Sedimentace', 'SVÚ Praha', NULL, 'negative', 'neg.', NULL, NULL, 1, '2025-12-22 10:47:41', '2025-12-22 10:47:41'),
(5, 2, 1, 1, '2025-12-22', 'Flotace', 'SVÚ Praha', NULL, 'negative', 'neg.', NULL, NULL, 1, '2025-12-22 10:47:41', '2025-12-25 11:22:49'),
(6, 2, 1, 1, '2025-12-22', 'Larvoskopie', 'SVÚ Praha', NULL, 'negative', 'neg.', NULL, NULL, 1, '2025-12-22 10:47:41', '2025-12-22 10:47:41'),
(7, 2, 1, 1, '2025-12-22', 'Sedimentace', 'SVÚ Praha', NULL, 'negative', 'neg.', NULL, NULL, 1, '2025-12-22 10:47:41', '2025-12-22 10:47:41');

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

--
-- Vypisuji data pro tabulku `hematology_results`
--

INSERT INTO `hematology_results` (`id`, `test_id`, `parameter_name`, `value`, `unit`) VALUES
(1, 1, 'Erytrocyty', '4.25', '10^12/L'),
(2, 1, 'Hematokrit', '0.26', '%'),
(3, 1, 'Hemoglobin', '89.00', 'g/L'),
(4, 1, 'MCHC', '344.00', 'g/L'),
(5, 1, 'MCH', '20.90', 'pg'),
(6, 1, 'MCV', '60.90', 'fL'),
(7, 1, 'Leukocyty', '5.46', '10^9/L'),
(8, 1, 'Lymfocyty', '20.00', '%'),
(9, 1, 'Monocyty', '3.00', '%'),
(10, 1, 'Eozinofily', '0.00', '%'),
(11, 1, 'Bazofily', '0.00', '%'),
(12, 1, 'Trombocyty', '301.00', '10^9/L'),
(13, 2, 'Erytrocyty', '6.20', '10^12/L'),
(14, 2, 'Hematokrit', '0.38', '%'),
(15, 2, 'Hemoglobin', '130.00', 'g/L'),
(16, 2, 'MCHC', '342.00', 'g/L'),
(17, 2, 'MCH', '21.00', 'pg'),
(18, 2, 'MCV', '61.30', 'fL'),
(19, 2, 'Leukocyty', '4.58', '10^9/L'),
(20, 2, 'Lymfocyty', '22.10', '%'),
(21, 2, 'Monocyty', '6.10', '%'),
(22, 2, 'Eozinofily', '0.70', '%'),
(23, 2, 'Bazofily', '0.20', '%'),
(24, 2, 'Trombocyty', '136.00', '10^9/L'),
(25, 3, 'Erytrocyty', '6.00', 'T/l'),
(26, 3, 'Hematokrit', '0.00', 'l/l'),
(27, 3, 'Hemoglobin', '161.00', 'g/l'),
(28, 3, 'Hypochromazie', '0.00', '-'),
(29, 3, 'Anizocytoza', '0.00', '-'),
(30, 3, 'retikulocyty', '27.00', '/nl'),
(31, 3, 'Leukocyty', '6.00', 'G/l'),
(32, 3, 'Neutrofily', '64.00', '%'),
(33, 3, 'Lymfocyty', '26.00', '%'),
(34, 3, 'Monocyty', '4.00', '%'),
(35, 3, 'Eozinofily', '6.00', '%'),
(36, 3, 'Bazofily', '0.00', '%'),
(37, 3, 'Tyčky', '0.00', '%'),
(38, 3, 'Neutrofily - absolutní', '4.00', 'G/l'),
(39, 3, 'Lymfocyty - absolutní', '1.00', 'G/l'),
(40, 3, 'Monocyty - absolutní', '0.00', 'G/l'),
(41, 3, 'Eozinofily - absolutní', '0.00', 'G/l'),
(42, 3, 'Bazofily - absolutní', '0.00', 'G/l'),
(43, 3, 'Tyčky - absolutní', '0.00', 'G/l'),
(44, 3, 'Trombocyty', '367.00', 'G/l');

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

--
-- Vypisuji data pro tabulku `hematology_tests`
--

INSERT INTO `hematology_tests` (`id`, `animal_id`, `test_date`, `test_location`, `reference_source`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 5, '2025-11-27', 'VETUNI', 'Laboklin', 'Laboklin pro koně', 1, '2025-12-25 11:42:31', '2025-12-25 11:42:31'),
(2, 5, '2025-11-28', 'VETUNI', 'Laboklin', '', 1, '2025-12-25 11:44:13', '2025-12-25 11:44:13'),
(3, 7, '2022-08-02', 'Laboklin', 'Laboklin', '', 1, '2026-01-01 19:43:19', '2026-01-01 19:43:19');

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

--
-- Vypisuji data pro tabulku `parasites`
--

INSERT INTO `parasites` (`id`, `scientific_name`, `common_name`, `category`, `is_active`) VALUES
(1, 'Ascaris lumbricoides', 'Škrkavka dětská', 'Hlístice', 1),
(2, 'Toxocara canis', 'Škrkavka psí', 'Hlístice', 1),
(3, 'Trichuris trichiura', 'Vlasovka', 'Hlístice', 1),
(4, 'Giardia lamblia', 'Giárdie', 'Prvoci', 1),
(5, 'Eimeria spp.', 'Kokcídie', 'Prvoci', 1),
(6, 'Strongyloides stercoralis', 'Háďátko', 'Hlístice', 1),
(7, 'Ancylostoma duodenale', 'Ankylostoma', 'Hlístice', 1),
(8, 'Taenia spp.', 'Tasemnice', 'Tasemnice', 1);

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

--
-- Vypisuji data pro tabulku `reference_ranges`
--

INSERT INTO `reference_ranges` (`id`, `test_type`, `parameter_name`, `species`, `source`, `min_value`, `max_value`, `unit`, `created_at`, `updated_at`) VALUES
(1, 'biochemistry', 'ALT', 'Medvěd hnědý', 'Idexx', 10.00, 50.00, 'U/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(2, 'biochemistry', 'AST', 'Medvěd hnědý', 'Idexx', 15.00, 60.00, 'U/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(3, 'biochemistry', 'ALP', 'Medvěd hnědý', 'Idexx', 20.00, 150.00, 'U/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(4, 'biochemistry', 'Kreatinin', 'Medvěd hnědý', 'Idexx', 60.00, 120.00, 'µmol/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(5, 'biochemistry', 'Urea', 'Medvěd hnědý', 'Idexx', 3.50, 8.50, 'mmol/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(6, 'biochemistry', 'Glukóza', 'Medvěd hnědý', 'Idexx', 4.00, 7.00, 'mmol/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(7, 'biochemistry', 'Celková bílkovina', 'Medvěd hnědý', 'Idexx', 55.00, 75.00, 'g/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(8, 'biochemistry', 'Albumin', 'Medvěd hnědý', 'Idexx', 25.00, 40.00, 'g/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(9, 'biochemistry', 'ALT', 'Medvěd hnědý', 'Laboklin', 12.00, 55.00, 'U/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(10, 'biochemistry', 'AST', 'Medvěd hnědý', 'Laboklin', 18.00, 65.00, 'U/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(11, 'biochemistry', 'ALP', 'Medvěd hnědý', 'Laboklin', 25.00, 160.00, 'U/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(12, 'biochemistry', 'Kreatinin', 'Medvěd hnědý', 'Laboklin', 55.00, 125.00, 'µmol/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(13, 'biochemistry', 'Urea', 'Medvěd hnědý', 'Laboklin', 3.00, 9.00, 'mmol/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(14, 'biochemistry', 'Glukóza', 'Medvěd hnědý', 'Laboklin', 3.80, 7.20, 'mmol/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(15, 'biochemistry', 'Celková bílkovina', 'Medvěd hnědý', 'Laboklin', 52.00, 78.00, 'g/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(16, 'biochemistry', 'Albumin', 'Medvěd hnědý', 'Laboklin', 23.00, 42.00, 'g/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(17, 'hematology', 'WBC', 'Medvěd hnědý', 'Idexx', 5.00, 15.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(18, 'hematology', 'RBC', 'Medvěd hnědý', 'Idexx', 5.00, 8.00, '10^12/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(19, 'hematology', 'HGB', 'Medvěd hnědý', 'Idexx', 120.00, 180.00, 'g/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(20, 'hematology', 'HCT', 'Medvěd hnědý', 'Idexx', 35.00, 55.00, '%', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(21, 'hematology', 'PLT', 'Medvěd hnědý', 'Idexx', 150.00, 500.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(22, 'hematology', 'Neutrofily', 'Medvěd hnědý', 'Idexx', 3.00, 11.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(23, 'hematology', 'Lymfocyty', 'Medvěd hnědý', 'Idexx', 1.00, 5.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(24, 'hematology', 'Monocyty', 'Medvěd hnědý', 'Idexx', 0.10, 1.50, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(25, 'hematology', 'WBC', 'Medvěd hnědý', 'Laboklin', 4.50, 16.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(26, 'hematology', 'RBC', 'Medvěd hnědý', 'Laboklin', 4.80, 8.20, '10^12/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(27, 'hematology', 'HGB', 'Medvěd hnědý', 'Laboklin', 115.00, 185.00, 'g/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(28, 'hematology', 'HCT', 'Medvěd hnědý', 'Laboklin', 33.00, 57.00, '%', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(29, 'hematology', 'PLT', 'Medvěd hnědý', 'Laboklin', 140.00, 520.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(30, 'hematology', 'Neutrofily', 'Medvěd hnědý', 'Laboklin', 2.80, 12.00, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(31, 'hematology', 'Lymfocyty', 'Medvěd hnědý', 'Laboklin', 0.90, 5.50, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(32, 'hematology', 'Monocyty', 'Medvěd hnědý', 'Laboklin', 0.08, 1.60, '10^9/L', '2025-12-23 15:29:31', '2025-12-23 15:29:31'),
(33, 'biochemistry', 'Glukóza', 'Osel domácí', 'Laboklin', 3.10, 5.40, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(34, 'biochemistry', 'Triacylglyceridy', 'Osel domácí', 'Laboklin', 0.00, 0.97, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(35, 'biochemistry', 'Cholesterol', 'Osel domácí', 'Laboklin', 1.80, 4.70, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(36, 'biochemistry', 'Bilirubin celkový', 'Osel domácí', 'Laboklin', 8.60, 59.90, 'µmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(37, 'biochemistry', 'ALP', 'Osel domácí', 'Laboklin', NULL, 352.00, 'U/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(38, 'biochemistry', 'GLDH', 'Osel domácí', 'Laboklin', NULL, 13.00, 'U/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(39, 'biochemistry', 'y-GT', 'Osel domácí', 'Laboklin', NULL, 44.00, 'U/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(40, 'biochemistry', 'AST', 'Osel domácí', 'Laboklin', NULL, 568.00, 'U/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(41, 'biochemistry', 'CK (Kreatinkináza)', 'Osel domácí', 'Laboklin', NULL, 452.00, 'U/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(42, 'biochemistry', 'Celková bílkovina', 'Osel domácí', 'Laboklin', 55.00, 75.00, 'g/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(43, 'biochemistry', 'Albumin', 'Osel domácí', 'Laboklin', 25.00, 54.00, 'g/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(44, 'biochemistry', 'Globuliny', 'Osel domácí', 'Laboklin', NULL, 51.00, 'g/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(45, 'biochemistry', 'A/G poměr', 'Osel domácí', 'Laboklin', 0.70, NULL, '', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(46, 'biochemistry', 'SDMA', 'Osel domácí', 'Laboklin', NULL, 0.75, 'µg/dL', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(47, 'biochemistry', 'Močovina', 'Osel domácí', 'Laboklin', 3.30, 6.70, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(48, 'biochemistry', 'Kreatinin', 'Osel domácí', 'Laboklin', 71.00, 159.00, 'µmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(49, 'biochemistry', 'Fosfor', 'Osel domácí', 'Laboklin', 0.70, 1.50, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(50, 'biochemistry', 'Hořčík', 'Osel domácí', 'Laboklin', 0.50, 0.90, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(51, 'biochemistry', 'Vápník', 'Osel domácí', 'Laboklin', 2.50, 3.40, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(52, 'biochemistry', 'Chloridy', 'Osel domácí', 'Laboklin', 95.00, 105.00, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(53, 'biochemistry', 'Sodík', 'Osel domácí', 'Laboklin', 125.00, 150.00, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(54, 'biochemistry', 'Draslík', 'Osel domácí', 'Laboklin', 2.80, 4.50, 'mmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(55, 'biochemistry', 'Železo', 'Osel domácí', 'Laboklin', 17.90, 64.50, 'µmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(56, 'biochemistry', 'T4', 'Osel domácí', 'Laboklin', 1.30, 4.10, 'nmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(57, 'biochemistry', 'FT4', 'Osel domácí', 'Laboklin', 9.00, 44.90, 'pmol/L', '2025-12-24 08:46:29', '2025-12-24 08:46:29'),
(58, 'hematology', 'Erytrocyty', 'Osel domácí', 'Laboklin', 6.00, 12.00, '10^12/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(59, 'hematology', 'Hematokrit', 'Osel domácí', 'Laboklin', 0.30, 0.50, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(60, 'hematology', 'Hemoglobin', 'Osel domácí', 'Laboklin', 110.00, 170.00, 'g/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(61, 'hematology', 'Hypochromazie', 'Osel domácí', 'Laboklin', 0.00, 0.00, '', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(62, 'hematology', 'Anizocytoza', 'Osel domácí', 'Laboklin', 0.00, 0.00, '', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(63, 'hematology', 'Leukocyty', 'Osel domácí', 'Laboklin', 5.00, 10.00, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(64, 'hematology', 'Neutrofily', 'Osel domácí', 'Laboklin', 45.00, 70.00, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(65, 'hematology', 'Lymfocyty', 'Osel domácí', 'Laboklin', 20.00, 45.00, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(66, 'hematology', 'Monocyty', 'Osel domácí', 'Laboklin', NULL, 5.00, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(67, 'hematology', 'Eozinofily', 'Osel domácí', 'Laboklin', NULL, 4.00, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(68, 'hematology', 'Bazofily', 'Osel domácí', 'Laboklin', NULL, 2.00, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(69, 'hematology', 'Tyčky', 'Osel domácí', 'Laboklin', NULL, 6.00, '%', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(70, 'hematology', 'Neutrofily - absolutní', 'Osel domácí', 'Laboklin', 3.00, 7.00, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(71, 'hematology', 'Lymfocyty - absolutní', 'Osel domácí', 'Laboklin', 1.50, 4.00, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(72, 'hematology', 'Monocyty - absolutní', 'Osel domácí', 'Laboklin', 0.04, 0.40, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(73, 'hematology', 'Eozinofily - absolutní', 'Osel domácí', 'Laboklin', 0.04, 3.00, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(74, 'hematology', 'Bazofily - absolutní', 'Osel domácí', 'Laboklin', NULL, 0.15, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(75, 'hematology', 'Tyčky - absolutní', 'Osel domácí', 'Laboklin', NULL, 0.60, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32'),
(76, 'hematology', 'Trombocyty', 'Osel domácí', 'Laboklin', 90.00, 300.00, '10^9/L', '2025-12-25 11:48:32', '2025-12-25 11:48:32');

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

--
-- Vypisuji data pro tabulku `urine_reference_ranges`
--

INSERT INTO `urine_reference_ranges` (`id`, `species`, `reference_source`, `parameter_name`, `min_value`, `max_value`, `reference_text`, `unit`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Pes', 'Synlab', 'Albumin - moč', 0.000, 30.000, NULL, 'mg/l', 'Referenční rozsah pro psy', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(2, 'Pes', 'Synlab', 'Kreatinin - moč', 2.500, 15.000, NULL, 'mmol/l', 'Referenční rozsah pro psy', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(3, 'Pes', 'Synlab', 'Albumin/Kreatinin - moč', 0.000, 30.000, NULL, 'g/mol', 'Referenční rozsah pro psy', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(4, 'Pes', 'Synlab', 'Bílkovina - moč', 0.000, 0.300, NULL, 'g/l', 'Referenční rozsah pro psy', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(5, 'Pes', 'Synlab', 'pH', 5.500, 7.500, NULL, '', 'Referenční rozsah pro psy', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(6, 'Pes', 'Synlab', 'Specifická hustota', 1.015, 1.045, NULL, 'kg/m3', 'Referenční rozsah pro psy', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(7, 'Kočka', 'Synlab', 'Albumin - moč', 0.000, 30.000, NULL, 'mg/l', 'Referenční rozsah pro kočky', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(8, 'Kočka', 'Synlab', 'Kreatinin - moč', 5.000, 20.000, NULL, 'mmol/l', 'Referenční rozsah pro kočky', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(9, 'Kočka', 'Synlab', 'Albumin/Kreatinin - moč', 0.000, 30.000, NULL, 'g/mol', 'Referenční rozsah pro kočky', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(10, 'Kočka', 'Synlab', 'Bílkovina - moč', 0.000, 0.300, NULL, 'g/l', 'Referenční rozsah pro kočky', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(11, 'Kočka', 'Synlab', 'pH', 6.000, 7.500, NULL, '', 'Referenční rozsah pro kočky', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(12, 'Kočka', 'Synlab', 'Specifická hustota', 1.020, 1.050, NULL, 'kg/m3', 'Referenční rozsah pro kočky', '2025-12-28 20:05:03', '2025-12-28 20:05:03'),
(25, 'Fosa madagaskarská', 'Synlab', 'Glukóza', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(26, 'Fosa madagaskarská', 'Synlab', 'Bílkovina', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(27, 'Fosa madagaskarská', 'Synlab', 'Bilirubin', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(28, 'Fosa madagaskarská', 'Synlab', 'Urobilinogen', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(29, 'Fosa madagaskarská', 'Synlab', 'Krev', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(30, 'Fosa madagaskarská', 'Synlab', 'Ketony', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(31, 'Fosa madagaskarská', 'Synlab', 'Nitrity', NULL, NULL, 'negativní', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:29:58'),
(32, 'Fosa madagaskarská', 'Synlab', 'Leukocyty', 0.000, 10.000, '', 'el/ul', NULL, '2025-12-30 12:29:58', '2025-12-30 15:48:32'),
(33, 'Fosa madagaskarská', 'Synlab', 'Bakterie', 0.000, 40.000, '', 'el/ul', NULL, '2025-12-30 12:29:58', '2025-12-30 15:48:32'),
(34, 'Fosa madagaskarská', 'Synlab', 'Drť', 0.000, 0.000, '', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 15:48:32'),
(35, 'Fosa madagaskarská', 'Synlab', 'Epitelie dlaždicovité', 0.000, 3.000, '', 'hpf', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(36, 'Fosa madagaskarská', 'Synlab', 'Krystaly kyseliny močové', 0.000, 0.000, '', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(37, 'Fosa madagaskarská', 'Synlab', 'Triplosféty (Struvity)', 0.000, 0.000, '', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(38, 'Fosa madagaskarská', 'Synlab', 'pH', 4.500, 6.000, '', '-', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(39, 'Fosa madagaskarská', 'Synlab', 'Specifická hustota', 1030.000, 1050.000, '', 'kg/m^3', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(40, 'Fosa madagaskarská', 'Synlab', 'Erytrocyty elementy', 0.000, 5.000, '', '10^6/l', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(41, 'Fosa madagaskarská', 'Synlab', 'Erytrocyty', 0.000, 0.000, '', 'el/ul', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(42, 'Fosa madagaskarská', 'Synlab', 'Leukocyty elementy', 11.000, 50.000, '', '10^6/l', NULL, '2025-12-30 12:29:58', '2025-12-30 12:32:41'),
(62, 'Fosa madagaskarská', 'Synlab', 'Hlen', 0.000, 0.000, '', '', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41'),
(63, 'Fosa madagaskarská', 'Synlab', 'Albumin - moč', 0.000, 2.000, '', 'mg/dl', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41'),
(64, 'Fosa madagaskarská', 'Synlab', 'Kreatinin - moč', NULL, NULL, 'negativní', 'mmol/l', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41'),
(65, 'Fosa madagaskarská', 'Synlab', 'Albumin/Kreatinin - moč', 0.000, 2.500, '', 'g/mol', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41'),
(66, 'Fosa madagaskarská', 'Synlab', 'Bílkovina - moč', 0.000, 0.100, '', 'g/l', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41'),
(67, 'Fosa madagaskarská', 'Synlab', 'Bílkovina/Kreatinin - moč', 0.000, 50.000, '', 'mg/mmol', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41'),
(71, 'Fosa madagaskarská', 'Synlab', 'Oxaláty', 0.000, 0.000, '', '-', NULL, '2025-12-30 12:32:41', '2025-12-30 12:32:41');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=19 ;

--
-- Vypisuji data pro tabulku `urine_results`
--

INSERT INTO `urine_results` (`id`, `test_id`, `parameter_name`, `value`, `unit`) VALUES
(1, 1, 'Glukóza', 'neg.', ''),
(2, 1, 'Bílkovina', '2', ''),
(3, 1, 'Bilirubin', 'neg.', ''),
(4, 1, 'Urobilinogen', 'neg.', ''),
(5, 1, 'pH', '7', ''),
(6, 1, 'Krev', 'neg.', ''),
(7, 1, 'Ketony', 'stopa', ''),
(8, 1, 'Nitrity', 'pozitivní', ''),
(9, 1, 'Specifická hustota', '1010', 'kg/m3'),
(10, 1, 'Erytrocyty elementy', '3', ''),
(11, 1, 'Leukocyty elementy', '5', ''),
(12, 1, 'Drť', '3', ''),
(13, 1, 'Hlen', '3', ''),
(14, 1, 'Albumin - moč', '0,63', 'mg/l'),
(15, 1, 'Kreatinin - moč', '20,37', 'mmol/l'),
(16, 1, 'Albumin/Kreatinin - moč', '0,31', 'g/mol'),
(17, 1, 'Bílkovina - moč', '0,27', 'g/l'),
(18, 1, 'Bílkovina/Kreatinin - moč', '13,3', 'index');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `urine_tests`
--

INSERT INTO `urine_tests` (`id`, `animal_id`, `test_date`, `test_location`, `reference_source`, `notes`, `created_by`, `created_at`) VALUES
(1, 6, '2025-12-28', '', 'Synlab', '', 1, '2025-12-28 14:00:29');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=12 ;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `created_at`, `is_active`, `password_reset_token`, `password_reset_expires`) VALUES
(1, 'admin', '$2y$10$1cqgQqd0c41nbMY1.DlV0..odilOvBUjJcOLUFCSLhgNoISNwkRby', 'Jan Štich', 'nakup.1@zootabor.eu', 'admin', '2025-12-18 00:06:01', 1, NULL, NULL),
(6, 'maksym.bilozerskyi', '$2y$10$OSMT5g9I8Sk2Jn468nA4vOdMWH6CCn3xpCP/.pX1pr.uzW7sG4ffS', 'Maksym Bilozerskyi', 'sekretariat@zootabor.eu', 'user_edit', '2025-12-23 10:17:58', 1, NULL, NULL),
(8, 'jan.stich', '$2y$10$1cqgQqd0c41nbMY1.DlV0..odilOvBUjJcOLUFCSLhgNoISNwkRby', 'Jan Štich', 'janstich98@seznam.cz', 'user_edit', '2025-12-30 16:30:49', 1, 'fee1f606d4e3ad499f55343390ea0aeabc7a2d02426b3328448ab541fbecca65', '2026-01-01 17:30:49'),
(9, 'evelina.nemravova', '$2y$10$rQ8VBKuZucFOZwaaDOzy2O4sTYCnxqoILfPOfO3lOsNtG4XFDbpY2', 'Evelína Nemravová', 'evelinka.nem@seznam.cz', 'user_edit', '2025-12-30 16:33:05', 1, NULL, NULL),
(10, 'test', '$2y$10$zFrimqIzj5JigrqAaoTLZeXrotwvzRtv4h6l7Lks1WSefIcad6Gse', 'Test', 'janstich98@seznam.cz', 'user_edit', '2025-12-31 18:13:42', 1, 'fa892bfa1d079eaa8afe9bbf90f81b361076fc9747e182b29579e7d59c7e2456', '2026-01-02 19:13:42'),
(11, 'evzen.korec', '$2y$10$EP5Az9cSVCzosTSZrsevAO8.JJvzSMUfh7TLF7ADgJjYWHKqpEGui', 'Evžen Korec', 'evzen.korec@email.cz', 'user_edit', '2026-01-01 12:06:11', 1, '81e481e5cf0ff3cd7af3b70cd29439437ff523050724899907dacfd0f534d7a1', '2026-01-03 13:06:11');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=34 ;

--
-- Vypisuji data pro tabulku `user_workplace_permissions`
--

INSERT INTO `user_workplace_permissions` (`id`, `user_id`, `workplace_id`, `can_view`, `can_edit`) VALUES
(6, 1, 1, 1, 1),
(7, 1, 2, 1, 1),
(8, 1, 3, 1, 1),
(9, 1, 4, 1, 1),
(14, 6, 1, 1, 1),
(15, 6, 2, 1, 1),
(16, 6, 3, 1, 1),
(17, 6, 4, 1, 0),
(26, 9, 1, 1, 1),
(27, 9, 2, 1, 1),
(28, 9, 3, 1, 1),
(29, 9, 4, 1, 1),
(30, 8, 1, 1, 1),
(31, 8, 2, 1, 1),
(32, 8, 3, 1, 1),
(33, 8, 4, 1, 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=5 ;

--
-- Vypisuji data pro tabulku `warehouse_consumption`
--

INSERT INTO `warehouse_consumption` (`id`, `item_id`, `weekly_consumption`, `desired_weeks_stock`, `notes`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 1, 151.00, 8, NULL, '2026-01-02 12:16:42', '2026-01-02 12:18:30', 9),
(4, 2, 97.00, 8, NULL, '2026-01-02 15:23:17', '2026-01-02 15:23:17', 1);

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

--
-- Vypisuji data pro tabulku `warehouse_items`
--

INSERT INTO `warehouse_items` (`id`, `item_code`, `workplace_id`, `name`, `category`, `unit`, `current_stock`, `min_stock_level`, `max_stock_level`, `supplier`, `storage_location`, `notes`, `created_at`, `updated_at`, `created_by`) VALUES
(1, '1', 1, 'Hovězí maso', 'food', 'kg', 1150.00, 100.00, 1200.00, 'Kostelec', 'Mrazicí box', NULL, '2026-01-02 09:40:18', '2026-01-02 16:01:35', 1),
(2, '2', 1, 'Vepřové maso', 'food', 'kg', 320.00, 100.00, 800.00, 'Kostelec', 'Mrazicí box', NULL, '2026-01-02 15:23:10', '2026-01-03 12:51:18', 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `warehouse_movements`
--

INSERT INTO `warehouse_movements` (`id`, `item_id`, `batch_id`, `movement_type`, `quantity`, `movement_date`, `reference_document`, `notes`, `created_at`, `created_by`) VALUES
(1, 1, NULL, 'adjustment', -100.00, '2026-01-02', NULL, 'Inventura', '2026-01-02 12:14:11', 9),
(2, 1, NULL, 'in', 500.00, '2026-01-02', NULL, NULL, '2026-01-02 12:25:52', 9);

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
-- Vypisuji data pro tabulku `workplaces`
--

INSERT INTO `workplaces` (`id`, `name`, `code`, `description`, `is_active`, `created_at`) VALUES
(1, 'ZOO Tábor', 'ZOO', 'Hlavní pracoviště - Zoologická zahrada Praha', 1, '2025-12-18 00:06:01'),
(2, 'Babice', 'BAB', 'První deponované pracoviště', 1, '2025-12-18 00:06:01'),
(3, 'Lipence', 'LIP', 'Druhé deponované pracoviště', 1, '2025-12-18 00:06:01'),
(4, 'Deponace', 'DEP', 'Zvířata darovaná nebo zapůjčená mimo organizaci', 1, '2025-12-18 21:16:39');

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
