<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/vaccines.php';
requireLogin();
requireConsent();

$db = getDB();
$userId = currentUserId();

// -- Weka chanjo kama imetolewa --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done'])) {
    $stmt = $db->prepare("UPDATE vaccinations SET status='done', given_date=? WHERE id=? AND user_id=?");
    $stmt->execute([date('Y-m-d'), (int)$_POST['mark_done'], $userId]);
    header('Location: vaccinations.php?dependent=' . (int)($_POST['dependent_id'] ?? 0));
    exit;
}

// -- Wanafamilia wenye tarehe ya kuzaliwa (watoto wanaoweza kufuatiliwa) --
$stmt = $db->prepare("SELECT * FROM dependents WHERE user_id = ? AND date_of_birth IS NOT NULL ORDER BY date_of_birth DESC");
$stmt->execute([$userId]);
$children = $stmt->fetchAll();

// Hakikisha kila mmoja ana ratiba yake tayari imetengenezwa
foreach ($children as $c) {
    ensureVaccinationScheduleForDependent($db, (int)$c['id'], $userId, $c['date_of_birth']);
}

$selectedId = isset($_GET['dependent']) ? (int)$_GET['dependent'] : ($children[0]['id'] ?? 0);

$schedule = [];
if ($selectedId) {
    $stmt = $db->prepare("SELECT * FROM vaccinations WHERE dependent_id = ? AND user_id = ? ORDER BY due_date ASC");
    $stmt->execute([$selectedId, $userId]);
    $schedule = $stmt->fetchAll();
}

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chanjo za Watoto - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Chanjo za Watoto</span>
  </header>

  <main class="home-page">
    <p class="subtitle">Fuatilia ratiba ya chanjo za mtoto kulingana na muongozo wa Tanzania (EPI). Hii ni muongozo wa jumla tu &mdash; muulize muuguzi wa kliniki kwa ratiba rasmi.</p>

    <?php if (!$children): ?>
      <div class="disclaimer-box">
        <span>&#8505;&#65039;</span>
        <span>Huna bado mwanafamilia mwenye tarehe ya kuzaliwa iliyowekwa. <a href="dependents.php">Ongeza mtoto</a> kwenye ukurasa wa "Familia Yangu" kwanza (weka tarehe ya kuzaliwa) ili tupate kutengeneza ratiba yake ya chanjo.</span>
      </div>
    <?php else: ?>

      <?php if (count($children) > 1): ?>
      <div class="card-list" style="margin-bottom:16px;">
        <?php foreach ($children as $c): ?>
          <a href="?dependent=<?= (int)$c['id'] ?>" class="list-card" style="<?= $c['id'] == $selectedId ? 'border-color:var(--blue);' : '' ?>">
            <div><strong><?= htmlspecialchars($c['name']) ?></strong><div class="muted"><?= htmlspecialchars($c['relationship'] ?: 'Mtoto') ?></div></div>
            <span>&#8250;</span>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <section class="section">
        <h2>Ratiba ya Chanjo</h2>
        <div class="card-list">
          <?php foreach ($schedule as $v):
            $isOverdue = $v['status'] === 'pending' && $v['due_date'] < $today;
            $badgeClass = $v['status'] === 'done' ? 'badge-self_care' : ($isOverdue ? 'badge-emergency' : 'badge-routine');
            $badgeText = $v['status'] === 'done' ? 'Imekamilika' : ($isOverdue ? 'Imepitwa na wakati' : 'Inakuja');
          ?>
            <div class="list-card">
              <div>
                <strong><?= htmlspecialchars($v['vaccine_name']) ?></strong>
                <div class="muted">
                  <?= $v['status'] === 'done'
                        ? 'Ilitolewa: ' . date('d M Y', strtotime($v['given_date']))
                        : 'Tarehe inayotakiwa: ' . date('d M Y', strtotime($v['due_date'])) ?>
                </div>
              </div>
              <?php if ($v['status'] === 'pending'): ?>
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="mark_done" value="<?= (int)$v['id'] ?>">
                  <input type="hidden" name="dependent_id" value="<?= (int)$selectedId ?>">
                  <button type="submit" class="badge <?= $badgeClass ?>" style="border:none;cursor:pointer;">Tia Alama: Imetolewa</button>
                </form>
              <?php else: ?>
                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
