<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cycle.php';
requireLogin();
requireConsent();

$userId = currentUserId();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'log_period') {
    $startDate = $_POST['start_date'] ?? null;
    $periodLength = max(1, min(14, (int)($_POST['period_length'] ?? 5)));
    if ($startDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $stmt = $db->prepare("INSERT INTO period_logs (user_id, start_date, period_length) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $startDate, $periodLength]);
    }
    header('Location: cycle-calendar.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    $avgCycle = max(15, min(45, (int)($_POST['avg_cycle_length'] ?? 28)));
    $avgPeriod = max(1, min(14, (int)($_POST['avg_period_length'] ?? 5)));
    $stmt = $db->prepare("UPDATE users SET avg_cycle_length = ?, avg_period_length = ? WHERE id = ?");
    $stmt->execute([$avgCycle, $avgPeriod, $userId]);
    header('Location: cycle-calendar.php'); exit;
}

$stmt = $db->prepare("SELECT avg_cycle_length, avg_period_length FROM users WHERE id = ?");
$stmt->execute([$userId]);
$settings = $stmt->fetch() ?: ['avg_cycle_length' => 28, 'avg_period_length' => 5];
$avgCycle = (int)$settings['avg_cycle_length'];
$avgPeriod = (int)$settings['avg_period_length'];

$stmt = $db->prepare("SELECT * FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 1");
$stmt->execute([$userId]);
$lastLog = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 12");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

$cycleInfo = $lastLog ? calculateCycleInfo($lastLog['start_date'], $avgCycle, $avgPeriod) : null;
$today = new DateTime('today');
$phaseLabel = $cycleInfo ? getPhaseLabel($cycleInfo['phase']) : '';
$phaseShort = $cycleInfo ? getPhaseShortLabel($cycleInfo['phase']) : '';
$phaseAdvice = $cycleInfo ? getPhaseHeadline($cycleInfo['phase']) : '';
$progress = $cycleInfo ? min(100, max(0, round(($cycleInfo['cycle_day'] / $avgCycle) * 100))) : 0;
$daysLeft = $cycleInfo ? max(0, $cycleInfo['days_until_next']) : null;
$fertile = $cycleInfo && $cycleInfo['fertile_start'] <= $cycleInfo['fertile_end'] ? $cycleInfo['fertile_start']->format('d M') . ' – ' . $cycleInfo['fertile_end']->format('d M') : '—';
$ovulation = $cycleInfo ? $cycleInfo['ovulation_date']->format('d M') : '—';
$periodEnd = $cycleInfo ? (clone $cycleInfo['current_period_start'])->modify('+' . ($avgPeriod - 1) . ' days') : null;
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kalenda ya Hedhi - <?= htmlspecialchars(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/theme.js"></script>
<style>
.cycle-hero{background:linear-gradient(135deg,#171525,#30234a);color:#fff;border-radius:24px;padding:22px;margin-bottom:18px;overflow:hidden;position:relative}.cycle-hero:after{content:'';position:absolute;width:180px;height:180px;border-radius:50%;right:-70px;top:-80px;background:rgba(255,255,255,.07)}
.cycle-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}.eyebrow{font-size:12px;opacity:.7;text-transform:uppercase;letter-spacing:1px}.cycle-number{font-size:42px;font-weight:800;line-height:1;margin-top:5px}.cycle-phase{margin-top:8px;font-size:14px;opacity:.9}.phase-pill{padding:8px 11px;border-radius:999px;background:rgba(255,255,255,.12);font-size:12px;white-space:nowrap}.progress-wrap{margin-top:22px}.progress-label{display:flex;justify-content:space-between;font-size:12px;opacity:.75;margin-bottom:8px}.progress-track{height:9px;background:rgba(255,255,255,.13);border-radius:99px;overflow:hidden}.progress-fill{height:100%;width:<?= $progress ?>%;background:linear-gradient(90deg,#f7a8c4,#dcb7ff);border-radius:99px}.today-marker{font-size:11px;margin-top:8px;opacity:.7}.insight{margin-top:18px;background:rgba(255,255,255,.09);padding:12px 14px;border-radius:15px;font-size:13px;line-height:1.45}
.stats-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:16px 0}.stat-card{background:var(--card-bg,#fff);border:1px solid rgba(127,127,127,.14);border-radius:18px;padding:15px}.stat-icon{font-size:20px}.stat-label{font-size:11px;opacity:.6;margin-top:7px}.stat-value{font-size:16px;font-weight:750;margin-top:3px}.stat-note{font-size:11px;opacity:.55;margin-top:3px}
.timeline{display:grid;grid-template-columns:repeat(7,1fr);gap:5px;margin-top:12px}.day-dot{border-radius:12px;padding:8px 2px;text-align:center;font-size:10px;border:1px solid rgba(127,127,127,.12)}.day-dot b{display:block;font-size:13px;margin-top:3px}.day-dot.period{background:#ffe3ec}.day-dot.fertile{background:#e6f8ed}.day-dot.ovulation{background:#fff0cf}.day-dot.today{outline:2px solid #7d5cff;outline-offset:1px}.legend{display:flex;gap:10px;flex-wrap:wrap;font-size:10px;opacity:.7;margin-top:10px}.legend span:before{content:'';display:inline-block;width:8px;height:8px;border-radius:3px;margin-right:4px;background:#ddd}.legend .l-period:before{background:#ffe3ec}.legend .l-fertile:before{background:#e6f8ed}.legend .l-ovu:before{background:#fff0cf}.section-toggle{cursor:pointer}.small-note{font-size:11px;opacity:.6;line-height:1.5}.history-row{display:flex;justify-content:space-between;align-items:center}.history-date{font-weight:700}.history-badge{font-size:11px;padding:6px 9px;border-radius:99px;background:rgba(125,92,255,.1)}
@media(min-width:600px){.stats-grid{grid-template-columns:repeat(4,1fr)}}
</style>
</head>
<body>
<div class="app-shell">
<header class="top-nav"><a href="dashboard.php" class="back-arrow">&#8249;</a><span class="top-title">Kalenda ya Hedhi</span></header>
<main class="home-page">
<?php if (!$lastLog): ?>
<section class="cycle-hero"><div class="eyebrow">Anza hapa</div><div style="font-size:25px;font-weight:800;margin-top:5px">Jua mzunguko wako kwa urahisi</div><div class="insight">Weka tarehe ya kuanza hedhi yako ya mwisho. Tutakutengenezea muhtasari wa siku za hedhi, dirisha la uwezekano wa kupata mimba na makadirio ya hedhi ijayo.</div></section>
<?php else: ?>
<section class="cycle-hero">
<div class="cycle-top"><div><div class="eyebrow">Leo • <?= $today->format('d M Y') ?></div><div class="cycle-number">Siku <?= (int)$cycleInfo['cycle_day'] ?></div><div class="cycle-phase"><?= htmlspecialchars($phaseLabel) ?></div></div><div class="phase-pill"><?= htmlspecialchars($phaseShort) ?></div></div>
<div class="progress-wrap"><div class="progress-label"><span>Maendeleo ya mzunguko</span><span><?= $progress ?>%</span></div><div class="progress-track"><div class="progress-fill"></div></div><div class="today-marker">Mwanzo: <?= date('d M Y', strtotime($lastLog['start_date'])) ?> · Hedhi ijayo: <?= $cycleInfo['next_period']->format('d M Y') ?></div></div>
<div class="insight"><strong><?= htmlspecialchars($phaseAdvice) ?></strong><br><?= $daysLeft === 0 ? 'Hedhi inayofuata inakadiriwa kuwa leo.' : 'Takriban siku ' . $daysLeft . ' zimebaki hadi hedhi inayofuata.' ?></div>
</section>

<div class="stats-grid">
<div class="stat-card"><div class="stat-icon">🩸</div><div class="stat-label">Hedhi</div><div class="stat-value">Siku 1–<?= $avgPeriod ?></div><div class="stat-note"><?= $lastLog['start_date'] ?></div></div>
<div class="stat-card"><div class="stat-icon">🌱</div><div class="stat-label">Fertile window</div><div class="stat-value"><?= htmlspecialchars($fertile) ?></div><div class="stat-note">Makadirio</div></div>
<div class="stat-card"><div class="stat-icon">✨</div><div class="stat-label">Ovulation</div><div class="stat-value"><?= htmlspecialchars($ovulation) ?></div><div class="stat-note">Makadirio</div></div>
<div class="stat-card"><div class="stat-icon">📅</div><div class="stat-label">Hedhi ijayo</div><div class="stat-value"><?= $daysLeft === 0 ? 'Leo' : $daysLeft . ' siku' ?></div><div class="stat-note"><?= $cycleInfo['next_period']->format('d M Y') ?></div></div>
</div>

<section class="section"><h2>Mzunguko wako kwa mtazamo</h2><div class="timeline">
<?php for($i=1;$i<=$avgCycle;$i++): $cls=[]; if($i<=$avgPeriod)$cls[]='period'; if($i>=$cycleInfo['fertile_start_day']&&$i<=$cycleInfo['fertile_end_day'])$cls[]='fertile'; if($i===$cycleInfo['ovulation_day'])$cls[]='ovulation'; if($i===$cycleInfo['cycle_day'])$cls[]='today'; ?>
<div class="day-dot <?= implode(' ',$cls) ?>"><span>S<?= $i ?></span><b><?= $i===$cycleInfo['cycle_day']?'●':'' ?></b></div>
<?php endfor; ?>
</div><div class="legend"><span class="l-period">Hedhi</span><span class="l-fertile">Fertile window</span><span class="l-ovu">Ovulation</span></div></section>

<section class="section"><h2>Ushauri wa leo</h2><div class="tips-list"><?php foreach (getPhaseTips($cycleInfo['phase']) as $tip): ?><div class="tip-item"><span>🌿</span><p><?= htmlspecialchars($tip) ?></p></div><?php endforeach; ?></div></section>
<?php endif; ?>

<section class="section"><h2>Weka hedhi mpya</h2><form method="POST" class="form-stack"><input type="hidden" name="action" value="log_period"><label>Tarehe ya kuanza hedhi</label><input type="date" name="start_date" max="<?= date('Y-m-d') ?>" required><label>Ilidumu siku ngapi?</label><input type="number" name="period_length" value="<?= $avgPeriod ?>" min="1" max="14" required><button type="submit" class="btn-primary">Hifadhi hedhi</button></form></section>

<section class="section"><h2>Mipangilio ya mzunguko</h2><form method="POST" class="form-stack"><input type="hidden" name="action" value="save_settings"><label>Wastani wa mzunguko (siku)</label><input type="number" name="avg_cycle_length" value="<?= $avgCycle ?>" min="15" max="45" required><label>Wastani wa hedhi (siku)</label><input type="number" name="avg_period_length" value="<?= $avgPeriod ?>" min="1" max="14" required><button type="submit" class="btn-primary">Sasisha mzunguko</button></form></section>

<?php if ($history): ?><section class="section"><h2>Historia ya hedhi</h2><div class="card-list"><?php foreach($history as $h): ?><div class="list-card history-row"><div><div class="history-date"><?= date('d M Y',strtotime($h['start_date'])) ?></div><div class="muted">Ilidumu siku <?= (int)$h['period_length'] ?></div></div><span class="history-badge">Imehifadhiwa</span></div><?php endforeach; ?></div></section><?php endif; ?>
<p class="disclaimer-box" style="margin-top:24px"><span>💡</span><span>Makadirio ya fertile window na ovulation si njia ya uhakika ya kuzuia mimba. Mizunguko inaweza kubadilika mwezi hadi mwezi. Kwa maumivu makali, kutokwa damu isivyo kawaida au dalili zinazokusumbua, tafuta ushauri wa mtaalamu wa afya.</span></p>
</main>
<nav class="bottom-nav"><a href="dashboard.php" class="nav-item"><span class="nav-icon">⌂</span><span>Nyumbani</span></a><a href="profile.php" class="nav-item"><span class="nav-icon">◎</span><span>Wasifu</span></a><a href="settings.php" class="nav-item"><span class="nav-icon">⚙</span><span>Mipangilio</span></a></nav>
</div>
</body></html>
