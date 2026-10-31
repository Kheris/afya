<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/articles.php';
requireLogin();
requireConsent();

$db = getDB();
$slug = $_GET['slug'] ?? '';

$stmt = $db->prepare("SELECT * FROM articles WHERE slug = ?");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: articles.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($article['title']) ?> - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
<style>
  .article-body { padding: 4px 4px 20px; line-height: 1.65; font-size: 15px; white-space: pre-line; }
  .article-category { display:inline-block; font-size:12px; color:var(--blue); font-weight:600; margin-bottom:6px; }
</style>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="articles.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Elimu ya Afya</span>
  </header>

  <main class="home-page">
    <span class="article-category"><?= htmlspecialchars($article['category']) ?></span>
    <h1><?= htmlspecialchars($article['title']) ?></h1>
    <div class="article-body"><?= htmlspecialchars($article['body']) ?></div>

    <p class="disclaimer-box">
      <span>&#9888;&#65039;</span>
      <span>Makala hii ni taarifa ya jumla kwa ajili ya uelewa, SI ushauri wa kimatibabu wa mtu mmoja mmoja. Kama una dalili, tumia 'Anza tathmini ya dalili' au muone daktari.</span>
    </p>

    <a href="assessment.php" class="btn-primary btn-block" style="margin-top:10px;">Anza tathmini ya dalili</a>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
