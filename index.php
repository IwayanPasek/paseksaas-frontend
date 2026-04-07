<?php
// ═══════════════════════════════════════════════════
//  STOREFRONT — Landing Page (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vite.php';

// ── Detect subdomain ──
// SITE_DOMAIN = 'websitewayan.my.id' (3 parts)
// Valid tenant URLs: {subdomain}.websitewayan.my.id (4+ parts)
// Apex domain or www: websitewayan.my.id / www.websitewayan.my.id → redirect to login
$http_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$site_domain = strtolower(SITE_DOMAIN);
$subdomain = '';

// Check if host ends with the site domain and has a prefix
if ($http_host !== $site_domain && str_ends_with($http_host, '.' . $site_domain)) {
    // Extract subdomain: "tokodemo.websitewayan.my.id" → "tokodemo"
    $subdomain = substr($http_host, 0, strlen($http_host) - strlen('.' . $site_domain));
}

// Redirect if not a valid store subdomain
if (!$subdomain || $subdomain === 'www') {
    header('Location: landing.php');
    exit;
}

// ── Fetch store data ──
$toko = null;
$list_produk = [];
$list_kategori = [];
$list_faq = [];

try {
    $pdo = getDB();

    $stmt = $pdo->prepare('SELECT * FROM toko WHERE subdomain = :sub LIMIT 1');
    $stmt->execute([':sub' => $subdomain]);
    $toko = $stmt->fetch();

    if ($toko) {
        if (($toko['status'] ?? 'active') !== 'active') {
            http_response_code(403);
            echo "<div style='text-align:center;font-family:Inter,sans-serif;margin-top:20vh;color:#737373;'><h1>Akses Ditangguhkan</h1><p>Toko ini sedang dalam proses peninjauan atau telah dinonaktifkan.</p></div>";
            exit;
        }
        $id = $toko['id_toko'];

        $stmt = $pdo->prepare('SELECT * FROM produk WHERE id_toko = ? ORDER BY id_produk DESC');
        $stmt->execute([$id]);
        $list_produk = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT * FROM kategori WHERE id_toko = ? ORDER BY id_kategori DESC');
        $stmt->execute([$id]);
        $list_kategori = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT * FROM faq_toko WHERE id_toko = ? ORDER BY id_faq ASC');
        $stmt->execute([$id]);
        $list_faq = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // Silently fail — store not found
}

if (!$toko) {
    http_response_code(404);
    echo "<div style='text-align:center;font-family:Inter,sans-serif;margin-top:20vh;color:#737373;'><h1>Toko Tidak Ditemukan</h1><p>404 — Subdomain tidak terdaftar.</p></div>";
    exit;
}

// ── Build React data ──
$wa = preg_replace('/[^0-9]/', '', $toko['kontak_wa'] ?? '081234567890');
if (str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);

$desc = !empty($toko['deskripsi_landing'])
    ? $toko['deskripsi_landing']
    : ($toko['knowledge_base'] ?? 'Temukan layanan terbaik kami dengan bantuan Asisten AI.');

$reactData = [
    'storeId'          => (int) $toko['id_toko'],
    'name'             => $toko['nama_toko'],
    'storeName'        => $toko['nama_toko'],
    'storeDescription' => $desc,
    'whatsappNumber'   => $wa,
    'logo'             => $toko['logo'] ?? null,
    'products'         => (array) ($list_produk ?: []),
    'categories'       => (array) ($list_kategori ?: []),
    'faqItems'         => (array) ($list_faq ?: []),
];

renderReactShell(
    htmlspecialchars($toko['nama_toko']) . ' — Pasek SaaS',
    'STORE_DATA',
    $reactData,
    $desc
);
