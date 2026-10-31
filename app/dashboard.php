<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/followup.php';
require_once __DIR__ . '/includes/vaccines.php';
require_once __DIR__ . '/includes/svgchart.php';
require_once __DIR__ . '/includes/cycle.php';
requireLogin();
requireConsent();
$db = getDB();
$user = currentUser();
$userId = currentUserId();

// -- Tathmini za hivi karibuni --
$stmt = $db->prepare("SELECT id, title, status, urgency_level, created_at FROM assessments WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$userId]);
$recent = $stmt->fetchAll();

// -- Ufuatiliaji wa baada ya tathmini (follow-up) --
$followUp = getPendingFollowUp($db, $userId);

// -- Tahadhari za chanjo za watoto --
$stmt = $db->prepare("SELECT id, date_of_birth FROM dependents WHERE user_id = ? AND date_of_birth IS NOT NULL");
$stmt->execute([$userId]);
foreach ($stmt->fetchAll() as $c) {
    ensureVaccinationScheduleForDependent($db, (int)$c['id'], $userId, $c['date_of_birth']);
}
$vaccAlerts = getVaccinationAlerts($db, $userId);
$vaccAlertCount = count($vaccAlerts['overdue']) + count($vaccAlerts['upcoming']);

// -- Muhtasari mdogo wa vipimo vya mwili (uzito) --
$stmt = $db->prepare("SELECT weight_kg, logged_at FROM vitals_logs WHERE user_id = ? AND dependent_id IS NULL AND weight_kg IS NOT NULL ORDER BY logged_at ASC LIMIT 30");
$stmt->execute([$userId]);
$weightRows = $stmt->fetchAll();
$weightPoints = array_map(fn($r) => ['label' => date('d/m', strtotime($r['logged_at'])), 'value' => (float)$r['weight_kg']], $weightRows);
$weightPoints = array_slice($weightPoints, -6);

// -- Muhtasari wa mzunguko wa hedhi --
$cycleInfo = null;
if (($user['sex_at_birth'] ?? '') !== 'male') {
    $stmt = $db->prepare("SELECT avg_cycle_length, avg_period_length FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch() ?: ['avg_cycle_length' => 28, 'avg_period_length' => 5];

    $stmt = $db->prepare("SELECT start_date FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastLog = $stmt->fetch();
    if ($lastLog) {
        $cycleInfo = calculateCycleInfo($lastLog['start_date'], (int)$settings['avg_cycle_length'], (int)$settings['avg_period_length']);
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nyumbani - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell gradient-bg">
  <main class="home-page">
    <h1>Habari <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>, hebu tuangalie unavyojisikia leo.</h1>
    <p class="subtitle">Taarifa zako zinabaki siri, zinakusaidia kuelewa dalili zako kwa usalama.</p>

    <?php if ($followUp): ?>
    <div class="disclaimer-box" style="border-color:var(--amber);background:#fff7e8;">
      <span>&#128172;</span>
      <div style="flex:1;">
        <strong>Ufuatiliaji: "<?= htmlspecialchars($followUp['title']) ?>"</strong>
        <p style="margin:4px 0 10px;">Uliuliza kuhusu hili siku chache zilizopita. Hali ikoje sasa?</p>
        <form method="POST" action="follow-up.php" style="display:flex;flex-wrap:wrap;gap:8px;">
          <input type="hidden" name="assessment_id" value="<?= (int)$followUp['id'] ?>">
          <button type="submit" name="response" value="saw_doctor" class="badge badge-routine" style="border:none;cursor:pointer;">Nilimuona daktari</button>
          <button type="submit" name="response" value="self_care" class="badge badge-self_care" style="border:none;cursor:pointer;">Nimepona</button>
          <button type="submit" name="response" value="still_sick" class="badge badge-emergency" style="border:none;cursor:pointer;">Bado si vizuri</button>
          <button type="submit" name="response" value="ignored" class="badge" style="border:1px solid var(--border);background:none;cursor:pointer;">Ruka swali hili</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <a href="assessment.php" class="btn-primary btn-block">Anza tathmini ya dalili</a>

    <?php if ($weightPoints || $cycleInfo): ?>
    <section class="section">
      <h2>Muhtasari wako</h2>
      <div class="card-list">
        <?php if ($weightPoints): ?>
          <a href="vitals.php" class="list-card" style="display:block;">
            <strong>&#128202; Uzito - mienendo ya hivi karibuni</strong>
            <div class="chart-box" style="margin-top:8px;"><?= renderSvgLineChart($weightPoints, '#2f80ed', 'kg') ?></div>
          </a>
        <?php endif; ?>
        <?php if ($cycleInfo): ?>
          <a href="calendar.php" class="list-card">
            <div>
              <strong>&#127807; <?= getPhaseLabel($cycleInfo['phase']) ?></strong>
              <div class="muted">
                <?= $cycleInfo['days_until_next'] !== null
                      ? ($cycleInfo['days_until_next'] === 0 ? 'Hedhi inatarajiwa leo/karibuni' : 'Siku ' . $cycleInfo['days_until_next'] . ' hadi mzunguko ujao')
                      : 'Weka tarehe ya hedhi yako ya mwisho' ?>
              </div>
            </div>
            <span>&#8250;</span>
          </a>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="section">
      <h2>Vipengele vingine</h2>
      <div class="card-list">
        <a href="nearby-care.php" class="list-card">
          <div><strong>&#128205; Vituo vya afya karibu</strong><div class="muted">Hospitali, zahanati na maduka ya dawa</div></div>
          <span>&#8250;</span>
        </a>
        <a href="vitals.php" class="list-card">
          <div><strong>&#128202; Vipimo vya Mwili</strong><div class="muted">Uzito, joto, shinikizo la damu</div></div>
          <span>&#8250;</span>
        </a>
        <a href="calendar.php" class="list-card">
          <div><strong>&#127807; Kalenda ya Hedhi</strong><div class="muted">Fuatilia mzunguko wako na upate ushauri</div></div>
          <span>&#8250;</span>
        </a>
        <a href="vaccinations.php" class="list-card">
          <div><strong>&#128137; Chanjo za Watoto</strong><div class="muted">Fuatilia ratiba ya chanjo za mtoto wako</div></div>
          <span><?php if ($vaccAlertCount > 0): ?><span class="badge badge-<?= count($vaccAlerts['overdue']) ? 'emergency' : 'routine' ?>" style="margin-right:6px;"><?= $vaccAlertCount ?></span><?php endif; ?>&#8250;</span>
        </a>
        <a href="articles.php" class="list-card">
          <div><strong>&#128218; Elimu ya Afya</strong><div class="muted">Makala fupi kuhusu magonjwa ya kawaida</div></div>
          <span>&#8250;</span>
        </a>
        <a href="emergency.php" class="list-card" style="border-color:var(--red);">
          <div><strong style="color:var(--red);">&#128680; Dharura (SOS)</strong><div class="muted">Nambari za haraka za dharura</div></div>
          <span>&#8250;</span>
        </a>
      </div>
    </section>

    <?php if ($recent): ?>
    <section class="section">
      <h2>Tathmini za hivi karibuni</h2>
      <div class="card-list">
        <?php foreach ($recent as $a): ?>
          <a href="assessment.php?id=<?= (int)$a['id'] ?>" class="list-card">
            <div>
              <strong><?= htmlspecialchars($a['title']) ?></strong>
              <div class="muted"><?= date('d M Y, H:i', strtotime($a['created_at'])) ?></div>
            </div>
            <span class="badge badge-<?= htmlspecialchars($a['urgency_level']) ?>"><?= htmlspecialchars($a['status'] === 'ongoing' ? 'Inaendelea' : 'Imekamilika') ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="nav-item active">
      <span class="nav-icon">&#8962;</span><span>Nyumbani</span>
    </a>
    <a href="profile.php" class="nav-item">
      <span class="nav-icon">&#9673;</span><span>Wasifu</span>
    </a>
    <a href="settings.php" class="nav-item">
      <span class="nav-icon">&#9881;</span><span>Mipangilio</span>
    </a>
  </nav>
</div>
</body>
</html>
