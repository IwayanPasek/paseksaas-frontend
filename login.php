<?php
// ═══════════════════════════════════════════════════
//  LOGIN GATEWAY — Secure Access (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vite.php';

// ── Remember-me token secret (HMAC signing key) ──
define('COOKIE_SECRET', hash('sha256', DB_PASS . '_paseksaas_cookie_sign'));

/**
 * Create a signed remember-me cookie value.
 * Format: base64(id|role|expiry|signature)
 */
function createRememberToken(string $id, string $role): string {
    $expiry = time() + (86400 * 30); // 30 days
    $payload = "$id|$role|$expiry";
    $sig = hash_hmac('sha256', $payload, COOKIE_SECRET);
    return base64_encode("$payload|$sig");
}

/**
 * Verify and decode a remember-me token.
 * Returns ['id' => ..., 'role' => ...] or false.
 */
function verifyRememberToken(string $token): array|false {
    $decoded = base64_decode($token, true);
    if (!$decoded) return false;

    $parts = explode('|', $decoded);
    if (count($parts) !== 4) return false;

    [$id, $role, $expiry, $sig] = $parts;

    // Check expiry
    if ((int) $expiry < time()) return false;

    // Verify HMAC signature
    $payload = "$id|$role|$expiry";
    $expected = hash_hmac('sha256', $payload, COOKIE_SECRET);
    if (!hash_equals($expected, $sig)) return false;

    return ['id' => $id, 'role' => $role];
}

/**
 * Set a secure remember-me cookie.
 */
function setRememberCookie(string $name, string $value, int $days = 30): void {
    setcookie($name, $value, [
        'expires'  => time() + (86400 * $days),
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly'  => true,
        'samesite' => 'Strict',
    ]);
}

// ── API Endpoint for React ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');

    try {
        $pdo = getDB();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Sistem sedang pemeliharaan.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $user = trim($data['username'] ?? '');
    $pass = $data['password'] ?? '';
    $remember = $data['remember'] ?? false;

    // Brute-force protection (locked 5 min after 3 failures)
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $wait = ceil(($_SESSION['lockout_time'] - time()) / 60);
        echo json_encode(['status' => 'error', 'message' => "Terlalu banyak percobaan. Sistem terkunci selama $wait menit."]);
        exit;
    }

    if (empty($user) || empty($pass)) {
        echo json_encode(['status' => 'error', 'message' => 'Harap isi identitas dan keyphrase.']);
        exit;
    }

    // Check Master Admin
    $stmt = $pdo->prepare('SELECT * FROM master_admin WHERE username = ?');
    $stmt->execute([$user]);
    $master = $stmt->fetch();

    if ($master && password_verify($pass, $master['password'])) {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['master_logged_in'] = true;
        $_SESSION['master_username'] = $master['username'];
        $_SESSION['role'] = 'master';
        $_SESSION['login_attempts'] = 0;

        if ($remember) {
            $token = createRememberToken($master['username'], 'master');
            setRememberCookie('remember_token', $token);
        }

        echo json_encode(['status' => 'success', 'redirect' => 'master.php']);
        exit;
    }

    // Check Tenant
    $stmt = $pdo->prepare('SELECT * FROM toko WHERE subdomain = ?');
    $stmt->execute([$user]);
    $toko = $stmt->fetch();

    if ($toko && password_verify($pass, $toko['password'])) {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['tenant_id'] = $toko['id_toko'];
        $_SESSION['nama_toko'] = $toko['nama_toko'];
        $_SESSION['role'] = 'tenant';
        $_SESSION['login_attempts'] = 0;

        if ($remember) {
            $token = createRememberToken((string)$toko['id_toko'], 'tenant');
            setRememberCookie('remember_token', $token);
        }

        echo json_encode(['status' => 'success', 'redirect' => 'admin.php']);
        exit;
    }

    // Failed
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 3) {
        $_SESSION['lockout_time'] = time() + (5 * 60);
        echo json_encode(['status' => 'error', 'message' => 'Otorisasi ditolak 3 kali. Akses diblokir sementara.']);
    } else {
        $sisa = 3 - $_SESSION['login_attempts'];
        echo json_encode(['status' => 'error', 'message' => "Identitas atau keyphrase salah. Sisa: $sisa percobaan"]);
    }
    exit;
}

// ── Auto-login from signed cookie ──
if (!isset($_SESSION['role']) && isset($_COOKIE['remember_token'])) {
    $tokenData = verifyRememberToken($_COOKIE['remember_token']);

    if ($tokenData) {
        try {
            $pdo = getDB();

            if ($tokenData['role'] === 'master') {
                $stmt = $pdo->prepare('SELECT * FROM master_admin WHERE username = ?');
                $stmt->execute([$tokenData['id']]);
                if ($stmt->fetch()) {
                    session_regenerate_id(true);
                    $_SESSION['master_logged_in'] = true;
                    $_SESSION['master_username'] = $tokenData['id'];
                    $_SESSION['role'] = 'master';
                    header('Location: master.php');
                    exit;
                }
            } elseif ($tokenData['role'] === 'tenant') {
                $stmt = $pdo->prepare('SELECT * FROM toko WHERE id_toko = ?');
                $stmt->execute([$tokenData['id']]);
                if ($toko = $stmt->fetch()) {
                    session_regenerate_id(true);
                    $_SESSION['tenant_id'] = $toko['id_toko'];
                    $_SESSION['nama_toko'] = $toko['nama_toko'];
                    $_SESSION['role'] = 'tenant';
                    header('Location: admin.php');
                    exit;
                }
            }
        } catch (PDOException $e) {
            // Continue to login page
        }
    }

    // Invalid token — clear it
    setRememberCookie('remember_token', '', -1);
}

// ── Redirect if already logged in ──
if (isset($_SESSION['role'])) {
    header('Location: ' . ($_SESSION['role'] === 'master' ? 'master.php' : 'admin.php'));
    exit;
}

// ── Render React Login ──
renderReactShell('Gateway — Pasek SaaS', 'LOGIN_DATA', [
    'is_login_page' => true,
    'master_wa'     => MASTER_WA,
]);
