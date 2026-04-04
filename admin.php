<?php
// ╔══════════════════════════════════════════════════════════════╗
//  HYBRID BRIDGE: ENTERPRISE ADMIN DASHBOARD (ANALYTICS, FAQ, PROFIL)
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

// ── 1. MANAJEMEN KATEGORI & PRODUK (Sama seperti sebelumnya) ──
if (isset($_POST['add_category'])) {
    $stmt = $pdo->prepare("INSERT INTO kategori (id_toko, nama_kategori) VALUES (?, ?)");
    $stmt->execute([$id_toko, htmlspecialchars($_POST['nama_kategori'])]);
    header("Location: admin.php?status=success&msg=Kategori Ditambahkan&tab=kategori"); exit;
}
if (isset($_GET['del_cat'])) {
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ? AND id_toko = ?");
    $stmt->execute([(int)$_GET['del_cat'], $id_toko]);
    header("Location: admin.php?status=success&msg=Kategori Dihapus&tab=kategori"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id_prod = !empty($_POST['id_produk']) ? (int)$_POST['id_produk'] : null;
    $nama    = htmlspecialchars($_POST['nama_produk']);
    $harga   = (int)$_POST['harga'];
    $desc    = htmlspecialchars($_POST['deskripsi']);
    $id_cat  = !empty($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : null;
    $nama_file = !empty($_POST['foto_lama']) ? $_POST['foto_lama'] : "default.jpg";

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        if ($nama_file !== "default.jpg" && file_exists(__DIR__ . "/assets/img/produk/" . $nama_file)) { @unlink(__DIR__ . "/assets/img/produk/" . $nama_file); }
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = "prod_" . time() . "_" . rand(100,999) . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/assets/img/produk/" . $nama_file);
    }

    if ($id_prod) {
        $pdo->prepare("UPDATE produk SET nama_produk=?, harga=?, deskripsi=?, foto_produk=?, id_kategori=? WHERE id_produk=? AND id_toko=?")
            ->execute([$nama, $harga, $desc, $nama_file, $id_cat, $id_prod, $id_toko]);
    } else {
        $pdo->prepare("INSERT INTO produk (id_toko, nama_produk, harga, deskripsi, foto_produk, id_kategori) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$id_toko, $nama, $harga, $desc, $nama_file, $id_cat]);
    }
    header("Location: admin.php?status=success&msg=Layanan Disimpan&tab=produk"); exit;
}
if (isset($_GET['hapus_prod'])) {
    $id_del = (int)$_GET['hapus_prod'];
    $img = $pdo->prepare("SELECT foto_produk FROM produk WHERE id_produk = ? AND id_toko = ?");
    $img->execute([$id_del, $id_toko]);
    $foto = $img->fetchColumn();
    if ($foto && $foto !== "default.jpg" && file_exists(__DIR__ . "/assets/img/produk/" . $foto)) { @unlink(__DIR__ . "/assets/img/produk/" . $foto); }
    $pdo->prepare("DELETE FROM produk WHERE id_produk = ? AND id_toko = ?")->execute([$id_del, $id_toko]);
    header("Location: admin.php?status=success&msg=Layanan Dihapus&tab=produk"); exit;
}

// ── 2. UPDATE PERSONA & FAQ AI ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_persona'])) {
    $pdo->prepare("UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id_toko = ?")
        ->execute([htmlspecialchars($_POST['ai_persona_prompt']), $_POST['ai_gaya_bahasa'], $id_toko]);
    header("Location: admin.php?status=success&msg=Karakter AI Diperbarui&tab=persona"); exit;
}
if (isset($_POST['add_faq'])) {
    $pdo->prepare("INSERT INTO faq_toko (id_toko, pertanyaan, jawaban) VALUES (?, ?, ?)")
        ->execute([$id_toko, htmlspecialchars($_POST['pertanyaan']), htmlspecialchars($_POST['jawaban'])]);
    header("Location: admin.php?status=success&msg=FAQ Ditambahkan&tab=persona"); exit;
}
if (isset($_GET['del_faq'])) {
    $pdo->prepare("DELETE FROM faq_toko WHERE id_faq = ? AND id_toko = ?")->execute([(int)$_GET['del_faq'], $id_toko]);
    header("Location: admin.php?status=success&msg=FAQ Dihapus&tab=persona"); exit;
}

// ── 3. UPDATE PROFIL TOKO ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama_toko_baru = htmlspecialchars($_POST['nama_toko']);
    $wa_baru = preg_replace('/[^0-9]/', '', $_POST['kontak_wa']);
    $desc_landing = htmlspecialchars($_POST['deskripsi_landing']);
    
    // Proses Logo
    $query_update = "UPDATE toko SET nama_toko=?, kontak_wa=?, deskripsi_landing=? WHERE id_toko=?";
    $params = [$nama_toko_baru, $wa_baru, $desc_landing, $id_toko];

    if (isset($_FILES['logo_toko']) && $_FILES['logo_toko']['error'] === 0) {
        $ext = pathinfo($_FILES['logo_toko']['name'], PATHINFO_EXTENSION);
        $nama_logo = "logo_" . $id_toko . "_" . time() . "." . $ext;
        move_uploaded_file($_FILES['logo_toko']['tmp_name'], __DIR__ . "/assets/img/produk/" . $nama_logo);
        
        $query_update = "UPDATE toko SET nama_toko=?, kontak_wa=?, deskripsi_landing=?, logo=? WHERE id_toko=?";
        $params = [$nama_toko_baru, $wa_baru, $desc_landing, $nama_logo, $id_toko];
        $_SESSION['nama_toko'] = $nama_toko_baru; // Update session
    }
    
    $pdo->prepare($query_update)->execute($params);
    header("Location: admin.php?status=success&msg=Profil Toko Diperbarui&tab=pengaturan"); exit;
}

// ── 4. KUMPULKAN DATA UNTUK REACT ──
$list_produk = $pdo->query("SELECT * FROM produk WHERE id_toko = $id_toko ORDER BY id_produk DESC")->fetchAll(PDO::FETCH_ASSOC);
$list_kategori = $pdo->query("SELECT * FROM kategori WHERE id_toko = $id_toko ORDER BY id_kategori DESC")->fetchAll(PDO::FETCH_ASSOC);
$list_log = $pdo->query("SELECT user_query, ai_response, created_at FROM log_chat WHERE id_toko = $id_toko ORDER BY id_log DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
$list_faq = $pdo->query("SELECT * FROM faq_toko WHERE id_toko = $id_toko ORDER BY id_faq DESC")->fetchAll(PDO::FETCH_ASSOC);
$data_toko = $pdo->query("SELECT nama_toko, kontak_wa, deskripsi_landing, logo, ai_persona_prompt, ai_gaya_bahasa, subdomain FROM toko WHERE id_toko = $id_toko")->fetch(PDO::FETCH_ASSOC);

// Data Analitik 7 Hari Terakhir (Untuk Recharts)
$grafik_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $display_date = date('d M', strtotime("-$i days"));
    $count = $pdo->query("SELECT COUNT(*) FROM log_chat WHERE id_toko = $id_toko AND DATE(created_at) = '$date'")->fetchColumn();
    $grafik_data[] = ['name' => $display_date, 'interaksi' => (int)$count];
}

$adminData = [
    'toko'        => $data_toko,
    'total_nilai' => array_sum(array_column($list_produk, 'harga')),
    'produk'      => $list_produk,
    'kategori'    => $list_kategori,
    'log'         => $list_log,
    'faq'         => $list_faq,
    'grafik'      => $grafik_data,
    'tab_aktif'   => $_GET['tab'] ?? 'dashboard'
];

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
    <title>Dashboard Admin - <?= htmlspecialchars($adminData['toko']['nama_toko']) ?></title>
    <script>window.ADMIN_DATA = <?= json_encode($adminData) ?>;</script>
    <?php if ($cssFile): ?><link rel="stylesheet" href="<?= $cssFile ?>"><?php endif; ?>
    <style>body { background-color: #f8fafc; margin: 0; font-family: sans-serif; }</style>
</head>
<body>
    <div id="root">
        <?php if (!$jsFile): ?><h2>Aplikasi React belum di-build.</h2><?php endif; ?>
    </div>
    <?php if ($jsFile): ?><script type="module" src="<?= $jsFile ?>"></script><?php endif; ?>
</body>
</html>
