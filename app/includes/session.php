<?php
/**
 * Session kwenye database (MySQL) kwa ajili ya Vercel.
 * Serverless functions hazina disk ya kudumu wala ya pamoja, kwa hiyo session za
 * faili zingepotea kila ombi jipya likienda kwenye instance nyingine.
 *
 * Kwenye hosting ya kawaida (si Vercel) tunaacha session za faili kama zilivyo,
 * isipokuwa ukiweka SESSION_DRIVER=db.
 */
require_once __DIR__ . '/../config.php';

class AfyaDbSessionHandler implements SessionHandlerInterface
{
    private function db(): PDO {
        require_once __DIR__ . '/db.php';
        return getDB();
    }

    private function ensureTable(): void {
        $this->db()->exec("CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            data MEDIUMTEXT NOT NULL,
            expires_at INT UNSIGNED NOT NULL,
            INDEX idx_sessions_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }

    public function read(string $id): string|false {
        try {
            $st = $this->db()->prepare("SELECT data FROM sessions WHERE id = ? AND expires_at > ?");
            $st->execute([$id, time()]);
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') { $this->ensureTable(); return ''; } // jedwali halipo bado
            throw $e;
        }
        $row = $st->fetch();
        return $row ? (string)$row['data'] : '';
    }

    public function write(string $id, string $data): bool {
        $st = $this->db()->prepare(
            "INSERT INTO sessions (id, data, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)"
        );
        return $st->execute([$id, $data, time() + SESSION_LIFETIME]);
    }

    public function destroy(string $id): bool {
        $st = $this->db()->prepare("DELETE FROM sessions WHERE id = ?");
        return $st->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false {
        $st = $this->db()->prepare("DELETE FROM sessions WHERE expires_at < ?");
        $st->execute([time()]);
        return $st->rowCount();
    }
}

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || IS_VERCEL;

    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (IS_VERCEL || env('SESSION_DRIVER') === 'db') {
        session_set_save_handler(new AfyaDbSessionHandler(), true);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');
    }
}
