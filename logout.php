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

// Clear remember-me cookies
setcookie('remember_master', '', time() - 42000, '/');
setcookie('remember_tenant', '', time() - 42000, '/');

session_destroy();

header('Location: login.php');
exit;
