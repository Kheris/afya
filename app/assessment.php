<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireConsent();

$db = getDB();
$userId = currentUserId();

$assessmentId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$forParam = $_GET['for'] ?? null; // 'self' au ID ya dependent
$conversation = [];
$forLabel = null;

// -- Kama tunaendeleza tathmini iliyopo --
if ($assessmentId) {
    $stmt = $db->prepare("SELECT * FROM assessments WHERE id = ? AND user_id = ?");
    $stmt->execute([$assessmentId, $userId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $conversation = json_decode($existing['conversation'], true) ?: [];
        $forLabel = $existing['for_label'];
    }
}

// -- Kama bado hatujachagua "kwa ajili ya nani", onyesha uchaguzi kwanza --
if (!$assessmentId && !$forParam) {
    $stmt = $db->prepare("SELECT id, name, relationship FROM dependents WHERE user_id = ? ORDER BY name");
    $stmt->execute([$userId]);
    $dependents = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="sw">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tathmini ya Dalili - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
    </head>
    <body>
    <div class="app-shell">
      <header class="top-nav">
        <a href="dashboard.php" class="back-arrow">&#8249;</a>
        <a href="dashboard.php" class="close-x">&times;</a>
      </header>
      <main class="auth-page">
        <h1>Tathmini hii ni kwa ajili ya nani?</h1>
        <div class="card-list">
          <a href="assessment.php?for=self" class="list-card"><div><strong>Mimi mwenyewe</strong></div><span>&#8250;</span></a>
          <?php foreach ($dependents as $d): ?>
            <a href="assessment.php?for=<?= (int)$d['id'] ?>" class="list-card">
              <div><strong><?= htmlspecialchars($d['name']) ?></strong><div class="muted"><?= htmlspecialchars($d['relationship'] ?: '') ?></div></div>
              <span>&#8250;</span>
            </a>
          <?php endforeach; ?>
          <a href="dependents.php" class="list-card"><div><strong>+ Ongeza mtu mwingine</strong></div><span>&#8250;</span></a>
        </div>
      </main>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// -- Bainisha "for label" kutoka kwa parameter --
$dependentId = null;
if ($forParam && $forParam !== 'self' && !$forLabel) {
    $stmt = $db->prepare("SELECT name FROM dependents WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$forParam, $userId]);
    $dep = $stmt->fetch();
    if ($dep) {
        $dependentId = (int)$forParam;
        $forLabel = $dep['name'];
    }
} elseif ($forParam === 'self' && !$forLabel) {
    $forLabel = 'Mwenyewe';
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tathmini ya Dalili - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="dashboard.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Tathmini: <?= htmlspecialchars($forLabel ?: 'Mwenyewe') ?></span>
    <a href="dashboard.php" class="close-x">&times;</a>
  </header>

  <main class="chat-page">
    <div id="chat-log" class="chat-log"></div>

    <div class="disclaimer-box">
      <span>&#128161;</span>
      <p>Kama kuna <strong>dalili za dharura</strong> (maumivu makali ya kifua, kushindwa kupumua, kuzimia), usitumie AfyaCheck. Nenda <a href="emergency.php">ukurasa wa Dharura</a> mara moja.</p>
    </div>
  </main>

  <form id="chat-form" class="chat-input-bar">
    <input type="text" id="chat-input" placeholder="Andika dalili zako hapa..." autocomplete="off" required>
    <button type="submit" id="send-btn">&#10148;</button>
  </form>
</div>

<script>
  window.AFYA_ASSESSMENT_ID = <?= json_encode($assessmentId) ?>;
  window.AFYA_INITIAL_CONVERSATION = <?= json_encode($conversation, JSON_UNESCAPED_UNICODE) ?>;
  window.AFYA_DEPENDENT_ID = <?= json_encode($dependentId) ?>;
  window.AFYA_FOR_LABEL = <?= json_encode($forLabel ?: 'Mwenyewe') ?>;
</script>
<script src="assets/js/assessment.js"></script>
</body>
</html>
