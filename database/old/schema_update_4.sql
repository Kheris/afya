-- AfyaCheck - Update #4: Chanjo za Watoto, Ufuatiliaji wa Baada ya Tathmini, Elimu ya Afya
-- Ingiza kupitia phpMyAdmin -> Import (haifuti data iliyopo)

-- --- Chanjo za Watoto ---
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --- Ufuatiliaji wa Baada ya Tathmini (Follow-up) ---
-- NOTE: "ADD COLUMN IF NOT EXISTS" inafanya kazi kwenye MariaDB (InfinityFree hutumia MariaDB).
-- Kama hosting yako inatumia MySQL ya Oracle, ondoa "IF NOT EXISTS" na endesha mara moja tu.
ALTER TABLE assessments ADD COLUMN IF NOT EXISTS follow_up_status ENUM('pending','saw_doctor','self_care','still_sick','ignored') DEFAULT NULL;
ALTER TABLE assessments ADD COLUMN IF NOT EXISTS follow_up_asked_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE assessments ADD COLUMN IF NOT EXISTS follow_up_responded_at TIMESTAMP NULL DEFAULT NULL;

-- --- Maktaba ya Elimu ya Afya ---
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(60) NOT NULL,
    summary VARCHAR(255) DEFAULT NULL,
    body LONGTEXT NOT NULL,
    keywords VARCHAR(255) DEFAULT NULL, -- maneno ya kutafuta uhusiano na dalili, yametenganishwa na koma
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --- Index kwa utendaji bora (hiari lakini inapendekezwa) ---
CREATE INDEX IF NOT EXISTS idx_vaccinations_user_status ON vaccinations (user_id, status, due_date);
CREATE INDEX IF NOT EXISTS idx_assessments_followup ON assessments (user_id, status, urgency_level, follow_up_status);
