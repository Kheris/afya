<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cycle.php';
require_once __DIR__ . '/includes/csrf.php';
requireLogin();
requireConsent();

$userId = currentUserId();
$db = getDB();

// Save a period log using the existing database schema.
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        http_response_code(403);
        exit('Ombi limekataliwa (token si sahihi). Rudi nyuma, onyesha upya ukurasa, na ujaribu tena.');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'log_period') {
        $startDate = $_POST['start_date'] ?? '';
        $periodLength = (int)($_POST['period_length'] ?? 5);
        $today = date('Y-m-d');

        $d = DateTime::createFromFormat('Y-m-d', $startDate);
        $validDate = $d && $d->format('Y-m-d') === $startDate;   // tarehe halisi (si "2026-02-31")
        if ($validDate && $startDate <= $today && $periodLength >= 1 && $periodLength <= 14) {
            // Usirudie rekodi ya tarehe ile ile (bonyeza mara mbili / refresh)
            $chk = $db->prepare('SELECT id FROM period_logs WHERE user_id = ? AND start_date = ?');
            $chk->execute([$userId, $startDate]);
            if ($row = $chk->fetch()) {
                $db->prepare('UPDATE period_logs SET period_length = ? WHERE id = ?')->execute([$periodLength, $row['id']]);
            } else {
                $db->prepare('INSERT INTO period_logs (user_id, start_date, period_length) VALUES (?, ?, ?)')
                   ->execute([$userId, $startDate, $periodLength]);
            }
        }
        header('Location: calendar.php');
        exit;
    }

    if ($action === 'delete_log') {
        $db->prepare('DELETE FROM period_logs WHERE id = ? AND user_id = ?')->execute([(int)($_POST['log_id'] ?? 0), $userId]);
        header('Location: calendar.php');
        exit;
    }

    if ($action === 'save_settings') {
        $avgCycle = max(15, min(45, (int)($_POST['avg_cycle_length'] ?? 28)));
        $avgPeriod = max(1, min(14, (int)($_POST['avg_period_length'] ?? 5)));
        $stmt = $db->prepare('UPDATE users SET avg_cycle_length = ?, avg_period_length = ? WHERE id = ?');
        $stmt->execute([$avgCycle, $avgPeriod, $userId]);
        header('Location: calendar.php');
        exit;
    }
}

$stmt = $db->prepare('SELECT avg_cycle_length, avg_period_length FROM users WHERE id = ?');
$stmt->execute([$userId]);
$settings = $stmt->fetch() ?: ['avg_cycle_length' => 28, 'avg_period_length' => 5];
$avgCycle = max(15, (int)$settings['avg_cycle_length']);
$avgPeriod = max(1, (int)$settings['avg_period_length']);

$stmt = $db->prepare('SELECT * FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 1');
$stmt->execute([$userId]);
$lastLog = $stmt->fetch();

$stmt = $db->prepare('SELECT * FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 12');
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

$cycleInfo = $lastLog ? calculateCycleInfo($lastLog['start_date'], $avgCycle, $avgPeriod) : null;

$cycleProgress = 0;
$ovulationDate = null;
$fStart = null;
$fEnd = null;
$daysToOvulation = null;
$cycleDays = [];

if ($cycleInfo) {
    $cycleProgress = min(100, max(0, (($cycleInfo['cycle_day'] - 1) / $avgCycle) * 100));
    $ovulationDay = max(1, $avgCycle - 14);
    $fStartDay = max(1, $ovulationDay - 5);
    $fEndDay = min($avgCycle, $ovulationDay + 1);

    $base = clone $cycleInfo['cycle_start'];
    $ovulationDate = (clone $base)->modify('+' . ($ovulationDay - 1) . ' days');
    $fStart = (clone $base)->modify('+' . ($fStartDay - 1) . ' days');
    $fEnd = (clone $base)->modify('+' . ($fEndDay - 1) . ' days');
    $today = new DateTime('today');
    $daysToOvulation = (int)$today->diff($ovulationDate)->format('%r%a');

    for ($i = 1; $i <= $avgCycle; $i++) {
        $date = (clone $base)->modify('+' . ($i - 1) . ' days');
        $type = 'normal';
        if ($i <= $avgPeriod) $type = 'period';
        elseif ($i === $ovulationDay) $type = 'ovulation';
        elseif ($i >= $fStartDay && $i <= $fEndDay) $type = 'fertile';
        if ($i === $cycleInfo['cycle_day']) $type .= ' today';
        $cycleDays[] = ['day' => $i, 'date' => $date, 'type' => $type];
    }
}

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalenda ya Hedhi - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/theme.js"></script>
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<style>
.calendar-wrap{max-width:760px;margin:0 auto;padding-bottom:30px}.hero-cycle{background:linear-gradient(135deg,#7c3aed,#ec4899);color:#fff;border-radius:24px;padding:24px;margin-bottom:18px;box-shadow:0 12px 30px rgba(124,58,237,.18)}.hero-top{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}.eyebrow{font-size:13px;opacity:.85}.cycle-number{font-size:42px;font-weight:800;line-height:1;margin:7px 0}.phase-pill{display:inline-block;padding:7px 11px;border-radius:999px;background:rgba(255,255,255,.18);font-size:13px}.progress-track{height:10px;background:rgba(255,255,255,.22);border-radius:99px;margin:20px 0 8px;overflow:hidden}.progress-fill{height:100%;background:#fff;border-radius:99px}.progress-meta{display:flex;justify-content:space-between;font-size:12px;opacity:.88}.stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:16px 0}.stat{padding:16px;border:1px solid #e8e8ee;border-radius:18px;background:#fff}.stat b{display:block;font-size:19px;margin-top:5px}.stat small{color:#777}.calendar-card,.panel{background:#fff;border:1px solid #e8e8ee;border-radius:20px;padding:18px;margin:16px 0}.calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:7px}.day{min-height:58px;border-radius:13px;background:#f7f7fa;padding:7px;text-align:center;font-size:11px}.day strong{display:block;font-size:15px;margin-top:4px}.day.period{background:#ffe4ec}.day.fertile{background:#dcfce7}.day.ovulation{background:#fef3c7}.day.today{outline:3px solid #7c3aed;outline-offset:1px}.legend{display:flex;flex-wrap:wrap;gap:12px;margin-top:14px;font-size:12px;color:#666}.dot{width:9px;height:9px;border-radius:50%;display:inline-block;margin-right:5px}.insight{display:flex;gap:12px;align-items:flex-start;padding:14px;border-radius:15px;background:#faf7ff}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.muted2{color:#777;font-size:13px}[data-theme="dark"] .stat,[data-theme="dark"] .calendar-card,[data-theme="dark"] .panel{background:#16213a;border-color:#2a3858;color:#e8ecf5}[data-theme="dark"] .day{background:#1d2a47;color:#e8ecf5}[data-theme="dark"] .day.period{background:#6b2140}[data-theme="dark"] .day.fertile{background:#14532d}[data-theme="dark"] .day.ovulation{background:#78560a}[data-theme="dark"] .insight{background:#1d2a47}[data-theme="dark"] .muted2,[data-theme="dark"] .stat small,[data-theme="dark"] .legend{color:#9fb0cf}.del-btn{background:none;border:none;font-size:18px;cursor:pointer;padding:6px;opacity:.7}.list-card{align-items:center}@media(max-width:600px){.stat-grid{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.cycle-number{font-size:36px}.calendar-grid{gap:5px}.day{min-height:52px;padding:5px 2px}}
</style>
</head>
<body>
<div class="app-shell">
<header class="top-nav"><a href="dashboard.php" class="back-arrow">&#8249;</a><span class="top-title">Kalenda ya Hedhi</span></header>
<main class="home-page calendar-wrap">
<?php if (!$lastLog): ?>
  <div class="panel"><h2>Anza kufuatilia mzunguko wako</h2><p class="muted2">Weka tarehe ya kwanza ya hedhi yako ya mwisho. Baada ya hapo kalenda itaonyesha mzunguko, siku za hedhi, fertile window na makadirio ya ovulation.</p></div>
<?php else: ?>
  <section class="hero-cycle">
    <div class="hero-top"><div><div class="eyebrow">Mzunguko wako wa sasa</div><div class="cycle-number">Siku <?= (int)$cycleInfo['cycle_day'] ?></div><span class="phase-pill"><?= e(getPhaseLabel($cycleInfo['phase'])) ?></span></div><div style="text-align:right"><div class="eyebrow">Mzunguko</div><strong><?= $avgCycle ?> siku</strong></div></div>
    <div class="progress-track"><div class="progress-fill" style="width:<?= $cycleProgress ?>%"></div></div>
    <div class="progress-meta"><span>Mzunguko ulianza <?= e($cycleInfo['cycle_start']->format('d M')) ?></span><span><?= round($cycleProgress) ?>%</span></div>
  </section>

  <div class="stat-grid">
    <div class="stat"><small>Hedhi ijayo</small><b><?= $cycleInfo['days_until_next'] === null ? '—' : ((int)$cycleInfo['days_until_next'] === 0 ? 'Leo' : 'Siku ' . (int)$cycleInfo['days_until_next']) ?></b><small><?= e($cycleInfo['next_period']->format('d M Y')) ?></small></div>
    <div class="stat"><small>Fertile window</small><b><?= e($fStart->format('d M')) ?>–<?= e($fEnd->format('d M')) ?></b><small>makadirio</small></div>
    <div class="stat"><small>Ovulation</small><b><?= e($ovulationDate->format('d M')) ?></b><small><?= $daysToOvulation > 0 ? 'imebaki siku '.$daysToOvulation : ($daysToOvulation === 0 ? 'leo' : 'imepita') ?></small></div>
  </div>

  <section class="calendar-card">
    <h2>Ratiba ya mzunguko</h2>
    <p class="muted2">Rangi zinaonyesha hatua tofauti za mzunguko wako.</p>
    <div class="calendar-grid">
      <?php foreach ($cycleDays as $d): ?>
        <div class="day <?= e($d['type']) ?>"><span>Siku</span><strong><?= $d['day'] ?></strong><small><?= e($d['date']->format('d M')) ?></small></div>
      <?php endforeach; ?>
    </div>
    <div class="legend"><span><i class="dot" style="background:#f9a8d4"></i>Hedhi</span><span><i class="dot" style="background:#86efac"></i>Fertile</span><span><i class="dot" style="background:#fcd34d"></i>Ovulation</span><span><i class="dot" style="background:#7c3aed"></i>Leo</span></div>
  </section>

  <section class="panel">
    <h2>Ushauri wa sasa</h2>
    <?php foreach (getPhaseTips($cycleInfo['phase']) as $tip): ?><div class="insight"><span>&#127807;</span><span><?= e($tip) ?></span></div><?php endforeach; ?>
  </section>
<?php endif; ?>

<section class="panel"><h2>Weka hedhi mpya</h2><form method="POST" class="form-stack"><?= csrfField() ?><input type="hidden" name="action" value="log_period"><label>Tarehe ya mwanzo wa hedhi</label><input type="date" name="start_date" max="<?= date('Y-m-d') ?>" required><label>Siku ngapi ilidumu?</label><input type="number" name="period_length" value="5" min="1" max="14" required><button type="submit" class="btn-primary">Hifadhi hedhi</button></form></section>
<section class="panel"><h2>Mipangilio ya mzunguko</h2><form method="POST" class="form-stack"><?= csrfField() ?><input type="hidden" name="action" value="save_settings"><div class="form-grid"><div><label>Wastani wa mzunguko</label><input type="number" name="avg_cycle_length" value="<?= $avgCycle ?>" min="15" max="45" required></div><div><label>Wastani wa hedhi</label><input type="number" name="avg_period_length" value="<?= $avgPeriod ?>" min="1" max="14" required></div></div><button type="submit" class="btn-primary">Hifadhi mipangilio</button></form></section>
<?php if ($history): ?><section class="panel"><h2>Historia ya hedhi</h2><?php foreach($history as $h): ?><div class="list-card"><div><strong><?= e(date('d M Y', strtotime($h['start_date']))) ?></strong><div class="muted">Ilidumu siku <?= (int)$h['period_length'] ?></div></div><form method="POST" onsubmit="return confirm('Futa rekodi hii?');" style="margin:0"><?= csrfField() ?><input type="hidden" name="action" value="delete_log"><input type="hidden" name="log_id" value="<?= (int)$h['id'] ?>"><button type="submit" class="del-btn" aria-label="Futa">&#128465;</button></form></div><?php endforeach; ?></section><?php endif; ?>
<p class="disclaimer-box"><span>&#128161;</span><span>Makadirio ya fertile window na ovulation si njia ya kuzuia mimba. Mzunguko unaweza kubadilika kutoka mwezi mmoja hadi mwingine.</span></p>
</main>
<nav class="bottom-nav"><a href="dashboard.php" class="nav-item"><span class="nav-icon">&#8962;</span><span>Nyumbani</span></a><a href="profile.php" class="nav-item"><span class="nav-icon">&#9673;</span><span>Wasifu</span></a><a href="settings.php" class="nav-item"><span class="nav-icon">&#9881;</span><span>Mipangilio</span></a></nav>
</div>
</body></html>
