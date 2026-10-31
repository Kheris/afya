-- AfyaCheck: schema kamili (mpya) - schema.sql + updates 1..5 + sessions.
-- Inaendeshwa kwenye database MPYA tupu. Inafanya kazi MySQL 5.7+/8, MariaDB, TiDB.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    sex_at_birth ENUM('female','male','not_specified') DEFAULT 'not_specified',
    is_smoker ENUM('yes','no','unknown') DEFAULT 'unknown',
    has_diabetes ENUM('yes','no','unknown') DEFAULT 'unknown',
    avg_cycle_length INT DEFAULT 28,
    avg_period_length INT DEFAULT 5,
    terms_accepted_at TIMESTAMP NULL DEFAULT NULL,
    consent_research TINYINT(1) DEFAULT 0,
    consent_usage TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dependents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    sex_at_birth ENUM('female','male','not_specified') DEFAULT 'not_specified',
    relationship VARCHAR(60) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) DEFAULT 'Tathmini ya dalili',
    conversation LONGTEXT NOT NULL,
    status ENUM('ongoing','completed') DEFAULT 'ongoing',
    summary TEXT DEFAULT NULL,
    urgency_level ENUM('emergency','urgent','routine','self_care','unknown') DEFAULT 'unknown',
    dependent_id INT DEFAULT NULL,
    for_label VARCHAR(100) DEFAULT 'Mwenyewe',
    follow_up_status ENUM('pending','saw_doctor','self_care','still_sick','ignored') DEFAULT NULL,
    follow_up_asked_at TIMESTAMP NULL DEFAULT NULL,
    follow_up_responded_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_assessments_followup (user_id, status, urgency_level, follow_up_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS period_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    start_date DATE NOT NULL,
    period_length INT DEFAULT 5,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_period_user_date (user_id, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rate_limits (
    user_id INT NOT NULL,
    usage_date DATE NOT NULL,
    message_count INT DEFAULT 0,
    PRIMARY KEY (user_id, usage_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dependent_id INT NOT NULL,
    user_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    due_date DATE NOT NULL,
    given_date DATE DEFAULT NULL,
    status ENUM('pending','done','missed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dependent_id) REFERENCES dependents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_vaccinations_user_status (user_id, status, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(60) NOT NULL,
    summary VARCHAR(255) DEFAULT NULL,
    body LONGTEXT NOT NULL,
    keywords VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Session za watumiaji (zinahitajika kwenye Vercel)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    data MEDIUMTEXT NOT NULL,
    expires_at INT UNSIGNED NOT NULL,
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
