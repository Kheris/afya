<?php
require_once __DIR__ . '/../config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10,
        ];
        if (DB_SSL) {
            // TLS (inahitajika na TiDB Cloud, Aiven, nk.)
            $ca = env('DB_SSL_CA', __DIR__ . '/../certs/ca-bundle.crt');
            $opts[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            error_log('DB connect failed: ' . $e->getMessage());
            http_response_code(500);
            die('Database connection error. Angalia Environment Variables (DB_HOST, DB_USER, DB_PASS, DB_NAME).');
        }
    }
    return $pdo;
}
