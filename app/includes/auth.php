<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function requireConsent(): void {
    $id = currentUserId();
    if (!$id) return;
    $stmt = getDB()->prepare("SELECT terms_accepted_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !$row['terms_accepted_at']) {
        header('Location: consent.php');
        exit;
    }
}

function currentUser(): ?array {
    $id = currentUserId();
    if (!$id) return null;
    $stmt = getDB()->prepare("SELECT id, name, email, date_of_birth, sex_at_birth, is_smoker, has_diabetes FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function loginUser(int $userId): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
