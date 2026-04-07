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

// ── Check Suspension ──
$stmtStatus = $pdo->prepare('SELECT status FROM toko WHERE id_toko = ?');
$stmtStatus->execute([$id_toko]);
$status = $stmtStatus->fetchColumn();

if ($status === 'suspended') {
    session_destroy();
    header('Location: login.php?status=error&msg=Akun+Anda+ditangguhkan.');
    exit;
}

// ── WebP Optimization Helper (Resizes to max width 800px and converts to WebP) ──
function optimizeToWebp(string $sourcePath, string $destPath, int $maxWidth = 800, int $quality = 85): bool {
    if (!extension_loaded('gd')) return false;
    $info = @getimagesize($sourcePath);
    if (!$info) return false;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($sourcePath); break;
        case 'image/png':  $img = @imagecreatefrompng($sourcePath); break;
        case 'image/webp': $img = @imagecreatefromwebp($sourcePath); break;
        case 'image/gif':  $img = @imagecreatefromgif($sourcePath); break;
        default: return false;
    }
    if (!$img) return false;

    $width = imagesx($img);
    $height = imagesy($img);

    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int)(($maxWidth / $width) * $height);
        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($newImg, false);
            imagesavealpha($newImg, true);
            $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
            imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
        }
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($img);
        $img = $newImg;
    }
    
    // Convert to webp
    $success = @imagewebp($img, $destPath, $quality);
    imagedestroy($img);
    return $success;
}

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

// ── Category: Delete (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_cat'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid&tab=kategori'); exit; }
    $stmt = $pdo->prepare('DELETE FROM kategori WHERE id_kategori = ? AND id_toko = ?');
    $stmt->execute([(int) $_POST['del_cat'], $id_toko]);
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

    // Handle file upload (validated)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime) && $_FILES['foto']['size'] <= $max_size) {
            if ($nama_file !== 'default.jpg' && file_exists(UPLOAD_DIR . $nama_file)) {
                @unlink(UPLOAD_DIR . $nama_file);
            }
            $webp_file = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.webp';
            if (optimizeToWebp($_FILES['foto']['tmp_name'], UPLOAD_DIR . $webp_file)) {
                $nama_file = $webp_file;
            } else {
                $nama_file = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $nama_file);
            }
        }
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

// ── Product: Delete (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_prod'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid&tab=produk'); exit; }
    $id_del = (int) $_POST['hapus_prod'];
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

// ── FAQ: Delete (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_faq'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid&tab=persona'); exit; }
    $pdo->prepare('DELETE FROM faq_toko WHERE id_faq = ? AND id_toko = ?')->execute([(int) $_POST['del_faq'], $id_toko]);
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
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['logo_toko']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['logo_toko']['tmp_name']);
        finfo_close($finfo);
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $logo = null;
        if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime) && $_FILES['logo_toko']['size'] <= 5 * 1024 * 1024) {
            $webp_logo = 'logo_' . $id_toko . '_' . time() . '.webp';
            if (optimizeToWebp($_FILES['logo_toko']['tmp_name'], UPLOAD_DIR . $webp_logo, 400)) { // Logo is smaller, max 400px
                $logo = $webp_logo;
            } else {
                $logo = 'logo_' . $id_toko . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['logo_toko']['tmp_name'], UPLOAD_DIR . $logo);
            }
        }
        if (!$logo) { header('Location: admin.php?status=error&msg=Format+file+tidak+valid&tab=pengaturan'); exit; }
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
    'store' => [
        'name'        => $data_toko['nama_toko'],
        'whatsapp'    => $data_toko['kontak_wa'],
        'description' => $data_toko['deskripsi_landing'],
        'logo'        => $data_toko['logo'],
        'ai_persona'  => $data_toko['ai_persona_prompt'],
        'ai_tone'     => $data_toko['ai_gaya_bahasa'] ?: 'formal',
        'subdomain'   => $data_toko['subdomain'],
    ],
    'total_inventory_value' => (int) array_sum(array_column($list_produk, 'harga')),
    'products'              => $list_produk,
    'categories'            => $list_kategori,
    'recent_logs'           => $list_log,
    'faqs'                  => $list_faq,
    'analytics'             => $grafik,
    'current_tab'           => $_GET['tab'] ?? 'dashboard',
    'csrf_token'            => $csrfToken,
    'is_impersonating'      => $_SESSION['is_impersonating'] ?? false,
];

renderReactShell(
    'Dashboard — ' . htmlspecialchars($data_toko['nama_toko']),
    'ADMIN_DATA',
    $adminData
);
