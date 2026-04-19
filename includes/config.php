<?php
/**
 * config.php — Centralized Configuration
 * 
 * All sensitive values are loaded from .env file.
 * NEVER commit real credentials to source control.
 */

// ── Load .env ──────────────────────────────────────────
$_envFile = __DIR__ . '/../.env';
$_env = [];

if (file_exists($_envFile)) {
    $_lines = file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($_lines as $_line) {
        $_line = trim($_line);
        if ($_line === '' || $_line[0] === '#') continue;
        if (strpos($_line, '=') === false) continue;
        [$_key, $_value] = explode('=', $_line, 2);
        $_env[trim($_key)] = trim($_value, " \t\n\r\0\x0B\"'");
    }
}
unset($_envFile, $_lines, $_line, $_key, $_value);

/**
 * Get an environment variable from .env or system env.
 * Dies with generic message if required and missing.
 */
function env(string $key, ?string $default = null): string {
    global $_env;
    $value = $_env[$key] ?? getenv($key) ?: $default;
    if ($value === null) {
        error_log("FATAL: Required environment variable '$key' is not set.");
        http_response_code(503);
        die('<div style="text-align:center;font-family:Inter,sans-serif;margin-top:20vh;color:#737373;"><h1>Configuration Error</h1><p>A required system configuration is missing. Please contact the administrator.</p></div>');
    }
    return $value;
}

// ── Database ───────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));
define('DB_NAME', env('DB_NAME'));

// ── Site ───────────────────────────────────────────────
define('SITE_DOMAIN', env('SITE_DOMAIN', 'websitewayan.my.id'));
define('MASTER_WA',   env('MASTER_WA', '6281234567890'));

// ── Security ──────────────────────────────────────────
// Separate secret for cookie signing — NOT derived from DB password
define('COOKIE_SECRET', env('COOKIE_SECRET', hash('sha256', 'CHANGE_ME_GENERATE_REAL_SECRET')));

// ── Paths ─────────────────────────────────────────────
define('BASE_DIR',    dirname(__DIR__));
define('UPLOAD_DIR',  BASE_DIR . '/assets/img/produk/');
define('DIST_DIR',    BASE_DIR . '/react-app/dist/assets/');

// ── Error Display ─────────────────────────────────────
// MUST be '0' in production
$_displayErrors = env('DISPLAY_ERRORS', '0');
ini_set('display_errors', $_displayErrors);
ini_set('log_errors', '1');
error_reporting(E_ALL);
unset($_displayErrors);
