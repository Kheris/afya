-- AfyaCheck - Update #3: Vipimo vya Mwili (Vitals)
-- Ingiza kupitia phpMyAdmin -> Import (haifuti data iliyopo)

CREATE TABLE IF NOT EXISTS vitals_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dependent_id INT DEFAULT NULL,
    weight_kg DECIMAL(5,2) DEFAULT NULL,
    temperature_c DECIMAL(4,1) DEFAULT NULL,
    systolic INT DEFAULT NULL,
    diastolic INT DEFAULT NULL,
    heart_rate INT DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    logged_at DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dependent_id) REFERENCES dependents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
