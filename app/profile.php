<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wasifu - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <main class="profile-page">
    <h1><?= htmlspecialchars($user['name']) ?></h1>

    <section class="section">
      <h2>Taarifa zako</h2>
      <div class="card-list">
        <a href="basic-info.php" class="list-card">
          <div><strong>Taarifa za msingi</strong></div>
          <span>&#8250;</span>
        </a>
        <a href="onboarding.php" class="list-card">
          <div><strong>Historia ya kiafya</strong></div>
          <span>&#8250;</span>
        </a>
        <a href="dependents.php" class="list-card">
          <div><strong>Familia yangu</strong></div>
          <span>&#8250;</span>
        </a>
      </div>
    </section>

    <section class="section">
      <h2>Shughuli zako</h2>
      <div class="card-list">
        <a href="history.php" class="list-card">
          <div><strong>Tathmini za kiafya</strong></div>
          <span>&#8250;</span>
        </a>
      </div>
    </section>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item active"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
