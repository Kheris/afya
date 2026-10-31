<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/articles.php';
requireLogin();
requireConsent();

$db = getDB();
seedArticlesIfEmpty($db);

$stmt = $db->query("SELECT slug, title, category, summary FROM articles ORDER BY category, title");
$articles = $stmt->fetchAll();

$byCategory = [];
foreach ($articles as $a) {
    $byCategory[$a['category']][] = $a;
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Elimu ya Afya - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Elimu ya Afya</span>
  </header>

  <main class="home-page">
    <p class="subtitle">Makala fupi za kukusaidia kuelewa afya yako vizuri zaidi. Hii ni taarifa ya jumla, si mbadala wa ushauri wa daktari.</p>

    <?php foreach ($byCategory as $category => $items): ?>
      <section class="section">
        <h2><?= htmlspecialchars($category) ?></h2>
        <div class="card-list">
          <?php foreach ($items as $a): ?>
            <a href="article.php?slug=<?= urlencode($a['slug']) ?>" class="list-card">
              <div><strong><?= htmlspecialchars($a['title']) ?></strong><div class="muted"><?= htmlspecialchars($a['summary']) ?></div></div>
              <span>&#8250;</span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
