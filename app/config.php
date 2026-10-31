<?php
// =====================================================
// AfyaCheck - Mipangilio Mikuu (Config)
// -----------------------------------------------------
// SIRI HAZIWEKWI HAPA TENA. Zinasomwa kutoka "Environment Variables"
// (Vercel > Project > Settings > Environment Variables).
// Kwa kompyuta yako (local) unaweza kutengeneza faili app/config.local.php
// (haipandi GitHub) inayorudisha array, mfano:
//   <?php return ['DB_HOST'=>'127.0.0.1','DB_NAME'=>'afya','DB_USER'=>'root','DB_PASS'=>''];
// =====================================================

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        static $local = null;
        if ($local === null) {
            $f = __DIR__ . '/config.local.php';
            $local = is_file($f) ? (array)(include $f) : [];
        }
        $v = getenv($key);
        if ($v !== false && $v !== '') return $v;
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        return $local[$key] ?? $default;
    }
}

// Je, tunaendeshwa kwenye Vercel? (filesystem ni read-only, isipokuwa /tmp)
define('IS_VERCEL', (bool)(env('VERCEL') || env('VERCEL_ENV')));

// --- Database (MySQL ya nje: TiDB Cloud / Aiven / Railway / nk.) ---
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', (int)env('DB_PORT', 3306));
define('DB_NAME', env('DB_NAME', ''));
define('DB_USER', env('DB_USER', ''));
define('DB_PASS', env('DB_PASS', ''));
define('DB_SSL',  in_array(strtolower((string)env('DB_SSL', '0')), ['1', 'true', 'yes', 'on'], true)); // weka 1 kwa TiDB/Aiven

// --- Gemini API ---
define('GEMINI_API_KEY', env('GEMINI_API_KEY', ''));
define('GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-3.8-flash'));
define('GEMINI_FALLBACK_MODEL', env('GEMINI_FALLBACK_MODEL', 'gemini-3.5-flash'));
define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta');   // AI Studio (ufunguo wa AIza...)
define('GEMINI_VERTEX_BASE', 'https://aiplatform.googleapis.com/v1');           // Vertex AI express (ufunguo wa AQ....)

// --- HIARI: Cloudflare Worker proxy (kwenye Vercel kwa kawaida HAIHITAJIKI) ---
if (env('AI_PROXY_URL') && env('PROXY_SECRET')) {
    define('AI_PROXY_URL', env('AI_PROXY_URL'));
    define('PROXY_SECRET', env('PROXY_SECRET'));
}

// --- Mipangilio mingine ---
define('APP_NAME', 'AfyaCheck');
define('APP_VERSION', '10.3.0');            // ongeza kila unapotoa update (hubadilisha cache ya PWA)
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // siku 7
define('APP_DEBUG', in_array(strtolower((string)env('APP_DEBUG', '0')), ['1', 'true', 'yes', 'on'], true));

// Folda inayoweza kuandikwa: Vercel = /tmp, hosting ya kawaida = includes/
define('AFYA_WRITABLE_DIR', IS_VERCEL ? sys_get_temp_dir() : __DIR__ . '/includes');

// Polyfill ya mb_* kwa hosting zisizo na mbstring
require_once __DIR__ . '/includes/compat.php';

error_reporting(E_ALL);
ini_set('display_errors', '0'); // usiwahi kuweka '1' kwenye tovuti halisi
ini_set('log_errors', '1');
if (!IS_VERCEL) {
    ini_set('error_log', __DIR__ . '/includes/debug.log');
} // kwenye Vercel makosa huenda kwenye Logs za project (stderr)

// Session zinahifadhiwa kwenye database (serverless haina disk ya kudumu)
require_once __DIR__ . '/includes/session.php';
