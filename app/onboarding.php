<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dob     = $_POST['dob'] ?: null;
    $sex     = $_POST['sex'] ?? 'not_specified';
    $smoker  = $_POST['smoker'] ?? 'unknown';
    $diabetes = $_POST['diabetes'] ?? 'unknown';

    $stmt = getDB()->prepare("UPDATE users SET date_of_birth = ?, sex_at_birth = ?, is_smoker = ?, has_diabetes = ? WHERE id = ?");
    $stmt->execute([$dob, $sex, $smoker, $diabetes, currentUserId()]);

    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Taarifa za Kiafya - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php require __DIR__ . '/includes/pwa-head.php'; ?>
<script src="assets/js/theme.js"></script>
</head>
<body>
<div class="app-shell">
  <main class="auth-page">
    <h1>Twambie kidogo kukuhusu</h1>
    <p class="subtitle">Taarifa hizi zinasaidia AI kutoa tathmini sahihi zaidi. Unaweza kuruka sehemu yoyote.</p>

    <form method="POST" class="form-stack">
      <label>Tarehe ya kuzaliwa</label>
      <input type="date" name="dob">

      <label>Jinsia uliyozaliwa nayo</label>
      <select name="sex">
        <option value="not_specified">Sipendi kusema</option>
        <option value="female">Mwanamke</option>
        <option value="male">Mwanaume</option>
      </select>

      <label>Je, unavuta sigara?</label>
      <select name="smoker">
        <option value="unknown">Sijui/Sipendi kusema</option>
        <option value="yes">Ndiyo</option>
        <option value="no">Hapana</option>
      </select>

      <label>Je, una kisukari (diabetes)?</label>
      <select name="diabetes">
        <option value="unknown">Sijui/Sipendi kusema</option>
        <option value="yes">Ndiyo</option>
        <option value="no">Hapana</option>
      </select>

      <button type="submit" class="btn-primary">Endelea</button>
    </form>
    <p class="switch-link"><a href="dashboard.php">Ruka hatua hii</a></p>
  </main>
</div>
</body>
</html>
