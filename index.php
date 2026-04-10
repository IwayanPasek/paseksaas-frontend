<?php
/** CACHE BUSTER: 2026-04-08 01:08:45 **/
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/vite.php';

// -- Detect subdomain --
$http_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$site_domain = strtolower(SITE_DOMAIN);
$subdomain = '';

if ($http_host !== $site_domain && str_ends_with($http_host, '.' . $site_domain)) {
    $subdomain = substr($http_host, 0, strlen($http_host) - strlen('.' . $site_domain));
}

if (!$subdomain || $subdomain === 'www') {
    header('Location: landing.php');
    exit;
}

// -- Fetch store data --
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
            echo "Access Suspended";
            exit;
        }

        $id_toko = (int) $toko['id_toko'];

        $stmt = $pdo->prepare('SELECT * FROM produk WHERE id_toko = :id AND status = "ready"');
        $stmt->execute([':id' => $id_toko]);
        $list_produk = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT * FROM kategori WHERE id_toko = :id');
        $stmt->execute([':id' => $id_toko]);
        $list_kategori = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT * FROM faq_toko WHERE id_toko = :id');
        $stmt->execute([':id' => $id_toko]);
        $list_faq = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (!$toko) {
    header("Location: https://" . $site_domain);
    exit;
}

$theme = $toko['theme_color'] ?? '#3b82f6';
$wa = $toko['kontak_wa'] ?? '';
$desc = $toko['deskripsi_landing'] ?? '';

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
    'themeColor'       => $theme,
];

renderReactShell(
    htmlspecialchars($toko['nama_toko']) . ' — Pasek SaaS',
    'STORE_DATA',
    $reactData,
    $desc
);
