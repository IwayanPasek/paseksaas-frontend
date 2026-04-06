<?php
// ═══════════════════════════════════════════════════
//  REGISTER GATEWAY — New Tenant Signup
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vite.php';

// ── API: Check Subdomain Availability ──
if (isset($_GET['api']) && $_GET['api'] === 'check-subdomain') {
    header('Content-Type: application/json');
    $sub = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_GET['sub'] ?? ''));
    
    if (strlen($sub) < 3) {
        echo json_encode(['available' => false, 'message' => 'Terlalu pendek']);
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

    $nama_pemilik = trim($data['nama_pemilik'] ?? '');
    $email = trim($data['email'] ?? '');
    $nama_toko = trim($data['nama_toko'] ?? '');
    $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $data['subdomain'] ?? ''));
    $password = $data['password'] ?? '';

    if (empty($nama_pemilik) || empty($email) || empty($nama_toko) || empty($subdomain) || strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Harap lengkapi semua data dengan benar (Password min. 8 karakter).']);
        exit;
    }

    try {
        $pdo = getDB();
        
        // Final check subdomain
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM toko WHERE subdomain = ?');
        $stmt->execute([$subdomain]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Subdomain sudah digunakan.']);
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
            "Halo! Saya asisten AI untuk $nama_toko. Pemilik toko adalah $nama_pemilik."
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Pendaftaran berhasil dikirim.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftar: ' . $e->getMessage()]);
    }
    exit;
}

// ── Render React Register ──
renderReactShell('Daftar Store — Pasek SaaS', 'REGISTER_DATA', [
    'is_register_page' => true,
    'site_domain'      => SITE_DOMAIN
]);
