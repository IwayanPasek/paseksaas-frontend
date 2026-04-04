<?php
// ╔══════════════════════════════════════════════════════════════╗
//  HYBRID BRIDGE: PHP Data Injector -> React Frontend
// ╚══════════════════════════════════════════════════════════════╝
session_start();

define('DB_HOST',     'localhost');
define('DB_USER',     'wayan_user');
define('DB_PASS',     'WayanPass123!');
define('DB_NAME',     'websitewayan_db');
define('BASE_DOMAIN', 'websitewayan.my.id');

$http_host = $_SERVER['HTTP_HOST'] ?? '';
$parts     = explode('.', $http_host);
$subdomain = (count($parts) >= 3) ? strtolower($parts[0]) : '';

$toko = null;
$list_produk = [];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($subdomain && !in_array($subdomain, ['www', 'websitewayan'])) {
        $stmt = $pdo->prepare("SELECT * FROM toko WHERE subdomain = :sub LIMIT 1");
        $stmt->execute([':sub' => $subdomain]);
        $toko = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($toko) {
        $stmt_all = $pdo->prepare("SELECT * FROM produk WHERE id_toko = ? ORDER BY id_produk DESC");
        $stmt_all->execute([$toko['id_toko']]);
        $list_produk = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Tangkap error secara diam-diam
}

// Redirect jika bukan subdomain toko
if (!$subdomain || in_array($subdomain, ['www', 'websitewayan'])) {
    header("Location: login.php");
    exit;
}

if (!$toko) {
    http_response_code(404);
    echo "<h1 style='text-align:center;font-family:sans-serif;margin-top:20vh;'>Toko Tidak Ditemukan (404)</h1>";
    exit;
}

// ── BUNGKUS DATA UNTUK REACT ──
$wa_raw = $toko['no_wa'] ?? $toko['whatsapp'] ?? '081234567890';
$wa_clean = preg_replace('/[^0-9]/', '', $wa_raw);
if (str_starts_with($wa_clean, '0')) $wa_clean = '62' . substr($wa_clean, 1);

$reactData = [
    'id_toko'   => (int)$toko['id_toko'],
    'nama_toko' => $toko['nama_toko'],
    'desc_toko' => $toko['knowledge_base'] ?: 'Temukan layanan terbaik kami dengan bantuan Asisten AI.',
    'wa_num'    => $wa_clean,
    'logo'      => $toko['logo'] ?? null,
    'products'  => $list_produk
];

// ── AUTO-DETECT VITE COMPILED ASSETS ──
$distPath = __DIR__ . '/react-app/dist/assets/';
$cssFile = '';
$jsFile = '';

if (is_dir($distPath)) {
    $files = scandir($distPath);
    foreach ($files as $file) {
        if (str_ends_with($file, '.css')) $cssFile = 'react-app/dist/assets/' . $file;
        if (str_ends_with($file, '.js'))  $jsFile  = 'react-app/dist/assets/' . $file;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($toko['nama_toko']) ?> - AI SaaS</title>
    
    <script>
        window.STORE_DATA = <?= json_encode($reactData) ?>;
    </script>

    <?php if ($cssFile): ?>
        <link rel="stylesheet" href="<?= $cssFile ?>">
    <?php else: ?>
        <style>body { font-family: sans-serif; text-align: center; margin-top: 20vh; } </style>
    <?php endif; ?>
</head>
<body>
    
    <div id="root">
        <?php if (!$jsFile): ?>
            <h2>Aplikasi React belum di-build. Jalankan `npm run build` di folder react-app.</h2>
        <?php endif; ?>
    </div>

    <?php if ($jsFile): ?>
        <script type="module" src="<?= $jsFile ?>"></script>
    <?php endif; ?>

</body>
</html>
