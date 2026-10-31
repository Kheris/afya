<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dharura - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Dharura</span>
  </header>

  <main class="home-page">
    <div class="emergency-banner">
      <h1>Je, huu ni Dharura?</h1>
      <p>Kama mtu ana maumivu makali ya kifua, kushindwa kupumua, kutokwa damu nyingi, kuzimia, au dalili za kiharusi (uso kupinda upande mmoja, kushindwa kuzungumza, mkono/mguu kufa ganzi) &mdash; PIGA SIMU SASA. Usisubiri.</p>
    </div>

    <div class="sos-grid">
      <a href="tel:112" class="sos-btn">
        <span class="sos-icon">&#128737;&#65039;</span>
        <span>Polisi</span>
        <span class="sos-number">112</span>
      </a>
      <a href="tel:115" class="sos-btn">
        <span class="sos-icon">&#128660;</span>
        <span>Huduma ya Wagonjwa</span>
        <span class="sos-number">115</span>
      </a>
      <a href="tel:114" class="sos-btn">
        <span class="sos-icon">&#128293;</span>
        <span>Zimamoto</span>
        <span class="sos-number">114</span>
      </a>
      <a href="tel:117" class="sos-btn">
        <span class="sos-icon">&#9877;&#65039;</span>
        <span>Msaada wa Afya</span>
        <span class="sos-number">117</span>
      </a>
    </div>

    <p class="disclaimer-box" style="margin-top:16px;">
      <span>&#8505;&#65039;</span>
      <span>Huduma ya magari ya wagonjwa (ambulance) ni chache na hutofautiana kwa mkoa -- kwenye baadhi ya maeneo, huduma binafsi ndiyo pekee inayopatikana. Kama inawezekana, kumpeleka mgonjwa hospitalini kwa gari lililopo karibu nawe kunaweza kuwa haraka zaidi kuliko kusubiri gari la wagonjwa.</span>
    </p>

    <section class="section">
      <h2>Ukiwa unasubiri msaada</h2>
      <div class="tips-list">
        <div class="tip-item"><span>1&#65039;&#8419;</span><p>Kaa pamoja na mgonjwa, usimwache peke yake.</p></div>
        <div class="tip-item"><span>2&#65039;&#8419;</span><p>Mwondoe mgonjwa mahali penye hatari (moto, barabara) kama inawezekana bila kumdhuru zaidi.</p></div>
        <div class="tip-item"><span>3&#65039;&#8419;</span><p>Kama hapumui na hana mapigo ya moyo, na umefundishwa CPR, anza CPR mara moja.</p></div>
        <div class="tip-item"><span>4&#65039;&#8419;</span><p>Andika muda dalili zilipoanza -- itasaidia madaktari.</p></div>
      </div>
    </section>

    <a href="nearby-care.php" class="btn-primary btn-block" style="margin-top:20px;background:var(--navy);">Tafuta hospitali karibu nawe</a>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
