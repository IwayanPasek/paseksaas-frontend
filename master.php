<?php
// ╔══════════════════════════════════════════════════════════════╗
//  HYBRID BRIDGE: MASTER CENTER (SUPER ADMIN)
// ╚══════════════════════════════════════════════════════════════╝
session_start();

// 1. Proteksi Halaman Master
if (!isset($_SESSION['master_logged_in'])) {
    header("Location: login.php");
    exit;
}

// 2. Koneksi Database
$db_host = 'localhost';
$db_user = 'wayan_user';
$db_pass = 'WayanPass123!';
$db_name = 'websitewayan_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// 3. Logika Pendaftaran Toko (Tenant) Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_tenant'])) {
    $nama_toko = htmlspecialchars(trim($_POST['nama_toko']));
    $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_POST['subdomain']));
    $password  = password_hash($_POST['password_toko'], PASSWORD_BCRYPT);
    $wa        = preg_replace('/[^0-9]/', '', $_POST['kontak_wa']); 
    $kb        = $_POST['knowledge_base'];

    try {
        $stmt = $pdo->prepare("INSERT INTO toko (nama_toko, subdomain, password, kontak_wa, knowledge_base) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama_toko, $subdomain, $password, $wa, $kb]);
        
        // Redirect dengan status success agar ditangkap oleh React Toast
        header("Location: master.php?status=success&msg=" . urlencode($nama_toko));
        exit;
    } catch(PDOException $e) {
        // Redirect dengan status error
        header("Location: master.php?status=error&msg=" . urlencode("Subdomain sudah digunakan atau terjadi error."));
        exit;
    }
}

// 4. Ambil Daftar Toko Terdaftar
$tenants = $pdo->query("SELECT id_toko, nama_toko, subdomain, kontak_wa, created_at FROM toko ORDER BY id_toko DESC")->fetchAll(PDO::FETCH_ASSOC);

// 5. Bungkus Data untuk React
$masterData = [
    'admin_session' => 'pasek@websitewayan_node',
    'total_nodes'   => count($tenants),
    'tenants'       => $tenants
];

// 6. Deteksi File React Vite (Assets)
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
    <title>Master Center — Infrastructure Management</title>
    
    <script>
        // Suntikkan data Master ke React
        window.MASTER_DATA = <?= json_encode($masterData) ?>;
    </script>
    
    <?php if ($cssFile): ?><link rel="stylesheet" href="<?= $cssFile ?>"><?php endif; ?>
    
    <style>
        /* Fallback dark theme jika React lambat loading */
        body { background-color: #080d1a; color: #e2e8f0; font-family: sans-serif; }
    </style>
</head>
<body>
    <div id="root">
        <?php if (!$jsFile): ?>
            <div style="text-align:center; padding: 50px; margin-top: 20vh;">
                <h2>Master React belum di-build.</h2>
                <p>Silakan jalankan <code>npm run build</code> di folder react-app.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($jsFile): ?><script type="module" src="<?= $jsFile ?>"></script><?php endif; ?>
</body>
</html>
