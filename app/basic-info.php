<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? $user['name']);
    $stmt = getDB()->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, currentUserId()]);
    $user = currentUser();
    $saved = true;
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Taarifa za Msingi - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="profile.php" class="back-arrow">&#8249;</a>
  </header>
  <main class="auth-page">
    <h1>Taarifa za msingi</h1>
    <?php if ($saved): ?><div class="alert-success"><p>Taarifa zimehifadhiwa.</p></div><?php endif; ?>
    <form method="POST" class="form-stack">
      <label>Jina</label>
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

      <label>Barua pepe</label>
      <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

      <button type="submit" class="btn-primary">Hifadhi</button>
    </form>
  </main>
</div>
</body>
</html>
