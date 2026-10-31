<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/svgchart.php';
requireLogin();
requireConsent();

$db = getDB();
$userId = currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = $_POST['weight_kg'] !== '' ? (float)$_POST['weight_kg'] : null;
    $temp   = $_POST['temperature_c'] !== '' ? (float)$_POST['temperature_c'] : null;
    $sys    = $_POST['systolic'] !== '' ? (int)$_POST['systolic'] : null;
    $dia    = $_POST['diastolic'] !== '' ? (int)$_POST['diastolic'] : null;
    $hr     = $_POST['heart_rate'] !== '' ? (int)$_POST['heart_rate'] : null;
    $date   = $_POST['logged_at'] ?: date('Y-m-d');

    if ($weight || $temp || $sys || $hr) {
        $stmt = $db->prepare("INSERT INTO vitals_logs (user_id, weight_kg, temperature_c, systolic, diastolic, heart_rate, logged_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $weight, $temp, $sys, $dia, $hr, $date]);
    }
    header('Location: vitals.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM vitals_logs WHERE user_id = ? AND dependent_id IS NULL ORDER BY logged_at ASC LIMIT 30");
$stmt->execute([$userId]);
$logs = $stmt->fetchAll();

$weightPoints = [];
$tempPoints = [];
$sysPoints = [];
foreach ($logs as $l) {
    $label = date('d/m', strtotime($l['logged_at']));
    if ($l['weight_kg'] !== null) $weightPoints[] = ['label' => $label, 'value' => (float)$l['weight_kg']];
    if ($l['temperature_c'] !== null) $tempPoints[] = ['label' => $label, 'value' => (float)$l['temperature_c']];
    if ($l['systolic'] !== null) $sysPoints[] = ['label' => $label, 'value' => (float)$l['systolic']];
}

$recentLogs = array_reverse($logs);
$recentLogs = array_slice($recentLogs, 0, 10);
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vipimo vya Mwili - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Vipimo vya Mwili</span>
  </header>

  <main class="home-page">

    <?php if ($weightPoints): ?>
    <section class="section">
      <h2>Uzito (kg)</h2>
      <div class="chart-box"><?= renderSvgLineChart($weightPoints, '#2f80ed', 'kg') ?></div>
    </section>
    <?php endif; ?>

    <?php if ($sysPoints): ?>
    <section class="section">
      <h2>Shinikizo la damu (systolic)</h2>
      <div class="chart-box"><?= renderSvgLineChart($sysPoints, '#e0563e', 'mmHg') ?></div>
    </section>
    <?php endif; ?>

    <?php if ($tempPoints): ?>
    <section class="section">
      <h2>Joto la mwili (&deg;C)</h2>
      <div class="chart-box"><?= renderSvgLineChart($tempPoints, '#2fae6c', '&deg;C') ?></div>
    </section>
    <?php endif; ?>

    <section class="section">
      <h2>Ongeza kipimo kipya</h2>
      <form method="POST" class="form-stack">
        <label>Tarehe</label>
        <input type="date" name="logged_at" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">

        <label>Uzito (kg)</label>
        <input type="number" step="0.1" name="weight_kg" placeholder="Mfano: 62.5">

        <label>Joto la mwili (&deg;C)</label>
        <input type="number" step="0.1" name="temperature_c" placeholder="Mfano: 36.8">

        <label>Shinikizo la damu (systolic / diastolic)</label>
        <div style="display:flex;gap:10px;">
          <input type="number" name="systolic" placeholder="120" style="flex:1;">
          <input type="number" name="diastolic" placeholder="80" style="flex:1;">
        </div>

        <label>Mapigo ya moyo (bpm)</label>
        <input type="number" name="heart_rate" placeholder="Mfano: 72">

        <button type="submit" class="btn-primary">Hifadhi kipimo</button>
      </form>
    </section>

    <?php if ($recentLogs): ?>
    <section class="section">
      <h2>Historia ya hivi karibuni</h2>
      <div class="card-list">
        <?php foreach ($recentLogs as $l): ?>
          <div class="list-card">
            <div>
              <strong><?= date('d M Y', strtotime($l['logged_at'])) ?></strong>
              <div class="muted">
                <?php
                  $parts = [];
                  if ($l['weight_kg']) $parts[] = $l['weight_kg'] . ' kg';
                  if ($l['temperature_c']) $parts[] = $l['temperature_c'] . ' &deg;C';
                  if ($l['systolic']) $parts[] = $l['systolic'] . '/' . $l['diastolic'] . ' mmHg';
                  if ($l['heart_rate']) $parts[] = $l['heart_rate'] . ' bpm';
                  echo implode(' &middot; ', $parts);
                ?>
              </div>
            </div>
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
