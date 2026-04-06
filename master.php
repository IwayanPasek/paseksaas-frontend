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

// ── Hyper-Admin: Action Logger ──
function masterLogAction(string $type, string $entity, ?int $id, ?string $details = null) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO audit_logs (id_admin, action_type, entity_type, entity_id, action_details, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $_SESSION['master_id'] ?? 0,
        $type,
        $entity,
        $id,
        $details,
        $_SERVER['REMOTE_ADDR']
    ]);
}

// ── Register New Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_tenant'])) {
    if (!csrfVerify()) {
        if (isset($_GET['api']) || isset($_POST['impersonate_tenant'])) {
            echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
            exit;
        }
        header('Location: master.php?status=error&msg=' . urlencode('Token CSRF tidak valid.'));
        exit;
    }

    $nama = htmlspecialchars(trim($_POST['nama_toko']));
    $sub  = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_POST['subdomain']));
    $pass = password_hash($_POST['password_toko'], PASSWORD_BCRYPT);
    $wa   = preg_replace('/[^0-9]/', '', $_POST['kontak_wa']);
    $kb   = trim($_POST['knowledge_base']);

    try {
        $stmt = $pdo->prepare('INSERT INTO toko (nama_toko, subdomain, password, kontak_wa, knowledge_base, status) VALUES (?, ?, ?, ?, ?, "active")');
        $stmt->execute([$nama, $sub, $pass, $wa, $kb]);
        $newId = $pdo->lastInsertId();
        masterLogAction('CREATE', 'TENANT', $newId, "Created tenant: $nama ($sub)");
        header('Location: master.php?status=success&msg=' . urlencode($nama));
    } catch (PDOException $e) {
        header('Location: master.php?status=error&msg=' . urlencode('Subdomain sudah digunakan atau terjadi error.'));
    }
    exit;
}

// ── Approve Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Token+CSRF+tidak+valid'); 
        exit; 
    }
    $id = (int) $_POST['approve_tenant'];
    $pdo->prepare("UPDATE toko SET status = 'active' WHERE id_toko = ?")->execute([$id]);
    masterLogAction('APPROVE', 'TENANT', $id);
    header('Location: master.php?status=success&msg=Tenant+Disetujui');
    exit;
}

// ── Suspend Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suspend_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Token+CSRF+tidak+valid'); 
        exit; 
    }
    $id = (int) $_POST['suspend_tenant'];
    $pdo->prepare("UPDATE toko SET status = 'suspended' WHERE id_toko = ?")->execute([$id]);
    masterLogAction('SUSPEND', 'TENANT', $id);
    header('Location: master.php?status=success&msg=Tenant+Ditangguhkan');
    exit;
}

// ── Unsuspend Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsuspend_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Token+CSRF+tidak+valid'); 
        exit; 
    }
    $id = (int) $_POST['unsuspend_tenant'];
    $pdo->prepare("UPDATE toko SET status = 'active' WHERE id_toko = ?")->execute([$id]);
    masterLogAction('UNSUSPEND', 'TENANT', $id);
    header('Location: master.php?status=success&msg=Tenant+Diaktifkan+Kembali');
    exit;
}

// ── Reject/Delete Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Token+CSRF+tidak+valid'); 
        exit; 
    }
    $id = (int) $_POST['reject_tenant'];
    $pdo->prepare("DELETE FROM toko WHERE id_toko = ?")->execute([$id]);
    masterLogAction('DELETE', 'TENANT', $id, "Permanent removal");
    header('Location: master.php?status=success&msg=Tenant+Dihapus');
    exit;
}

// ── Generate Impersonation Token ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['impersonate_tenant'])) {
    header('Content-Type: application/json');
    if (!csrfVerify()) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }
    $id = (int) $_POST['impersonate_tenant'];
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 1800); // 30 mins
    
    $pdo->prepare("INSERT INTO impersonation_tokens (token, id_toko, expires_at) VALUES (?, ?, ?)")
        ->execute([$token, $id, $expires]);
        
    masterLogAction('IMPERSONATE', 'TENANT', $id, "Generated token: $token");
    
    echo json_encode(['status' => 'success', 'token' => $token]);
    exit;
}

// ── Fetch tenants ──
$tenants = $pdo->query('SELECT id_toko, nama_toko, email, subdomain, kontak_wa, status, created_at FROM toko ORDER BY id_toko DESC')->fetchAll();

// Mask WhatsApp numbers for privacy (show first 4 + last 3 digits)
foreach ($tenants as &$t) {
    $wa = $t['kontak_wa'];
    if (strlen($wa) > 7) {
        $t['kontak_wa'] = substr($wa, 0, 4) . str_repeat('*', strlen($wa) - 7) . substr($wa, -3);
    }
}
unset($t);

// ── Global Analytics ──
$stats = [
    'total_tenants' => count($tenants),
    'active_tenants' => count(array_filter($tenants, fn($t) => $t['status'] === 'active')),
    'total_chats' => (int) $pdo->query("SELECT COUNT(*) FROM log_chat")->fetchColumn(),
    'total_products' => (int) $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn(),
];

// Audit Logs (Latest 50)
$audit_logs = $pdo->query("SELECT * FROM audit_logs ORDER BY id_audit DESC LIMIT 50")->fetchAll();

// Growth Chart (Last 14 days)
$growth = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM toko WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $growth[] = ['date' => date('d M', strtotime($date)), 'count' => (int) $stmt->fetchColumn()];
}

$adminUser = $_SESSION['master_username'] ?? 'admin';

renderReactShell('Hyper-Admin — Command Center', 'MASTER_DATA', [
    'admin_session' => $adminUser . '@' . SITE_DOMAIN,
    'stats'         => $stats,
    'tenants'       => $tenants,
    'audit_logs'    => $audit_logs,
    'growth'        => $growth,
    'csrf_token'    => $csrfToken,
]);
