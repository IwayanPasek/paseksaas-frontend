<?php
// ╔══════════════════════════════════════════════════════════════╗
//  KONFIGURASI & PROTEKSI SUBDOMAIN (PRODUCTION MODE)
// ╚══════════════════════════════════════════════════════════════╝
session_start();

define('DB_HOST',     'localhost');
define('DB_USER',     'wayan_user');
define('DB_PASS',     'WayanPass123!');
define('DB_NAME',     'websitewayan_db');
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
             background:#f0f9ff;font-family:'DM Sans',sans-serif;text-align:center;padding:2rem}
        .num{font-size:7rem;font-weight:900;color:#0ea5e9;line-height:1}
        h2{font-size:1.2rem;color:#64748b;margin:.5rem 0 1.5rem;font-weight:500}
        a{color:#0ea5e9;text-decoration:none;font-weight:600}
    </style></head>
    <body>
        <div>
            <div class="num">404</div>
            <h2>Toko "<?= htmlspecialchars($subdomain) ?>" tidak ditemukan.</h2>
            <a href="https://<?= BASE_DOMAIN ?>">← Kembali ke Beranda</a>
        </div>
    </body></html>
    <?php exit;
}

// ── Helper vars & Sanitasi ───────────────────────────────────
$nama_toko = htmlspecialchars($toko['nama_toko'] ?? 'Toko Kami');
$raw_kb    = $toko['knowledge_base'] ?? '';
$desc_toko = htmlspecialchars($raw_kb ?: 'Selamat datang! Temukan produk terbaik kami dengan bantuan Asisten AI.');
$logo_toko = $toko['logo'] ?? '';
$toko_id   = (int)$toko['id_toko'];

// Bersihkan nomor WhatsApp
$wa_raw   = $toko['no_wa'] ?? $toko['whatsapp'] ?? '081234567890';
$wa_clean = preg_replace('/[^0-9]/', '', $wa_raw);
if (str_starts_with($wa_clean, '0')) {
    $wa_clean = '62' . substr($wa_clean, 1);
}

$harga_arr = array_column($list_produk, 'harga');
$min_harga = !empty($harga_arr) ? min($harga_arr) : 0;

function safe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function safeJs(string $s): string { return htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $nama_toko ?></title>
    <meta name="description" content="<?= $desc_toko ?>">

    <!-- Font: DM Sans only -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['"DM Sans"','sans-serif'],
            },
            colors: {
              sky: {
                50:  '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd',
                300: '#7dd3fc', 400: '#38bdf8', 500: '#0ea5e9',
                600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e',
              },
            },
            borderRadius: {
              '2xl': '1rem', '3xl': '1.5rem', '4xl': '2rem',
            },
          }
        }
      }
    </script>

    <style>
        /* ══════════════════════════════════════════════
           VARIABLES & BASE
        ══════════════════════════════════════════════ */
        :root {
            --white:    #ffffff;
            --slate-50: #f8fafc;
            --slate-100:#f1f5f9;
            --slate-200:#e2e8f0;
            --slate-400:#94a3b8;
            --slate-500:#64748b;
            --slate-700:#334155;
            --slate-900:#0f172a;
            --sky-500:  #0ea5e9;
            --sky-600:  #0284c7;
            --sky-50:   #f0f9ff;
            --sky-100:  #e0f2fe;
            --emerald:  #10b981;
            --emerald-dark: #059669;
            --radius-pill: 9999px;
            --radius-card: 20px;
            --shadow-soft: 0 4px 24px rgba(15,23,41,.07);
            --shadow-float: 0 8px 40px rgba(15,23,41,.1);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0; font-family: 'DM Sans', sans-serif;
            background: var(--white); color: var(--slate-900);
            -webkit-font-smoothing: antialiased; overflow-x: hidden;
        }
        body.no-scroll { overflow: hidden; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: var(--slate-200); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--sky-500); }

        /* ══════════════════════════════════════════════
           NAVBAR — Sticky, glass on scroll
        ══════════════════════════════════════════════ */
        #navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            padding: 1rem 1.5rem;
            transition: background .3s, padding .3s, box-shadow .3s;
        }
        #navbar.scrolled {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(20px) saturate(180%);
            padding: .65rem 1.5rem;
            box-shadow: 0 2px 24px rgba(14,165,233,.08);
            border-bottom: 1px solid rgba(14,165,233,.12);
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-logo { display: flex; align-items: center; gap: .6rem; text-decoration: none; }
        .nav-logo-mark {
            width: 40px; height: 40px; border-radius: 14px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            font-weight: 800; font-size: .95rem; color: #fff; overflow: hidden;
            transition: all .3s;
        }
        #navbar.scrolled .nav-logo-mark {
            background: var(--sky-500); border-color: var(--sky-500); color: #fff;
        }
        .nav-logo-mark img { width: 100%; height: 100%; object-fit: cover; }
        .nav-brand {
            font-weight: 800; font-size: 1.05rem; color: #fff;
            transition: color .3s;
        }
        #navbar.scrolled .nav-brand { color: var(--slate-900); }

        .nav-right { display: flex; align-items: center; gap: .6rem; }
        .nav-pill {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .4rem 1rem; border-radius: var(--radius-pill);
            font-size: .82rem; font-weight: 600; text-decoration: none;
            transition: all .2s;
        }
        .nav-pill-ghost {
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.85);
            border: 1.5px solid rgba(255,255,255,.22);
        }
        #navbar.scrolled .nav-pill-ghost {
            background: var(--slate-100);
            color: var(--slate-700);
            border-color: var(--slate-200);
        }
        .nav-pill-sky {
            background: var(--sky-500); color: #fff;
            border: 1.5px solid transparent;
            box-shadow: 0 4px 14px rgba(14,165,233,.3);
        }
        .nav-pill-sky:hover { background: var(--sky-600); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,.4); }
        .nav-pill-ghost:hover { background: rgba(255,255,255,.22); border-color: rgba(255,255,255,.4); }
        #navbar.scrolled .nav-pill-ghost:hover { background: var(--sky-50); border-color: var(--sky-200); color: var(--sky-600); }

        /* ══════════════════════════════════════════════
           HERO — Warm gradient, friendly
        ══════════════════════════════════════════════ */
        #hero {
            min-height: 100vh;
            background: linear-gradient(160deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 80%, #38bdf8 100%);
            display: flex; align-items: center;
            position: relative; overflow: hidden;
            padding-top: 4rem;
        }
        /* Decorative blobs */
        .hero-blob-1 {
            position: absolute; top: -10%; right: -5%;
            width: 45vw; height: 45vw;
            background: radial-gradient(circle, rgba(255,255,255,.1) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .hero-blob-2 {
            position: absolute; bottom: -15%; left: -5%;
            width: 40vw; height: 40vw;
            background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        /* Dot grid pattern */
        #hero::before {
            content: '';
            position: absolute; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: linear-gradient(to bottom, transparent, rgba(0,0,0,.4) 30%, rgba(0,0,0,.4) 70%, transparent);
        }

        .hero-inner {
            position: relative; z-index: 1;
            max-width: 1200px; margin: 0 auto;
            padding: 4rem 1.5rem 5rem; width: 100%;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 3rem; align-items: center;
        }

        /* Tag pill above heading */
        .hero-tag {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.3);
            color: #fff; border-radius: var(--radius-pill);
            padding: .35rem 1rem; font-size: .75rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase; margin-bottom: 1.5rem;
            animation: slideIn .5s ease both;
        }
        .hero-tag-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #bae6fd;
            animation: blink 2s ease infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

        .hero-h1 {
            font-size: clamp(2.4rem, 5.5vw, 4.5rem);
            font-weight: 800; line-height: 1.08;
            color: #fff; margin: 0 0 1.25rem;
            letter-spacing: -.02em;
            animation: slideIn .5s .1s ease both;
        }
        .hero-h1 span { color: #bae6fd; }

        .hero-sub {
            font-size: 1.05rem; line-height: 1.75; color: rgba(255,255,255,.75);
            font-weight: 400; margin: 0 0 2.25rem; max-width: 480px;
            animation: slideIn .5s .2s ease both;
        }

        .hero-actions {
            display: flex; gap: .85rem; flex-wrap: wrap;
            animation: slideIn .5s .3s ease both;
        }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .9rem 2rem; border-radius: var(--radius-pill);
            background: #fff; color: var(--sky-600);
            font-weight: 700; font-size: .95rem; text-decoration: none;
            border: none; cursor: pointer;
            box-shadow: 0 8px 28px rgba(0,0,0,.15);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(0,0,0,.22); }
        .btn-hero-ghost {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .9rem 1.75rem; border-radius: var(--radius-pill);
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.9);
            border: 1.5px solid rgba(255,255,255,.28);
            font-weight: 600; font-size: .92rem; text-decoration: none; cursor: pointer;
            transition: background .2s, border-color .2s;
        }
        .btn-hero-ghost:hover { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); }

        /* Hero stat pills */
        .hero-stats {
            display: flex; gap: .65rem; margin-top: 2.75rem; flex-wrap: wrap;
            animation: slideIn .5s .4s ease both;
        }
        .hero-stat-pill {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: var(--radius-pill);
            padding: .4rem 1rem; font-size: .8rem; color: rgba(255,255,255,.9); font-weight: 600;
        }
        .hero-stat-pill i { font-size: .9rem; color: #bae6fd; }

        /* Hero right: floating card */
        .hero-card {
            background: rgba(255,255,255,.12);
            border: 1.5px solid rgba(255,255,255,.22);
            border-radius: 28px; padding: 2rem;
            backdrop-filter: blur(16px);
            animation: floatY 3s ease-in-out infinite;
        }
        @keyframes floatY { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        .hero-card-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.2); border-radius: var(--radius-pill);
            padding: .28rem .8rem; font-size: .7rem; font-weight: 700;
            color: #fff; letter-spacing: .05em; text-transform: uppercase;
            margin-bottom: 1.1rem;
        }
        .hero-card-title {
            font-weight: 800; font-size: 1.1rem; color: #fff; margin: 0 0 .4rem;
        }
        .hero-card-sub {
            font-size: .8rem; color: rgba(255,255,255,.6); line-height: 1.6; margin: 0 0 1.25rem;
        }
        /* Mini chat preview */
        .mini-chat { display: flex; flex-direction: column; gap: .5rem; }
        .mc-bubble {
            padding: .5rem .85rem; border-radius: 16px;
            font-size: .75rem; line-height: 1.45; max-width: 88%;
        }
        .mc-bubble.ai {
            background: rgba(255,255,255,.15); color: rgba(255,255,255,.85);
            border-radius: 16px 16px 16px 3px;
        }
        .mc-bubble.user {
            background: #fff; color: var(--sky-700);
            font-weight: 600; align-self: flex-end;
            border-radius: 16px 16px 3px 16px;
        }

        @keyframes slideIn { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

        /* ══════════════════════════════════════════════
           WAVE DIVIDER
        ══════════════════════════════════════════════ */
        .wave-divider {
            display: block; width: 100%; overflow: hidden;
            line-height: 0; background: transparent;
        }
        .wave-divider svg { display: block; width: 100%; height: 64px; }

        /* ══════════════════════════════════════════════
           KATALOG SECTION
        ══════════════════════════════════════════════ */
        #katalog {
            background: var(--white);
            padding: 4rem 1.5rem 5rem;
        }
        .katalog-inner { max-width: 1200px; margin: 0 auto; }

        /* Section header */
        .section-label {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--sky-50); color: var(--sky-600);
            border: 1.5px solid var(--sky-100);
            border-radius: var(--radius-pill);
            padding: .3rem .85rem; font-size: .73rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase; margin-bottom: .9rem;
        }
        .section-h2 {
            font-size: clamp(1.75rem, 4vw, 2.6rem);
            font-weight: 800; color: var(--slate-900);
            letter-spacing: -.02em; line-height: 1.15; margin: 0 0 .5rem;
        }
        .section-sub {
            font-size: 1rem; color: var(--slate-500); line-height: 1.65; margin: 0;
        }
        .section-header-row {
            display: flex; align-items: flex-end; justify-content: space-between;
            margin-bottom: 2.25rem; gap: 1.25rem; flex-wrap: wrap;
        }
        .count-badge {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .38rem .9rem; border-radius: var(--radius-pill);
            background: var(--slate-100); border: 1px solid var(--slate-200);
            color: var(--slate-500); font-size: .8rem; font-weight: 600;
        }

        /* ── Product Grid ────────────────────────────── */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 col desktop */
            gap: 1.25rem;
        }

        .product-card {
            background: var(--white); border-radius: var(--radius-card);
            border: 1.5px solid var(--slate-100);
            box-shadow: var(--shadow-soft);
            overflow: hidden; display: flex; flex-direction: column;
            transition: transform .3s cubic-bezier(.34,1.56,.64,1),
                        box-shadow .3s, border-color .3s;
            animation: slideIn .4s ease both;
        }
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 48px rgba(14,165,233,.12);
            border-color: var(--sky-200);
        }
        .product-card:nth-child(1){animation-delay:.05s} .product-card:nth-child(2){animation-delay:.10s}
        .product-card:nth-child(3){animation-delay:.15s} .product-card:nth-child(4){animation-delay:.20s}
        .product-card:nth-child(5){animation-delay:.25s} .product-card:nth-child(6){animation-delay:.30s}
        .product-card:nth-child(n+7){animation-delay:.35s}

        .prod-img-wrap {
            position: relative; aspect-ratio: 4/3; overflow: hidden;
            background: linear-gradient(135deg, var(--sky-50), var(--slate-100));
        }
        .prod-img-wrap img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .45s ease;
        }
        .product-card:hover .prod-img-wrap img { transform: scale(1.06); }
        .prod-placeholder {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: .5rem;
            color: var(--slate-300);
        }
        .prod-placeholder i { font-size: 2.5rem; }
        .prod-placeholder span { font-size: .75rem; font-weight: 600; }

        /* Badge overlay */
        .prod-badge {
            position: absolute; top: .65rem; left: .65rem;
            background: var(--sky-500); color: #fff;
            font-size: .62rem; font-weight: 700; padding: .2rem .6rem;
            border-radius: var(--radius-pill); letter-spacing: .04em;
        }

        .prod-body { padding: 1rem 1rem .75rem; flex: 1; display: flex; flex-direction: column; }
        .prod-name {
            font-weight: 700; font-size: .95rem;
            color: var(--slate-900); line-height: 1.35;
            margin: 0 0 .35rem;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .prod-desc {
            font-size: .78rem; color: var(--slate-400); line-height: 1.55;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
            flex: 1; margin-bottom: .85rem;
        }
        .prod-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: .75rem; border-top: 1px solid var(--slate-100); gap: .4rem;
        }
        .prod-price {
            font-weight: 800; font-size: 1rem; color: var(--slate-900);
        }
        .prod-price small { font-size: .68rem; font-weight: 500; color: var(--slate-400); margin-right: .06rem; }

        .prod-actions { display: flex; gap: .4rem; margin-top: .65rem; }
        .btn-ai {
            flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
            padding: .5rem .6rem; border-radius: var(--radius-pill);
            background: var(--sky-500); color: #fff;
            font-size: .75rem; font-weight: 700; border: none; cursor: pointer;
            box-shadow: 0 4px 12px rgba(14,165,233,.3);
            transition: background .18s, transform .15s, box-shadow .18s;
        }
        .btn-ai:hover { background: var(--sky-600); transform: scale(1.03); box-shadow: 0 6px 18px rgba(14,165,233,.4); }
        .btn-wa {
            flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
            padding: .5rem .6rem; border-radius: var(--radius-pill);
            background: transparent; color: var(--emerald);
            border: 1.5px solid rgba(16,185,129,.25);
            font-size: .75rem; font-weight: 700; cursor: pointer;
            transition: all .18s;
        }
        .btn-wa:hover { background: rgba(16,185,129,.08); border-color: var(--emerald); transform: scale(1.03); }

        .empty-state {
            grid-column: 1/-1; text-align: center; padding: 5rem 2rem;
        }
        .empty-state-icon {
            width: 72px; height: 72px; border-radius: 24px;
            background: var(--sky-50); border: 2px dashed var(--sky-200);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; color: var(--sky-300);
            margin: 0 auto 1rem;
        }
        .empty-state h3 { font-weight: 800; font-size: 1.15rem; color: var(--slate-700); margin: 0 0 .4rem; }
        .empty-state p { font-size: .875rem; color: var(--slate-400); }

        /* ══════════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════════ */
        #footer {
            background: var(--slate-900);
            color: rgba(255,255,255,.4);
            padding: 2.25rem 1.5rem;
            text-align: center; font-size: .8rem; line-height: 1.8;
        }
        #footer strong { color: var(--sky-400); }
        .footer-logo {
            display: inline-flex; align-items: center; gap: .5rem;
            color: rgba(255,255,255,.7); font-weight: 700; font-size: .95rem;
            margin-bottom: .6rem;
        }
        .footer-logo-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--sky-500); display: inline-block;
        }

        /* ══════════════════════════════════════════════
           FLOATING CHAT WIDGET
        ══════════════════════════════════════════════ */

        /* Teaser bubble above FAB */
        #chat-teaser {
            position: fixed; bottom: calc(1.5rem + 62px + 12px); right: 1.5rem; z-index: 199;
            background: var(--slate-900); color: #fff;
            padding: .65rem 1.1rem; border-radius: 16px 16px 4px 16px;
            font-size: .82rem; font-weight: 600; line-height: 1.4;
            box-shadow: 0 8px 28px rgba(0,0,0,.18);
            max-width: 200px; text-align: right;
            animation: bounceIn .5s cubic-bezier(.34,1.56,.64,1) both;
            pointer-events: none;
        }
        #chat-teaser::after {
            content: '';
            position: absolute; bottom: -7px; right: 18px;
            width: 14px; height: 14px;
            background: var(--slate-900);
            clip-path: polygon(0 0, 100% 0, 100% 100%);
        }
        @keyframes bounceIn { from{opacity:0;transform:scale(.7) translateY(8px)} to{opacity:1;transform:scale(1) translateY(0)} }

        /* FAB Button */
        #chat-fab {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 200;
            width: 60px; height: 60px; border-radius: 20px;
            background: var(--sky-500); color: #fff;
            border: none; cursor: pointer; font-size: 1.45rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 28px rgba(14,165,233,.4);
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .2s, background .2s;
        }
        #chat-fab:hover { transform: scale(1.08); box-shadow: 0 12px 36px rgba(14,165,233,.5); }
        #chat-fab.open  {
            background: var(--slate-700); transform: scale(.92) rotate(90deg);
            box-shadow: 0 8px 28px rgba(15,23,41,.3);
        }
        .fab-notif {
            position: absolute; top: -5px; right: -5px;
            width: 19px; height: 19px; border-radius: 50%;
            background: #f43f5e; color: #fff; font-size: .6rem; font-weight: 800;
            border: 2.5px solid #fff; display: none;
            align-items: center; justify-content: center;
        }
        .fab-notif.show { display: flex; }

        /* Chat Window */
        #chat-window {
            position: fixed;
            bottom: calc(1.5rem + 60px + 12px); right: 1.5rem; z-index: 200;
            width: 380px; max-width: calc(100vw - 1.5rem);
            height: 580px; max-height: calc(100dvh - 100px);
            border-radius: 26px;
            background: var(--white);
            border: 1.5px solid var(--slate-100);
            box-shadow: 0 28px 72px rgba(15,23,41,.15), 0 0 0 1px rgba(14,165,233,.06);
            display: flex; flex-direction: column; overflow: hidden;
            opacity: 0; transform: scale(.85) translateY(30px); pointer-events: none;
            transform-origin: bottom right;
            transition: opacity .3s, transform .4s cubic-bezier(.34,1.56,.64,1);
        }
        #chat-window.open { opacity: 1; transform: scale(1) translateY(0); pointer-events: all; }

        /* Chat Header */
        .chat-head {
            flex-shrink: 0;
            background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 100%);
            padding: 1.25rem 1.15rem 1rem;
        }
        .chat-head-row { display: flex; align-items: center; gap: .75rem; }
        .chat-avatar {
            width: 40px; height: 40px; border-radius: 14px; flex-shrink: 0;
            background: var(--sky-500); display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; color: #fff;
            box-shadow: 0 4px 14px rgba(14,165,233,.35);
        }
        .chat-name {
            font-weight: 800; font-size: .95rem; color: #fff;
        }
        .chat-status {
            display: flex; align-items: center; gap: .35rem;
            font-size: .68rem; color: rgba(255,255,255,.55); margin-top: .08rem;
        }
        .status-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #4ade80; box-shadow: 0 0 6px #4ade80;
        }
        .chat-close-btn {
            width: 30px; height: 30px; margin-left: auto; flex-shrink: 0;
            border-radius: 9px; background: rgba(255,255,255,.1);
            border: none; cursor: pointer; color: rgba(255,255,255,.6);
            display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
            transition: background .15s, color .15s;
        }
        .chat-close-btn:hover { background: rgba(255,255,255,.2); color: #fff; }

        /* Quick chips */
        .chat-chips {
            display: flex; gap: .4rem; margin-top: .9rem;
            overflow-x: auto; padding-bottom: .1rem;
        }
        .chat-chips::-webkit-scrollbar { display: none; }
        .chip {
            padding: .3rem .75rem; border-radius: var(--radius-pill); flex-shrink: 0;
            background: rgba(255,255,255,.14); border: 1.5px solid rgba(255,255,255,.22);
            color: rgba(255,255,255,.85); font-size: .7rem; font-weight: 600; cursor: pointer;
            white-space: nowrap; transition: background .15s;
        }
        .chip:hover { background: rgba(255,255,255,.25); }

        /* Messages Area */
        #chat-msgs {
            flex: 1; overflow-y: auto; padding: 1.15rem 1rem .75rem;
            display: flex; flex-direction: column; gap: .9rem;
            background: var(--slate-50);
        }
        #chat-msgs::-webkit-scrollbar { width: 3px; }
        #chat-msgs::-webkit-scrollbar-thumb { background: var(--slate-200); border-radius: 2px; }

        /* Message rows */
        .msg-row { display: flex; gap: .45rem; align-items: flex-end; }
        .msg-row.user { flex-direction: row-reverse; }
        .msg-av {
            width: 27px; height: 27px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .72rem;
        }
        .msg-av.ai   { background: var(--sky-100); color: var(--sky-600); }
        .msg-av.user { background: var(--slate-200); color: var(--slate-600); }
        .msg-inner { display: flex; flex-direction: column; max-width: 82%; }

        /* Bubbles */
        .bubble-ai {
            padding: .72rem 1rem; border-radius: 18px 18px 18px 4px;
            font-size: .875rem; line-height: 1.58;
            background: var(--white); color: var(--slate-900);
            border: 1.5px solid var(--slate-100);
            box-shadow: 0 2px 10px rgba(15,23,41,.05);
            animation: popIn .3s cubic-bezier(.34,1.56,.64,1) both;
        }
        .bubble-user {
            padding: .72rem 1rem; border-radius: 18px 18px 4px 18px;
            font-size: .875rem; line-height: 1.58;
            background: var(--sky-500); color: #fff;
            box-shadow: 0 6px 18px rgba(14,165,233,.22);
            animation: popIn .3s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes popIn { from{opacity:0;transform:scale(.82) translateY(6px)} to{opacity:1;transform:scale(1) translateY(0)} }
        .msg-time {
            font-size: .61rem; color: var(--slate-400);
            margin-top: .18rem; font-weight: 500;
        }
        .msg-row.user .msg-time { text-align: right; }

        /* Typing indicator */
        .typing-row { display: flex; gap: .45rem; align-items: flex-end; }
        .typing-bubble {
            background: var(--white); border: 1.5px solid var(--slate-100);
            border-radius: 18px 18px 18px 4px;
            padding: .72rem 1rem; display: flex; gap: 5px; align-items: center;
            box-shadow: 0 2px 10px rgba(15,23,41,.05);
        }
        .td { width: 6px; height: 6px; border-radius: 50%; background: var(--sky-400); animation: tdPulse 1.4s infinite; }
        .td:nth-child(2){animation-delay:.2s} .td:nth-child(3){animation-delay:.4s}
        @keyframes tdPulse { 0%,100%{opacity:.3;transform:scale(.85)} 50%{opacity:1;transform:scale(1.15)} }

        /* Product card in chat */
        .chat-prod-card {
            background: var(--white); border: 1.5px solid var(--slate-100);
            border-radius: 18px; overflow: hidden;
            box-shadow: 0 4px 18px rgba(15,23,41,.07);
            animation: popIn .35s cubic-bezier(.34,1.56,.64,1) both;
            max-width: 240px; margin-top: .25rem;
        }
        .chat-prod-img {
            width: 100%; aspect-ratio: 4/3; object-fit: cover;
            background: linear-gradient(135deg, var(--sky-50), var(--slate-100));
            display: block;
        }
        .chat-prod-body { padding: .85rem; }
        .chat-prod-name { font-weight: 700; font-size: .88rem; color: var(--slate-900); margin: 0 0 .25rem; }
        .chat-prod-price { font-weight: 800; font-size: .95rem; color: var(--sky-500); margin: 0 0 .65rem; }
        .chat-prod-btn {
            width: 100%; padding: .55rem; border-radius: var(--radius-pill);
            background: var(--emerald); color: #fff;
            font-weight: 700; font-size: .78rem; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: .4rem;
            box-shadow: 0 4px 12px rgba(16,185,129,.28);
            transition: background .15s, transform .15s;
        }
        .chat-prod-btn:hover { background: var(--emerald-dark); transform: scale(1.02); }

        /* WA Card in chat */
        .wa-card {
            border-radius: 18px; overflow: hidden;
            border: 1.5px solid rgba(16,185,129,.18);
            background: var(--white); max-width: 240px; margin-top: .25rem;
            box-shadow: 0 6px 22px rgba(16,185,129,.12);
            animation: popIn .3s cubic-bezier(.34,1.56,.64,1) both;
        }
        .wa-card-head {
            background: linear-gradient(135deg, var(--emerald), var(--emerald-dark));
            padding: 1.1rem 1.15rem; color: #fff;
            display: flex; align-items: center; gap: .85rem;
        }
        .wa-card-icon { font-size: 2rem; line-height: 1; }
        .wa-card-title { font-weight: 800; font-size: 1rem; margin-bottom: .12rem; }
        .wa-card-sub { font-size: .72rem; opacity: .85; display: flex; align-items: center; gap: .3rem; }
        .wa-card-body { padding: 1rem; }
        .wa-btn {
            width: 100%; padding: .65rem; border-radius: var(--radius-pill);
            background: var(--emerald); color: #fff;
            font-weight: 700; font-size: .82rem; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: .45rem;
            box-shadow: 0 4px 14px rgba(16,185,129,.3);
            transition: background .15s, transform .2s;
        }
        .wa-btn:hover { background: var(--emerald-dark); transform: translateY(-1px); }

        /* Chat Input */
        .chat-input-bar {
            flex-shrink: 0; padding: .8rem .9rem;
            border-top: 1px solid var(--slate-100);
            background: var(--white);
        }
        .input-wrap {
            display: flex; align-items: flex-end; gap: .4rem;
            background: var(--slate-50); border: 1.5px solid var(--slate-200);
            border-radius: 16px; padding: .5rem .5rem .5rem .9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap:focus-within {
            background: var(--white); border-color: var(--sky-400);
            box-shadow: 0 0 0 3px rgba(14,165,233,.1);
        }
        #chat-text {
            flex: 1; border: none; background: transparent; resize: none;
            font-family: 'DM Sans', sans-serif; font-size: .875rem; color: var(--slate-900);
            outline: none; line-height: 1.5; max-height: 90px; overflow-y: auto;
        }
        #chat-text::placeholder { color: var(--slate-400); }
        #chat-send {
            width: 36px; height: 36px; flex-shrink: 0;
            background: var(--sky-500); border: none; border-radius: 12px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .88rem;
            box-shadow: 0 4px 12px rgba(14,165,233,.3);
            transition: background .18s, transform .15s;
        }
        #chat-send:hover:not(:disabled) { background: var(--sky-600); transform: scale(1.06); }
        #chat-send:disabled { opacity: .4; cursor: not-allowed; }
        .chat-footer-row {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: .38rem; padding: 0 .1rem;
        }
        .chat-hint { font-size: .62rem; color: var(--slate-400); }
        .chat-clear-btn {
            font-size: .62rem; color: var(--slate-400); font-weight: 600;
            background: none; border: none; cursor: pointer; transition: color .15s;
        }
        .chat-clear-btn:hover { color: #f43f5e; }

        /* ══════════════════════════════════════════════
           RESPONSIVE — CRITICAL MOBILE RULES
        ══════════════════════════════════════════════ */
        @media (max-width: 1024px) {
            .products-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .products-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
            .hero-inner { grid-template-columns: 1fr; padding: 5.5rem 1.25rem 4rem; }
            .hero-card { display: none; }
            #navbar { padding: .75rem 1rem; }
            #navbar.scrolled { padding: .5rem 1rem; }
        }
        /* === MOBILE: 1 COLUMN WAJIB === */
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr !important; /* FULL WIDTH */
                gap: 1rem;
            }
            /* Di mobile: card horizontal untuk feel lebih compact */
            .product-card { display: grid; grid-template-columns: 130px 1fr; }
            .prod-img-wrap { aspect-ratio: 1/1; border-radius: 0; }
            .prod-badge { top: .5rem; left: .5rem; }
            .hero-actions { flex-direction: column; }
            .btn-hero-primary, .btn-hero-ghost { width: 100%; justify-content: center; }
            .hero-stats { gap: .5rem; }
            .hero-stat-pill { font-size: .75rem; padding: .35rem .8rem; }

            /* Chat fullscreen di mobile */
            #chat-window {
                width: 100vw; max-width: 100vw;
                height: 100dvh; max-height: 100dvh;
                bottom: 0; right: 0;
                border-radius: 0;
                transform: translateY(100%) scale(1);
                transform-origin: bottom center;
            }
            #chat-window.open { transform: translateY(0) scale(1); }
            #chat-fab.open { display: none; }
            body.no-scroll { overflow: hidden; }
            #chat-teaser { right: 1rem; bottom: calc(1rem + 62px + 12px); }
            #chat-fab { right: 1rem; bottom: 1rem; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════ -->
<nav id="navbar">
    <div class="nav-inner">
        <!-- Logo -->
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

        <!-- Right -->
        <div class="nav-right">
            <a href="#katalog" class="nav-pill nav-pill-ghost hidden sm:inline-flex">
                <i class="bi bi-grid-3x3-gap"></i> Lihat Layanan
            </a>
            <a href="login.php" class="nav-pill nav-pill-sky">
                <i class="bi bi-shield-lock"></i>
                <span class="hidden sm:inline">Admin</span>
            </a>
        </div>
    </div>
</nav>


<!-- ══════════════════════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════════════════════ -->
<section id="hero">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>

    <div class="hero-inner">
        <!-- Left: Copy -->
        <div>
            <div class="hero-tag">
                <div class="hero-tag-dot"></div>
                AI-Powered &middot; <?= safe($subdomain) ?>.<?= BASE_DOMAIN ?>
            </div>

            <h1 class="hero-h1">
                Kami ahlinya<br>
                beresin <span>masalahmu.</span><br>
                Tinggal duduk manis! 😊
            </h1>

            <p class="hero-sub"><?= $desc_toko ?></p>

            <div class="hero-actions">
                <a href="#katalog" class="btn-hero-primary">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Lihat Layanan
                </a>
                <button class="btn-hero-ghost" onclick="openChat()">
                    <i class="bi bi-stars"></i> Tanya AI Gratis
                </button>
            </div>

            <?php if (!empty($list_produk)): ?>
            <div class="hero-stats">
                <div class="hero-stat-pill">
                    <i class="bi bi-box-seam"></i>
                    <?= count($list_produk) ?> Produk Tersedia
                </div>
                <?php if ($min_harga > 0): ?>
                <div class="hero-stat-pill">
                    <i class="bi bi-tag"></i>
                    Mulai Rp <?= number_format($min_harga, 0, ',', '.') ?>
                </div>
                <?php endif; ?>
                <div class="hero-stat-pill">
                    <i class="bi bi-lightning-charge"></i>
                    AI Siap 24/7
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Preview card -->
        <div class="hero-right" style="justify-content:flex-end">
            <div class="hero-card">
                <div class="hero-card-badge">
                    <i class="bi bi-stars"></i> AI Assistant
                </div>
                <h3 class="hero-card-title">Bingung pilih produk?</h3>
                <p class="hero-card-sub">Tanya langsung ke AI kami, gratis dan ramah!</p>
                <div class="mini-chat">
                    <div class="mc-bubble ai">Hai kak! Lagi nyari apa nih? 😊</div>
                    <div class="mc-bubble user">Produk paling laris apa?</div>
                    <div class="mc-bubble ai">Wah, aku bantu cariin ya! Sebentar...</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Wave divider -->
<div class="wave-divider">
    <svg viewBox="0 0 1440 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0,64 C360,0 1080,64 1440,32 L1440,64 L0,64 Z" fill="#ffffff"/>
    </svg>
</div>


<!-- ══════════════════════════════════════════════════════════
     KATALOG PRODUK
══════════════════════════════════════════════════════════ -->
<section id="katalog">
    <div class="katalog-inner">

        <div class="section-header-row">
            <div>
                <div class="section-label">
                    <i class="bi bi-grid-3x3-gap"></i> Katalog Produk
                </div>
                <h2 class="section-h2">Semua Produk & Layanan</h2>
                <p class="section-sub">Temukan yang Anda butuhkan — atau tanya AI kami, gratis! ✌️</p>
            </div>
            <?php if (!empty($list_produk)): ?>
            <div class="count-badge">
                <i class="bi bi-box-seam"></i> <?= count($list_produk) ?> item
            </div>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php if (empty($list_produk)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-bag-x"></i></div>
                    <h3>Produk belum tersedia</h3>
                    <p>Admin toko belum menambahkan produk apapun.</p>
                </div>
            <?php else: ?>
                <?php foreach ($list_produk as $p):
                    $pnama  = safe($p['nama_produk'] ?? '—');
                    $pdesc  = safe($p['deskripsi'] ?? '');
                    $pfoto  = $p['foto_produk'] ?? '';
                    $pharga = number_format((float)($p['harga'] ?? 0), 0, ',', '.');
                    $pnamaJs  = safeJs($p['nama_produk'] ?? '');
                    $pdescJs  = safeJs(mb_substr($p['deskripsi'] ?? '', 0, 120));
                    $pfotoJs  = safeJs($pfoto);
                    $phargaJs = safeJs('Rp ' . $pharga);
                ?>
                <div class="product-card">
                    <!-- Foto -->
                    <div class="prod-img-wrap">
                        <?php if ($pfoto): ?>
                            <img src="assets/img/produk/<?= safe($pfoto) ?>" alt="<?= $pnama ?>"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="prod-placeholder" style="display:none">
                                <i class="bi bi-image"></i>
                                <span>Foto tidak tersedia</span>
                            </div>
                        <?php else: ?>
                            <div class="prod-placeholder">
                                <i class="bi bi-image"></i>
                                <span>Belum ada foto</span>
                            </div>
                        <?php endif; ?>
                        <div class="prod-badge">Tersedia</div>
                    </div>

                    <!-- Body -->
                    <div class="prod-body">
                        <h3 class="prod-name"><?= $pnama ?></h3>
                        <?php if ($pdesc): ?>
                            <p class="prod-desc"><?= $pdesc ?></p>
                        <?php endif; ?>
                        <div class="prod-footer">
                            <div class="prod-price"><small>Rp </small><?= $pharga ?></div>
                        </div>
                        <div class="prod-actions">
                            <button class="btn-ai"
                                onclick='tanyaProduk(<?= $pnamaJs ?>, <?= $pdescJs ?>, <?= $pfotoJs ?>, <?= $phargaJs ?>)'>
                                <i class="bi bi-stars"></i> Tanya AI
                            </button>
                            <button class="btn-wa"
                                onclick='beliViaWA(<?= $pnamaJs ?>, <?= $phargaJs ?>)'>
                                <i class="bi bi-whatsapp"></i> Beli
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ -->
<footer id="footer">
    <div class="footer-logo">
        <div class="footer-logo-dot"></div>
        <?= $nama_toko ?>
    </div>
    <p>&copy; <?= date('Y') ?> <strong><?= $nama_toko ?></strong> &mdash; Ditenagai oleh <strong>Pasek AI Engine</strong></p>
    <p style="font-size:.72rem;margin-top:.2rem;opacity:.3"><?= safe($subdomain) ?>.<?= BASE_DOMAIN ?></p>
</footer>


<!-- ══════════════════════════════════════════════════════════
     FLOATING CHAT WIDGET
══════════════════════════════════════════════════════════ -->

<!-- Teaser bubble -->
<div id="chat-teaser">
    Ada yang bingung? Tanya aja, gratis kok! ✌️
</div>

<!-- Chat Window -->
<div id="chat-window" role="dialog" aria-modal="true" aria-label="Asisten AI">

    <!-- Header -->
    <div class="chat-head">
        <div class="chat-head-row">
            <div class="chat-avatar"><i class="bi bi-robot"></i></div>
            <div style="flex:1;min-width:0">
                <div class="chat-name">Asisten <?= $nama_toko ?></div>
                <div class="chat-status">
                    <div class="status-dot"></div>
                    <span>Online & siap bantu kamu!</span>
                </div>
            </div>
            <button class="chat-close-btn" onclick="closeChat()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Quick chips -->
        <div class="chat-chips">
            <button class="chip" onclick="sendChip('Tampilkan semua produk')">🛍️ Semua Produk</button>
            <button class="chip" onclick="sendChip('Produk paling murah berapa?')">💰 Termurah</button>
            <button class="chip" onclick="sendChip('Ada promo apa sekarang?')">🎁 Ada Promo?</button>
            <button class="chip" onclick="tampilkanKontakWA()">📦 Cara Pesan</button>
        </div>
    </div>

    <!-- Messages -->
    <div id="chat-msgs"></div>

    <!-- Input bar -->
    <div class="chat-input-bar">
        <div class="input-wrap">
            <textarea id="chat-text" rows="1" placeholder="Ketik pertanyaanmu di sini..." aria-label="Pesan"></textarea>
            <button id="chat-send" aria-label="Kirim pesan">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
        <div class="chat-footer-row">
            <span class="chat-hint"><i class="bi bi-shield-check me-1"></i>Aman &amp; Gratis</span>
            <button class="chat-clear-btn" onclick="clearChat()">Hapus Chat</button>
        </div>
    </div>

</div>

<!-- FAB Button -->
<button id="chat-fab" onclick="toggleChat()" aria-label="Buka atau tutup asisten AI">
    <span class="fab-notif" id="fab-notif">1</span>
    <i class="bi bi-chat-dots-fill" id="fab-icon"></i>
</button>


<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT (Fungsional — tidak diubah strukturnya)
══════════════════════════════════════════════════════════ -->
<script>
/* ── Config PHP → JS ──────────────────────────────────────── */
const ID_TOKO   = <?= $toko_id ?>;
const NAMA_TOKO = <?= json_encode($toko['nama_toko'] ?? '') ?>;
const IMG_PATH  = 'assets/img/produk/';
const WA_NUM    = <?= json_encode($wa_clean) ?>;

/* ── Session ID ───────────────────────────────────────────── */
if (!localStorage.getItem('ai_session')) {
    localStorage.setItem('ai_session', 'sess_' + Math.random().toString(36).substr(2, 9));
}
const SESSION_ID = localStorage.getItem('ai_session');

/* ── State ────────────────────────────────────────────────── */
let chatOpen = false;
let busy     = false;

/* ── DOM ──────────────────────────────────────────────────── */
const chatWin = document.getElementById('chat-window');
const msgs    = document.getElementById('chat-msgs');
const txt     = document.getElementById('chat-text');
const sendBtn = document.getElementById('chat-send');
const notif   = document.getElementById('fab-notif');
const fab     = document.getElementById('chat-fab');
const fabIcon = document.getElementById('fab-icon');
const teaser  = document.getElementById('chat-teaser');

/* ── Utils ────────────────────────────────────────────────── */
const esc = s => {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(s ?? '')));
    return d.innerHTML;
};
const timeNow  = () => new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
const scrollDown = () => setTimeout(() => { msgs.scrollTop = msgs.scrollHeight; }, 55);

/* ── Open / Close ─────────────────────────────────────────── */
function openChat() {
    if (chatOpen) return;
    chatOpen = true;
    chatWin.classList.add('open');
    fab.classList.add('open');
    fabIcon.className = 'bi bi-x-lg';
    notif.classList.remove('show');
    teaser.style.display = 'none';

    if (window.innerWidth <= 480) {
        document.body.classList.add('no-scroll');
    }

    if (!msgs.childElementCount) showWelcome();
    setTimeout(() => txt.focus(), 350);
}

function closeChat() {
    chatOpen = false;
    chatWin.classList.remove('open');
    fab.classList.remove('open');
    fabIcon.className = 'bi bi-chat-dots-fill';
    document.body.classList.remove('no-scroll');
}

function toggleChat() { chatOpen ? closeChat() : openChat(); }

/* ── Welcome ──────────────────────────────────────────────── */
function showWelcome() {
    addBubble('ai', `Heyy, selamat datang di <b>${esc(NAMA_TOKO)}</b>! 👋<br>Ada yang bisa aku bantu? Mau cari produk atau info pemesanan?`);
}

/* ── Add Bubble ───────────────────────────────────────────── */
function addBubble(role, html) {
    const row = document.createElement('div');
    row.className = `msg-row ${role}`;
    row.innerHTML = `
        <div class="msg-av ${role}">
            ${role === 'ai' ? '<i class="bi bi-stars"></i>' : '<i class="bi bi-person-fill"></i>'}
        </div>
        <div class="msg-inner">
            <div class="${role === 'ai' ? 'bubble-ai' : 'bubble-user'}">${html}</div>
            <div class="msg-time">${timeNow()}</div>
        </div>`;
    msgs.appendChild(row);
    scrollDown();
    return row;
}

/* ── WA Direct ────────────────────────────────────────────── */
function beliViaWA(nama, harga) {
    const text = `Halo Admin *${NAMA_TOKO}*! Saya mau tanya soal:\n\n🛍️ *${nama}*\n💰 ${harga}\n\nMasih tersedia kak?`;
    window.open(`https://wa.me/${WA_NUM}?text=${encodeURIComponent(text)}`, '_blank');
}

/* ── WA Card in Chat ──────────────────────────────────────── */
function tampilkanKontakWA() {
    const row = document.createElement('div');
    row.className = 'msg-row ai';
    row.innerHTML = `
        <div class="msg-av ai"><i class="bi bi-stars"></i></div>
        <div class="msg-inner">
            <div class="wa-card">
                <div class="wa-card-head">
                    <div class="wa-card-icon"><i class="bi bi-whatsapp"></i></div>
                    <div>
                        <div class="wa-card-title">Hubungi Admin</div>
                        <div class="wa-card-sub">
                            <div class="status-dot" style="background:#fff;box-shadow:0 0 5px #fff"></div>
                            Online, respon cepat!
                        </div>
                    </div>
                </div>
                <div class="wa-card-body">
                    <button class="wa-btn" onclick="window.open('https://wa.me/${WA_NUM}', '_blank')">
                        <i class="bi bi-chat-dots-fill"></i> Chat Sekarang
                    </button>
                </div>
            </div>
            <div class="msg-time">${timeNow()}</div>
        </div>`;
    msgs.appendChild(row);
    scrollDown();
}

/* ── Product Card in Chat ─────────────────────────────────── */
function addProductCard(nama, harga, foto, desc) {
    const row = document.createElement('div');
    row.className = 'msg-row ai';

    const imgHtml = foto
        ? `<img src="${IMG_PATH}${esc(foto)}" alt="${esc(nama)}" class="chat-prod-img"
               onerror="this.style.display='none'">`
        : '';

    row.innerHTML = `
        <div class="msg-av ai"><i class="bi bi-stars"></i></div>
        <div class="msg-inner">
            <div class="chat-prod-card">
                ${imgHtml}
                <div class="chat-prod-body">
                    <div class="chat-prod-name">${esc(nama)}</div>
                    <div class="chat-prod-price">${esc(harga)}</div>
                    <button class="chat-prod-btn" onclick='beliViaWA(${JSON.stringify(nama)}, ${JSON.stringify(harga)})'>
                        <i class="bi bi-whatsapp"></i> Pesan via WhatsApp
                    </button>
                </div>
            </div>
            <div class="msg-time">${timeNow()}</div>
        </div>`;
    msgs.appendChild(row);
    scrollDown();
}

/* ── Typing Indicator ─────────────────────────────────────── */
function showTyping() {
    removeTyping();
    const el = document.createElement('div');
    el.className = 'typing-row'; el.id = 'typing-row';
    el.innerHTML = `
        <div class="msg-av ai"><i class="bi bi-stars"></i></div>
        <div class="typing-bubble">
            <div class="td"></div><div class="td"></div><div class="td"></div>
        </div>`;
    msgs.appendChild(el);
    scrollDown();
}
function removeTyping() {
    const el = document.getElementById('typing-row');
    if (el) el.remove();
}

/* ── SEND CHAT ────────────────────────────────────────────── */
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

        addBubble('ai', esc(data.reply || '_(tidak ada balasan)_').replace(/\n/g, '<br>'));

        // Render kartu produk jika ada rekomendasi dari AI
        if (data.db_result && Array.isArray(data.db_result) && data.db_result.length > 0) {
            data.db_result.forEach(prod => {
                const hargaFmt = 'Rp ' + Number(prod.harga).toLocaleString('id-ID');
                addProductCard(prod.nama_produk, hargaFmt, prod.foto_produk, prod.deskripsi);
            });
        }

        // Auto-trigger kartu WA jika AI menyebut 'whatsapp' atau 'pesan'
        if (data.reply && (data.reply.toLowerCase().includes('whatsapp') || data.reply.toLowerCase().includes('pesan'))) {
            setTimeout(tampilkanKontakWA, 450);
        }

    } catch (err) {
        removeTyping();
        addBubble('ai', `<span style="color:#dc2626"><i class="bi bi-exclamation-circle me-1"></i>Aduh, ada gangguan koneksi nih. Coba lagi ya!</span>`);
    } finally {
        sendBtn.disabled = false; busy = false; txt.focus();
    }
}

/* ── Tombol dari Katalog ──────────────────────────────────── */
function tanyaProduk(nama, desc, foto, harga) {
    openChat();
    addProductCard(nama, harga, foto, desc);
    txt.value = `Kak, ceritain dong soal "${nama}" lebih lengkapnya!`;
    setTimeout(sendChat, 200);
}

function sendChip(text) { txt.value = text; sendChat(); }

function clearChat() {
    if (!confirm('Yakin mau hapus semua chat?')) return;
    msgs.innerHTML = '';
    showWelcome();
}

/* ── Textarea auto-resize ─────────────────────────────────── */
txt.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 90) + 'px';
});

/* ── Enter = send, Shift+Enter = newline ──────────────────── */
txt.addEventListener('keypress', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); }
});
sendBtn.addEventListener('click', sendChat);

/* ── Navbar scroll behavior ───────────────────────────────── */
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
}, { passive: true });

/* ── Init: teaser bubble & notif badge ───────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    // Sembunyikan teaser setelah 5 detik
    setTimeout(() => {
        if (!chatOpen) teaser.style.opacity = '0';
        setTimeout(() => { teaser.style.display = 'none'; }, 500);
    }, 5000);
    teaser.style.transition = 'opacity .5s';

    // Tampilkan notif badge di FAB
    setTimeout(() => {
        notif.classList.add('show');
        setTimeout(() => notif.classList.remove('show'), 5000);
    }, 2500);
});
</script>

</body>
</html>
