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
    header('Location: login.php?status=error&msg=Your+account+is+suspended.');
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

// ── Create Category ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Invalid+CSRF+token'); exit; }
    $nama = htmlspecialchars(trim($_POST['categoryName'] ?? $_POST['nama_kategori'] ?? ''));
    if (!empty($nama)) {
        $stmt = $pdo->prepare('INSERT INTO kategori (id_toko, nama_kategori) VALUES (?, ?)');
        $stmt->execute([$_SESSION['tenant_id'], $nama]);
    }
    header('Location: admin.php?tab=categories&status=success&msg=Category+Added');
    exit;
}

// ── Category: Delete (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['deleteCategoryId']) || isset($_POST['del_cat']))) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid&tab=categories'); exit; }
    $id_del = (int) ($_POST['deleteCategoryId'] ?? $_POST['del_cat']);
    $stmt = $pdo->prepare('DELETE FROM kategori WHERE id_kategori = ? AND id_toko = ?');
    $stmt->execute([$id_del, $id_toko]);
    header('Location: admin.php?status=success&msg=Category+Deleted&tab=categories');
    exit;
}

// ── Product: Save (Create/Update) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Invalid+CSRF+token'); exit; }

    $id_prod  = !empty($_POST['productId']) ? (int) $_POST['productId'] : (!empty($_POST['id_produk']) ? (int) $_POST['id_produk'] : null);
    $nama     = trim($_POST['productName'] ?? $_POST['nama_produk'] ?? '');
    $harga    = (int) ($_POST['price'] ?? $_POST['harga'] ?? 0);
    $desc     = trim($_POST['description'] ?? $_POST['deskripsi'] ?? '');
    $id_cat   = !empty($_POST['categoryId']) ? (int) $_POST['categoryId'] : (!empty($_POST['id_kategori']) ? (int) $_POST['id_kategori'] : null);
    $nama_file = !empty($_POST['oldImage']) ? $_POST['oldImage'] : (!empty($_POST['foto_lama']) ? $_POST['foto_lama'] : 'default.jpg');

    // Handle file upload (validated)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime) && $_FILES['image']['size'] <= $max_size) {
            if ($nama_file !== 'default.jpg' && file_exists(UPLOAD_DIR . $nama_file)) {
                @unlink(UPLOAD_DIR . $nama_file);
            }
            $webp_file = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.webp';
            if (optimizeToWebp($_FILES['image']['tmp_name'], UPLOAD_DIR . $webp_file)) {
                $nama_file = $webp_file;
            } else {
                $nama_file = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $nama_file);
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
    header('Location: admin.php?status=success&msg=Service+Saved&tab=products');
    exit;
}

// ── Product: Delete (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['deleteProductId']) || isset($_POST['hapus_prod']))) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid&tab=products'); exit; }
    $id_del = (int) ($_POST['deleteProductId'] ?? $_POST['hapus_prod']);
    $img = $pdo->prepare('SELECT foto_produk FROM produk WHERE id_produk = ? AND id_toko = ?');
    $img->execute([$id_del, $id_toko]);
    $foto = $img->fetchColumn();
    if ($foto && $foto !== 'default.jpg' && file_exists(UPLOAD_DIR . $foto)) {
        @unlink(UPLOAD_DIR . $foto);
    }
    $pdo->prepare('DELETE FROM produk WHERE id_produk = ? AND id_toko = ?')->execute([$id_del, $id_toko]);
    header('Location: admin.php?status=success&msg=Service+Deleted&tab=products');
    exit;
}

// ── AI Persona: Update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_persona'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Invalid+CSRF+token'); exit; }
    $pdo->prepare('UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id_toko = ?')
        ->execute([
            trim($_POST['aiPersonaPrompt'] ?? $_POST['ai_persona_prompt'] ?? ''), 
            $_POST['aiTone'] ?? $_POST['ai_gaya_bahasa'] ?? 'formal', 
            $id_toko
        ]);
    header('Location: admin.php?status=success&msg=AI+Persona+Updated&tab=persona');
    exit;
}

// ── FAQ: Add ──
if (isset($_POST['add_faq'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Invalid+CSRF+token'); exit; }
    $pdo->prepare('INSERT INTO faq_toko (id_toko, pertanyaan, jawaban) VALUES (?, ?, ?)')
        ->execute([
            $id_toko, 
            trim($_POST['question'] ?? $_POST['pertanyaan'] ?? ''), 
            trim($_POST['answer'] ?? $_POST['jawaban'] ?? '')
        ]);
    header('Location: admin.php?status=success&msg=FAQ+Added&tab=persona');
    exit;
}

// ── FAQ: Delete (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['deleteFaqId']) || isset($_POST['del_faq']))) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Token+CSRF+tidak+valid&tab=persona'); exit; }
    $id_del = (int) ($_POST['deleteFaqId'] ?? $_POST['del_faq']);
    $pdo->prepare('DELETE FROM faq_toko WHERE id_faq = ? AND id_toko = ?')->execute([$id_del, $id_toko]);
    header('Location: admin.php?status=success&msg=FAQ+Deleted&tab=persona');
    exit;
}

// ── Store Profile: Update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    if (!csrfVerify()) { header('Location: admin.php?status=error&msg=Invalid+CSRF+token'); exit; }

    $nama_toko = trim($_POST['storeName'] ?? $_POST['nama_toko'] ?? '');
    $wa = preg_replace('/[^0-9]/', '', $_POST['whatsappNumber'] ?? $_POST['kontak_wa'] ?? '');
    $desc = trim($_POST['storeDescription'] ?? $_POST['deskripsi_landing'] ?? '');

    $query = 'UPDATE toko SET nama_toko=?, kontak_wa=?, deskripsi_landing=? WHERE id_toko=?';
    $params = [$nama_toko, $wa, $desc, $id_toko];

    if (isset($_FILES['storeLogo']) && $_FILES['storeLogo']['error'] === 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['storeLogo']['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['storeLogo']['tmp_name']);
        finfo_close($finfo);
        $logo = null;
        if (in_array($ext, $allowed_ext) && in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) && $_FILES['storeLogo']['size'] <= 5 * 1024 * 1024) {
            $webp_logo = 'logo_' . $id_toko . '_' . time() . '.webp';
            if (optimizeToWebp($_FILES['storeLogo']['tmp_name'], UPLOAD_DIR . $webp_logo, 400)) {
                $logo = $webp_logo;
            } else {
                $logo = 'logo_' . $id_toko . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['storeLogo']['tmp_name'], UPLOAD_DIR . $logo);
            }
        }
        if ($logo) {
            $query = 'UPDATE toko SET nama_toko=?, kontak_wa=?, deskripsi_landing=?, logo=? WHERE id_toko=?';
            $params = [$nama_toko, $wa, $desc, $logo, $id_toko];
            $_SESSION['nama_toko'] = $nama_toko;
        }
    }

    $pdo->prepare($query)->execute($params);
    header('Location: admin.php?status=success&msg=Profile+Updated&tab=settings');
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

// Map Products to English keys
$mapped_products = array_map(fn($p) => [
    'id'          => (int)$p['id_produk'],
    'name'        => $p['nama_produk'],
    'price'       => (int)$p['harga'],
    'description' => $p['deskripsi'],
    'image'       => $p['foto_produk'] ?: 'default.jpg',
    'categoryId'  => (int)$p['id_kategori'],
], $list_produk);

// Map Categories
$mapped_categories = array_map(fn($c) => [
    'id'   => (int)$c['id_kategori'],
    'name' => $c['nama_kategori'],
], $list_kategori);

// Map Recent Logs
$mapped_logs = array_map(fn($l) => [
    'query'    => $l['user_query'],
    'response' => $l['ai_response'],
    'date'     => $l['created_at'],
], $list_log);

// Map FAQs
$mapped_faqs = array_map(fn($f) => [
    'id'       => (int)$f['id_faq'],
    'question' => $f['pertanyaan'],
    'answer'   => $f['jawaban'],
], $list_faq);

$adminData = [
    'store' => [
        'name'        => $data_toko['nama_toko'],
        'whatsapp'    => $data_toko['kontak_wa'],
        'description' => $data_toko['deskripsi_landing'],
        'logo'        => $data_toko['logo'],
        'aiPersona'   => $data_toko['ai_persona_prompt'],
        'aiTone'      => $data_toko['ai_gaya_bahasa'] ?: 'formal',
        'subdomain'   => $data_toko['subdomain'],
    ],
    'totalInventoryValue' => (int) array_sum(array_column($list_produk, 'harga')),
    'products'            => $mapped_products,
    'categories'          => $mapped_categories,
    'recentLogs'          => $mapped_logs,
    'faqs'                => $mapped_faqs,
    'analytics'           => $grafik,
    'currentTab'          => $_GET['tab'] ?? 'dashboard',
    'csrfToken'           => $csrfToken,
    'isImpersonating'     => $_SESSION['is_impersonating'] ?? false,
];

renderReactShell(
    'Dashboard — ' . htmlspecialchars($data_toko['nama_toko']),
    'ADMIN_DATA',
    $adminData
);