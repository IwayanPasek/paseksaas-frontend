<?php
// ═══════════════════════════════════════════════════
//  FIX ADMIN — Master password reset tool (DEV ONLY)
//  WARNING: Remove or restrict in production!
// ═══════════════════════════════════════════════════

// Basic IP restriction — only allow localhost
$allowed = ['127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed)) {
    http_response_code(403);
    die('<h1>403 Forbidden</h1><p>Tool ini hanya bisa diakses dari server lokal.</p>');
}

require_once __DIR__ . '/includes/db.php';

try {
    $pdo = getDB();
    $pdo->exec('TRUNCATE TABLE master_admin');

    $username = 'pasek';
    $password = 'pasek';
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('INSERT INTO master_admin (username, password) VALUES (?, ?)');
    $stmt->execute([$username, $hash]);

    echo "<div style='font-family:Inter,sans-serif;max-width:400px;margin:15vh auto;text-align:center;'>";
    echo "<h2>✅ Berhasil!</h2>";
    echo "<p>User <b>$username</b> didaftarkan ulang.</p>";
    echo "<p style='color:#737373;font-size:12px;'>Hash: <code>$hash</code></p>";
    echo "<a href='login.php' style='color:#171717;'>→ Ke Halaman Login</a>";
    echo "</div>";
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
