<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = currentUser();
$db = getDB();

// Kama tayari amekubali, ruka moja kwa moja
$stmt = $db->prepare("SELECT terms_accepted_at FROM users WHERE id = ?");
$stmt->execute([currentUserId()]);
$row = $stmt->fetch();
if ($row && $row['terms_accepted_at']) {
    header('Location: onboarding.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acceptedTerms = isset($_POST['accept_terms']);
    $consentData   = isset($_POST['consent_data']);
    $consentResearch = isset($_POST['consent_research']) ? 1 : 0;
    $consentUsage     = isset($_POST['consent_usage']) ? 1 : 0;

    if (!$acceptedTerms || !$consentData) {
        $error = 'Lazima ukubali masharti mawili ya kwanza ili kuendelea kutumia AfyaCheck.';
    } else {
        $stmt = $db->prepare("UPDATE users SET terms_accepted_at = NOW(), consent_research = ?, consent_usage = ? WHERE id = ?");
        $stmt->execute([$consentResearch, $consentUsage, currentUserId()]);
        header('Location: onboarding.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Idhini - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <main class="auth-page">
    <h1>Thibitisha uko tayari</h1>
    <p class="subtitle">Tafadhali soma na ukubali maelezo yafuatayo kabla ya kuendelea. Unaweza kubadilisha idhini yako wakati wowote kwenye Mipangilio.</p>

    <?php if ($error): ?><div class="alert-error"><p><?= htmlspecialchars($error) ?></p></div><?php endif; ?>

    <form method="POST" class="form-stack">
      <label class="checkbox-row">
        <input type="checkbox" name="accept_terms" required>
        <span>Nakubali <strong>Masharti na Matumizi</strong> ya AfyaCheck, na ninathibitisha kuwa nina umri wa miaka 16 au zaidi.</span>
      </label>

      <label class="checkbox-row">
        <input type="checkbox" name="consent_data" required>
        <span>Nakubali AfyaCheck kutumia taarifa za kiafya ninazoshiriki kwa hiari yangu ili kunipatia tathmini na ushauri wa kiafya (kupitia AI).</span>
      </label>

      <label class="checkbox-row">
        <input type="checkbox" name="consent_research">
        <span>(Hiari) Ninakubali kualikwa kushiriki tafiti zinazolenga kuboresha huduma za afya.</span>
      </label>

      <label class="checkbox-row">
        <input type="checkbox" name="consent_usage">
        <span>(Hiari) Ninakubali kushiriki data ya matumizi ya app (tracking) kusaidia kuboresha AfyaCheck.</span>
      </label>

      <button type="submit" class="btn-primary">Endelea</button>
    </form>

    <p class="disclaimer-box" style="margin-top:20px;">
      <span>&#9888;&#65039;</span>
      <span>AfyaCheck si mbadala wa ushauri wa kitaalamu wa daktari. Tathmini zote ni za kukuongoza tu, si uchunguzi wa uhakika.</span>
    </p>
  </main>
</div>
</body>
</html>
