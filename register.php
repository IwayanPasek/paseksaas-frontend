<?php
// ═══════════════════════════════════════════════════
//  REGISTER GATEWAY — New Tenant Signup
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/vite.php';

// ── API: Check Subdomain Availability ──
if (isset($_GET['api']) && $_GET['api'] === 'check-subdomain') {
    header('Content-Type: application/json');
    $sub = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_GET['sub'] ?? ''));
    
    if (strlen($sub) < 3) {
        echo json_encode(['available' => false, 'message' => 'Too short']);
        exit;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM toko WHERE subdomain = ?');
        $stmt->execute([$sub]);
        $exists = $stmt->fetchColumn() > 0;
        echo json_encode(['available' => !$exists]);
    } catch (Exception $e) {
        echo json_encode(['available' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── API: Submit Registration ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api']) && $_GET['api'] === 'submit') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    if (!csrfVerify($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please reload the page.']);
        exit;
    }

    $nama_pemilik = trim($data['ownerName'] ?? $data['nama_pemilik'] ?? '');
    $email = trim($data['email'] ?? '');
    $nama_toko = trim($data['storeName'] ?? $data['nama_toko'] ?? '');
    $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $data['subdomain'] ?? ''));
    $password = $data['password'] ?? '';

    if (empty($nama_pemilik) || empty($email) || empty($nama_toko) || empty($subdomain) || strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Please complete all data correctly (Password min. 8 characters).']);
        exit;
    }

    try {
        $pdo = getDB();
        
        // Final check subdomain & email
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM toko WHERE subdomain = ?');
        $stmt->execute([$subdomain]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Subdomain is already in use.']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM toko WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email address is already registered.']);
            exit;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        
        // Status is 'pending' for manual approval
        $stmt = $pdo->prepare('INSERT INTO toko (nama_toko, email, subdomain, password, status, knowledge_base) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $nama_toko, 
            $email, 
            $subdomain, 
            $hashed, 
            'pending',
            "Hello! I am the AI assistant for $nama_toko. The store owner is $nama_pemilik."
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Registration successfully submitted.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
    exit;
}

// ── Render React Register ──
renderReactShell('Register Store — Pasek SaaS', 'REGISTER_DATA', [
    'isRegisterPage' => true,
    'siteDomain'     => SITE_DOMAIN,
    'csrfToken'      => csrfToken()
]);
