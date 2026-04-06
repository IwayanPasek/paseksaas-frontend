<?php
// ═══════════════════════════════════════════════════
//  ADMIN DASHBOARD — PHP Backend (CRUD + React Bridge)
// ═══════════════════════════════════════════════════
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/vite.php';

$id_toko = requireTenant();
$pdo = getDB();

// ════════════════════════════════════════════════════
//  MUTATIONS (POST/GET actions)
// ════════════════════════════════════════════════════

// ── Category: Add ──
if (isset($_POST['add_category'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid'); exit; }
    $stmt = $pdo->prepare('INSERT INTO kategori (id_toko, nama_kategori) VALUES (?, ?)');
    $stmt->execute([$id_toko, trim($_POST['nama_kategori'])]);
    header('Location: admin.php?status=success&msg=Kategori+Ditambahkan&tab=kategori');
    exit;
}

// ── Category: Delete ──
if (isset($_GET['del_cat'])) {
    $stmt = $pdo->prepare('DELETE FROM kategori WHERE id_kategori = ? AND id_toko = ?');
    $stmt->execute([(int) $_GET['del_cat'], $id_toko]);
    header('Location: admin.php?status=success&msg=Kategori+Dihapus&tab=kategori');
    exit;
}

// ── Product: Save (Create/Update) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid'); exit; }

    $id_prod  = !empty($_POST['id_produk']) ? (int) $_POST['id_produk'] : null;
    $nama     = trim($_POST['nama_produk']);
    $harga    = (int) $_POST['harga'];
    $desc     = trim($_POST['deskripsi']);
    $id_cat   = !empty($_POST['id_kategori']) ? (int) $_POST['id_kategori'] : null;
    $nama_file = !empty($_POST['foto_lama']) ? $_POST['foto_lama'] : 'default.jpg';

    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        if ($nama_file !== 'default.jpg' && file_exists(UPLOAD_DIR . $nama_file)) {
            @unlink(UPLOAD_DIR . $nama_file);
        }
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $nama_file);
    }

    if ($id_prod) {
        $pdo->prepare('UPDATE produk SET nama_produk=?, harga=?, deskripsi=?, foto_produk=?, id_kategori=? WHERE id_produk=? AND id_toko=?')
            ->execute([$nama, $harga, $desc, $nama_file, $id_cat, $id_prod, $id_toko]);
    } else {
        $pdo->prepare('INSERT INTO produk (id_toko, nama_produk, harga, deskripsi, foto_produk, id_kategori) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$id_toko, $nama, $harga, $desc, $nama_file, $id_cat]);
    }
    header('Location: admin.php?status=success&msg=Layanan+Disimpan&tab=produk');
    exit;
}

// ── Product: Delete ──
if (isset($_GET['hapus_prod'])) {
    $id_del = (int) $_GET['hapus_prod'];
    $img = $pdo->prepare('SELECT foto_produk FROM produk WHERE id_produk = ? AND id_toko = ?');
    $img->execute([$id_del, $id_toko]);
    $foto = $img->fetchColumn();
    if ($foto && $foto !== 'default.jpg' && file_exists(UPLOAD_DIR . $foto)) {
        @unlink(UPLOAD_DIR . $foto);
    }
    $pdo->prepare('DELETE FROM produk WHERE id_produk = ? AND id_toko = ?')->execute([$id_del, $id_toko]);
    header('Location: admin.php?status=success&msg=Layanan+Dihapus&tab=produk');
    exit;
}

// ── AI Persona: Update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_persona'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid'); exit; }
    $pdo->prepare('UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id_toko = ?')
        ->execute([trim($_POST['ai_persona_prompt']), $_POST['ai_gaya_bahasa'], $id_toko]);
    header('Location: admin.php?status=success&msg=Karakter+AI+Diperbarui&tab=persona');
    exit;
}

// ── FAQ: Add ──
if (isset($_POST['add_faq'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid'); exit; }
    $pdo->prepare('INSERT INTO faq_toko (id_toko, pertanyaan, jawaban) VALUES (?, ?, ?)')
        ->execute([$id_toko, trim($_POST['pertanyaan']), trim($_POST['jawaban'])]);
    header('Location: admin.php?status=success&msg=FAQ+Ditambahkan&tab=persona');
    exit;
}

// ── FAQ: Delete ──
if (isset($_GET['del_faq'])) {
    $pdo->prepare('DELETE FROM faq_toko WHERE id_faq = ? AND id_toko = ?')->execute([(int) $_GET['del_faq'], $id_toko]);
    header('Location: admin.php?status=success&msg=FAQ+Dihapus&tab=persona');
    exit;
}

// ── Store Profile: Update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid'); exit; }

    $nama_toko = trim($_POST['nama_toko']);
    $wa = preg_replace('/[^0-9]/', '', $_POST['kontak_wa']);
    $desc = trim($_POST['deskripsi_landing']);

    $query = 'UPDATE toko SET nama_toko=?, kontak_wa=?, deskripsi_landing=? WHERE id_toko=?';
    $params = [$nama_toko, $wa, $desc, $id_toko];

    if (isset($_FILES['logo_toko']) && $_FILES['logo_toko']['error'] === 0) {
        $ext = pathinfo($_FILES['logo_toko']['name'], PATHINFO_EXTENSION);
        $logo = 'logo_' . $id_toko . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['logo_toko']['tmp_name'], UPLOAD_DIR . $logo);
        $query = 'UPDATE toko SET nama_toko=?, kontak_wa=?, deskripsi_landing=?, logo=? WHERE id_toko=?';
        $params = [$nama_toko, $wa, $desc, $logo, $id_toko];
        $_SESSION['nama_toko'] = $nama_toko;
    }

    $pdo->prepare($query)->execute($params);
    header('Location: admin.php?status=success&msg=Profil+Toko+Diperbarui&tab=pengaturan');
    exit;
}

// ════════════════════════════════════════════════════
//  DATA COLLECTION (all using prepared statements)
// ════════════════════════════════════════════════════

$stmt = $pdo->prepare('SELECT * FROM produk WHERE id_toko = ? ORDER BY id_produk DESC');
$stmt->execute([$id_toko]);
$list_produk = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM kategori WHERE id_toko = ? ORDER BY id_kategori DESC');
$stmt->execute([$id_toko]);
$list_kategori = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT user_query, ai_response, created_at FROM log_chat WHERE id_toko = ? ORDER BY id_log DESC LIMIT 20');
$stmt->execute([$id_toko]);
$list_log = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM faq_toko WHERE id_toko = ? ORDER BY id_faq DESC');
$stmt->execute([$id_toko]);
$list_faq = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT nama_toko, kontak_wa, deskripsi_landing, logo, ai_persona_prompt, ai_gaya_bahasa, subdomain FROM toko WHERE id_toko = ?');
$stmt->execute([$id_toko]);
$data_toko = $stmt->fetch();

// Analytics: 7-day chat trend
$grafik = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $display = date('d M', strtotime("-$i days"));
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM log_chat WHERE id_toko = ? AND DATE(created_at) = ?');
    $stmt->execute([$id_toko, $date]);
    $grafik[] = ['name' => $display, 'interaksi' => (int) $stmt->fetchColumn()];
}

// ── Inject CSRF token for React forms ──
$csrfToken = csrfToken();

$adminData = [
    'toko'        => $data_toko,
    'total_nilai' => array_sum(array_column($list_produk, 'harga')),
    'produk'      => $list_produk,
    'kategori'    => $list_kategori,
    'log'         => $list_log,
    'faq'         => $list_faq,
    'grafik'      => $grafik,
    'tab_aktif'   => $_GET['tab'] ?? 'dashboard',
    'csrf_token'  => $csrfToken,
];

renderReactShell(
    'Dashboard — ' . htmlspecialchars($data_toko['nama_toko']),
    'ADMIN_DATA',
    $adminData
);
