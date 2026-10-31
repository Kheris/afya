-- AfyaCheck - Update #1: Kalenda ya Hedhi
-- Ingiza hii kupitia phpMyAdmin -> database yako -> Import
-- (Hii HAIFUTI data yako iliyopo, inaongeza tu jedwali jipya)

CREATE TABLE IF NOT EXISTS period_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    start_date DATE NOT NULL,
    period_length INT DEFAULT 5,      -- siku ngapi hedhi inadumu
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mipangilio ya mzunguko wa mtumiaji (wastani wa siku za mzunguko)
ALTER TABLE users ADD COLUMN IF NOT EXISTS avg_cycle_length INT DEFAULT 28;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avg_period_length INT DEFAULT 5;
