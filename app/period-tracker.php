<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cycle.php';
requireLogin();
requireConsent();

$userId = currentUserId();
$db = getDB();

// -- Hifadhi kipindi kipya cha hedhi --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_period') {
    $startDate = $_POST['start_date'] ?? null;
    $periodLength = (int)($_POST['period_length'] ?? 5);
    if ($startDate) {
        $stmt = $db->prepare("INSERT INTO period_logs (user_id, start_date, period_length) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $startDate, $periodLength]);
    }
    header('Location: period-tracker.php');
    exit;
}

// -- Hifadhi mipangilio ya mzunguko --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $avgCycle  = (int)($_POST['avg_cycle_length'] ?? 28);
    $avgPeriod = (int)($_POST['avg_period_length'] ?? 5);
    $stmt = $db->prepare("UPDATE users SET avg_cycle_length = ?, avg_period_length = ? WHERE id = ?");
    $stmt->execute([$avgCycle, $avgPeriod, $userId]);
    header('Location: period-tracker.php');
    exit;
}

// -- Pata mipangilio ya mtumiaji --
$stmt = $db->prepare("SELECT avg_cycle_length, avg_period_length FROM users WHERE id = ?");
$stmt->execute([$userId]);
$settings = $stmt->fetch() ?: ['avg_cycle_length' => 28, 'avg_period_length' => 5];

// -- Pata kipindi cha mwisho kilichowekwa --
$stmt = $db->prepare("SELECT * FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 1");
$stmt->execute([$userId]);
$lastLog = $stmt->fetch();

// -- Historia yote --
$stmt = $db->prepare("SELECT * FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 12");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

$cycleInfo = null;
if ($lastLog) {
    $cycleInfo = calculateCycleInfo($lastLog['start_date'], (int)$settings['avg_cycle_length'], (int)$settings['avg_period_length']);
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalenda ya Hedhi - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Kalenda ya Hedhi</span>
  </header>

  <main class="home-page">

    <?php if (!$lastLog): ?>
      <p class="subtitle">Bado hujaweka tarehe ya hedhi yako ya mwisho. Weka hapa chini ili tuanze kukadiria mzunguko wako.</p>
    <?php else: ?>
      <div class="cycle-card">
        <div class="cycle-day-big">Siku ya <?= $cycleInfo['cycle_day'] ?></div>
        <div class="cycle-phase-label"><?= htmlspecialchars(getPhaseLabel($cycleInfo['phase'])) ?></div>
        <?php if ($cycleInfo['days_until_next'] !== null): ?>
          <div class="cycle-next">
            <?= $cycleInfo['days_until_next'] === 0 ? 'Hedhi inatarajiwa leo au tayari imechelewa' : 'Hedhi inayofuata inatarajiwa baada ya siku ' . $cycleInfo['days_until_next'] ?>
            (<?= $cycleInfo['next_period']->format('d M Y') ?>)
          </div>
        <?php endif; ?>
        <?php if ($cycleInfo['fertile_start']): ?>
          <div class="cycle-fertile">
            Kipindi cha uwezekano wa kupata mimba: <?= $cycleInfo['fertile_start']->format('d M') ?> - <?= $cycleInfo['fertile_end']->format('d M') ?>
          </div>
        <?php endif; ?>
      </div>

      <section class="section">
        <h2>Ushauri kwa awamu hii</h2>
        <div class="tips-list">
          <?php foreach (getPhaseTips($cycleInfo['phase']) as $tip): ?>
            <div class="tip-item"><span>&#127807;</span><p><?= htmlspecialchars($tip) ?></p></div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="section">
      <h2>Weka hedhi mpya</h2>
      <form method="POST" class="form-stack">
        <input type="hidden" name="action" value="log_period">
        <label>Tarehe ya mwanzo wa hedhi</label>
        <input type="date" name="start_date" max="<?= date('Y-m-d') ?>" required>
        <label>Siku ngapi ilidumu (au inatarajiwa kudumu)</label>
        <input type="number" name="period_length" value="5" min="1" max="14" required>
        <button type="submit" class="btn-primary">Hifadhi</button>
      </form>
    </section>

    <section class="section">
      <h2>Mipangilio ya mzunguko wako</h2>
      <form method="POST" class="form-stack">
        <input type="hidden" name="action" value="save_settings">
        <label>Wastani wa siku za mzunguko (kawaida ni 21-35)</label>
        <input type="number" name="avg_cycle_length" value="<?= (int)$settings['avg_cycle_length'] ?>" min="15" max="45" required>
        <label>Wastani wa siku za hedhi</label>
        <input type="number" name="avg_period_length" value="<?= (int)$settings['avg_period_length'] ?>" min="1" max="14" required>
        <button type="submit" class="btn-primary">Hifadhi mipangilio</button>
      </form>
    </section>

    <?php if ($history): ?>
    <section class="section">
      <h2>Historia</h2>
      <div class="card-list">
        <?php foreach ($history as $h): ?>
          <div class="list-card">
            <div>
              <strong><?= date('d M Y', strtotime($h['start_date'])) ?></strong>
              <div class="muted">Ilidumu siku <?= (int)$h['period_length'] ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <p class="disclaimer-box" style="margin-top:24px;">
      <span>&#128161;</span>
      <span>Hii ni makadirio ya jumla tu, si uhakika wa 100%. Kama mzunguko wako unabadilika sana, unakosa hedhi kwa muda mrefu, au una maumivu makali, muone daktari.</span>
    </p>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a>
    <a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a>
    <a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a>
  </nav>
</div>
</body>
</html>
