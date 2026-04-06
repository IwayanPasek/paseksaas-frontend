<?php
// ═══════════════════════════════════════════════════
//  STOREFRONT — Landing Page (PHP → React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vite.php';

// ── Detect subdomain ──
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$parts     = explode('.', $http_host);
$subdomain = (count($parts) >= 3) ? strtolower($parts[0]) : '';

// Redirect if not a store subdomain
if (!$subdomain || in_array($subdomain, ['www', SITE_DOMAIN])) {
    header('Location: login.php');
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
    'id_toko'    => (int) $toko['id_toko'],
    'nama_toko'  => $toko['nama_toko'],
    'desc_toko'  => $desc,
    'wa_num'     => $wa,
    'logo'       => $toko['logo'] ?? null,
    'products'   => $list_produk,
    'categories' => $list_kategori,
    'faq'        => $list_faq,
];

renderReactShell(
    htmlspecialchars($toko['nama_toko']) . ' — Pasek SaaS',
    'STORE_DATA',
    $reactData
);
