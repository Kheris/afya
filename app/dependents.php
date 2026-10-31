<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireConsent();

$db = getDB();
$userId = currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob  = $_POST['dob'] ?: null;
    $sex  = $_POST['sex'] ?? 'not_specified';
    $rel  = trim($_POST['relationship'] ?? '');

    if ($name !== '') {
        $stmt = $db->prepare("INSERT INTO dependents (user_id, name, date_of_birth, sex_at_birth, relationship) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $dob, $sex, $rel]);
    }
    header('Location: dependents.php');
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM dependents WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET['delete'], $userId]);
    header('Location: dependents.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM dependents WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$dependents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Familia Yangu - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <header class="top-nav">
    <a href="profile.php" class="back-arrow">&#8249;</a>
    <span class="top-title">Familia Yangu</span>
  </header>

  <main class="home-page">
    <p class="subtitle">Ongeza wanafamilia wako (watoto, wazazi, mwenzi) ili uweze kufanya tathmini ya dalili kwa niaba yao pia.</p>

    <?php if ($dependents): ?>
      <div class="card-list">
        <?php foreach ($dependents as $d): ?>
          <div class="list-card">
            <div>
              <strong><?= htmlspecialchars($d['name']) ?></strong>
              <div class="muted"><?= htmlspecialchars($d['relationship'] ?: 'Mwanafamilia') ?><?= $d['date_of_birth'] ? ' &middot; ' . date('d M Y', strtotime($d['date_of_birth'])) : '' ?></div>
            </div>
            <a href="?delete=<?= (int)$d['id'] ?>" onclick="return confirm('Una uhakika unataka kumfuta?')" style="color:var(--red);text-decoration:none;">&times;</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section class="section">
      <h2>Ongeza mwanafamilia</h2>
      <form method="POST" class="form-stack">
        <label>Jina</label>
        <input type="text" name="name" placeholder="Jina la mwanafamilia" required>

        <label>Uhusiano</label>
        <input type="text" name="relationship" placeholder="Mfano: Mtoto, Mzazi, Mwenzi">

        <label>Tarehe ya kuzaliwa (hiari)</label>
        <input type="date" name="dob">

        <label>Jinsia aliyozaliwa nayo</label>
        <select name="sex">
          <option value="not_specified">Sipendi kusema</option>
          <option value="female">Mwanamke</option>
          <option value="male">Mwanaume</option>
        </select>

        <button type="submit" class="btn-primary">Ongeza</button>
      </form>
    </section>
  </main>
</div>
</body>
</html>
