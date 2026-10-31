<?php
// Ukurasa wa uchunguzi wa AI (unahitaji kuingia). Futa faili hii ukimaliza kutatua tatizo.
@ini_set('display_errors', '1');   // ukurasa huu tu, na unaonekana kwa mtumiaji aliyeingia
error_reporting(E_ALL);
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// --- Hatua ya 1: hakikisha faili za toleo jipya zipo na ni sahihi ---
$problems = [];
foreach (['includes/ai.php', 'includes/localchat.php', 'includes/csrf.php', 'includes/compat.php'] as $f) {
    if (!is_file(__DIR__ . '/' . $f)) $problems[] = "Faili halipo: $f";
}
if (!$problems) {
    require_once __DIR__ . '/includes/ai.php';
    require_once __DIR__ . '/includes/csrf.php';
    foreach (['afyaNetworkBlocked', 'afyaClearBlocked', 'afyaGoogleModes', 'afyaModelList', 'callGemini', 'callProxy'] as $fn) {
        if (!function_exists($fn)) $problems[] = "includes/ai.php ni YA ZAMANI (haina function $fn)";
    }
    foreach (['GEMINI_API_KEY', 'GEMINI_MODEL', 'GEMINI_VERTEX_BASE', 'GEMINI_API_BASE', 'APP_VERSION'] as $c) {
        if (!defined($c)) $problems[] = "config.php ni YA ZAMANI (haina $c)";
    }
}
if ($problems):
    $aiFile = __DIR__ . '/includes/ai.php';
?>
<!DOCTYPE html><html lang="sw" translate="no"><head><meta charset="UTF-8"><meta name="google" content="notranslate"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Uchunguzi wa AI</title></head><body style="font-family:sans-serif;max-width:620px;margin:20px auto;padding:0 14px">
<h1>Uchunguzi wa AI - tatizo la faili</h1>
<p style="color:#b91c1c;font-weight:700">Toleo jipya halijapakiwa vizuri kwenye hosting yako:</p>
<ul><?php foreach ($problems as $p) echo '<li>' . htmlspecialchars($p) . '</li>'; ?></ul>
<p>includes/ai.php kwenye hosting: <?= is_file($aiFile) ? filesize($aiFile) . ' bytes, ' . date('Y-m-d H:i', filemtime($aiFile)) : 'haipo' ?>
 (toleo sahihi ni zaidi ya 8000 bytes na lina "afyaNetworkBlocked").</p>
<p><b>Suluhisho:</b> pakia upya folda <code>includes/</code> yote na <code>config.php</code> (au ongeza mistari mipya ya <code>GEMINI_VERTEX_BASE</code>, <code>GEMINI_API_BASE</code>, <code>APP_VERSION</code> kutoka config.php ya zip), chagua "Overwrite". Kisha onyesha upya ukurasa huu.</p>
</body></html><?php
    exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfValid() && ($_POST['do'] ?? '') === 'unblock') {
    afyaClearBlocked();
    header('Location: test-ai.php');
    exit;
}

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
$masked = strlen($key) > 10 ? substr($key, 0, 4) . '...' . substr($key, -4) : '(haijawekwa)';

$run = isset($_GET['run']);
$rows = [];
if ($run) {
    @set_time_limit(90);
    $msgs = [['role' => 'user', 'content' => 'Jibu kwa neno moja tu: sawa']];
    foreach (afyaGoogleModes() as $mode) {
        foreach ([GEMINI_MODEL, 'gemini-2.5-flash'] as $model) {
            $t = microtime(true);
            $r = callGemini($msgs, 'Jibu kwa ufupi.', $model, $mode);
            $rows[] = ['Google ' . $mode . ' / ' . $model, $r['ok'] ? 'OK' : 'FAIL', $r['status'], $r['ok'] ? $r['reply'] : $r['error'], round(microtime(true) - $t, 1)];
            if ($r['ok']) break;
            if (!in_array($r['status'], [400, 404], true)) break;
        }
    }
    if (defined('AI_PROXY_URL') && defined('PROXY_SECRET')) {
        $t = microtime(true);
        $r = callProxy($msgs, 'Jibu kwa ufupi.');
        $rows[] = ['Cloudflare proxy', $r['ok'] ? 'OK' : 'FAIL', $r['status'], $r['ok'] ? $r['reply'] : $r['error'], round(microtime(true) - $t, 1)];
    }
}
$log = (!IS_VERCEL && is_file(__DIR__ . '/includes/debug.log')) ? array_slice(file(__DIR__ . '/includes/debug.log'), -15) : (IS_VERCEL ? ['Kwenye Vercel, kumbukumbu ziko: Project > Logs'] : []);
?>
<!DOCTYPE html><html lang="sw" translate="no"><head><meta charset="UTF-8"><meta name="google" content="notranslate"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Uchunguzi wa AI</title><link rel="stylesheet" href="assets/css/style.css">
<style>table{width:100%;border-collapse:collapse;font-size:13px}td,th{border:1px solid #ccc;padding:6px;text-align:left;vertical-align:top;word-break:break-word}.ok{color:#15803d;font-weight:700}.fail{color:#b91c1c;font-weight:700}pre{white-space:pre-wrap;font-size:12px;background:#f3f4f6;padding:8px;border-radius:8px;color:#111}</style></head>
<body><div class="app-shell"><main class="home-page">
<h1>Uchunguzi wa AI</h1>
<p>Ufunguo: <code><?= h($masked) ?></code> (<?= strpos($key, 'AQ.') === 0 ? 'Vertex express' : 'AI Studio' ?>)</p>
<table>
<tr><td>PHP</td><td><?= h(PHP_VERSION) ?></td></tr>
<tr><td>cURL</td><td><?= function_exists('curl_init') ? '<span class="ok">ipo</span>' : '<span class="fail">HAIPO</span>' ?></td></tr>
<tr><td>mbstring halisi</td><td><?= extension_loaded('mbstring') ? 'ipo' : 'haipo (polyfill inatumika)' ?></td></tr>
<tr><td>Mtandao wa nje umewekwa "umezuiwa"</td><td><?= afyaNetworkBlocked() ? '<span class="fail">NDIYO (dakika 5)</span>' : 'hapana' ?></td></tr>
<tr><td>Models</td><td><?= h(implode(', ', afyaModelList())) ?></td></tr>
</table>
<p style="margin-top:14px"><a class="btn-primary btn-block" href="test-ai.php?run=1">Jaribu AI sasa</a></p>
<?php if (afyaNetworkBlocked()): ?><form method="POST"><?= csrfField() ?><input type="hidden" name="do" value="unblock"><button class="btn-primary btn-block" style="margin-top:8px">Ondoa alama ya "umezuiwa"</button></form><?php endif; ?>
<?php if ($run): ?>
<h2>Matokeo</h2>
<table><tr><th>Njia</th><th>Hali</th><th>Code</th><th>Jibu/kosa</th><th>Sek</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= h($r[0]) ?></td><td class="<?= $r[1] === 'OK' ? 'ok' : 'fail' ?>"><?= h($r[1]) ?></td><td><?= h($r[2]) ?></td><td><?= h($r[3]) ?></td><td><?= h($r[4]) ?></td></tr><?php endforeach; ?></table>
<p class="muted">Code 0 = hosting imezuia muunganisho wa nje (tumia Cloudflare Worker). 401/403 = ufunguo/ruhusa. 404 = jina la model.</p>
<?php endif; ?>
<h2>debug.log (mistari 15 ya mwisho)</h2>
<pre><?= $log ? h(implode('', $log)) : '(tupu)' ?></pre>
</main></div></body></html>
