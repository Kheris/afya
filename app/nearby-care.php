<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireConsent();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vituo vya Afya Karibu - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Vituo vya Afya Karibu</span>
  </header>

  <main class="home-page">
    <p class="subtitle">Tunahitaji ruhusa ya kujua mahali ulipo ili kukuonyesha hospitali, zahanati na maduka ya dawa yaliyo karibu nawe.</p>

    <div id="map" class="map-box">Inapakia ramani...</div>

    <div id="facility-status" class="subtitle" style="margin-top:16px;"></div>
    <div id="facility-list" class="card-list" style="margin-top:12px;"></div>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/nearby-care.js"></script>
</body>
</html>
