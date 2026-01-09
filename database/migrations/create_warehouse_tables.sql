-- Migration: Create warehouse/inventory management tables
-- This system manages food ("Krmiva") and medicaments ("Léčiva") inventory

-- Warehouse items (food and medicaments)
CREATE TABLE IF NOT EXISTS `warehouse_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `workplace_id` INT(11) NULL COMMENT 'NULL = Central warehouse (Centrální sklad)',
    `name` VARCHAR(200) NOT NULL,
    `category` ENUM('food', 'medicament') NOT NULL COMMENT 'food = Krmiva, medicament = Léčiva',
    `unit` VARCHAR(50) NOT NULL COMMENT 'kg, L, ks (pieces), balení (packages), etc.',
    `current_stock` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `min_stock_level` DECIMAL(10,2) NULL COMMENT 'Minimum stock level for low stock alerts',
    `max_stock_level` DECIMAL(10,2) NULL COMMENT 'Maximum desired stock level',
    `supplier` VARCHAR(200) NULL COMMENT 'Supplier name',
    `storage_location` VARCHAR(200) NULL COMMENT 'Where stored in warehouse (shelf, room, etc.)',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    PRIMARY KEY (`id`),
    KEY `workplace_id` (`workplace_id`),
    KEY `category` (`category`),
    FOREIGN KEY (`workplace_id`) REFERENCES `workplaces`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item batches with expiration dates
-- One item can have multiple batches with different expiration dates
CREATE TABLE IF NOT EXISTS `warehouse_batches` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) NOT NULL,
    `batch_number` VARCHAR(100) NULL COMMENT 'Batch/lot number for traceability',
    `quantity` DECIMAL(10,2) NOT NULL,
    `expiration_date` DATE NULL,
    `received_date` DATE NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    PRIMARY KEY (`id`),
    KEY `item_id` (`item_id`),
    KEY `expiration_date` (`expiration_date`),
    FOREIGN KEY (`item_id`) REFERENCES `warehouse_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock movements (in/out/adjustment)
CREATE TABLE IF NOT EXISTS `warehouse_movements` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) NOT NULL,
    `batch_id` INT(11) NULL COMMENT 'Which batch this movement affects',
    `movement_type` ENUM('in', 'out', 'adjustment') NOT NULL COMMENT 'in = příjem, out = výdej, adjustment = inventura/oprava',
    `quantity` DECIMAL(10,2) NOT NULL COMMENT 'Positive for in, negative for out',
    `movement_date` DATE NOT NULL,
    `reference_document` VARCHAR(200) NULL COMMENT 'Invoice number, delivery note, etc.',
    `notes` TEXT NULL COMMENT 'Reason for movement (e.g., Weekly feeding, Delivery from supplier)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    PRIMARY KEY (`id`),
    KEY `item_id` (`item_id`),
    KEY `batch_id` (`batch_id`),
    KEY `movement_date` (`movement_date`),
    KEY `movement_type` (`movement_type`),
    FOREIGN KEY (`item_id`) REFERENCES `warehouse_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`batch_id`) REFERENCES `warehouse_batches`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Weekly consumption rates (manual entry)
CREATE TABLE IF NOT EXISTS `warehouse_consumption` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) NOT NULL,
    `weekly_consumption` DECIMAL(10,2) NOT NULL COMMENT 'Manually entered estimated weekly usage',
    `desired_weeks_stock` INT(11) NOT NULL DEFAULT 4 COMMENT 'How many weeks of stock to maintain',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `item_id` (`item_id`),
    FOREIGN KEY (`item_id`) REFERENCES `warehouse_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for performance
CREATE INDEX idx_warehouse_items_workplace_category ON warehouse_items(workplace_id, category);
CREATE INDEX idx_warehouse_movements_date_type ON warehouse_movements(movement_date, movement_type);
