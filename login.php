<?php
// ╔══════════════════════════════════════════════════════════════╗
//  HYBRID BRIDGE & API: SECURE LOGIN GATEWAY
// ╚══════════════════════════════════════════════════════════════╝
session_start();

$db_host = 'localhost';
$db_user = 'wayan_user';
$db_pass = 'WayanPass123!';
$db_name = 'websitewayan_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    if (isset($_GET['api'])) {
        die(json_encode(['status' => 'error', 'message' => 'Sistem sedang pemeliharaan.']));
    }
    die("Sistem sedang pemeliharaan.");
}

// ── 1. API ENDPOINT UNTUK REACT ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user = trim($data['username'] ?? '');
    $pass = $data['password'] ?? '';
    $remember = $data['remember'] ?? false;

    // Proteksi Brute-Force (Terkunci 5 Menit jika 3x gagal)
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $wait = ceil(($_SESSION['lockout_time'] - time()) / 60);
        echo json_encode(['status' => 'error', 'message' => "Terlalu banyak percobaan. Sistem terkunci selama $wait menit."]);
        exit;
    }

    if (empty($user) || empty($pass)) {
        echo json_encode(['status' => 'error', 'message' => 'Harap isi identitas dan keyphrase.']);
        exit;
    }

    // TAHAP A: Cek Master Admin
    $stmt_master = $pdo->prepare("SELECT * FROM master_admin WHERE username = ?");
    $stmt_master->execute([$user]);
    $master = $stmt_master->fetch();

    if ($master && password_verify($pass, $master['password'])) {
        $_SESSION['master_logged_in'] = true;
        $_SESSION['role'] = 'master';
        $_SESSION['login_attempts'] = 0; // Reset brute-force
        
        if ($remember) {
            setcookie('remember_master', $user, time() + (86400 * 30), "/"); // 30 Hari
        }
        echo json_encode(['status' => 'success', 'redirect' => 'master.php']);
        exit;
    }

    // TAHAP B: Cek Tenant / Toko
    $stmt_toko = $pdo->prepare("SELECT * FROM toko WHERE subdomain = ?");
    $stmt_toko->execute([$user]);
    $toko = $stmt_toko->fetch();

    if ($toko && password_verify($pass, $toko['password'])) {
        $_SESSION['tenant_id'] = $toko['id_toko'];
        $_SESSION['nama_toko'] = $toko['nama_toko'];
        $_SESSION['role'] = 'tenant';
        $_SESSION['login_attempts'] = 0; // Reset brute-force
        
        if ($remember) {
            setcookie('remember_tenant', $toko['id_toko'], time() + (86400 * 30), "/"); // 30 Hari
        }
        echo json_encode(['status' => 'success', 'redirect' => 'admin.php']);
        exit;
    }

    // TAHAP C: Gagal Login (Catat percobaan)
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 3) {
        $_SESSION['lockout_time'] = time() + (5 * 60); // Kunci 5 menit
        echo json_encode(['status' => 'error', 'message' => 'Otorisasi ditolak 3 kali. Akses diblokir sementara.']);
    } else {
        $sisa = 3 - $_SESSION['login_attempts'];
        echo json_encode(['status' => 'error', 'message' => "Identitas atau keyphrase salah. Sisa: $sisa percobaan"]);
    }
    exit;
}

// ── 2. AUTO LOGIN DARI COOKIE (Remember Me) ──
if (!isset($_SESSION['role'])) {
    if (isset($_COOKIE['remember_master'])) {
        $_SESSION['master_logged_in'] = true;
        $_SESSION['role'] = 'master';
        header("Location: master.php"); 
        exit;
    } elseif (isset($_COOKIE['remember_tenant'])) {
        $stmt = $pdo->prepare("SELECT * FROM toko WHERE id_toko = ?");
        $stmt->execute([$_COOKIE['remember_tenant']]);
        if ($toko = $stmt->fetch()) {
            $_SESSION['tenant_id'] = $toko['id_toko'];
            $_SESSION['nama_toko'] = $toko['nama_toko'];
            $_SESSION['role'] = 'tenant';
            header("Location: admin.php"); 
            exit;
        }
    }
}

// ── 3. JIKA SUDAH LOGIN, REDIRECT LANGSUNG ──
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'master') { header("Location: master.php"); exit; }
    if ($_SESSION['role'] === 'tenant') { header("Location: admin.php"); exit; }
}

// ── 4. DATA UNTUK REACT ──
$loginData = [
    'is_login_page' => true,
    'master_wa' => '6281234567890' // GANTI DENGAN NO WA ANDA
];

// Deteksi File Vite
$distPath = __DIR__ . '/react-app/dist/assets/';
$cssFile = ''; $jsFile = '';
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
    <title>Gateway — Pasek SaaS</title>
    <script>window.LOGIN_DATA = <?= json_encode($loginData) ?>;</script>
    <?php if ($cssFile): ?><link rel="stylesheet" href="<?= $cssFile ?>"><?php endif; ?>
    <style>body { background: #080d1a; margin: 0; overflow: hidden; }</style>
</head>
<body>
    <div id="root">
        <?php if (!$jsFile): ?>
            <h2 style="color:white; text-align:center; margin-top:20vh; font-family:sans-serif;">
                React Gateway belum di-build. Jalankan `npm run build`.
            </h2>
        <?php endif; ?>
    </div>
    <?php if ($jsFile): ?><script type="module" src="<?= $jsFile ?>"></script><?php endif; ?>
</body>
</html>
