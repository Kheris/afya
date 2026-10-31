<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUserId()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = 'Tafadhali weka jina lako.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Barua pepe si sahihi.';
    if (strlen($password) < 8) $errors[] = 'Password lazima iwe na herufi/namba 8 au zaidi.';

    if (empty($errors)) {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Barua pepe hii tayari imesajiliwa. Jaribu kuingia (Log in).';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            loginUser((int)$db->lastInsertId());
            header('Location: consent.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fungua Akaunti - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="login.php" class="back-arrow">&#8249;</a>
  </header>

  <main class="auth-page">
    <h1>Fungua akaunti yako</h1>
    <p class="subtitle">Jisajili kwa barua pepe na password ili uanze kutumia AfyaCheck.</p>

    <?php if ($errors): ?>
      <div class="alert-error">
        <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="form-stack">
      <label>Jina lako</label>
      <input type="text" name="name" placeholder="Andika jina lako" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

      <label>Barua pepe</label>
      <input type="email" name="email" placeholder="Andika barua pepe yako" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="Weka password" required>
      <small class="hint">Password iwe na herufi 8 au zaidi.</small>

      <button type="submit" class="btn-primary">Jisajili</button>
    </form>

    <p class="switch-link">Una akaunti tayari? <a href="login.php">Ingia</a></p>
  </main>
</div>
</body>
</html>
