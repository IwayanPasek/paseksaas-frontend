<?php
// ╔══════════════════════════════════════════════════════════════╗
//  HYBRID BRIDGE: ADMIN DASHBOARD (PHP -> REACT)
// ╚══════════════════════════════════════════════════════════════╝
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ── Proteksi Login & Fallback ID Toko ─────────────────────────
if (empty($_SESSION['tenant_id']) && empty($_SESSION['id_toko'])) { 
    header("Location: login.php"); 
    exit; 
}

$id_toko   = (int) ($_SESSION['tenant_id'] ?? $_SESSION['id_toko']);
$nama_toko = $_SESSION['nama_toko'] ?? 'Dashboard Admin';

// ── Koneksi Database ──────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=websitewayan_db;charset=utf8mb4",
        "wayan_user", "WayanPass123!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// ── Proses DB: Tambah Produk ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
    $nama      = htmlspecialchars(trim($_POST['nama_produk']));
    $harga     = (int) $_POST['harga'];
    $desc      = htmlspecialchars(trim($_POST['deskripsi']));
    $nama_file = "default.jpg";

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== 4) {
        $file_error = $_FILES['foto']['error'];
        $file_size  = $_FILES['foto']['size'];

        if ($file_error === 1 || $file_error === 2 || $file_size > 2097152) {
            header("Location: admin.php?status=error&msg=" . urlencode("Gagal: Ukuran foto maksimal 2MB!"));
            exit;
        }

        $ext       = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nama_file = "prod_" . $id_toko . "_" . time() . "." . $ext;
        $target_dir = __DIR__ . "/assets/img/produk/";
        
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $nama_file);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO produk (id_toko, nama_produk, harga, deskripsi, foto_produk) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_toko, $nama, $harga, $desc, $nama_file]);
        header("Location: admin.php?status=success&msg=" . urlencode($nama)); 
        exit;
    } catch (Exception $e) {
        header("Location: admin.php?status=error&msg=" . urlencode("Database Error: " . $e->getMessage())); 
        exit;
    }
}

// ── Proses DB: Hapus Produk ───────────────────────────────────
if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM produk WHERE id_produk = ? AND id_toko = ?");
        $stmt->execute([(int)$_GET['hapus'], $id_toko]);
        header("Location: admin.php?status=deleted"); 
        exit;
    } catch (Exception $e) {
        header("Location: admin.php?status=error&msg=" . urlencode("Gagal menghapus produk.")); 
        exit;
    }
}

// ── Proses DB: Update AI Persona ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_persona'])) {
    $persona_prompt = htmlspecialchars(trim($_POST['ai_persona_prompt'] ?? ''));
    $gaya_bahasa    = $_POST['ai_gaya_bahasa'] ?? 'formal';

    try {
        $stmt = $pdo->prepare("UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id_toko = ?");
        $stmt->execute([$persona_prompt, $gaya_bahasa, $id_toko]);
    } catch (Exception $e) {
        $stmt2 = $pdo->prepare("UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id = ?");
        $stmt2->execute([$persona_prompt, $gaya_bahasa, $id_toko]);
    }
    header("Location: admin.php?status=persona_saved"); 
    exit;
}

// ── Pengambilan Data untuk React ──────────────────────────────
$prods = $pdo->prepare("SELECT * FROM produk WHERE id_toko = ? ORDER BY id_produk DESC");
$prods->execute([$id_toko]);
$list_produk = $prods->fetchAll();

$stmt_log = $pdo->prepare("SELECT user_query, ai_response, created_at FROM log_chat WHERE id_toko = ? ORDER BY id_log DESC LIMIT 10");
$stmt_log->execute([$id_toko]);
$list_log = $stmt_log->fetchAll();

try {
    $stmt_toko = $pdo->prepare("SELECT ai_persona_prompt, ai_gaya_bahasa, subdomain FROM toko WHERE id_toko = ?");
    $stmt_toko->execute([$id_toko]);
    $data_toko = $stmt_toko->fetch();
} catch (Exception $e) {
    $stmt_toko = $pdo->prepare("SELECT ai_persona_prompt, ai_gaya_bahasa, subdomain FROM toko WHERE id = ?");
    $stmt_toko->execute([$id_toko]);
    $data_toko = $stmt_toko->fetch();
}

$total_nilai = array_sum(array_column($list_produk, 'harga'));

// Bungkus data menjadi JSON untuk dibaca React
$adminData = [
    'id_toko'     => $id_toko,
    'nama_toko'   => $nama_toko,
    'subdomain'   => $data_toko['subdomain'] ?? '',
    'persona'     => $data_toko['ai_persona_prompt'] ?? '',
    'gaya_bahasa' => $data_toko['ai_gaya_bahasa'] ?? 'formal',
    'total_nilai' => $total_nilai,
    'produk'      => $list_produk,
    'log'         => $list_log
];

// ── Deteksi File React Vite ───────────────────────────────────
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
    <title>Dashboard Admin - <?= htmlspecialchars($nama_toko) ?></title>
    <script>
        // Suntikkan data Admin ke React
        window.ADMIN_DATA = <?= json_encode($adminData) ?>;
    </script>
    <?php if ($cssFile): ?><link rel="stylesheet" href="<?= $cssFile ?>"><?php endif; ?>
</head>
<body class="bg-slate-50">
    <div id="root">
        <?php if (!$jsFile): ?>
            <div style="text-align:center; padding: 50px; font-family: sans-serif;">
                <h2>Admin React belum di-build.</h2>
                <p>Silakan jalankan <code>npm run build</code> di folder react-app.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($jsFile): ?><script type="module" src="<?= $jsFile ?>"></script><?php endif; ?>
</body>
</html>
