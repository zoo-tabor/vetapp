-- Enhanced Vaccination System
-- Created: 2026-01-08

-- Enhance vaccination_plans table
ALTER TABLE vaccination_plans
ADD COLUMN vaccine_id INT NULL COMMENT 'Links to warehouse medicaments',
ADD COLUMN vaccination_interval_days INT NULL COMMENT 'User-defined interval (365, 730, 1095, etc.)',
ADD COLUMN requires_booster BOOLEAN DEFAULT FALSE COMMENT 'Primary vaccination needs booster',
ADD COLUMN booster_days INT NULL COMMENT 'Days until booster (typically 14)',
ADD COLUMN booster_plan_id INT NULL COMMENT 'Links to the booster vaccination plan',
ADD COLUMN animal_category VARCHAR(100) NULL COMMENT 'Šelmy Kočkovité, Psovité, Kopytníci, etc.',
ADD COLUMN month_planned TINYINT NULL COMMENT 'Month (1-12) for planning grid',
ADD COLUMN notification_sent_7days BOOLEAN DEFAULT FALSE,
ADD COLUMN notification_sent_1day BOOLEAN DEFAULT FALSE,
ADD FOREIGN KEY (vaccine_id) REFERENCES medicaments(id) ON DELETE SET NULL,
ADD FOREIGN KEY (booster_plan_id) REFERENCES vaccination_plans(id) ON DELETE SET NULL;

-- Add animal category to animals table
ALTER TABLE animals
ADD COLUMN animal_category VARCHAR(100) NULL COMMENT 'User-adjustable category (Šelmy Kočkovité, etc.)';

-- Create vaccination cost estimates table
CREATE TABLE IF NOT EXISTS vaccination_cost_estimates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    workplace_id INT,
    vaccine_id INT,
    animal_category VARCHAR(100),
    estimated_doses INT NOT NULL DEFAULT 0,
    cost_per_dose DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workplace_id) REFERENCES workplaces(id) ON DELETE CASCADE,
    FOREIGN KEY (vaccine_id) REFERENCES medicaments(id) ON DELETE CASCADE,
    INDEX idx_year_workplace (year, workplace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create 5-year vaccination schedule table
CREATE TABLE IF NOT EXISTS vaccination_schedule_5year (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    year INT NOT NULL,
    vaccine_id INT,
    planned_month TINYINT COMMENT '1-12 for month',
    vaccination_type VARCHAR(100) COMMENT 'RCP, DHPPi+L4, Covexin, etc.',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (vaccine_id) REFERENCES medicaments(id) ON DELETE SET NULL,
    INDEX idx_year (year),
    INDEX idx_animal_year (animal_id, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create vaccine type color mapping table (for UI color coding)
CREATE TABLE IF NOT EXISTS vaccine_type_colors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaccine_type VARCHAR(100) NOT NULL UNIQUE,
    color_hex VARCHAR(7) NOT NULL COMMENT 'e.g. #e74c3c',
    abbreviation VARCHAR(20) NOT NULL COMMENT 'Short name for grid display',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vaccine_type (vaccine_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default vaccine type colors (matching PDF scheme with readable text)
INSERT INTO vaccine_type_colors (vaccine_type, color_hex, abbreviation) VALUES
('RCP', '#e74c3c', 'RCP'),
('RCPCH', '#c0392b', 'RCPCH'),
('DHPPi+L4', '#3498db', 'DHPPi'),
('DHP+L4', '#2980b9', 'DHP'),
('L4', '#85c1e9', 'L4'),
('Covexin 10', '#27ae60', 'C10'),
('Covexin', '#27ae60', 'C'),
('Nobivac Rabies', '#f39c12', 'R')
ON DUPLICATE KEY UPDATE color_hex=VALUES(color_hex), abbreviation=VALUES(abbreviation);
