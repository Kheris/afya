<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUserId()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getDB()->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        loginUser((int)$user['id']);
        header('Location: dashboard.php');
        exit;
    } else {
        $errors[] = 'Barua pepe au password si sahihi.';
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingia - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <main class="auth-page">
    <div class="brand-mark">AfyaCheck</div>
    <h1>Karibu tena</h1>
    <p class="subtitle">Ingia kwenye akaunti yako.</p>

    <?php if ($errors): ?>
      <div class="alert-error">
        <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="form-stack">
      <label>Barua pepe</label>
      <input type="email" name="email" placeholder="Andika barua pepe yako" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Weka password" required>

      <button type="submit" class="btn-primary">Ingia</button>
    </form>

    <p class="switch-link">Huna akaunti? <a href="register.php">Fungua akaunti mpya</a></p>
  </main>
</div>
</body>
</html>
