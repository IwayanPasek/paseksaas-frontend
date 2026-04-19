<?php
// ═══════════════════════════════════════════════════
//  MASTER CENTER — Super Admin (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/error_handler.php';
require_once __DIR__ . '/includes/vite.php';

requireMaster();
$pdo = getDB();
$csrfToken = csrfToken();

// ── Hyper-Admin: Action Logger ──
function masterLogAction(string $type, string $entity, ?int $id, ?string $details = null) {
    global $pdo;
    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (id_admin, action_type, entity_type, entity_id, action_details, ip_address, user_agent) 
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $_SESSION['master_id'] ?? 0,
        $type,
        $entity,
        $id,
        $details,
        $_SERVER['REMOTE_ADDR'],
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
    ]);
}

// ── Register New Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_tenant'])) {
    if (!csrfVerify()) {
        if (isset($_GET['api']) || isset($_POST['impersonate_tenant'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
            exit;
        }
        header('Location: master.php?status=error&msg=' . urlencode('Invalid CSRF token.'));
        exit;
    }

    $nama = htmlspecialchars(trim($_POST['storeName'] ?? $_POST['nama_toko'] ?? ''));
    $sub  = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_POST['subdomain'] ?? ''));
    $pass = password_hash($_POST['storePassword'] ?? $_POST['password_toko'] ?? '', PASSWORD_BCRYPT, ['cost' => 12]);
    $wa   = preg_replace('/[^0-9]/', '', $_POST['whatsappNumber'] ?? $_POST['kontak_wa'] ?? '');
    $kb   = trim($_POST['aiContext'] ?? $_POST['knowledge_base'] ?? '');

    try {
        $stmt = $pdo->prepare('INSERT INTO toko (nama_toko, subdomain, password, kontak_wa, knowledge_base, status) VALUES (?, ?, ?, ?, ?, "active")');
        $stmt->execute([$nama, $sub, $pass, $wa, $kb]);
        $newId = $pdo->lastInsertId();
        masterLogAction('CREATE', 'TENANT', $newId, "Created tenant: $nama ($sub)");
        header('Location: master.php?status=success&msg=' . urlencode($nama));
    } catch (PDOException $e) {
        error_log("Tenant creation error: " . $e->getMessage());
        header('Location: master.php?status=error&msg=' . urlencode('Subdomain is already in use or an error occurred.'));
    }
    exit;
}

// ── Approve Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Invalid+CSRF+token'); 
        exit; 
    }
    $id = (int) $_POST['approve_tenant'];
    $pdo->prepare("UPDATE toko SET status = 'active' WHERE id_toko = ?")->execute([$id]);
    masterLogAction('APPROVE', 'TENANT', $id);
    header('Location: master.php?status=success&msg=Tenant+Approved');
    exit;
}

// ── Suspend Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suspend_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Invalid+CSRF+token'); 
        exit; 
    }
    $id = (int) $_POST['suspend_tenant'];
    $pdo->prepare("UPDATE toko SET status = 'suspended' WHERE id_toko = ?")->execute([$id]);
    masterLogAction('SUSPEND', 'TENANT', $id);
    header('Location: master.php?status=success&msg=Tenant+Suspended');
    exit;
}

// ── Unsuspend Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsuspend_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Invalid+CSRF+token'); 
        exit; 
    }
    $id = (int) $_POST['unsuspend_tenant'];
    $pdo->prepare("UPDATE toko SET status = 'active' WHERE id_toko = ?")->execute([$id]);
    masterLogAction('UNSUSPEND', 'TENANT', $id);
    header('Location: master.php?status=success&msg=Tenant+Reactivated');
    exit;
}

// ── Reject/Delete Tenant ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_tenant'])) {
    if (!csrfVerify()) { 
        header('Location: master.php?status=error&msg=Invalid+CSRF+token'); 
        exit; 
    }
    $id = (int) $_POST['reject_tenant'];
    $pdo->prepare("DELETE FROM toko WHERE id_toko = ?")->execute([$id]);
    masterLogAction('DELETE', 'TENANT', $id, "Permanent removal");
    header('Location: master.php?status=success&msg=Tenant+Deleted');
    exit;
}

// ── Generate Impersonation Token ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['impersonate_tenant'])) {
    header('Content-Type: application/json');
    if (!csrfVerify()) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        exit;
    }
    $id = (int) $_POST['impersonate_tenant'];
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 1800); // 30 mins
    
    $pdo->prepare("INSERT INTO impersonation_tokens (token, id_toko, created_by, expires_at) VALUES (?, ?, ?, ?)")
        ->execute([$token, $id, $_SESSION['master_id'] ?? 0, $expires]);
        
    // SECURITY: Hash the token in audit log to prevent extraction from logs
    $tokenHash = substr(hash('sha256', $token), 0, 12);
    masterLogAction('IMPERSONATE', 'TENANT', $id, "Generated token (hash: $tokenHash...)");
    
    echo json_encode(['status' => 'success', 'token' => $token]);
    exit;
}

// ── Fetch tenants (FIXED: Added LIMIT for safety) ──
$tenants = $pdo->query('SELECT id_toko, nama_toko, email, subdomain, kontak_wa, status, created_at FROM toko ORDER BY id_toko DESC LIMIT 500')->fetchAll();

// Mask WhatsApp numbers for privacy (show first 4 + last 3 digits)
foreach ($tenants as &$t) {
    $wa = $t['kontak_wa'];
    if (strlen($wa) > 7) {
        $t['kontak_wa'] = substr($wa, 0, 4) . str_repeat('*', strlen($wa) - 7) . substr($wa, -3);
    }
}
unset($t);

// ── Global Analytics (FIXED: Single optimized query instead of unbounded COUNT) ──
$statsQuery = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM toko LIMIT 1) AS totalTenants,
        (SELECT COUNT(*) FROM toko WHERE status = 'active' LIMIT 1) AS activeTenants,
        (SELECT COUNT(*) FROM log_chat LIMIT 1) AS totalInteractions,
        (SELECT COUNT(*) FROM produk LIMIT 1) AS totalServices
")->fetch();

$stats = [
    'totalTenants'      => (int) $statsQuery['totalTenants'],
    'activeTenants'     => (int) $statsQuery['activeTenants'],
    'totalInteractions' => (int) $statsQuery['totalInteractions'],
    'totalServices'     => (int) $statsQuery['totalServices'],
];

// Audit Logs (Latest 50)
$audit_logs = $pdo->query("SELECT * FROM audit_logs ORDER BY id_audit DESC LIMIT 50")->fetchAll();

// Growth Chart (Last 14 days) — FIXED: Single aggregated query
$growth = [];
$startDate = date('Y-m-d', strtotime('-13 days'));
$growthQuery = $pdo->prepare("SELECT DATE(created_at) as reg_date, COUNT(*) as reg_count FROM toko WHERE created_at >= ? GROUP BY DATE(created_at)");
$growthQuery->execute([$startDate]);
$growthData = [];
while ($row = $growthQuery->fetch()) {
    $growthData[$row['reg_date']] = (int) $row['reg_count'];
}
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $growth[] = ['date' => date('d M', strtotime($date)), 'count' => $growthData[$date] ?? 0];
}

// Map Tenants to English
$mapped_tenants = array_map(fn($t) => [
    'id'        => (int)$t['id_toko'],
    'name'      => $t['nama_toko'],
    'email'     => $t['email'],
    'subdomain' => $t['subdomain'],
    'whatsapp'  => $t['kontak_wa'],
    'status'    => $t['status'],
    'createdAt' => $t['created_at'],
], $tenants);

// Map Audit Logs
$mapped_audit_logs = array_map(fn($l) => [
    'id'        => (int)$l['id_audit'],
    'adminId'   => (int)$l['id_admin'],
    'type'      => $l['action_type'],
    'entity'    => $l['entity_type'],
    'entityId'  => (int)$l['entity_id'],
    'details'   => $l['action_details'],
    'ipAddress' => $l['ip_address'],
    'date'      => $l['created_at'],
], $audit_logs);

$adminUser = $_SESSION['master_username'] ?? 'admin';

renderReactShell('Hyper-Admin — Command Center', 'MASTER_DATA', [
    'adminSession' => $adminUser . '@' . SITE_DOMAIN,
    'stats'        => $stats,
    'tenants'      => $mapped_tenants,
    'auditLogs'    => $mapped_audit_logs,
    'growth'       => $growth,
    'csrfToken'    => $csrfToken,
]);
