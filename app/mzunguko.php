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
    header('Location: mzunguko.php');
    exit;
}

// -- Hifadhi mipangilio ya mzunguko --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $avgCycle  = (int)($_POST['avg_cycle_length'] ?? 28);
    $avgPeriod = (int)($_POST['avg_period_length'] ?? 5);
    $stmt = $db->prepare("UPDATE users SET avg_cycle_length = ?, avg_period_length = ? WHERE id = ?");
    $stmt->execute([$avgCycle, $avgPeriod, $userId]);
    header('Location: mzunguko.php');
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
      <section class="mc-empty-card">
        <h2>Start your cycle</h2>
        <p>Bado hujaweka tarehe ya hedhi yako ya mwisho. Weka hapa chini ili tuanze kukadiria mzunguko wako.</p>
      </section>
    <?php else: ?>
      <?php
        $cycleDay = (int)$cycleInfo['cycle_day'];
        $cycleLength = max(1, (int)$settings['avg_cycle_length']);
        $periodLength = max(1, (int)$settings['avg_period_length']);
        $progressPct = min(100, max(0, (($cycleDay - 1) / max(1, $cycleLength - 1)) * 100));
        $ovulationDay = max(1, $cycleLength - 14);
        $nextPeriodDay = $cycleLength;
        $daysLeft = max(0, $cycleLength - $cycleDay);
        $phaseLabel = $cycleInfo['phase'] === 'menstrual' ? 'Menstrual Days' : ucfirst(getPhaseLabel($cycleInfo['phase']));
        $fertileStart = $cycleInfo['fertile_start'];
        $fertileEnd = $cycleInfo['fertile_end'];
      ?>

      <section class="mc-hero">
        <div class="mc-hero-top">
          <div>
            <span class="mc-eyebrow">Your current cycle</span>
            <div class="mc-day">Day <?= $cycleDay ?></div>
            <span class="mc-phase-pill"><?= htmlspecialchars($phaseLabel) ?></span>
          </div>
          <div class="mc-frequency"><span>Frequency</span><strong><?= $cycleLength ?> days</strong></div>
        </div>
        <?php
          $mcMascotText = $cycleInfo['phase'] === 'menstrual' ? 'Pumzika na ujitunze' : ($cycleInfo['phase'] === 'follicular' ? 'Unaendelea vizuri' : ($cycleInfo['phase'] === 'ovulation' ? 'Mwili wako unaendelea' : 'Karibu kumaliza mzunguko'));
          $mascotAccent = $cycleInfo['phase'] === 'menstrual' ? '#ff8dbd' : ($cycleInfo['phase'] === 'follicular' ? '#7edc9a' : ($cycleInfo['phase'] === 'ovulation' ? '#ffd45c' : '#b991ff'));
        ?>
        <div class="mc-progress-wrap">
          <div class="mc-progress-track" aria-label="Cycle progress <?= number_format($progressPct,0) ?> percent">
            <div class="mc-progress-fill" style="width:<?= number_format($progressPct,2,'.','') ?>%"></div>
            <div class="mc-progress-mascot" style="left:<?= number_format($progressPct,2,'.','') ?>%;--mascot-accent:<?= $mascotAccent ?>" title="<?= htmlspecialchars($mcMascotText) ?>" aria-label="<?= htmlspecialchars($mcMascotText) ?>">
              <span class="mc-mascot-bob">
                <svg viewBox="0 0 54 54" role="img" aria-hidden="true">
                  <circle cx="27" cy="27" r="23" fill="rgba(255,255,255,.96)"/>
                  <path d="M18 31c0-7 4-12 9-12s9 5 9 12" fill="var(--mascot-accent)"/>
                  <circle cx="22" cy="25" r="2" fill="#4b355b"/><circle cx="32" cy="25" r="2" fill="#4b355b"/>
                  <path d="M23 30c2 2 6 2 8 0" fill="none" stroke="#4b355b" stroke-width="1.7" stroke-linecap="round"/>
                  <path d="M17 18l-3-4 6 2M37 18l3-4-6 2" fill="var(--mascot-accent)"/>
                  <circle cx="15" cy="33" r="2.5" fill="var(--mascot-accent)" opacity=".75"/><circle cx="39" cy="33" r="2.5" fill="var(--mascot-accent)" opacity=".75"/>
                </svg>
              </span>
            </div>
          </div>
        </div>
        <div class="mc-progress-meta"><span>Started <?= date('d M', strtotime($lastLog['start_date'])) ?></span><strong><?= number_format($progressPct,0) ?>%</strong></div>
      </section>

      <section class="mc-stats-grid">
        <div class="mc-stat"><span>Next period</span><strong>Day <?= $nextPeriodDay ?></strong><small><?= $cycleInfo['next_period']->format('d M Y') ?></small></div>
        <div class="mc-stat"><span>Fertile Window</span><strong><?= $fertileStart ? $fertileStart->format('d M') . '–' . $fertileEnd->format('d M') : '—' ?></strong><small>Projections</small></div>
        <div class="mc-stat"><span>Ovulation</span><strong><?= (new DateTime($lastLog['start_date'], new DateTimeZone('Africa/Dar_es_Salaam')))->modify('+' . ($ovulationDay - 1) . ' days')->format('d M') ?></strong><small><?= $daysLeft ?> days left</small></div>
      </section>

      <section class="mc-panel">
        <h2>Cycle schedule</h2>
        <p class="mc-panel-subtitle">The colors reflect the different stages of your cycle.</p>
        <div class="mc-days">
          <?php for ($d=1; $d <= $cycleLength; $d++):
            $date = clone $lastLogDate = new DateTime($lastLog['start_date'], new DateTimeZone('Africa/Dar_es_Salaam'));
            $date->modify('+' . ($d-1) . ' days');
            $cls='';
            if ($d <= $periodLength) $cls='menstrual';
            elseif ($d >= max(1,$ovulationDay-5) && $d < $ovulationDay) $cls='fertile';
            elseif ($d === $ovulationDay) $cls='ovulation';
            if ($d === $cycleDay) $cls .= ' today';
          ?>
            <div class="mc-day-cell <?= trim($cls) ?>"><strong>Day <?= $d ?></strong><small><?= $date->format('d M') ?></small></div>
          <?php endfor; ?>
        </div>
        <div class="mc-legend"><span><i class="dot menstrual"></i>Menstruation</span><span><i class="dot fertile"></i>Fertile</span><span><i class="dot ovulation"></i>Ovulation</span><span><i class="dot today"></i>Today</span></div>
      </section>

      <section class="mc-panel">
        <h2>Current advice</h2>
        <div class="mc-advice-list">
          <?php foreach (array_slice(getPhaseTips($cycleInfo['phase']), 0, 4) as $tip): ?>
            <div class="mc-advice"><span>🌿</span><p><?= htmlspecialchars($tip) ?></p></div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="mc-panel mc-form-panel">
      <h2>Set up a new period</h2>
      <form method="POST" class="form-stack">
        <input type="hidden" name="action" value="log_period">
        <label>Date of onset of menstruation</label>
        <input type="date" name="start_date" max="<?= date('Y-m-d') ?>" required>
        <label>How many days did it last?</label>
        <input type="number" name="period_length" value="5" min="1" max="14" required>
        <button type="submit" class="btn-primary">Save your period</button>
      </form>
    </section>

    <section class="mc-panel mc-form-panel">
      <h2>Mipangilio ya mzunguko</h2>
      <form method="POST" class="form-stack mc-settings-form">
        <input type="hidden" name="action" value="save_settings">
        <div><label>Wastani wa mzunguko</label><input type="number" name="avg_cycle_length" value="<?= (int)$settings['avg_cycle_length'] ?>" min="15" max="45" required></div>
        <div><label>Wastani wa hedhi</label><input type="number" name="avg_period_length" value="<?= (int)$settings['avg_period_length'] ?>" min="1" max="14" required></div>
        <button type="submit" class="btn-primary">Hifadhi mipangilio</button>
      </form>
    </section>

    <?php if ($history): ?>
    <section class="mc-panel">
      <h2>Historia ya hedhi</h2>
      <div class="card-list">
        <?php foreach ($history as $h): ?>
          <div class="list-card"><div><strong><?= date('d M Y', strtotime($h['start_date'])) ?></strong><div class="muted">Ilidumu siku <?= (int)$h['period_length'] ?></div></div></div>
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
