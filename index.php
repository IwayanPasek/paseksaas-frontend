<?php
// ╔══════════════════════════════════════════════════════════════╗
//  KONFIGURASI & PROTEKSI SUBDOMAIN (PRODUCTION MODE)
// ╚══════════════════════════════════════════════════════════════╝
session_start();

define('DB_HOST',    'localhost');
define('DB_USER',    'wayan_user');
define('DB_PASS',    'WayanPass123!');
define('DB_NAME',    'websitewayan_db');
define('AI_API_BASE', 'https://' . $_SERVER['HTTP_HOST']);
define('BASE_DOMAIN', 'websitewayan.my.id');

// ── Deteksi Subdomain ─────────────────────────────────────────
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$parts     = explode('.', $http_host);
$subdomain = (count($parts) >= 3) ? strtolower($parts[0]) : '';

$toko        = null;
$list_produk = [];
$db_err      = null;

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
    // Di mode produksi, error kita tangkap tanpa menampilkan baris query
    $db_err = true; 
}

// ── Guard: domain utama → redirect login ─────────────────────
if (!$subdomain || in_array($subdomain, ['www', 'websitewayan'])) {
    header("Location: login.php");
    exit;
}

// ── Guard: toko tidak ditemukan → 404 ────────────────────────
if (!$toko) {
    http_response_code(404);
    ?><!DOCTYPE html>
    <html lang="id"><head>
    <meta charset="UTF-8"><title>404 — Toko Tidak Ditemukan</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;
             background:#0f1729;font-family:Georgia,serif;color:#faf8f5;text-align:center;padding:2rem}
        .num{font-size:7rem;font-weight:900;color:#f59e0b;line-height:1}
        h2{font-size:1.3rem;color:#94a3b8;margin:.5rem 0 1.5rem}
        a{color:#f59e0b;text-underline-offset:4px;text-decoration:none}
        a:hover{text-decoration:underline}
    </style></head>
    <body>
        <div>
            <div class="num">404</div>
            <h2>Toko "<?= htmlspecialchars($subdomain) ?>" tidak ditemukan.</h2>
            <a href="https://<?= BASE_DOMAIN ?>">← Kembali ke Beranda Utama</a>
        </div>
    </body></html>
    <?php exit;
}

// ── Helper vars & Sanitasi Data ──────────────────────────────
$nama_toko = htmlspecialchars($toko['nama_toko'] ?? 'Toko Kami');
$raw_kb    = $toko['knowledge_base'] ?? '';
$desc_toko = htmlspecialchars($raw_kb ?: 'Selamat datang! Temukan produk terbaik kami dengan bantuan Asisten AI.');
$logo_toko = $toko['logo'] ?? '';
$toko_id   = (int)$toko['id_toko'];

// Cek & Bersihkan Nomor WhatsApp (Otomatis ke format +62)
$wa_raw = $toko['no_wa'] ?? $toko['whatsapp'] ?? '081234567890';
$wa_clean = preg_replace('/[^0-9]/', '', $wa_raw);
if (str_starts_with($wa_clean, '0')) {
    $wa_clean = '62' . substr($wa_clean, 1);
}

$harga_arr = array_column($list_produk, 'harga');
$min_harga = !empty($harga_arr) ? min($harga_arr) : 0;

function safe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
// Fungsi ini krusial agar tanda kutip di nama produk tidak merusak HTML onClick
function safeJs(string $s): string { return htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $nama_toko ?> — AI Smart Shopping</title>
    <meta name="description" content="<?= $desc_toko ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;0,900;1,500;1,700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              serif: ['"Playfair Display"','Georgia','serif'],
              sans:  ['"DM Sans"','sans-serif'],
              mono:  ['"DM Mono"','monospace'],
            }
          }
        }
      }
    </script>

    <style>
        /* ══════════════════════════════════════════════
           CSS VARIABLES & BASE
        ══════════════════════════════════════════════ */
        :root {
            --cream:      #faf8f5;
            --ink:        #0f1729;
            --ink-muted:  #64748b;
            --amber:      #f59e0b;
            --amber-pale: #fef3c7;
            --border:     rgba(15,23,41,.1);
            --r-card:     20px;
            --chat-w:     390px;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0; font-family: 'DM Sans', sans-serif;
            background: var(--cream); color: var(--ink);
            -webkit-font-smoothing: antialiased; overflow-x: hidden;
        }

        /* Lock scroll body saat chat terbuka di HP */
        body.mobile-chat-open { overflow: hidden; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1c9bc; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--amber); }

        /* ══════════════════════════════════════════════
           NAVBAR
        ══════════════════════════════════════════════ */
        #navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 100; padding: 1rem 1.5rem; transition: all .3s; }
        #navbar.scrolled { background: rgba(250,248,245,.93); backdrop-filter: blur(20px) saturate(180%); border-bottom: 1px solid var(--border); box-shadow: 0 2px 20px rgba(15,23,41,.06); padding: .6rem 1.5rem; }
        .nav-inner { max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
        .nav-logo { display: flex; align-items: center; gap: .7rem; text-decoration: none; }
        .nav-logo-mark { width: 40px; height: 40px; border-radius: 12px; background: rgba(250,248,245,.15); border: 1px solid rgba(250,248,245,.2); color: var(--amber); font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; transition: all .3s; }
        #navbar.scrolled .nav-logo-mark { background: var(--ink); border-color: transparent; }
        .nav-logo-mark img { width: 100%; height: 100%; object-fit: cover; }
        .nav-brand { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.1rem; letter-spacing: -.02em; color: #fff; transition: color .3s; }
        #navbar.scrolled .nav-brand { color: var(--ink); }
        .nav-right { display: flex; align-items: center; gap: .75rem; }
        .nav-count { font-size: .78rem; font-family: 'DM Mono', monospace; color: rgba(250,248,245,.5); padding: .3rem .75rem; border-radius: 100px; border: 1px solid rgba(250,248,245,.15); transition: all .3s; }
        #navbar.scrolled .nav-count { color: var(--ink-muted); border-color: var(--border); }
        .nav-login { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem 1.1rem; border-radius: 10px; background: rgba(250,248,245,.1); color: rgba(250,248,245,.8); border: 1px solid rgba(250,248,245,.18); font-size: .82rem; font-weight: 500; text-decoration: none; transition: all .2s; }
        #navbar.scrolled .nav-login { background: var(--ink); color: var(--cream); border-color: transparent; }
        .nav-login:hover { background: var(--amber) !important; color: var(--ink) !important; border-color: transparent !important; }

        /* ══════════════════════════════════════════════
           HERO
        ══════════════════════════════════════════════ */
        #hero { min-height: 92vh; background: var(--ink); display: flex; align-items: center; position: relative; overflow: hidden; }
        #hero::before { content: ''; position: absolute; top: -15%; left: -5%; width: 55vw; height: 55vh; border-radius: 50%; background: radial-gradient(ellipse, rgba(245,158,11,.1) 0%, transparent 65%); pointer-events: none; }
        #hero::after { content: ''; position: absolute; bottom: -10%; right: 5%; width: 45vw; height: 45vh; border-radius: 50%; background: radial-gradient(ellipse, rgba(107,143,113,.08) 0%, transparent 65%); pointer-events: none; }
        .hero-stripe { position: absolute; inset: 0; pointer-events: none; background: repeating-linear-gradient(-55deg, transparent, transparent 60px, rgba(255,255,255,.01) 60px, rgba(255,255,255,.01) 61px); }
        .hero-inner { position: relative; z-index: 1; max-width: 1280px; margin: 0 auto; padding: 7rem 1.5rem 6rem; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; }
        .hero-eyebrow { display: inline-flex; align-items: center; gap: .5rem; background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.22); color: var(--amber); border-radius: 100px; padding: .3rem 1rem; font-size: .75rem; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; margin-bottom: 1.5rem; animation: fadeUp .6s ease both; }
        .eyebrow-ping { width: 7px; height: 7px; border-radius: 50%; background: var(--amber); animation: ping 2s cubic-bezier(0,0,.2,1) infinite; }
        @keyframes ping { 75%,100% { transform: scale(1.8); opacity: 0; } }
        .hero-h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem, 5.5vw, 5.2rem); font-weight: 900; line-height: 1.04; color: var(--cream); letter-spacing: -.03em; margin: 0 0 1.5rem; animation: fadeUp .6s .1s ease both; }
        .hero-h1 em { font-style: italic; color: var(--amber); }
        .hero-desc { font-size: 1.05rem; line-height: 1.75; color: rgba(250,248,245,.55); font-weight: 300; max-width: 480px; margin: 0 0 2.25rem; animation: fadeUp .6s .2s ease both; }
        .hero-btns { display: flex; gap: .9rem; flex-wrap: wrap; animation: fadeUp .6s .3s ease both; }
        .btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .8rem 1.9rem; border-radius: 14px; background: var(--amber); color: var(--ink); font-weight: 700; font-size: .95rem; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 4px 24px rgba(245,158,11,.3); transition: transform .2s, box-shadow .2s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 36px rgba(245,158,11,.42); }
        .btn-ghost { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .8rem 1.6rem; border-radius: 14px; background: transparent; border: 1px solid rgba(250,248,245,.18); color: rgba(250,248,245,.7); font-weight: 500; font-size: .92rem; text-decoration: none; cursor: pointer; transition: all .2s; }
        .btn-ghost:hover { background: rgba(250,248,245,.09); color: var(--cream); border-color: rgba(250,248,245,.32); }
        
        .hero-stats { display: flex; gap: 0; margin-top: 3.5rem; border-top: 1px solid rgba(250,248,245,.08); padding-top: 2.5rem; animation: fadeUp .6s .4s ease both; }
        .hero-stat { flex: 1; padding-right: 2rem; }
        .hero-stat + .hero-stat { padding-left: 2rem; padding-right: 0; border-left: 1px solid rgba(250,248,245,.08); }
        .stat-num { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 2rem; color: var(--cream); line-height: 1; }
        .stat-label { font-size: .7rem; color: rgba(250,248,245,.35); text-transform: uppercase; letter-spacing: .07em; margin-top: .3rem; }
        
        .hero-right { display: flex; justify-content: flex-end; animation: fadeUp .6s .2s ease both; }
        .hero-preview { background: rgba(250,248,245,.05); border: 1px solid rgba(250,248,245,.1); border-radius: 24px; padding: 2rem; width: 100%; max-width: 320px; backdrop-filter: blur(10px); }
        .preview-badge { display: inline-block; padding: .25rem .75rem; border-radius: 100px; background: rgba(245,158,11,.15); color: var(--amber); font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 1rem; }
        .preview-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; color: var(--cream); font-weight: 600; margin: 0 0 .5rem; }
        .preview-desc { font-size: .8rem; color: rgba(250,248,245,.4); line-height: 1.6; margin: 0 0 1.25rem; }
        .preview-chat { display: flex; flex-direction: column; gap: .55rem; }
        .pchat { padding: .5rem .85rem; border-radius: 12px; font-size: .75rem; line-height: 1.45; max-width: 85%; }
        .pchat.ai { background: rgba(250,248,245,.08); color: rgba(250,248,245,.65); border-radius: 12px 12px 12px 2px; }
        .pchat.user { background: var(--amber); color: var(--ink); font-weight: 500; align-self: flex-end; border-radius: 12px 12px 2px 12px; }

        /* ══════════════════════════════════════════════
           KATALOG PRODUK
        ══════════════════════════════════════════════ */
        #katalog { padding: 5.5rem 1.5rem 6rem; max-width: 1280px; margin: 0 auto; }
        .section-eyebrow { font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--amber); margin-bottom: .5rem; }
        .section-line { height: 3px; width: 48px; background: var(--amber); border-radius: 2px; margin-top: .75rem; }
        .section-h2 { font-family: 'Playfair Display', serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; color: var(--ink); letter-spacing: -.03em; line-height: 1.1; margin: 0; }
        .katalog-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 2.75rem; gap: 1rem; flex-wrap: wrap; }
        .katalog-count { font-size: .8rem; color: var(--ink-muted); font-family: 'DM Mono', monospace; padding: .35rem .9rem; border-radius: 100px; border: 1px solid var(--border); background: #fff; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
        .product-card { background: #fff; border: 1px solid var(--border); border-radius: var(--r-card); overflow: hidden; display: flex; flex-direction: column; transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s, border-color .3s; animation: fadeUp .5s ease both; }
        .product-card:hover { transform: translateY(-7px); box-shadow: 0 20px 60px rgba(15,23,41,.12); border-color: rgba(245,158,11,.28); }
        .prod-img-wrap { position: relative; aspect-ratio: 4/3; overflow: hidden; background: linear-gradient(135deg, #f4f0e8, #ede7da); }
        .prod-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .product-card:hover .prod-img-wrap img { transform: scale(1.07); }
        .prod-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; opacity: .18; }
        .prod-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,41,.28), transparent 55%); opacity: 0; transition: opacity .3s; }
        .product-card:hover .prod-overlay { opacity: 1; }
        .prod-body { padding: 1.25rem 1.25rem .9rem; flex: 1; display: flex; flex-direction: column; }
        .prod-name { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.05rem; color: var(--ink); line-height: 1.3; margin: 0 0 .4rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .prod-desc { font-size: .79rem; color: var(--ink-muted); line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; flex: 1; margin-bottom: 1rem; }
        .prod-footer { display: flex; align-items: center; justify-content: space-between; padding-top: .9rem; border-top: 1px solid var(--border); gap: .5rem; }
        .prod-price { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.05rem; color: var(--ink); }
        .prod-price small { font-family: 'DM Sans', sans-serif; font-size: .68rem; font-weight: 400; color: var(--ink-muted); margin-right: .1rem; }
        .prod-actions { display: flex; gap: .4rem; margin-top: .75rem; }
        .btn-konsultasi { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .35rem; padding: .5rem .75rem; border-radius: 10px; background: var(--ink); color: var(--cream); font-size: .75rem; font-weight: 600; border: none; cursor: pointer; transition: background .18s, transform .15s, box-shadow .18s; }
        .btn-konsultasi:hover { background: var(--amber); color: var(--ink); transform: scale(1.03); box-shadow: 0 4px 14px rgba(245,158,11,.28); }
        .btn-beli { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .35rem; padding: .5rem .75rem; border-radius: 10px; background: transparent; color: var(--ink); border: 1.5px solid var(--border); font-size: .75rem; font-weight: 600; cursor: pointer; transition: all .18s; }
        .btn-beli:hover { background: var(--amber-pale); border-color: var(--amber); color: var(--ink); transform: scale(1.03); }

        .empty-state { grid-column: 1/-1; text-align: center; padding: 5rem 2rem; color: var(--ink-muted); }
        .empty-state i { font-size: 3rem; opacity: .2; display: block; margin-bottom: .75rem; }
        .empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--ink); margin: 0 0 .5rem; }

        /* FOOTER */
        #footer { background: var(--ink); color: rgba(250,248,245,.38); padding: 2.5rem 1.5rem; text-align: center; font-size: .82rem; line-height: 1.8; }
        #footer strong { color: var(--amber); }

        /* ══════════════════════════════════════════════
           FLOATING CHAT WIDGET & WA CARD
        ══════════════════════════════════════════════ */
        #chat-fab { position: fixed; bottom: 1.75rem; right: 1.75rem; z-index: 200; width: 58px; height: 58px; border-radius: 18px; background: var(--ink); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--amber); font-size: 1.4rem; box-shadow: 0 8px 28px rgba(15,23,41,.3); transition: transform .3s cubic-bezier(.4,0,.2,1), box-shadow .2s, background .2s; }
        #chat-fab:hover { transform: scale(1.08) rotate(5deg); box-shadow: 0 12px 40px rgba(15,23,41,.4); }
        #chat-fab.open  { transform: scale(.9) rotate(90deg); background: #f43f5e; color: #fff; box-shadow: 0 8px 28px rgba(244,63,94,.35); }
        .fab-badge { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: var(--amber); color: var(--ink); font-size: .62rem; font-weight: 800; border: 2px solid var(--cream); display: none; align-items: center; justify-content: center; font-family: 'DM Mono', monospace; }
        .fab-badge.show { display: flex; }

        #chat-window {
            position: fixed; bottom: calc(1.75rem + 58px + 12px); right: 1.75rem; z-index: 200;
            width: var(--chat-w); max-width: calc(100vw - 1.5rem); height: 580px; max-height: calc(100vh - 120px);
            border-radius: 24px; background: rgba(255,255,255,.96);
            backdrop-filter: blur(24px) saturate(180%); -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(15,23,41,.08); box-shadow: 0 25px 60px rgba(15,23,41,.18);
            display: flex; flex-direction: column; overflow: hidden;
            opacity: 0; transform: scale(.85) translateY(40px); pointer-events: none; transform-origin: bottom right;
            transition: opacity .3s ease, transform .4s cubic-bezier(.175,.885,.32,1.275);
        }
        #chat-window.open { opacity: 1; transform: scale(1) translateY(0); pointer-events: all; }

        .chat-head { flex-shrink: 0; background: linear-gradient(135deg, #0f1729 0%, #1e293b 100%); padding: 1.25rem 1.15rem; }
        .chat-head-top { display: flex; align-items: center; gap: .75rem; }
        .chat-av { width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0; background: var(--amber); color: var(--ink); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(245,158,11,.3); }
        .chat-title-wrap { flex: 1; min-width: 0; }
        .chat-title { font-family: 'Playfair Display', serif; font-size: .95rem; font-weight: 700; color: #fff; letter-spacing: -.01em; }
        .chat-sub { display: flex; align-items: center; gap: .4rem; font-size: .7rem; color: rgba(255,255,255,.4); margin-top: .1rem; }
        .online-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 6px #22c55e; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.5} }
        .chat-head-btns { display: flex; gap: .25rem; }
        .chat-hbtn { width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: none; cursor: pointer; color: rgba(255,255,255,.5); font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: background .15s, color .15s; }
        .chat-hbtn:hover { background: rgba(255,255,255,.14); color: #fff; }

        .chat-chips { display: flex; gap: .4rem; margin-top: .85rem; overflow-x: auto; padding-bottom: .15rem; }
        .chat-chips::-webkit-scrollbar { display: none; }
        .chip { padding: .3rem .75rem; border-radius: 100px; flex-shrink: 0; background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.25); color: var(--amber); font-size: .7rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .15s, border-color .15s; }
        .chip:hover { background: rgba(245,158,11,.28); border-color: var(--amber); }

        #chat-msgs { flex: 1; overflow-y: auto; padding: 1.25rem 1rem .75rem; display: flex; flex-direction: column; gap: .85rem; background-image: radial-gradient(rgba(15,23,41,.04) 0.5px, transparent 0.5px); background-size: 14px 14px; background-color: #fcfaf7; }
        #chat-msgs::-webkit-scrollbar { width: 3px; }
        #chat-msgs::-webkit-scrollbar-thumb { background: #d1c9bc; border-radius: 2px; }

        .msg-row { display: flex; gap: .5rem; align-items: flex-end; }
        .msg-row.user { flex-direction: row-reverse; }
        .msg-av { width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; }
        .msg-av.ai   { background: var(--amber); color: var(--ink); }
        .msg-av.user { background: #e2e8f0; color: var(--ink); }
        .msg-inner { display: flex; flex-direction: column; max-width: 82%; }

        .bubble-ai { padding: .7rem .95rem; border-radius: 20px 20px 20px 4px; font-size: .875rem; line-height: 1.55; background: #fff; color: var(--ink); border: 1px solid rgba(15,23,41,.06); box-shadow: 0 4px 12px rgba(0,0,0,.04); animation: bubbleIn .3s cubic-bezier(.34,1.56,.64,1) both; }
        .bubble-user { padding: .7rem .95rem; border-radius: 20px 20px 4px 20px; font-size: .875rem; line-height: 1.55; background: var(--ink); color: #fff; box-shadow: 0 8px 20px rgba(15,23,41,.18); align-self: flex-end; animation: bubbleIn .3s cubic-bezier(.34,1.56,.64,1) both; }
        @keyframes bubbleIn { from{opacity:0;transform:scale(.88) translateY(6px)} to{opacity:1;transform:scale(1) translateY(0)} }
        .msg-time { font-size: .62rem; color: rgba(15,23,41,.28); margin-top: .18rem; font-family: 'DM Mono', monospace; }
        .msg-row.user .msg-time { text-align: right; }

        /* Chat Product Card */
        .chat-product-card { background: #fff; border: 1px solid rgba(15,23,41,.08); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.06); animation: bubbleIn .35s cubic-bezier(.34,1.56,.64,1) both; max-width: 260px; margin-top: 4px; }
        .chat-product-img { width: 100%; aspect-ratio: 4/3; object-fit: cover; background: linear-gradient(135deg, #f4f0e8, #ede7da); display: block; }
        .chat-product-img.placeholder { display: flex; align-items: center; justify-content: center; font-size: 2.5rem; opacity: .2; }
        .chat-product-body { padding: .85rem 1rem; }
        .chat-product-name { font-family: 'Playfair Display', serif; font-weight: 700; font-size: .92rem; color: var(--ink); margin: 0 0 .25rem; }
        .chat-product-price { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1rem; color: var(--amber); margin: 0 0 .75rem; }
        .chat-product-actions { display: flex; gap: .4rem; }
        .chat-btn-beli { flex: 1; padding: .5rem .6rem; border-radius: 9px; background: #22c55e; color: #fff; border: none; cursor: pointer; font-size: .75rem; font-weight: 600; text-align: center; box-shadow: 0 4px 12px rgba(34,197,94,.25); transition: all .15s; display: flex; align-items: center; justify-content: center; gap: .35rem; }
        .chat-btn-beli:hover { background: #16a34a; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(34,197,94,.35); }

        /* WA Modern Card */
        .wa-card { border-radius: 16px; overflow: hidden; border: 1px solid rgba(34,197,94,.2); background: #fff; box-shadow: 0 8px 24px rgba(34,197,94,.15); max-width: 260px; animation: bubbleIn .3s both; margin-top: .5rem; }
        .wa-card-head { background: linear-gradient(135deg, #22c55e, #16a34a); padding: 1.15rem; color: #fff; display: flex; align-items: center; gap: 1rem; }
        .wa-card-icon { font-size: 2rem; line-height: 1; text-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .wa-card-title { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.05rem; letter-spacing: -.01em; margin-bottom: .15rem; }
        .wa-card-sub { font-size: .75rem; opacity: .9; font-weight: 500; display: flex; align-items: center; gap: .3rem; }
        .wa-card-body { padding: 1.15rem; text-align: center; }
        .wa-btn { display: flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .75rem; border-radius: 12px; background: #22c55e; color: #fff; font-weight: 600; font-size: .85rem; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(34,197,94,.3); transition: all .2s; }
        .wa-btn:hover { background: #16a34a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(34,197,94,.4); }

        .typing-row { display: flex; gap: .5rem; align-items: flex-end; }
        .typing-bubble { background: #fff; border: 1px solid rgba(15,23,41,.06); border-radius: 20px 20px 20px 4px; padding: .7rem .95rem; display: flex; gap: 5px; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,.04); }
        .td { width: 6px; height: 6px; border-radius: 50%; background: var(--amber); animation: dotPulse 1.4s infinite; }
        .td:nth-child(2) { animation-delay:.2s }
        .td:nth-child(3) { animation-delay:.4s }
        @keyframes dotPulse { 0%,100%{opacity:.3;transform:scale(1)} 50%{opacity:1;transform:scale(1.2)} }

        .chat-input-bar { flex-shrink: 0; padding: .8rem .9rem; border-top: 1px solid rgba(15,23,41,.05); background: rgba(255,255,255,.97); }
        .input-wrap { display: flex; align-items: flex-end; gap: .45rem; background: #f1f5f9; border: 1.5px solid transparent; border-radius: 16px; padding: .5rem .55rem .5rem .9rem; transition: border-color .2s, background .2s, box-shadow .2s; }
        .input-wrap:focus-within { background: #fff; border-color: rgba(245,158,11,.45); box-shadow: 0 0 0 3px rgba(245,158,11,.08); }
        #chat-text { flex: 1; border: none; background: transparent; resize: none; font-family: 'DM Sans', sans-serif; font-size: .875rem; color: var(--ink); outline: none; line-height: 1.5; max-height: 90px; overflow-y: auto; }
        #chat-text::placeholder { color: #b0a898; }
        #chat-send { width: 34px; height: 34px; flex-shrink: 0; background: var(--ink); border: none; border-radius: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .85rem; box-shadow: 0 4px 12px rgba(15,23,41,.2); transition: background .18s, transform .15s; }
        #chat-send:hover:not(:disabled) { background: var(--amber); color: var(--ink); transform: scale(1.06); }
        #chat-send:disabled { opacity: .4; cursor: not-allowed; }
        .chat-footer-row { display: flex; justify-content: space-between; align-items: center; margin-top: .4rem; padding: 0 .1rem; }
        .chat-hint { font-size: .63rem; color: #b0a898; }
        .chat-clear-btn { font-size: .63rem; color: #b0a898; font-weight: 600; background: none; border: none; cursor: pointer; transition: color .15s; }
        .chat-clear-btn:hover { color: #f43f5e; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }

        /* ══════════════════════════════════════════════
           MOBILE RESPONSIVE (MEDIA QUERIES)
        ══════════════════════════════════════════════ */
        @media (max-width: 900px) { 
            .hero-inner { grid-template-columns: 1fr; } 
            .hero-right  { display: none; } 
        }
        
        @media (max-width: 640px) { 
            #navbar { padding: .75rem 1rem; }
            #navbar.scrolled { padding: .5rem 1rem; }
            .nav-login { padding: .4rem .75rem; }
            .nav-login span { display: none; } 
            
            #hero { min-height: 100vh; }
            .hero-inner { padding-top: 6.5rem; }
            .hero-stats { flex-wrap: wrap; gap: 1.5rem; }
            .hero-stat { border-left: none !important; padding: 0 !important; min-width: 40%; }
            
            #katalog { padding: 3.5rem 1rem 4rem; } 
            
            /* Fullscreen Chat on Mobile */
            #chat-window { 
                width: 100vw; max-width: 100vw;
                height: 100dvh; max-height: 100dvh;
                bottom: 0; right: 0; border-radius: 0;
                transform: translateY(100%) scale(1);
            }
            .chat-head { border-radius: 0; }
            #chat-fab { right: 1rem; bottom: 1rem; }
            
            /* Sembunyikan FAB jika chat terbuka agar tombol send tidak terhalang */
            #chat-fab.open { display: none; }
        }

        @media (max-width: 480px) { 
            .products-grid { grid-template-columns: 1fr; } 
            .hero-btns { flex-direction: column; }
            .btn-primary, .btn-ghost { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<nav id="navbar">
    <div class="nav-inner">
        <a href="#" class="nav-logo">
            <div class="nav-logo-mark" id="logo-mark">
                <?php if ($logo_toko): ?>
                    <img src="<?= safe($logo_toko) ?>" alt="Logo">
                <?php else: ?>
                    <?= strtoupper(mb_substr($toko['nama_toko'] ?? 'T', 0, 2)) ?>
                <?php endif; ?>
            </div>
            <span class="nav-brand" id="nav-brand"><?= $nama_toko ?></span>
        </a>
        <div class="nav-right">
            <span class="nav-count hidden sm:block"><?= count($list_produk) ?> Produk</span>
            <a href="login.php" class="nav-login">
                <i class="bi bi-shield-lock"></i><span>Admin</span>
            </a>
        </div>
    </div>
</nav>

<section id="hero">
    <div class="hero-stripe"></div>
    <div class="hero-inner">
        <div>
            <div class="hero-eyebrow">
                <div class="eyebrow-ping"></div>
                AI-Powered &middot; <?= safe($subdomain) ?>.<?= BASE_DOMAIN ?>
            </div>
            <h1 class="hero-h1">Belanja Lebih<br><em>Cerdas.</em></h1>
            <p class="hero-desc"><?= $desc_toko ?></p>
            <div class="hero-btns">
                <a href="#katalog" class="btn-primary">
                    <i class="bi bi-grid-3x3-gap-fill"></i>Jelajahi Produk
                </a>
                <button class="btn-ghost" onclick="openChat()">
                    <i class="bi bi-stars"></i>Tanya AI
                </button>
            </div>
            <?php if (!empty($list_produk)): ?>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="stat-num"><?= count($list_produk) ?></div>
                    <div class="stat-label">Produk</div>
                </div>
                <?php if ($min_harga > 0): ?>
                <div class="hero-stat">
                    <div class="stat-num"><?= number_format($min_harga, 0, ',', '.') ?></div>
                    <div class="stat-label">Harga Mulai (Rp)</div>
                </div>
                <?php endif; ?>
                <div class="hero-stat">
                    <div class="stat-num">24/7</div>
                    <div class="stat-label">Asisten AI</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="hero-right">
            <div class="hero-preview">
                <div class="preview-badge">✦ AI Assistant Aktif</div>
                <h3 class="preview-title">Tanya apa saja tentang produk kami</h3>
                <p class="preview-desc">Asisten kami siap membantu Anda menemukan produk yang tepat kapan saja.</p>
                <div class="preview-chat">
                    <div class="pchat ai">Halo! Ada yang bisa saya bantu? 👋</div>
                    <div class="pchat user">Produk apa yang bagus?</div>
                    <div class="pchat ai">Tentu! Berdasarkan kebutuhan Anda...</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="katalog">
    <div class="katalog-header">
        <div>
            <div class="section-eyebrow"><i class="bi bi-grid-3x3-gap me-1"></i>Katalog Produk</div>
            <h2 class="section-h2">Koleksi Eksklusif</h2>
            <div class="section-line"></div>
        </div>
        <?php if (!empty($list_produk)): ?>
        <div class="katalog-count"><?= count($list_produk) ?> item tersedia</div>
        <?php endif; ?>
    </div>

    <div class="products-grid">
        <?php if (empty($list_produk)): ?>
            <div class="empty-state">
                <i class="bi bi-box-seam"></i>
                <h3>Belum ada produk</h3>
                <p>Toko ini belum menambahkan produk.</p>
            </div>
        <?php else: ?>
            <?php foreach ($list_produk as $p):
                $pnama  = safe($p['nama_produk'] ?? '—');
                $pdesc  = safe($p['deskripsi'] ?? '');
                $pfoto  = $p['foto_produk'] ?? '';
                $pharga = number_format((float)($p['harga'] ?? 0), 0, ',', '.');
                
                // MENGGUNAKAN safeJs() AGAR HTML ONCLICK TETAP AMAN DARI TANDA KUTIP
                $pnamaJs  = safeJs($p['nama_produk'] ?? '');
                $pdescJs  = safeJs(mb_substr($p['deskripsi'] ?? '', 0, 120));
                $pfotoJs  = safeJs($pfoto);
                $phargaJs = safeJs('Rp ' . $pharga);
            ?>
            <div class="product-card">
                <div class="prod-img-wrap">
                    <?php if ($pfoto): ?>
                        <img src="assets/img/produk/<?= safe($pfoto) ?>" alt="<?= $pnama ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="prod-placeholder" style="display:none">📦</div>
                    <?php else: ?>
                        <div class="prod-placeholder">📦</div>
                    <?php endif; ?>
                    <div class="prod-overlay"></div>
                </div>
                <div class="prod-body">
                    <h3 class="prod-name"><?= $pnama ?></h3>
                    <?php if ($pdesc): ?>
                        <p class="prod-desc"><?= $pdesc ?></p>
                    <?php endif; ?>
                    <div class="prod-footer">
                        <div class="prod-price"><small>Rp</small><?= $pharga ?></div>
                    </div>
                    <div class="prod-actions">
                        <button class="btn-konsultasi" onclick='tanyaProduk(<?= $pnamaJs ?>, <?= $pdescJs ?>, <?= $pfotoJs ?>, <?= $phargaJs ?>)'>
                            <i class="bi bi-stars"></i>Konsultasi AI
                        </button>
                        <button class="btn-beli" onclick='beliViaWA(<?= $pnamaJs ?>, <?= $phargaJs ?>)'>
                            <i class="bi bi-cart-plus"></i>Beli
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<footer id="footer">
    <p>&copy; <?= date('Y') ?> <strong><?= $nama_toko ?></strong> &mdash; Ditenagai oleh <strong>Pasek AI Engine</strong></p>
    <p style="font-size:.75rem;margin-top:.2rem;opacity:.35"><?= safe($subdomain) ?>.<?= BASE_DOMAIN ?></p>
</footer>

<div id="chat-window" role="dialog" aria-modal="true" aria-label="Asisten AI">
    <div class="chat-head">
        <div class="chat-head-top">
            <div class="chat-av"><i class="bi bi-robot"></i></div>
            <div class="chat-title-wrap">
                <div class="chat-title">Pasek Smart Assistant</div>
                <div class="chat-sub">
                    <div class="online-dot"></div>
                    <span class="text-[10px] uppercase tracking-widest font-semibold">Ready to help</span>
                </div>
            </div>
            <div class="chat-head-btns">
                <button class="chat-hbtn" onclick="closeChat()" title="Tutup Chat">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div class="chat-chips">
            <button class="chip" onclick="sendChip('Tampilkan semua produk')">🛍️ Semua Produk</button>
            <button class="chip" onclick="sendChip('Produk dengan harga termurah')">💰 Termurah</button>
            <button class="chip" onclick="sendChip('Ada promo apa sekarang?')">🎁 Promo</button>
            <button class="chip" onclick="tampilkanKontakWA()">📦 Cara Pesan</button>
        </div>
    </div>

    <div id="chat-msgs"></div>

    <div class="chat-input-bar">
        <div class="input-wrap">
            <textarea id="chat-text" rows="1" placeholder="Tulis pesan Anda..." aria-label="Pesan"></textarea>
            <button id="chat-send" aria-label="Kirim"><i class="bi bi-send-fill"></i></button>
        </div>
        <div class="chat-footer-row">
            <span class="chat-hint"><i class="bi bi-shield-check me-1"></i>Secure AI Session</span>
            <button class="chat-clear-btn" onclick="clearChat()">Clear History</button>
        </div>
    </div>
</div>

<button id="chat-fab" onclick="toggleChat()" aria-label="Buka asisten AI">
    <span class="fab-badge" id="fab-badge">1</span>
    <i class="bi bi-chat-dots-fill" id="fab-icon"></i>
</button>

<script>
const ID_TOKO   = <?= $toko_id ?>;
const NAMA_TOKO = <?= json_encode($toko['nama_toko'] ?? '') ?>;
const IMG_PATH  = 'assets/img/produk/';
const WA_NUM    = <?= json_encode($wa_clean) ?>;

if (!localStorage.getItem('ai_session')) {
    localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));
}
const SESSION_ID = localStorage.getItem('ai_session');

let chatOpen  = false;
let busy      = false;

const chatWin  = document.getElementById('chat-window');
const msgs     = document.getElementById('chat-msgs');
const txt      = document.getElementById('chat-text');
const sendBtn  = document.getElementById('chat-send');
const badge    = document.getElementById('fab-badge');
const fab      = document.getElementById('chat-fab');
const fabIcon  = document.getElementById('fab-icon');

const esc = s => {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(s ?? '')));
    return d.innerHTML;
};
const timeNow  = () => new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
const scrollDown = () => setTimeout(() => { msgs.scrollTop = msgs.scrollHeight; }, 50);

function openChat() {
    if (chatOpen) return;
    chatOpen = true;
    chatWin.classList.add('open');
    fab.classList.add('open');
    fabIcon.className = 'bi bi-x-lg';
    badge.classList.remove('show');
    
    // Kunci scroll layar utama saat chat terbuka di HP
    if (window.innerWidth <= 640) {
        document.body.classList.add('mobile-chat-open');
    }

    if (!msgs.childElementCount) showWelcome();
    setTimeout(() => txt.focus(), 350);
}

function closeChat() {
    chatOpen = false;
    chatWin.classList.remove('open');
    fab.classList.remove('open');
    fabIcon.className = 'bi bi-chat-dots-fill';
    document.body.classList.remove('mobile-chat-open');
}

function toggleChat() { chatOpen ? closeChat() : openChat(); }

function showWelcome() {
    addBubble('ai', `Halo! 👋 Saya asisten pintar di <b>${esc(NAMA_TOKO)}</b>.<br>Ingin mencari produk atau butuh info pemesanan?`);
}

function addBubble(role, html) {
    const row = document.createElement('div');
    row.className = `msg-row ${role}`;
    row.innerHTML = `
        <div class="msg-av ${role}">${role === 'ai' ? '<i class="bi bi-stars"></i>' : '<i class="bi bi-person-fill"></i>'}</div>
        <div class="msg-inner">
            <div class="${role === 'ai' ? 'bubble-ai' : 'bubble-user'}">${html}</div>
            <div class="msg-time">${timeNow()}</div>
        </div>`;
    msgs.appendChild(row);
    scrollDown();
    return row;
}

// ── FUNGSI MENUJU WHATSAPP INSTAN ──
function beliViaWA(nama, harga) {
    const text = `Halo Admin *${NAMA_TOKO}*, saya tertarik untuk membeli produk:\n\n🛍️ *${nama}*\n💰 ${harga}\n\nApakah stoknya masih tersedia?`;
    const url = `https://wa.me/${WA_NUM}?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

// ── KARTU KONTAK WHATSAPP MODERN DALAM CHAT ──
function tampilkanKontakWA() {
    const row = document.createElement('div');
    row.className = 'msg-row ai';
    row.innerHTML = `
        <div class="msg-av ai"><i class="bi bi-stars"></i></div>
        <div class="msg-inner">
            <div class="wa-card">
                <div class="wa-card-head">
                    <i class="bi bi-whatsapp wa-card-icon"></i>
                    <div>
                        <div class="wa-card-title">WhatsApp Admin</div>
                        <div class="wa-card-sub"><div class="online-dot" style="background:#fff; box-shadow:0 0 6px #fff;"></div> Online & Siap Membantu</div>
                    </div>
                </div>
                <div class="wa-card-body">
                    <button class="wa-btn" onclick="window.open('https://wa.me/${WA_NUM}', '_blank')">
                        <i class="bi bi-chat-dots-fill"></i> Hubungi Sekarang
                    </button>
                </div>
            </div>
            <div class="msg-time">${timeNow()}</div>
        </div>`;
    msgs.appendChild(row);
    scrollDown();
}

function addProductCard(nama, harga, foto, desc) {
    const row = document.createElement('div');
    row.className = 'msg-row ai';

    const imgHtml = foto
        ? `<img src="${IMG_PATH}${esc(foto)}" alt="${esc(nama)}" class="chat-product-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
        : '';
    const placeholderStyle = foto ? 'display:none' : '';

    row.innerHTML = `
        <div class="msg-av ai"><i class="bi bi-stars"></i></div>
        <div class="msg-inner">
            <div class="chat-product-card">
                ${imgHtml}
                <div class="chat-product-img placeholder" style="${placeholderStyle}">📦</div>
                <div class="chat-product-body">
                    <div class="chat-product-name">${esc(nama)}</div>
                    <div class="chat-product-price">${esc(harga)}</div>
                    <div class="chat-product-actions">
                        <button class="chat-btn-beli w-full" onclick='beliViaWA(${JSON.stringify(nama)}, ${JSON.stringify(harga)})'>
                            <i class="bi bi-whatsapp" style="font-size:1rem; color:#22c55e;"></i> Beli via WA
                        </button>
                    </div>
                </div>
            </div>
            <div class="msg-time">${timeNow()}</div>
        </div>`;
    msgs.appendChild(row);
    scrollDown();
}

function showTyping() {
    removeTyping();
    const el = document.createElement('div');
    el.className = 'typing-row'; el.id = 'typing-row';
    el.innerHTML = `
        <div class="msg-av ai"><i class="bi bi-stars"></i></div>
        <div class="typing-bubble"><div class="td"></div><div class="td"></div><div class="td"></div></div>`;
    msgs.appendChild(el);
    scrollDown();
}

function removeTyping() {
    const el = document.getElementById('typing-row');
    if (el) el.remove();
}

async function sendChat() {
    const msg = txt.value.trim();
    if (!msg || busy) return;

    txt.value = ''; txt.style.height = 'auto';
    sendBtn.disabled = true; busy = true;

    if (!chatOpen) openChat();
    addBubble('user', esc(msg));
    showTyping();

    const payload = { id_toko: ID_TOKO, session_id: SESSION_ID, user_message: msg };

    try {
        const res  = await fetch('/api/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        removeTyping();

        // Render Teks
        addBubble('ai', esc(data.reply || '_(tidak ada balasan)_').replace(/\n/g, '<br>'));

        // Render Kartu Produk jika direkomendasikan AI
        if (data.db_result && Array.isArray(data.db_result) && data.db_result.length > 0) {
            data.db_result.forEach(prod => {
                const hargaFormat = 'Rp ' + Number(prod.harga).toLocaleString('id-ID');
                addProductCard(prod.nama_produk, hargaFormat, prod.foto_produk, prod.deskripsi);
            });
        }

        // Auto-Trigger Kartu WA jika AI menyebut 'whatsapp' atau 'pesan'
        if (data.reply && (data.reply.toLowerCase().includes('whatsapp') || data.reply.toLowerCase().includes('pesan'))) {
            setTimeout(tampilkanKontakWA, 400);
        }

    } catch (err) {
        removeTyping();
        addBubble('ai', `<span style="color:#dc2626"><i class="bi bi-exclamation-triangle me-1"></i>Maaf, terjadi gangguan koneksi ke server AI.</span>`);
    } finally {
        sendBtn.disabled = false; busy = false; txt.focus();
    }
}

// Fungsi tombol dari Katalog
function tanyaProduk(nama, desc, foto, harga) {
    openChat();
    addProductCard(nama, harga, foto, desc);
    txt.value = `Tolong berikan info lengkap tentang produk "${nama}".`;
    setTimeout(sendChat, 150);
}

function sendChip(text) {
    txt.value = text;
    sendChat();
}

function clearChat() {
    if (!confirm('Hapus semua riwayat percakapan?')) return;
    msgs.innerHTML = '';
    showWelcome();
}

txt.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 90) + 'px';
});

txt.addEventListener('keypress', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); }
});
sendBtn.addEventListener('click', sendChat);

window.addEventListener('scroll', () => {
    const scrolled = window.scrollY > 50;
    const nav = document.getElementById('navbar');
    nav.classList.toggle('scrolled', scrolled);
    const lm = document.getElementById('logo-mark');
    const nb = document.getElementById('nav-brand');
    if (lm) lm.classList.toggle('hero-mode', !scrolled);
    if (nb) nb.style.color = scrolled ? '' : '#fff';
}, { passive: true });

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        badge.classList.add('show');
        setTimeout(() => badge.classList.remove('show'), 5000);
    }, 2500);
});
</script>

</body>
</html>
