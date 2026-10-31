-- AfyaCheck - Update #2: Familia, Idhini, na Ulinzi wa AI
-- Ingiza kupitia phpMyAdmin -> Import (haifuti data iliyopo)

CREATE TABLE IF NOT EXISTS dependents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    sex_at_birth ENUM('female','male','not_specified') DEFAULT 'not_specified',
    relationship VARCHAR(60) DEFAULT NULL,  -- mfano: "Mtoto", "Mzazi", "Mwenzi"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE assessments ADD COLUMN IF NOT EXISTS dependent_id INT DEFAULT NULL;
ALTER TABLE assessments ADD COLUMN IF NOT EXISTS for_label VARCHAR(100) DEFAULT 'Mwenyewe';

ALTER TABLE users ADD COLUMN IF NOT EXISTS terms_accepted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS consent_research TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS consent_usage TINYINT(1) DEFAULT 0;

CREATE TABLE IF NOT EXISTS rate_limits (
    user_id INT NOT NULL,
    usage_date DATE NOT NULL,
    message_count INT DEFAULT 0,
    PRIMARY KEY (user_id, usage_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
