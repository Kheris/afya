<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mipangilio - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <span class="top-title">Mipangilio</span>
  </header>
  <main class="home-page">
    <section class="section">
      <h2>Akaunti</h2>
      <div class="card-list">
        <a href="basic-info.php" class="list-card"><div><strong>Taarifa za akaunti</strong></div><span>&#8250;</span></a>
      </div>
    </section>
    <section class="section">
      <h2>Muonekano</h2>
      <div class="card-list">
        <div class="list-card" style="cursor:pointer;" onclick="toggleAfyaTheme()">
          <div><strong>&#127769; Dark Mode</strong><div class="muted theme-state">...</div></div>
          <span>&#8635;</span>
        </div>
      </div>
    </section>
    <section class="section">
      <h2>Msaada</h2>
      <div class="card-list">
        <a href="mailto:support@yourdomain.com" class="list-card"><div><strong>Maoni yako</strong></div><span>&#8250;</span></a>
      </div>
    </section>
    <p class="switch-link"><a href="logout.php">Toka (Log out)</a></p>
    <p class="version-tag">Version 1.0.0</p>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item active"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
