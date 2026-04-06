<?php
// ═══════════════════════════════════════════════════
//  MASTER CENTER — Super Admin (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/vite.php';

requireMaster();
$pdo = getDB();

// ── Register New Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_tenant'])) {
    if (!csrfVerify()) {
        header('Location: master.php?status=error&msg=' . urlencode('Token CSRF tidak valid.'));
        exit;
    }

    $nama = htmlspecialchars(trim($_POST['nama_toko']));
    $sub  = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_POST['subdomain']));
    $pass = password_hash($_POST['password_toko'], PASSWORD_BCRYPT);
    $wa   = preg_replace('/[^0-9]/', '', $_POST['kontak_wa']);
    $kb   = $_POST['knowledge_base'];

    try {
        $stmt = $pdo->prepare('INSERT INTO toko (nama_toko, subdomain, password, kontak_wa, knowledge_base) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nama, $sub, $pass, $wa, $kb]);
        header('Location: master.php?status=success&msg=' . urlencode($nama));
    } catch (PDOException $e) {
        header('Location: master.php?status=error&msg=' . urlencode('Subdomain sudah digunakan atau terjadi error.'));
    }
    exit;
}

// ── Fetch tenants ──
$tenants = $pdo->query('SELECT id_toko, nama_toko, subdomain, kontak_wa, created_at FROM toko ORDER BY id_toko DESC')->fetchAll();

$csrfToken = csrfToken();

renderReactShell('Master Center — Infrastructure Management', 'MASTER_DATA', [
    'admin_session' => 'pasek@' . SITE_DOMAIN,
    'total_nodes'   => count($tenants),
    'tenants'       => $tenants,
    'csrf_token'    => $csrfToken,
]);
