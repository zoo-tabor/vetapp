-- Vaccination Plans Tables
-- Created: 2026-01-08

-- Vaccination plans for animals
CREATE TABLE IF NOT EXISTS vaccination_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    vaccine_name VARCHAR(255) NOT NULL,
    planned_date DATE NOT NULL,
    status ENUM('planned', 'completed', 'overdue', 'cancelled') DEFAULT 'planned',
    administered_date DATE NULL,
    administered_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (administered_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_animal_id (animal_id),
    INDEX idx_planned_date (planned_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vaccination history records
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

-- Vaccine templates (common vaccines for quick selection)
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
('FeLV', 'Feline Leukemia Virus', 365, 'Feline')
ON DUPLICATE KEY UPDATE name=name;
