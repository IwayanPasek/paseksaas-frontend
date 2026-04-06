<?php
// ═══════════════════════════════════════════════════
//  LOGOUT — Clear session + remember-me cookies
// ═══════════════════════════════════════════════════
session_start();

$_SESSION = [];

// Clear session cookie
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Clear remember-me token (unified secure cookie)
setcookie('remember_token', '', [
    'expires'  => time() - 42000,
    'path'     => '/',
    'secure'   => true,
    'httponly'  => true,
    'samesite' => 'Strict',
]);

// Clear legacy cookies (if any remain from old code)
setcookie('remember_master', '', time() - 42000, '/');
setcookie('remember_tenant', '', time() - 42000, '/');

session_destroy();

header('Location: login.php');
exit;
