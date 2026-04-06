<?php
// ═══════════════════════════════════════════════════
//  LOGIN GATEWAY — Secure Access (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vite.php';

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
        $_SESSION['master_logged_in'] = true;
        $_SESSION['role'] = 'master';
        $_SESSION['login_attempts'] = 0;
        if ($remember) setcookie('remember_master', $user, time() + (86400 * 30), '/');
        echo json_encode(['status' => 'success', 'redirect' => 'master.php']);
        exit;
    }

    // Check Tenant
    $stmt = $pdo->prepare('SELECT * FROM toko WHERE subdomain = ?');
    $stmt->execute([$user]);
    $toko = $stmt->fetch();

    if ($toko && password_verify($pass, $toko['password'])) {
        $_SESSION['tenant_id'] = $toko['id_toko'];
        $_SESSION['nama_toko'] = $toko['nama_toko'];
        $_SESSION['role'] = 'tenant';
        $_SESSION['login_attempts'] = 0;
        if ($remember) setcookie('remember_tenant', $toko['id_toko'], time() + (86400 * 30), '/');
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

// ── Auto-login from cookie ──
if (!isset($_SESSION['role'])) {
    try {
        $pdo = getDB();
        if (isset($_COOKIE['remember_master'])) {
            $_SESSION['master_logged_in'] = true;
            $_SESSION['role'] = 'master';
            header('Location: master.php');
            exit;
        } elseif (isset($_COOKIE['remember_tenant'])) {
            $stmt = $pdo->prepare('SELECT * FROM toko WHERE id_toko = ?');
            $stmt->execute([$_COOKIE['remember_tenant']]);
            if ($toko = $stmt->fetch()) {
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
