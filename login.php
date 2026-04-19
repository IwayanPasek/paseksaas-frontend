<?php
// ═══════════════════════════════════════════════════
//  LOGIN GATEWAY — Secure Access (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/error_handler.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/vite.php';

/**
 * Create a signed remember-me cookie value.
 * Uses COOKIE_SECRET from .env (NOT derived from DB password).
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

// ── Secure Impersonation Gateway ──
if (isset($_GET['impersonate_token'])) {
    try {
        $pdo = getDB();
        $token = $_GET['impersonate_token'];
        
        $stmt = $pdo->prepare('SELECT it.*, t.nama_toko FROM impersonation_tokens it JOIN toko t ON it.id_toko = t.id_toko WHERE it.token = ? AND it.expires_at > NOW() AND it.used_at IS NULL');
        $stmt->execute([$token]);
        $data = $stmt->fetch();

        if ($data) {
            // Mark token as used (single-use)
            $pdo->prepare('UPDATE impersonation_tokens SET used_at = NOW() WHERE id = ?')->execute([$data['id']]);
            
            // Setup Tenant Session
            session_regenerate_id(true);
            $_SESSION['tenant_id'] = $data['id_toko'];
            $_SESSION['nama_toko'] = $data['nama_toko'];
            $_SESSION['role']      = 'tenant';
            $_SESSION['is_impersonating'] = true;

            header("Location: admin.php?status=success&msg=Impersonation+Mode+Active");
            exit;
        } else {
            header("Location: login.php?status=error&msg=Impersonation+token+expired+or+invalid.");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Impersonation error: " . $e->getMessage());
        // Fallback to normal login
    }
}

// ── API Endpoint for React ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');

    try {
        $pdo = getDB();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'System under maintenance.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!csrfVerify($data)) {
        logSecurityEvent('CSRF_FAIL', 'medium', 'Login form CSRF token mismatch');
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please reload the page.']);
        exit;
    }

    $user = trim($data['username'] ?? '');
    $pass = $data['password'] ?? '';
    $remember = $data['remember'] ?? false;

    // IP-based brute-force protection (replaces session-based)
    $lockoutRemaining = checkRateLimit();
    if ($lockoutRemaining > 0) {
        $wait = ceil($lockoutRemaining / 60);
        echo json_encode(['status' => 'error', 'message' => "Too many failed attempts. Try again in $wait minutes."]);
        exit;
    }

    if (empty($user) || empty($pass)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
        exit;
    }

    // Check Master Admin
    try {
        $stmt = $pdo->prepare('SELECT * FROM master_admin WHERE username = ?');
        $stmt->execute([$user]);
        $master = $stmt->fetch();

        if ($master && password_verify($pass, $master['password'])) {
            // Check account lockout
            if (($master['status'] ?? 'active') !== 'active') {
                echo json_encode(['status' => 'error', 'message' => 'Master account suspended.']);
                exit;
            }

            session_regenerate_id(true); 
            $_SESSION['master_logged_in'] = true;
            $_SESSION['master_id'] = $master['id_admin'] ?? $master['id'] ?? 1;
            $_SESSION['master_username'] = $master['username'];
            $_SESSION['role'] = 'master';

            // Record successful login
            recordLoginAttempt($user, true);
            clearLoginAttempts();

            // Update last login info
            $pdo->prepare('UPDATE master_admin SET last_login_at = NOW(), last_login_ip = ?, failed_login_count = 0 WHERE id_admin = ?')
                ->execute([$_SERVER['REMOTE_ADDR'] ?? '', $master['id_admin']]);

            if ($remember) {
                $token = createRememberToken($master['username'], 'master');
                setRememberCookie('remember_token', $token);
            }

            echo json_encode(['status' => 'success', 'redirect' => 'master.php']);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Master Login Error: " . $e->getMessage());
    }

    // Check Tenant
    try {
        $stmt = $pdo->prepare('SELECT * FROM toko WHERE subdomain = ?');
        $stmt->execute([$user]);
        $toko = $stmt->fetch();

        if ($toko && password_verify($pass, $toko['password'])) {
            $status = $toko['status'] ?? 'active';
            if ($status !== 'active') {
                $msg = $status === 'pending' 
                    ? 'Your account is being reviewed.' 
                    : 'Your account is suspended.';
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['tenant_id'] = $toko['id_toko'] ?? $toko['id'];
            $_SESSION['nama_toko'] = $toko['nama_toko'];
            $_SESSION['role'] = 'tenant';

            // Record successful login
            recordLoginAttempt($user, true);
            clearLoginAttempts();

            if ($remember) {
                $token = createRememberToken((string)$_SESSION['tenant_id'], 'tenant');
                setRememberCookie('remember_token', $token);
            }

            echo json_encode(['status' => 'success', 'redirect' => 'admin.php']);
            exit;
        }
    } catch (PDOException $e) {
        // SECURITY: Do NOT leak database error details to client
        error_log("Tenant Login Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'A system error occurred. Please try again later.']);
        exit;
    }

    // Failed login — record and respond
    recordLoginAttempt($user, false);
    $remaining = getRemainingAttempts();
    
    if ($remaining <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Access temporarily blocked.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Incorrect username or password. $remaining attempts remaining."]);
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
            error_log("Auto-login error: " . $e->getMessage());
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
    'isLoginPage'     => true,
    'masterWhatsapp'  => MASTER_WA,
    'csrfToken'       => csrfToken(),
]);
