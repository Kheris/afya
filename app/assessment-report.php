<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/articles.php';
requireLogin();

$db = getDB();
$userId = currentUserId();
$assessmentId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM assessments WHERE id = ? AND user_id = ?");
$stmt->execute([$assessmentId, $userId]);
$assessment = $stmt->fetch();

if (!$assessment) {
    die('Tathmini haikupatikana.');
}

$conversation = json_decode($assessment['conversation'], true) ?: [];
$user = currentUser();

seedArticlesIfEmpty($db);
$relatedText = $assessment['title'] . ' ' . ($assessment['summary'] ?? '');
$relatedArticles = getRelatedArticles($db, $relatedText, 2);

$urgencyLabels = [
    'emergency' => 'DHARURA',
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
<title>Ripoti ya Tathmini - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
<style>
  .report-page { padding: 24px; max-width: 700px; margin: 0 auto; }
  .report-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--navy); padding-bottom:14px; margin-bottom:20px; }
  .report-header h1 { font-size:22px; margin:0; }
  .report-meta { font-size:13px; color:var(--gray-text); margin-bottom:20px; }
  .report-meta div { margin-bottom:4px; }
  .report-msg { margin-bottom:14px; padding:10px 14px; border-radius:10px; }
  .report-msg.user { background:#f2f4f7; }
  .report-msg.assistant { background:var(--blue-light); }
  .report-msg .role { font-size:11px; font-weight:700; text-transform:uppercase; color:var(--gray-text); margin-bottom:4px; }
  .no-print { margin-bottom: 20px; }
  @media print {
    .no-print, .bottom-nav { display:none !important; }
    body { background:#fff; }
    .app-shell { padding-bottom:0; }
  }
</style>
</head>
<body>
<div class="app-shell">
  <div class="report-page">
    <?php
      $waText = "Ripoti ya Tathmini ya Afya - " . APP_NAME . "\n"
              . "Kwa ajili ya: " . ($assessment['for_label'] ?: 'Mwenyewe') . "\n"
              . "Tarehe: " . date('d M Y, H:i', strtotime($assessment['created_at'])) . "\n"
              . "Kiwango cha uharaka: " . ($urgencyLabels[$assessment['urgency_level']] ?? 'Haijabainika') . "\n\n"
              . "Muhtasari: " . ($assessment['summary'] ?: 'Angalia mazungumzo kamili kwenye ripoti.') . "\n\n"
              . "(Ripoti kamili imetengenezwa na AfyaCheck AI - si uchunguzi rasmi wa kimatibabu.)";
      $waLink = "https://wa.me/?text=" . rawurlencode($waText);
    ?>
    <div class="no-print" style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="assessment.php?id=<?= $assessmentId ?>" class="btn-primary" style="flex:1;text-align:center;min-width:120px;">&#8249; Rudi</a>
      <button onclick="window.print()" class="btn-primary" style="flex:1;min-width:120px;background:var(--navy);border:none;cursor:pointer;">&#128424;&#65039; Chapisha / PDF</button>
      <a href="<?= htmlspecialchars($waLink) ?>" target="_blank" rel="noopener" class="btn-primary" style="flex:1;min-width:120px;background:#25D366;text-align:center;">&#128172; Tuma WhatsApp</a>
    </div>

    <div class="report-header">
      <h1><?= APP_NAME ?></h1>
      <span class="badge badge-<?= htmlspecialchars($assessment['urgency_level']) ?>"><?= $urgencyLabels[$assessment['urgency_level']] ?? 'Haijabainika' ?></span>
    </div>

    <div class="report-meta">
      <div><strong>Kwa ajili ya:</strong> <?= htmlspecialchars($assessment['for_label'] ?: 'Mwenyewe') ?></div>
      <div><strong>Tarehe:</strong> <?= date('d M Y, H:i', strtotime($assessment['created_at'])) ?></div>
      <div><strong>Imeandikishwa na:</strong> <?= htmlspecialchars($user['name']) ?></div>
    </div>

    <h2 style="font-size:16px;">Mazungumzo ya Tathmini</h2>
    <?php foreach ($conversation as $msg): ?>
      <div class="report-msg <?= $msg['role'] === 'user' ? 'user' : 'assistant' ?>">
        <div class="role"><?= $msg['role'] === 'user' ? 'Mtumiaji' : 'AfyaCheck AI' ?></div>
        <div><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
      </div>
    <?php endforeach; ?>

    <?php if ($relatedArticles): ?>
    <div class="no-print">
      <h2 style="font-size:16px;margin-top:24px;">Soma zaidi</h2>
      <div class="card-list">
        <?php foreach ($relatedArticles as $ra): ?>
          <a href="article.php?slug=<?= urlencode($ra['slug']) ?>" class="list-card">
            <div><strong><?= htmlspecialchars($ra['title']) ?></strong><div class="muted"><?= htmlspecialchars($ra['summary']) ?></div></div>
            <span>&#8250;</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <p class="disclaimer-box" style="margin-top:20px;">
      <span>&#9888;&#65039;</span>
      <span>Ripoti hii imetengenezwa na AI kwa lengo la kukuongoza tu. SI uchunguzi rasmi wa kimatibabu (diagnosis). Muone daktari kwa uchunguzi na matibabu rasmi.</span>
    </p>
  </div>
</div>
</body>
</html>
