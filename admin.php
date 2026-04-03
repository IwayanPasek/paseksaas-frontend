<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ── Proteksi Login & Fallback ID Toko ─────────────────────────
if (empty($_SESSION['tenant_id']) && empty($_SESSION['id_toko'])) { 
    header("Location: login.php"); 
    exit; 
}

// Mengatasi perubahan nama kolom di session dan menguncinya sebagai Integer
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

// ── Tambah Produk (Versi Bulletproof & Aman) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
    $nama      = htmlspecialchars(trim($_POST['nama_produk']));
    $harga     = (int) $_POST['harga'];
    $desc      = htmlspecialchars(trim($_POST['deskripsi']));
    $nama_file = "default.jpg";
    $upload_ok = true;

    // Proses Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== 4) {
        $file_error = $_FILES['foto']['error'];
        $file_size  = $_FILES['foto']['size'];

        // Validasi Maksimal 2MB
        if ($file_error === 1 || $file_error === 2 || $file_size > 2097152) {
            header("Location: admin.php?status=error&msg=" . urlencode("Gagal: Ukuran foto maksimal 2MB!"));
            exit;
        } elseif ($file_error !== UPLOAD_ERR_OK) {
            header("Location: admin.php?status=error&msg=" . urlencode("Error upload sistem: " . $file_error));
            exit;
        }

        $ext       = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nama_file = "prod_" . $id_toko . "_" . time() . "." . $ext;
        $target_dir = __DIR__ . "/assets/img/produk/";
        
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $nama_file)) {
            $upload_ok = true;
        } else {
            header("Location: admin.php?status=error&msg=" . urlencode("Gagal menyimpan foto ke server."));
            exit;
        }
    }

    // Eksekusi DB
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

// ── Hapus Produk ──────────────────────────────────────────────
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

// ── Update AI Persona ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_persona'])) {
    $persona_prompt = htmlspecialchars(trim($_POST['ai_persona_prompt'] ?? ''));
    $gaya_bahasa    = $_POST['ai_gaya_bahasa'] ?? 'formal';
    $allowed_gaya   = ['formal', 'santai', 'profesional', 'ramah', 'singkat'];
    if (!in_array($gaya_bahasa, $allowed_gaya)) { $gaya_bahasa = 'formal'; }

    try {
        $stmt = $pdo->prepare("UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id_toko = ?");
        $stmt->execute([$persona_prompt, $gaya_bahasa, $id_toko]);
        header("Location: admin.php?status=persona_saved"); 
        exit;
    } catch (Exception $e) {
        // Fallback jika primary key toko ternyata 'id' bukan 'id_toko'
        try {
            $stmt2 = $pdo->prepare("UPDATE toko SET ai_persona_prompt = ?, ai_gaya_bahasa = ? WHERE id = ?");
            $stmt2->execute([$persona_prompt, $gaya_bahasa, $id_toko]);
            header("Location: admin.php?status=persona_saved"); 
            exit;
        } catch (Exception $e2) {
            header("Location: admin.php?status=error&msg=" . urlencode($e2->getMessage())); 
            exit;
        }
    }
}

// ── Query Data ────────────────────────────────────────────────
$prods = $pdo->prepare("SELECT * FROM produk WHERE id_toko = ? ORDER BY id_produk DESC");
$prods->execute([$id_toko]);
$list_produk = $prods->fetchAll();

$stmt_log = $pdo->prepare("SELECT user_query, ai_response, created_at FROM log_chat WHERE id_toko = ? ORDER BY id_log DESC LIMIT 5");
$stmt_log->execute([$id_toko]);
$list_log = $stmt_log->fetchAll();

// Fallback pencarian ID Toko di pengaturan persona
try {
    $stmt_toko = $pdo->prepare("SELECT ai_persona_prompt, ai_gaya_bahasa FROM toko WHERE id_toko = ?");
    $stmt_toko->execute([$id_toko]);
    $data_toko = $stmt_toko->fetch();
} catch (Exception $e) {
    $stmt_toko = $pdo->prepare("SELECT ai_persona_prompt, ai_gaya_bahasa FROM toko WHERE id = ?");
    $stmt_toko->execute([$id_toko]);
    $data_toko = $stmt_toko->fetch();
}

$current_persona = $data_toko['ai_persona_prompt'] ?? '';
$current_gaya    = $data_toko['ai_gaya_bahasa']    ?? 'formal';

$total_nilai = array_sum(array_column($list_produk, 'harga'));
$total_log   = count($list_log);

$gaya_options = [
    'formal'      => 'Formal — Sopan dan terstruktur',
    'santai'      => 'Santai — Casual dan akrab',
    'profesional' => 'Profesional — Bisnis dan to-the-point',
    'ramah'       => 'Ramah — Hangat dan suportif',
    'singkat'     => 'Singkat — Jawaban padat, tanpa basa-basi',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard — <?= htmlspecialchars($nama_toko) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans:  ['"DM Sans"','sans-serif'],
              mono:  ['"DM Mono"','monospace'],
            }
          }
        }
      }
    </script>

    <style>
        /* ══════════════════════════════════════════════
           VARIABLES (LIGHT MODE - CLEAN & ZEN)
        ══════════════════════════════════════════════ */
        :root {
            /* Backgrounds */
            --bg:         #f8fafc;
            --surface:    #ffffff;
            --surface-2:  #f1f5f9;
            --surface-3:  #e2e8f0;
            
            /* Borders */
            --border:     #e2e8f0;
            --border-2:   #cbd5e1;
            
            /* Text Colors */
            --text:       #0f172a;
            --text-p:     #334155;
            --muted:      #64748b;
            --muted-2:    #94a3b8;
            
            /* Brand Colors (WATER BLUE) */
            --brand:      #0ea5e9;
            --brand-dark: #0284c7;
            --brand-dim:  #e0f2fe;
            --brand-glow: rgba(14, 165, 233, 0.2);
            
            /* Accent Colors */
            --green:      #10b981; --green-dim:  #d1fae5;
            --red:        #ef4444; --red-dim:    #ffe4e6;
            --purple:     #8b5cf6; --purple-dim: #ede9fe;
            --amber:      #f59e0b; --amber-dim:  #fef3c7;
            
            /* Sizing */
            --nav-h:      65px;
            --topbar-h:   60px;
            
            /* Shadows */
            --shadow-sm:  0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow:     0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-md:  0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --shadow-lg:  0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }

        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0; font-family: 'DM Sans', sans-serif;
            background: var(--bg); color: var(--text-p);
            min-height: 100vh; -webkit-font-smoothing: antialiased;
            padding-bottom: var(--nav-h);
        }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 4px; }

        /* ══════════════════════════════════════════════
           LAYOUT & SIDEBAR (DESKTOP)
        ══════════════════════════════════════════════ */
        .app-shell { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        #sidebar {
            width: 250px; background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 50;
            overflow-y: auto;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            box-shadow: var(--shadow-sm);
        }
        .sidebar-logo {
            padding: 1.5rem 1.5rem 1rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: .7rem; flex-shrink: 0;
        }
        .logo-mark {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff;
            font-weight: 800; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            box-shadow: 0 4px 10px var(--brand-glow);
        }
        .logo-text { font-weight: 800; font-size: 1.1rem; letter-spacing: -.02em; color: var(--text); }
        .logo-text span { color: var(--brand); }

        .sidebar-nav { padding: 1.5rem 1rem 0; flex: 1; }
        .nav-section-label {
            font-size: .65rem; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: var(--muted-2);
            padding: 0 .5rem; margin-bottom: .5rem;
        }
        .nav-item {
            display: flex; align-items: center; gap: .7rem;
            padding: .65rem .85rem; border-radius: 10px;
            font-size: .85rem; font-weight: 600; color: var(--muted);
            text-decoration: none; cursor: pointer;
            transition: all .2s; border: none; background: none; width: 100%; text-align: left;
        }
        .nav-item:hover { background: var(--surface-2); color: var(--text); }
        .nav-item.active { background: var(--brand-dim); color: var(--brand-dark); }
        .nav-item i { font-size: 1.1rem; width: 20px; text-align: center; }
        
        .nav-badge {
            margin-left: auto; font-size: .65rem; font-weight: 700;
            background: var(--brand); color: #fff;
            padding: .1rem .45rem; border-radius: 100px; font-family: 'DM Mono', monospace;
        }
        .nav-badge.blue  { background: var(--brand-dim); color: var(--brand-dark); }
        .nav-badge.green { background: var(--green-dim); color: var(--green); }

        .sidebar-footer { padding: 1rem; border-top: 1px solid var(--border); flex-shrink: 0; background: var(--surface-2); }
        .tenant-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 12px; padding: .75rem .9rem;
            display: flex; align-items: center; gap: .75rem;
            box-shadow: var(--shadow-sm);
        }
        .tenant-avatar {
            width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
            background: var(--brand-dim); color: var(--brand-dark);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .85rem;
        }
        .tenant-name { font-size: .85rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tenant-id { font-size: .65rem; color: var(--muted); font-family: 'DM Mono', monospace; margin-top: .1rem; }

        /* ══════════════════════════════════════════════
           BOTTOM NAV (MOBILE) - TANPA BLUR EFEK
        ══════════════════════════════════════════════ */
        #bottom-nav {
            display: none; 
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
            background: #ffffff; /* Solid white untuk menghindari bug blur */
            border-top: 1px solid var(--border);
            height: var(--nav-h); padding: 0 .5rem;
            box-shadow: 0 -4px 20px rgba(0,0,0,.05);
        }
        .bnav-inner { display: flex; align-items: stretch; justify-content: space-around; height: 100%; }
        .bnav-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .25rem; flex: 1; padding: .35rem .25rem;
            background: none; border: none; cursor: pointer;
            color: var(--muted-2); font-size: .6rem; font-weight: 700;
            text-decoration: none; border-radius: 10px; transition: all .2s;
            position: relative;
        }
        .bnav-btn i { font-size: 1.35rem; line-height: 1; transition: transform .2s; }
        .bnav-btn:hover { color: var(--brand); }
        .bnav-btn.active { color: var(--brand-dark); }
        .bnav-btn.active i { transform: scale(1.15); }

        /* ══════════════════════════════════════════════
           MAIN CONTENT & TOPBAR - TANPA BLUR EFEK
        ══════════════════════════════════════════════ */
        #main-content {
            margin-left: 250px; flex: 1; min-height: 100vh;
            display: flex; flex-direction: column;
        }

        .topbar {
            position: sticky; top: 0; z-index: 40;
            background: rgba(255,255,255,.98); /* Solid white, hindari backdrop-filter bug */
            border-bottom: 1px solid var(--border);
            height: var(--topbar-h); padding: 0 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-left { display: flex; align-items: center; gap: .75rem; }
        .topbar-title { font-weight: 800; font-size: 1.15rem; color: var(--text); letter-spacing: -.02em; }
        .topbar-sub { font-size: .75rem; color: var(--muted); font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: .6rem; }

        .icon-btn {
            width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; transition: all .2s;
            text-decoration: none; position: relative;
        }
        .icon-btn-ghost { background: var(--surface-2); color: var(--muted); border: 1px solid transparent; }
        .icon-btn-ghost:hover { background: var(--surface-3); color: var(--text); }
        
        .icon-btn-brand { background: var(--brand-dim); color: var(--brand-dark); border: 1px solid transparent; }
        .icon-btn-brand:hover { background: var(--brand); color: #fff; }
        
        .icon-btn-red { background: var(--red-dim); color: var(--red); border: 1px solid transparent; }
        .icon-btn-red:hover { background: var(--red); color: #fff; }

        /* ── Page body ────────────────────────────────── */
        .page-body { padding: 1.75rem; flex: 1; max-width: 1400px; margin: 0 auto; width: 100%; }
        .section-panel { display: none; }
        .section-panel.active { display: block; animation: fadeUp .4s ease both; }

        /* ── Stat Cards ───────────────────────────────── */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 16px;
            padding: 1.25rem; display: flex; align-items: center; gap: 1rem;
            transition: all .3s; animation: fadeUp .4s ease both; cursor: default;
            box-shadow: var(--shadow-sm);
        }
        .stat-card:hover { border-color: var(--brand); transform: translateY(-3px); box-shadow: var(--shadow); }
        .stat-card:nth-child(1){animation-delay:.05s} .stat-card:nth-child(2){animation-delay:.1s}
        .stat-card:nth-child(3){animation-delay:.15s} .stat-card:nth-child(4){animation-delay:.2s}
        
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .si-brand  { background: var(--brand-dim); color: var(--brand-dark); }
        .si-green  { background: var(--green-dim); color: var(--green); }
        .si-amber  { background: var(--amber-dim); color: var(--amber); }
        .si-purple { background: var(--purple-dim); color: var(--purple); }
        
        .stat-label { font-size: .7rem; font-weight: 700; color: var(--muted); margin-bottom: .25rem; text-transform: uppercase; letter-spacing: .05em; }
        .stat-value { font-weight: 800; font-size: 1.5rem; color: var(--text); line-height: 1; }
        .stat-sub { font-size: .7rem; color: var(--muted-2); margin-top: .3rem; font-family: 'DM Mono', monospace; font-weight: 500; }

        /* ── Panel & Form ─────────────────────────────── */
        .panel { 
            background: var(--surface); border: 1px solid var(--border); 
            border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm); 
        }
        .panel-head { 
            padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border); 
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            background: rgba(248, 250, 252, 0.5);
        }
        .panel-title { font-weight: 800; font-size: 1.05rem; color: var(--text); display: flex; align-items: center; gap: .6rem; }
        .panel-title i { color: var(--brand); font-size: 1.2rem; }
        .panel-badge { font-family: 'DM Mono', monospace; font-size: .65rem; font-weight: 600; background: var(--brand-dim); color: var(--brand-dark); padding: .25rem .6rem; border-radius: 100px; }
        .panel-body { padding: 1.5rem; }

        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; font-size: .75rem; font-weight: 700; color: var(--text-p); margin-bottom: .4rem; }
        .form-input { 
            width: 100%; background: var(--surface-2); border: 1px solid var(--border-2); 
            border-radius: 10px; padding: .65rem .85rem; color: var(--text); 
            font-family: 'DM Sans', sans-serif; font-size: .9rem; 
            outline: none; transition: all .2s; 
        }
        .form-input:focus { background: var(--surface); border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-dim); }
        .form-input::placeholder { color: var(--muted-2); }
        textarea.form-input { resize: vertical; min-height: 90px; }

        .file-label { 
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem; 
            width: 100%; background: var(--surface-2); border: 2px dashed var(--border-2); 
            border-radius: 12px; padding: 1.25rem; cursor: pointer; 
            transition: all .2s; text-align: center;
        }
        .file-label i { font-size: 1.8rem; color: var(--muted-2); transition: color .2s; }
        .file-label:hover { border-color: var(--brand); background: var(--brand-dim); }
        .file-label:hover i { color: var(--brand); }
        .file-text-main { font-size: .85rem; font-weight: 700; color: var(--text-p); }
        .file-text-sub { font-size: .7rem; color: var(--muted); margin-top: .2rem; }
        input[type="file"] { display: none; }

        .btn-submit {
            width: 100%; padding: .75rem; border: none; border-radius: 10px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: .9rem;
            transition: all .2s;
        }
        .btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
        .btn-brand { background: var(--brand); color: #fff; box-shadow: 0 4px 12px var(--brand-glow); }
        .btn-brand:hover:not(:disabled)  { background: var(--brand-dark); transform: translateY(-2px); box-shadow: 0 6px 16px var(--brand-glow); }
        
        .btn-outline { background: transparent; border: 2px solid var(--border-2); color: var(--text-p); }
        .btn-outline:hover:not(:disabled) { border-color: var(--brand); color: var(--brand); background: var(--brand-dim); }

        /* ── Table ────────────────────────────────────── */
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .data-table thead th { 
            padding: .85rem 1.2rem; font-size: .7rem; font-weight: 700; 
            letter-spacing: .05em; text-transform: uppercase; color: var(--muted); 
            text-align: left; border-bottom: 2px solid var(--surface-2); 
            background: var(--surface);
        }
        .data-table tbody tr { transition: background .2s; }
        .data-table tbody tr:hover { background: var(--surface-2); }
        .data-table td { padding: 1rem 1.2rem; vertical-align: middle; border-bottom: 1px solid var(--border); }
        .data-table tbody tr:last-child td { border-bottom: none; }
        
        .prod-thumb { width: 44px; height: 44px; border-radius: 10px; overflow: hidden; background: var(--surface-2); border: 1px solid var(--border); flex-shrink: 0; }
        .prod-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .prod-name-cell { font-size: .95rem; font-weight: 700; color: var(--text); margin-bottom: .15rem; }
        .prod-id-cell { font-family: 'DM Mono', monospace; font-size: .7rem; color: var(--muted); }
        .price-cell { font-weight: 800; color: var(--brand-dark); font-size: 1rem; }
        .price-cell small { font-size: .7rem; font-weight: 600; color: var(--muted); margin-right: .1rem; }

        /* ── Log Cards ────────────────────────────────── */
        .log-list { display: flex; flex-direction: column; gap: 1rem; }
        .log-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm); }
        .log-card-head { padding: .85rem 1.2rem; background: var(--surface-2); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .log-q { font-size: .9rem; font-weight: 700; color: var(--text); display: flex; align-items: flex-start; gap: .6rem; flex: 1; min-width: 0; }
        .log-q i { color: var(--brand); flex-shrink: 0; margin-top: .15rem; font-size: 1.1rem; }
        .log-q span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .log-time { font-family: 'DM Mono', monospace; font-size: .7rem; font-weight: 500; color: var(--muted); white-space: nowrap; background: var(--surface); padding: .2rem .5rem; border-radius: 6px; border: 1px solid var(--border-2); }
        .log-card-body { padding: 1rem 1.2rem; }
        .log-ai-label { display: inline-flex; align-items: center; gap: .4rem; font-size: .65rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--brand); margin-bottom: .5rem; }
        .log-reply { font-size: .85rem; color: var(--text-p); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

        /* ── Info Banner & Empty State ────────────────── */
        .info-banner { border-radius: 12px; padding: 1rem 1.2rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: .85rem; }
        .info-banner.brand { background: var(--brand-dim); border: 1px solid rgba(14,165,233,.2); }
        .info-banner i { flex-shrink: 0; margin-top: .1rem; font-size: 1.2rem; color: var(--brand-dark); }
        .info-banner p { font-size: .85rem; color: var(--brand-dark); line-height: 1.6; margin: 0; font-weight: 500; }
        .info-banner strong { color: var(--text); font-weight: 800; }
        
        .empty-state { padding: 4rem 2rem; text-align: center; color: var(--muted); }
        .empty-state i { font-size: 3rem; color: var(--border-2); display: block; margin-bottom: 1rem; }
        .empty-state p { font-size: .95rem; font-weight: 500; margin: 0; color: var(--muted); }

        /* ══════════════════════════════════════════════
           TOAST NOTIFICATION
        ══════════════════════════════════════════════ */
        #toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: .75rem; pointer-events: none; }
        .toast { display: flex; align-items: flex-start; gap: .85rem; background: var(--surface); border-radius: 16px; padding: 1rem 1.25rem; width: 350px; max-width: calc(100vw - 3rem); box-shadow: var(--shadow-lg); border: 1px solid var(--border); pointer-events: all; transform: translateX(calc(100% + 2rem)); opacity: 0; transition: all .4s cubic-bezier(.34,1.56,.64,1); }
        .toast.show { transform: translateX(0); opacity: 1; }
        
        .toast-icon { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .toast-icon.success { background: var(--green-dim); color: var(--green); }
        .toast-icon.error   { background: var(--red-dim);   color: var(--red); }
        .toast-icon.info    { background: var(--brand-dim); color: var(--brand-dark); }
        
        .toast-body { flex: 1; min-width: 0; padding-top: .1rem; }
        .toast-title { font-size: .9rem; font-weight: 800; color: var(--text); margin-bottom: .2rem; }
        .toast-msg   { font-size: .8rem; color: var(--text-p); line-height: 1.5; }
        .toast-close { width: 24px; height: 24px; border-radius: 6px; border: none; background: var(--surface-2); color: var(--muted); cursor: pointer; font-size: .9rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .2s; }
        .toast-close:hover { background: var(--border); color: var(--text); }

        /* OVERLAY AMAN DARI BLUR */
        #sidebar-overlay { display: none; position: fixed; inset: 0; z-index: 45; background: rgba(15,23,42,.6); }
        #sidebar-overlay.show { display: block; }
        
        @keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
        @keyframes spin   { to{transform:rotate(360deg)} }
        .spin { animation: spin .8s linear infinite; display: inline-block; }

        /* ══════════════════════════════════════════════
           RESPONSIVE (PERBAIKAN KOLOM & PADDING MOBILE)
        ══════════════════════════════════════════════ */
        .layout-2-col { display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.5rem; align-items: start; }
        
        /* Grid Input yang otomatis menjadi 1 baris di HP */
        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        @media (max-width: 1024px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .layout-2-col { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            #sidebar       { transform: translateX(-100%); }
            #sidebar.open  { transform: translateX(0); }
            #main-content  { margin-left: 0; }
            #bottom-nav    { display: flex; }
            
            .page-body     { padding: 1rem; }
            .topbar        { padding: 0 1rem; }
            .stats-row     { grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-bottom: 1.5rem; }
            .stat-card     { padding: 1rem; flex-direction: column; align-items: flex-start; gap: .75rem; }
            .stat-value    { font-size: 1.35rem; }
            
            .input-grid    { grid-template-columns: 1fr; gap: 0; } /* Input berjejer ke bawah */
            
            .data-table    { font-size: .85rem; }
            .data-table td, .data-table th { padding: .75rem .5rem; }
            
            #toast-container { top: auto; bottom: calc(var(--nav-h) + .5rem); right: .5rem; left: .5rem; }
            .toast { width: 100%; max-width: 100%; box-sizing: border-box; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr 1fr; gap: .5rem; }
            .stat-card { padding: .85rem; }
            .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
            .stat-value { font-size: 1.15rem; }
            .stat-sub   { display: none; }
            .panel-head { flex-direction: column; align-items: flex-start; }
            .topbar-title { font-size: 1rem; }
        }
    </style>
</head>
<body>

<div id="toast-container"></div>
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="app-shell">

    <aside id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-mark">P</div>
            <span class="logo-text">Pasek<span>SaaS</span></span>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section-label">Menu Utama</div>
            <nav style="display:flex;flex-direction:column;gap:.3rem;margin-bottom:1.5rem">
                <button class="nav-item active" onclick="showSection('dashboard', this)">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </button>
                <a href="index.php" target="_blank" class="nav-item">
                    <i class="bi bi-shop"></i> Kunjungi Toko
                    <i class="bi bi-box-arrow-up-right" style="margin-left:auto;font-size:.7rem;opacity:.5"></i>
                </a>
            </nav>

            <div class="nav-section-label">Manajemen</div>
            <nav style="display:flex;flex-direction:column;gap:.3rem">
                <button class="nav-item" onclick="showSection('produk', this)">
                    <i class="bi bi-box-seam"></i> Produk Toko
                    <span class="nav-badge"><?= count($list_produk) ?></span>
                </button>
                <button class="nav-item" onclick="showSection('log', this)">
                    <i class="bi bi-chat-text"></i> Log Chat AI
                    <span class="nav-badge blue"><?= $total_log ?></span>
                </button>
                <button class="nav-item" onclick="showSection('persona', this)">
                    <i class="bi bi-stars"></i> Persona AI
                    <span class="nav-badge green" style="text-transform:capitalize"><?= htmlspecialchars($current_gaya) ?></span>
                </button>
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="tenant-card">
                <div class="tenant-avatar"><?= strtoupper(mb_substr($nama_toko, 0, 2)) ?></div>
                <div style="flex:1;min-width:0">
                    <div class="tenant-name"><?= htmlspecialchars($nama_toko) ?></div>
                    <div class="tenant-id">Tenant ID: <?= $id_toko ?></div>
                </div>
            </div>
            <a href="logout.php" class="btn-submit btn-outline" style="margin-top:1rem;color:var(--red);border-color:var(--red-dim)">
                <i class="bi bi-box-arrow-left"></i> Keluar Akun
            </a>
        </div>
    </aside>

    <div id="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="icon-btn icon-btn-ghost" id="hamburger" onclick="openSidebar()" style="display:none">
                    <i class="bi bi-list" style="font-size:1.5rem"></i>
                </button>
                <div>
                    <div class="topbar-title" id="topbar-title">Dasbor Utama</div>
                    <div class="topbar-sub"><?= htmlspecialchars($nama_toko) ?> &middot; <?= date('d M Y') ?></div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="icon-btn icon-btn-ghost" onclick="window.location.reload()" data-tooltip="Refresh Data">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <a href="logout.php" class="icon-btn icon-btn-red" data-tooltip="Keluar">
                    <i class="bi bi-power"></i>
                </a>
            </div>
        </header>

        <div class="page-body">

            <div class="stats-row">
                <div class="stat-card" onclick="showSection('produk')" style="cursor:pointer">
                    <div class="stat-icon si-brand"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-value"><?= count($list_produk) ?></div>
                        <div class="stat-sub">Item aktif di etalase</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon si-green"><i class="bi bi-tags"></i></div>
                    <div>
                        <div class="stat-label">Nilai Inventaris</div>
                        <div class="stat-value" style="font-size:1.15rem">Rp <?= number_format($total_nilai, 0, ',', '.') ?></div>
                        <div class="stat-sub">Estimasi harga jual</div>
                    </div>
                </div>
                <div class="stat-card" onclick="showSection('log')" style="cursor:pointer">
                    <div class="stat-icon si-amber"><i class="bi bi-chat-square-dots"></i></div>
                    <div>
                        <div class="stat-label">Log Interaksi AI</div>
                        <div class="stat-value"><?= $total_log ?></div>
                        <div class="stat-sub">Percakapan tersimpan</div>
                    </div>
                </div>
                <div class="stat-card" onclick="showSection('persona')" style="cursor:pointer">
                    <div class="stat-icon si-purple"><i class="bi bi-stars"></i></div>
                    <div>
                        <div class="stat-label">Gaya Chat AI</div>
                        <div class="stat-value" style="font-size:1.15rem;text-transform:capitalize"><?= htmlspecialchars($current_gaya) ?></div>
                        <div class="stat-sub"><?= $current_persona ? 'Instruksi aktif' : 'Gunakan default' ?></div>
                    </div>
                </div>
            </div>

            <div class="section-panel active" id="section-dashboard">
                
                <div class="layout-2-col">
                    
                    <div class="panel">
                        <div class="panel-head">
                            <div class="panel-title"><i class="bi bi-plus-circle-fill"></i> Tambah Produk Baru</div>
                        </div>
                        <div class="panel-body">
                            <form method="POST" enctype="multipart/form-data" id="form-tambah">
                                <input type="hidden" name="tambah_produk" value="1">

                                <div class="input-grid">
                                    <div class="form-group">
                                        <label class="form-label">Nama Produk / Layanan</label>
                                        <input type="text" name="nama_produk" class="form-input" placeholder="Contoh: Paket Cuci Kilat" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Harga (Rupiah)</label>
                                        <input type="number" name="harga" class="form-input" placeholder="Contoh: 50000" min="0" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Deskripsi Lengkap <span style="color:var(--brand);font-weight:800;font-size:.85rem;margin-left:.2rem">*</span></label>
                                    <textarea name="deskripsi" class="form-input" rows="3" placeholder="Jelaskan detail produk ini. Informasi ini akan dibaca oleh AI untuk menjawab pertanyaan pembeli..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Foto Produk (Opsional)</label>
                                    <div id="foto-preview-wrap" style="display:none;margin-bottom:1rem;position:relative">
                                        <div style="width:100%;height:160px;border-radius:12px;overflow:hidden;background:var(--surface-2);border:1px solid var(--border)">
                                            <img id="foto-preview-img" src="" alt="Preview" style="width:100%;height:100%;object-fit:cover;display:block">
                                        </div>
                                        <div style="margin-top:.5rem;display:flex;align-items:center;justify-content:space-between">
                                            <div style="display:flex;flex-direction:column">
                                                <span id="foto-preview-name" style="font-size:.8rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px"></span>
                                                <span id="foto-preview-size" style="font-size:.7rem;color:var(--muted);font-family:'DM Mono',monospace"></span>
                                            </div>
                                            <button type="button" onclick="clearFoto()" class="btn-submit btn-outline" style="width:auto;padding:.3rem .6rem;font-size:.7rem">
                                                <i class="bi bi-trash"></i> Ganti
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <label class="file-label" id="file-label" for="foto-input">
                                        <i class="bi bi-image"></i>
                                        <div>
                                            <div class="file-text-main">Klik untuk memilih foto</div>
                                            <div class="file-text-sub">Format JPG atau PNG (Maksimal 2MB)</div>
                                        </div>
                                    </label>
                                    <input type="file" name="foto" id="foto-input" accept="image/*" onchange="handleFotoChange(this)">
                                </div>

                                <div style="display:flex;gap:1rem;margin-top:1.5rem">
                                    <button type="button" class="btn-submit btn-outline" onclick="resetForm('form-tambah')" style="flex:1">
                                        Batal
                                    </button>
                                    <button type="submit" class="btn-submit btn-brand" id="submit-btn" style="flex:2">
                                        <i class="bi bi-plus-lg"></i> Simpan Etalase
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-head">
                            <div class="panel-title"><i class="bi bi-view-list"></i> Baru Ditambahkan</div>
                            <button class="icon-btn icon-btn-brand" onclick="showSection('produk')" data-tooltip="Lihat Seluruh Etalase">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                        <?php
                            $recent = array_slice($list_produk, 0, 6);
                            if (empty($recent)):
                        ?>
                            <div class="empty-state">
                                <i class="bi bi-box-seam"></i>
                                <p>Etalase Anda masih kosong.<br>Ayo tambahkan produk pertama Anda!</p>
                            </div>
                        <?php else: ?>
                        <div style="overflow-x:auto">
                            <table class="data-table">
                                <thead><tr>
                                    <th>Detail Produk</th><th>Harga</th><th style="text-align:center">Aksi</th>
                                </tr></thead>
                                <tbody>
                                    <?php foreach ($recent as $p): ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:1rem">
                                                <div class="prod-thumb">
                                                    <img src="assets/img/produk/<?= htmlspecialchars($p['foto_produk']) ?>" alt=""
                                                         onerror="this.parentElement.innerHTML='<div style=width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--border-2)>📦</div>'">
                                                </div>
                                                <div>
                                                    <div class="prod-name-cell"><?= htmlspecialchars($p['nama_produk']) ?></div>
                                                    <div class="prod-id-cell">ID Item: #<?= $p['id_produk'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><div class="price-cell"><small>Rp</small><?= number_format($p['harga'], 0, ',', '.') ?></div></td>
                                        <td style="text-align:center">
                                            <a href="admin.php?hapus=<?= $p['id_produk'] ?>"
                                               onclick="return confirm('Anda yakin ingin menghapus produk \'<?= htmlspecialchars(addslashes($p['nama_produk'])) ?>\'?')"
                                               class="icon-btn icon-btn-red" style="margin: 0 auto;" data-tooltip="Hapus Produk">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-panel" id="section-produk">
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class="bi bi-box-seam"></i> Daftar Seluruh Produk</div>
                        <span class="panel-badge"><?= count($list_produk) ?> produk aktif</span>
                    </div>
                    <?php if (empty($list_produk)): ?>
                        <div class="empty-state">
                            <i class="bi bi-box-seam"></i>
                            <p>Tidak ada produk yang tersedia.</p>
                            <button class="btn-submit btn-brand" style="max-width:200px;margin:1.5rem auto 0" onclick="showSection('dashboard')">Tambah Sekarang</button>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead><tr>
                                <th>Produk & Layanan</th><th>Harga</th><th>Konteks AI (Deskripsi)</th>
                                <th style="text-align:center">Hapus</th>
                            </tr></thead>
                            <tbody>
                                <?php foreach ($list_produk as $p): ?>
                                <tr>
                                    <td style="min-width:220px">
                                        <div style="display:flex;align-items:center;gap:1rem">
                                            <div class="prod-thumb">
                                                <img src="assets/img/produk/<?= htmlspecialchars($p['foto_produk']) ?>" alt=""
                                                     onerror="this.parentElement.innerHTML='<div style=width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--border-2)>📦</div>'">
                                            </div>
                                            <div>
                                                <div class="prod-name-cell"><?= htmlspecialchars($p['nama_produk']) ?></div>
                                                <div class="prod-id-cell">ID: #<?= $p['id_produk'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><div class="price-cell"><small>Rp</small><?= number_format($p['harga'], 0, ',', '.') ?></div></td>
                                    <td style="max-width:300px">
                                        <div style="font-size:.8rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.6">
                                            <?= htmlspecialchars($p['deskripsi'] ?? '—') ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center">
                                        <a href="admin.php?hapus=<?= $p['id_produk'] ?>"
                                           onclick="return confirm('Hapus permanen \'<?= htmlspecialchars(addslashes($p['nama_produk'])) ?>\'?')"
                                           class="icon-btn icon-btn-red" style="margin: 0 auto;">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section-panel" id="section-log">
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class="bi bi-chat-text"></i> Histori Percakapan AI</div>
                        <span class="panel-badge">Otomatis Dihapus jika terlalu lama</span>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($list_log)): ?>
                            <div class="empty-state">
                                <i class="bi bi-chat-square-dots"></i>
                                <p>Belum ada pelanggan yang mengobrol dengan asisten AI Anda.</p>
                            </div>
                        <?php else: ?>
                        <div class="log-list">
                            <?php foreach ($list_log as $log):
                                $resp = json_decode($log['ai_response'], true);
                                $reply_text = $resp['reply'] ?? '—';
                            ?>
                            <div class="log-card">
                                <div class="log-card-head">
                                    <div class="log-q">
                                        <i class="bi bi-person-fill"></i>
                                        <span>"<?= htmlspecialchars($log['user_query']) ?>"</span>
                                    </div>
                                    <div class="log-time"><i class="bi bi-clock me-1"></i><?= date('d M, H:i', strtotime($log['created_at'])) ?></div>
                                </div>
                                <div class="log-card-body">
                                    <div class="log-ai-label"><i class="bi bi-robot"></i> Asisten Menjawab:</div>
                                    <div class="log-reply"><?= htmlspecialchars($reply_text) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-panel" id="section-persona">
                <div class="panel" style="max-width:700px; margin:0 auto">
                    <div class="panel-head">
                        <div class="panel-title"><i class="bi bi-stars"></i> Kustomisasi Karakter AI</div>
                    </div>
                    <div class="panel-body">

                        <div class="info-banner brand">
                            <i class="bi bi-info-circle-fill"></i>
                            <p>Teks di bawah ini akan bertindak sebagai <strong>"Otak Utama"</strong> untuk AI Anda. Berikan aturan khusus agar AI membalas persis seperti yang Anda mau.</p>
                        </div>

                        <form method="POST" id="form-persona">
                            <input type="hidden" name="update_persona" value="1">

                            <div class="form-group">
                                <label class="form-label">Pilih Gaya Bahasa</label>
                                <select name="ai_gaya_bahasa" class="form-input" id="select-gaya" style="padding:1rem; font-weight:700">
                                    <?php foreach ($gaya_options as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $current_gaya === $val ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Instruksi Tambahan (Opsional)</label>
                                <textarea
                                    name="ai_persona_prompt"
                                    class="form-input"
                                    rows="8"
                                    placeholder="Contoh: Selalu sapa pelanggan dengan 'Halo Kakak!'. Jika ditanya lokasi, beri tahu bahwa kita ada di Jl. Raya Bali..."
                                    style="line-height:1.6"
                                ><?= htmlspecialchars($current_persona) ?></textarea>
                            </div>

                            <button type="submit" class="btn-submit btn-brand" id="btn-persona">
                                <i class="bi bi-floppy"></i> Update Karakter AI Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<nav id="bottom-nav">
    <div class="bnav-inner">
        <button class="bnav-btn active" id="bnav-dashboard" onclick="showSection('dashboard', null, this)">
            <i class="bi bi-grid-1x2"></i><span>Utama</span>
        </button>
        <button class="bnav-btn" id="bnav-produk" onclick="showSection('produk', null, this)">
            <i class="bi bi-box-seam"></i><span>Produk</span>
            <div class="bnav-badge"><?= count($list_produk) ?></div>
        </button>
        <button class="bnav-btn" id="bnav-log" onclick="showSection('log', null, this)">
            <i class="bi bi-chat-text"></i><span>Log AI</span>
        </button>
        <button class="bnav-btn" id="bnav-persona" onclick="showSection('persona', null, this)">
            <i class="bi bi-stars"></i><span>Persona</span>
        </button>
    </div>
</nav>

<script>
const toastContainer = document.getElementById('toast-container');

function showToast(type, title, msg, icon, duration = 4500) {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `
        <div class="toast-icon ${type}"><i class="bi ${icon}"></i></div>
        <div class="toast-body">
            <div class="toast-title">${title}</div>
            <div class="toast-msg">${msg}</div>
        </div>
        <button class="toast-close" onclick="removeToast(this.closest('.toast'))">
            <i class="bi bi-x-lg"></i>
        </button>`;
    toastContainer.appendChild(t);
    requestAnimationFrame(() => { requestAnimationFrame(() => t.classList.add('show')); });
    setTimeout(() => removeToast(t), duration);
}

function removeToast(el) {
    if (!el) return;
    el.classList.remove('show');
    setTimeout(() => el.remove(), 400);
}

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('status')) {
    const status = urlParams.get('status');
    const msg    = decodeURIComponent(urlParams.get('msg') || '');

    const map = {
        success      : ['success', 'Berhasil Disimpan!', `Item <b>${msg}</b> telah masuk ke etalase Anda.`, 'bi-check-circle-fill'],
        error        : ['error',   'Gagal Diproses',      msg || 'Ada masalah sistem.', 'bi-exclamation-triangle-fill'],
        deleted      : ['info',    'Produk Dihapus',      'Produk telah dihapus dari sistem secara permanen.', 'bi-trash3-fill'],
        persona_saved: ['info',    'Karakter Diperbarui', 'AI sekarang akan berbicara sesuai instruksi baru Anda.', 'bi-stars'],
    };
    const cfg = map[status];
    if (cfg) showToast(...cfg);
    window.history.replaceState({}, document.title, 'admin.php');
}

const sectionTitles = {
    dashboard: 'Dasbor Utama', produk: 'Manajemen Produk',
    log: 'Histori Percakapan AI', persona: 'Pengaturan Persona AI',
};

function showSection(id, sidebarBtn, bnavBtn) {
    document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
    document.getElementById(`section-${id}`).classList.add('active');
    document.getElementById('topbar-title').textContent = sectionTitles[id] || id;

    document.querySelectorAll('#sidebar .nav-item').forEach(n => n.classList.remove('active'));
    if (sidebarBtn) sidebarBtn.classList.add('active');

    document.querySelectorAll('.bnav-btn').forEach(b => b.classList.remove('active'));
    if (bnavBtn) {
        bnavBtn.classList.add('active');
    } else {
        const bnav = document.getElementById(`bnav-${id}`);
        if (bnav) bnav.classList.add('active');
    }

    if (window.innerWidth <= 768) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        closeSidebar();
    }
}

const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');
function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow='hidden'; }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow=''; }

function checkMobile() {
    const ham = document.getElementById('hamburger');
    if (window.innerWidth <= 768) { ham.style.display = 'flex'; }
    else { ham.style.display = 'none'; closeSidebar(); }
}
window.addEventListener('resize', checkMobile);
checkMobile();

function handleFotoChange(input) {
    if (!input.files || !input.files[0]) { resetFotoUI(); return; }
    const file = input.files[0];
    
    if (file.size > 2097152) {
        showToast('error', 'Foto Terlalu Besar', 'Maksimal 2MB. Silakan kompres atau pilih foto lain.', 'bi-x-circle-fill');
        clearFoto();
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('foto-preview-img').src = e.target.result;
        document.getElementById('foto-preview-name').textContent = file.name;
        document.getElementById('foto-preview-size').textContent = formatBytes(file.size);
        document.getElementById('foto-preview-wrap').style.display = 'block';
        document.getElementById('file-label').style.display = 'none';
    };
    reader.readAsDataURL(file);
}
function clearFoto() { document.getElementById('foto-input').value = ''; resetFotoUI(); }
function resetFotoUI() {
    document.getElementById('foto-preview-wrap').style.display = 'none';
    document.getElementById('foto-preview-img').src = '';
    document.getElementById('file-label').style.display = 'flex';
}
function formatBytes(b) {
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
    return (b/1048576).toFixed(1) + ' MB';
}

document.getElementById('form-tambah').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.8';
    btn.innerHTML = `<i class="bi bi-arrow-clockwise spin"></i> Sedang Menyimpan...`;
});

function resetForm(id) {
    document.getElementById(id).reset();
    resetFotoUI();
}

document.getElementById('form-persona').addEventListener('submit', function() {
    const btn = document.getElementById('btn-persona');
    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.8';
    btn.innerHTML = `<i class="bi bi-arrow-clockwise spin"></i> Memperbarui AI...`;
});
</script>

</body>
</html>
