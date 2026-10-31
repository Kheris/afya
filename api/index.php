<?php
/**
 * Router mmoja kwa kurasa zote za PHP kwenye Vercel.
 * vercel.json inaelekeza /xxx.php  ->  /api/index.php?__p=xxx
 * na hapa tunapakia app/xxx.php. Kurasa zako za PHP hazijabadilika.
 */

// 1) Tafuta njia ya ukurasa unaoombwa
$p = null;
if (isset($_GET['__p']) && is_string($_GET['__p'])) {
    $p = $_GET['__p'];
} else {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $p = preg_replace('/\.php$/', '', trim($path, '/'));
}
unset($_GET['__p'], $_REQUEST['__p']);
if (isset($_SERVER['QUERY_STRING'])) {
    $_SERVER['QUERY_STRING'] = preg_replace('/(^|&)__p=[^&]*/', '', $_SERVER['QUERY_STRING']);
    $_SERVER['QUERY_STRING'] = ltrim($_SERVER['QUERY_STRING'], '&');
}

$p = trim((string)$p, '/');
if ($p === '' || $p === 'api/index') { $p = 'index'; }

// 2) Usalama: herufi salama tu, na kamwe usifungue config/includes/certs moja kwa moja
$blocked = '#^(config|config\.local|includes(/|$)|certs(/|$))#i';
if (!preg_match('#^[A-Za-z0-9_-]+(/[A-Za-z0-9_-]+)*$#', $p) || preg_match($blocked, $p)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$base = realpath(__DIR__ . '/../app');
$file = realpath($base . '/' . $p . '.php');
if ($file === false || strpos($file, $base . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

// 3) Fanya mazingira yafanane na Apache ili kurasa zifanye kazi kama kawaida
chdir(dirname($file));
$_SERVER['SCRIPT_NAME']     = '/' . $p . '.php';
$_SERVER['PHP_SELF']        = '/' . $p . '.php';
$_SERVER['SCRIPT_FILENAME'] = $file;

require $file;
