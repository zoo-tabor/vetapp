-- Complete Vaccination System
-- Created: 2026-01-08
-- This script creates all vaccination-related tables with enhanced features

-- ============================================
-- ADD ANIMAL CATEGORY TO ANIMALS TABLE FIRST
-- ============================================
-- Add column if it doesn't exist
-- If this fails because column already exists, that's okay - continue with the rest
ALTER TABLE animals
ADD COLUMN animal_category VARCHAR(100) NULL COMMENT 'User-adjustable category (Šelmy Kočkovité, etc.)';

-- ============================================
-- VACCINATION PLANS TABLE (Enhanced)
-- ============================================
CREATE TABLE IF NOT EXISTS vaccination_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    vaccine_id INT NULL COMMENT 'Links to warehouse_items (where category = Vakcíny)',
    vaccine_name VARCHAR(255) NOT NULL,
    planned_date DATE NOT NULL,
    month_planned TINYINT NULL COMMENT 'Month (1-12) for planning grid',
    vaccination_interval_days INT NULL COMMENT 'User-defined interval (365, 730, 1095, etc.)',
    requires_booster BOOLEAN DEFAULT FALSE COMMENT 'Primary vaccination needs booster',
    booster_days INT NULL COMMENT 'Days until booster (typically 14)',
    booster_plan_id INT NULL COMMENT 'Links to the booster vaccination plan',
    animal_category VARCHAR(100) NULL COMMENT 'Šelmy Kočkovité, Psovité, Kopytníci, etc.',
    status ENUM('planned', 'completed', 'overdue', 'cancelled') DEFAULT 'planned',
    administered_date DATE NULL,
    administered_by INT NULL,
    notes TEXT,
    notification_sent_7days BOOLEAN DEFAULT FALSE,
    notification_sent_1day BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (administered_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_animal_id (animal_id),
    INDEX idx_planned_date (planned_date),
    INDEX idx_status (status),
    INDEX idx_vaccine_id (vaccine_id),
    INDEX idx_animal_category (animal_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add booster_plan_id foreign key after table is created
ALTER TABLE vaccination_plans
ADD CONSTRAINT fk_vaccination_plans_booster
FOREIGN KEY (booster_plan_id) REFERENCES vaccination_plans(id) ON DELETE SET NULL;

-- ============================================
-- VACCINATION HISTORY TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS vaccination_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    vaccine_name VARCHAR(255) NOT NULL,
    vaccination_date DATE NOT NULL,
    batch_number VARCHAR(100),
    expiry_date DATE,
    administered_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (administered_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_animal_id (animal_id),
    INDEX idx_vaccination_date (vaccination_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VACCINE TEMPLATES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS vaccine_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    frequency_days INT COMMENT 'Recommended frequency in days',
    species VARCHAR(100) COMMENT 'Recommended for species',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vaccine_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some common vaccine templates
INSERT INTO vaccine_templates (name, description, frequency_days, species) VALUES
('Rabies', 'Rabies vaccination', 365, NULL),
('DHPP', 'Distemper, Hepatitis, Parvovirus, Parainfluenza', 365, 'Canine'),
('FVRCP', 'Feline Viral Rhinotracheitis, Calicivirus, Panleukopenia', 365, 'Feline'),
('Bordetella', 'Kennel cough', 180, 'Canine'),
('Leptospirosis', 'Leptospirosis vaccine', 365, 'Canine'),
('FeLV', 'Feline Leukemia Virus', 365, 'Feline'),
('RCP', 'Rhinotracheitis, Calicivirus, Panleukopenia', 365, 'Feline'),
('RCPCH', 'RCP + Chlamydia', 365, 'Feline'),
('DHPPi+L4', 'Distemper, Hepatitis, Parvovirus, Parainfluenza, Leptospirosis', 365, 'Canine'),
('DHP+L4', 'Distemper, Hepatitis, Parvovirus, Leptospirosis', 365, 'Canine'),
('Covexin 10', 'Clostridial diseases vaccine', 365, NULL),
('Nobivac Rabies', 'Rabies vaccine', 1095, NULL)
ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- VACCINE TYPE COLOR MAPPING TABLE
-- ============================================
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
('Nobivac Rabies', '#f39c12', 'R'),
('Rabies', '#f39c12', 'RAB'),
('DHPP', '#3498db', 'DHPP'),
('FVRCP', '#e74c3c', 'FVRCP'),
('Bordetella', '#9b59b6', 'BOR'),
('Leptospirosis', '#85c1e9', 'LEPT'),
('FeLV', '#e67e22', 'FeLV')
ON DUPLICATE KEY UPDATE color_hex=VALUES(color_hex), abbreviation=VALUES(abbreviation);

-- ============================================
-- VACCINATION COST ESTIMATES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS vaccination_cost_estimates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    workplace_id INT,
    vaccine_id INT NULL COMMENT 'Links to warehouse_items (where category = Vakcíny)',
    animal_category VARCHAR(100),
    estimated_doses INT NOT NULL DEFAULT 0,
    cost_per_dose DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workplace_id) REFERENCES workplaces(id) ON DELETE CASCADE,
    INDEX idx_year_workplace (year, workplace_id),
    INDEX idx_vaccine_id (vaccine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5-YEAR VACCINATION SCHEDULE TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS vaccination_schedule_5year (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    year INT NOT NULL,
    vaccine_id INT NULL COMMENT 'Links to warehouse_items (where category = Vakcíny)',
    planned_month TINYINT COMMENT '1-12 for month',
    vaccination_type VARCHAR(100) COMMENT 'RCP, DHPPi+L4, Covexin, etc.',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    INDEX idx_year (year),
    INDEX idx_animal_year (animal_id, year),
    INDEX idx_vaccine_id (vaccine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUCCESS MESSAGE
-- ============================================
SELECT 'Vaccination system tables created successfully!' AS status;

-- ============================================
-- VERIFICATION QUERIES (Optional - uncomment to run)
-- ============================================
-- SHOW TABLES LIKE 'vaccination%';
-- DESCRIBE vaccination_plans;
-- DESCRIBE vaccination_cost_estimates;
-- DESCRIBE vaccination_schedule_5year;
-- DESCRIBE vaccine_type_colors;
-- SELECT * FROM vaccine_type_colors;
-- SELECT * FROM vaccine_templates;
