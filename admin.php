<?php
// ╔══════════════════════════════════════════════════════════════╗
//  HYBRID BRIDGE: FULL ADMIN DASHBOARD (CRUD, LOGS, PERSONA)
// ╚══════════════════════════════════════════════════════════════╝
session_start();

if (empty($_SESSION['tenant_id'])) { 
    header("Location: login.php"); 
    exit; 
}
$id_toko = (int)$_SESSION['tenant_id'];

$db_host = 'localhost';
$db_user = 'wayan_user';
$db_pass = 'WayanPass123!';
$db_name = 'websitewayan_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) { 
    die("Database Error"); 
}

// ── 1. MANAJEMEN KATEGORI ──
if (isset($_POST['add_category'])) {
    $nama_kat = htmlspecialchars(trim($_POST['nama_kategori']));
    if (!empty($nama_kat)) {
        $stmt = $pdo->prepare("INSERT INTO kategori (id_toko, nama_kategori) VALUES (?, ?)");
        $stmt->execute([$id_toko, $nama_kat]);
    }
    header("Location: admin.php?status=success&msg=Kategori Ditambahkan&tab=kategori"); 
    exit;
}

if (isset($_GET['del_cat'])) {
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ? AND id_toko = ?");
    $stmt->execute([(int)$_GET['del_cat'], $id_toko]);
    header("Location: admin.php?status=success&msg=Kategori Dihapus&tab=kategori"); 
    exit;
}

// ── 2. TAMBAH / EDIT PRODUK ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id_prod = !empty($_POST['id_produk']) ? (int)$_POST['id_produk'] : null;
    $nama    = htmlspecialchars($_POST['nama_produk']);
    $harga   = (int)$_POST['harga'];
    $desc    = htmlspecialchars($_POST['deskripsi']);
    $id_cat  = !empty($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : null;
    
    $nama_file = !empty($_POST['foto_lama']) ? $_POST['foto_lama'] : "default.jpg";

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        if ($nama_file !== "default.jpg" && file_exists(__DIR__ . "/assets/img/produk/" . $nama_file)) { 
            @unlink(__DIR__ . "/assets/img/produk/" . $nama_file); 
        }
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = "prod_" . time() . "_" . rand(100,999) . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/assets/img/produk/" . $nama_file);
    }

    if ($id_prod) {
        $stmt = $pdo->prepare("UPDATE produk SET nama_produk=?, harga=?, deskripsi=?, foto_produk=?, id_kategori=? WHERE id_produk=? AND id_toko=?");
        $stmt->execute([$nama, $harga, $desc, $nama_file, $id_cat, $id_prod, $id_toko]);
        $msg = "Layanan Diperbarui";
    } else {
        $stmt = $pdo->prepare("INSERT INTO produk (id_toko, nama_produk, harga, deskripsi, foto_produk, id_kategori) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_toko, $nama, $harga, $desc, $nama_file, $id_cat]);
        $msg = "Layanan Ditambahkan";
    }
    header("Location: admin.php?status=success&msg=" . urlencode($msg) . "&tab=produk"); 
    exit;
}

// ── 3. HAPUS PRODUK ──
if (isset($_GET['hapus'])) {
    $id_del = (int)$_GET['hapus'];
    $stmt_img = $pdo->prepare("SELECT foto_produk FROM produk WHERE id_produk = ? AND id_toko = ?");
    $stmt_img->execute([$id_del, $id_toko]);
    $img = $stmt_img->fetchColumn();
    
    if ($img && $img !== "default.jpg" && file_exists(__DIR__ . "/assets/img/produk/" . $img)) {
        @unlink(__DIR__ . "/assets/img/produk/" . $img); 
    }
    
    $stmt = $pdo->prepare("DELETE FROM produk WHERE id_produk = ? AND id_toko = ?");
    $stmt->execute([$id_del, $id_toko]);
    header("Location: admin.php?status=success&msg=Layanan Dihapus&tab=produk"); 
    exit;
}

// ── 4. UPDATE PERSONA AI ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_persona'])) {
    $persona = htmlspecialchars(trim($_POST['ai_persona_prompt'] ?? ''));
    $gaya    = $_POST['ai_gaya_bahasa'] ?? 'formal';
    $stmt = $pdo->prepare("UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id_toko = ?");
    $stmt->execute([$persona, $gaya, $id_toko]);
    header("Location: admin.php?status=success&msg=Karakter AI Diperbarui&tab=persona"); 
    exit;
}

// ── 5. KUMPULKAN DATA UNTUK REACT ──
$list_produk = [];
$list_kategori = [];
$list_log = [];
$data_toko = [];

try {
    $list_produk = $pdo->query("SELECT * FROM produk WHERE id_toko = $id_toko ORDER BY id_produk DESC")->fetchAll(PDO::FETCH_ASSOC);
    $list_kategori = $pdo->query("SELECT * FROM kategori WHERE id_toko = $id_toko ORDER BY id_kategori DESC")->fetchAll(PDO::FETCH_ASSOC);
    $list_log = $pdo->query("SELECT user_query, ai_response, created_at FROM log_chat WHERE id_toko = $id_toko ORDER BY id_log DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
    $data_toko = $pdo->query("SELECT ai_persona_prompt, ai_gaya_bahasa, subdomain FROM toko WHERE id_toko = $id_toko")->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$total_nilai = array_sum(array_column($list_produk, 'harga'));

$adminData = [
    'nama_toko'   => $_SESSION['nama_toko'] ?? 'Admin',
    'subdomain'   => $data_toko['subdomain'] ?? '',
    'persona'     => $data_toko['ai_persona_prompt'] ?? '',
    'gaya_bahasa' => $data_toko['ai_gaya_bahasa'] ?? 'formal',
    'total_nilai' => $total_nilai,
    'produk'      => $list_produk,
    'kategori'    => $list_kategori,
    'log'         => $list_log,
    'tab_aktif'   => $_GET['tab'] ?? 'dashboard'
];

// ── DETEKSI VITE ASSETS ──
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
    <title>Dashboard Admin - <?= htmlspecialchars($adminData['nama_toko']) ?></title>
    <script>window.ADMIN_DATA = <?= json_encode($adminData) ?>;</script>
    <?php if ($cssFile): ?><link rel="stylesheet" href="<?= $cssFile ?>"><?php endif; ?>
    <style>body { background-color: #f8fafc; margin: 0; font-family: sans-serif; }</style>
</head>
<body>
    <div id="root">
        <?php if (!$jsFile): ?>
            <div style="text-align:center; padding: 50px;">
                <h2>Admin React belum di-build.</h2>
                <p>Silakan jalankan <code>npm run build</code> di folder react-app.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($jsFile): ?><script type="module" src="<?= $jsFile ?>"></script><?php endif; ?>
</body>
</html>
