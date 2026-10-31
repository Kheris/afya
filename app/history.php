<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$stmt = getDB()->prepare("SELECT id, title, status, urgency_level, created_at FROM assessments WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([currentUserId()]);
$assessments = $stmt->fetchAll();

$urgencyLabels = [
    'emergency' => 'Dharura',
    'urgent'    => 'Haraka',
    'routine'   => 'Kawaida',
    'self_care' => 'Jitunze mwenyewe',
    'unknown'   => 'Haijabainika',
];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historia ya Tathmini - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="profile.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Tathmini za kiafya</span>
  </header>
  <main class="home-page">
    <?php if (!$assessments): ?>
      <p class="subtitle">Bado hujafanya tathmini yoyote.</p>
      <a href="assessment.php" class="btn-primary btn-block">Anza tathmini ya kwanza</a>
    <?php else: ?>
      <div class="card-list">
        <?php foreach ($assessments as $a): ?>
          <a href="<?= $a['status'] === 'completed' ? 'assessment-report.php?id=' . (int)$a['id'] : 'assessment.php?id=' . (int)$a['id'] ?>" class="list-card">
            <div>
              <strong><?= htmlspecialchars($a['title']) ?></strong>
              <div class="muted"><?= date('d M Y, H:i', strtotime($a['created_at'])) ?></div>
            </div>
            <span class="badge badge-<?= htmlspecialchars($a['urgency_level']) ?>"><?= $urgencyLabels[$a['urgency_level']] ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
