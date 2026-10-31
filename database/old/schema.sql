-- AfyaCheck - Muundo wa Database
-- Ingiza faili hii kupitia phpMyAdmin ya InfinityFree (Import tab)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    sex_at_birth ENUM('female','male','not_specified') DEFAULT 'not_specified',
    is_smoker ENUM('yes','no','unknown') DEFAULT 'unknown',
    has_diabetes ENUM('yes','no','unknown') DEFAULT 'unknown',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) DEFAULT 'Tathmini ya dalili',
    conversation LONGTEXT NOT NULL,   -- JSON ya ujumbe wote (user + AI)
    status ENUM('ongoing','completed') DEFAULT 'ongoing',
    summary TEXT DEFAULT NULL,        -- muhtasari wa mwisho wa AI
    urgency_level ENUM('emergency','urgent','routine','self_care','unknown') DEFAULT 'unknown',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
