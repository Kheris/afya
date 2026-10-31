-- AfyaCheck - Update #5: utendaji wa Kalenda ya Hedhi (salama kuendesha mara nyingi)
-- Ingiza kupitia phpMyAdmin -> Import. Haifuti data.
CREATE INDEX IF NOT EXISTS idx_period_user_date ON period_logs (user_id, start_date);
