<?php
session_start();

// 1. Proteksi Halaman Master - Hanya admin 'pasek' yang bisa masuk
if (!isset($_SESSION['master_logged_in'])) {
    header("Location: login.php");
    exit;
}

// 2. Koneksi Database
$db_host = 'localhost';
$db_user = 'wayan_user';
$db_pass = 'WayanPass123!';
$db_name = 'websitewayan_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$status_msg = "";

// 3. Logika Pendaftaran Toko (Tenant) Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_tenant'])) {
    $nama_toko = htmlspecialchars(trim($_POST['nama_toko']));
    // Bersihkan subdomain dari karakter aneh
    $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $_POST['subdomain']));
    // Hash password sebelum disimpan ke database
    $password  = password_hash($_POST['password_toko'], PASSWORD_BCRYPT);
    // Bersihkan nomor WA (hanya angka)
    $wa        = preg_replace('/[^0-9]/', '', $_POST['kontak_wa']); 
    $kb        = $_POST['knowledge_base'];

    try {
        $stmt = $pdo->prepare("INSERT INTO toko (nama_toko, subdomain, password, kontak_wa, knowledge_base) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama_toko, $subdomain, $password, $wa, $kb]);
        
        $status_msg = "<div class='p-4 mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-2xl text-sm'>
                        <i class='bi bi-check-circle-fill me-2'></i>
                        <b>Berhasil!</b> Toko $nama_toko telah aktif.
                      </div>";
    } catch(PDOException $e) {
        $status_msg = "<div class='p-4 mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-2xl text-sm'>
                        <i class='bi bi-exclamation-triangle-fill me-2'></i>
                        <b>Gagal:</b> Subdomain sudah digunakan atau error sistem.
                      </div>";
    }
}

// 4. Ambil Daftar Toko Terdaftar
$tenants = $pdo->query("SELECT * FROM toko ORDER BY id_toko DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Center — Infrastructure Management</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #080d1a;
            color: #e2e8f0;
        }
        .glass-card {
            background: rgba(13, 20, 39, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .input-dark {
            background: rgba(8, 13, 26, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .input-dark:focus {
            border-color: #f5a623;
            outline: none;
            box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.1);
        }
    </style>
</head>
<body class="min-h-screen">

    <nav class="border-b border-white/5 px-8 py-5 flex justify-between items-center bg-[#080d1a]/80 backdrop-blur-md sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="bg-amber-500 text-slate-900 w-10 h-10 rounded-xl flex items-center justify-center font-black shadow-lg shadow-amber-500/20">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <span style="font-family: 'Syne', sans-serif;" class="font-bold text-xl tracking-tight text-white">MASTER<span class="text-amber-500">CENTER</span></span>
        </div>
        <div class="flex items-center gap-6">
            <div class="hidden md:flex flex-col text-right">
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Admin Session</span>
                <span class="text-xs text-amber-500 font-mono">pasek@websitewayan_node</span>
            </div>
            <a href="logout.php" class="bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white px-5 py-2.5 rounded-2xl text-xs font-bold transition-all border border-rose-500/20">
                LOGOUT
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-5">
                <div class="mb-8">
                    <h2 style="font-family: 'Syne', sans-serif;" class="text-3xl font-extrabold text-white mb-2">Deploy New <span class="text-amber-500">Tenant</span></h2>
                    <p class="text-slate-400 text-sm">Daftarkan subdomain baru untuk aktivasi AI Smart Shop.</p>
                </div>

                <?= $status_msg ?>

                <div class="glass-card p-8 rounded-[35px]">
                    <form method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Business Identity</label>
                            <input type="text" name="nama_toko" required placeholder="Nama Toko" 
                                   class="input-dark w-full px-5 py-4 rounded-2xl text-sm text-white">
                            
                            <div class="flex items-center input-dark rounded-2xl px-5 py-4">
                                <input type="text" name="subdomain" required placeholder="subdomain" 
                                       class="bg-transparent border-none focus:ring-0 text-sm flex-1 outline-none text-white">
                                <span class="text-slate-600 text-xs font-bold">.websitewayan.my.id</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Access Control</label>
                            <input type="password" name="password_toko" required placeholder="Set Password Admin Toko" 
                                   class="input-dark w-full px-5 py-4 rounded-2xl text-sm text-white">
                            
                            <input type="text" name="kontak_wa" required placeholder="62812345678 (WhatsApp)" 
                                   class="input-dark w-full px-5 py-4 rounded-2xl text-sm text-white">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">AI Knowledge Base</label>
                            <textarea name="knowledge_base" rows="3" required 
                                      placeholder="Informasi umum toko (lokasi, jam buka, dll)..." 
                                      class="input-dark w-full px-5 py-4 rounded-2xl text-sm text-white resize-none"></textarea>
                        </div>

                        <button type="submit" name="register_tenant" 
                                class="w-full bg-amber-500 text-slate-900 font-black py-4 rounded-2xl hover:bg-amber-400 transition-all transform active:scale-95 shadow-xl shadow-amber-500/20 tracking-tight">
                            INITIALIZE SYSTEM
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="flex justify-between items-center mb-8">
                    <h2 style="font-family: 'Syne', sans-serif;" class="text-2xl font-extrabold text-white">Cloud <span class="text-slate-500 text-lg italic">Instance</span></h2>
                    <span class="bg-slate-800 px-4 py-1.5 rounded-full text-[10px] font-bold text-amber-500 border border-white/5"><?= count($tenants) ?> ACTIVE NODES</span>
                </div>

                <div class="space-y-4">
                    <?php if(empty($tenants)): ?>
                        <div class="text-center py-20 border-2 border-dashed border-slate-800 rounded-[40px]">
                            <i class="bi bi-inbox text-4xl text-slate-700"></i>
                            <p class="text-slate-600 font-bold mt-4">Belum ada tenant yang dideploy.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($tenants as $t): ?>
                    <div class="glass-card p-6 rounded-3xl flex items-center justify-between group hover:border-amber-500/30 transition-all">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center font-bold text-xl text-amber-500 border border-white/5 group-hover:bg-amber-500 group-hover:text-slate-900 transition-all">
                                <?= strtoupper(substr($t['nama_toko'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-lg"><?= htmlspecialchars($t['nama_toko']) ?></h3>
                                <div class="flex items-center gap-2 mt-1 text-slate-500">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                    <p class="text-[11px] font-mono tracking-tighter"><?= $t['subdomain'] ?>.websitewayan.my.id</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <a href="https://wa.me/<?= $t['kontak_wa'] ?>" target="_blank" 
                               class="w-11 h-11 rounded-xl bg-white/5 flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:bg-emerald-500/10 transition-all" title="WhatsApp Admin">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://<?= $t['subdomain'] ?>.websitewayan.my.id" target="_blank" 
                               class="w-11 h-11 rounded-xl bg-white/5 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-500/10 transition-all" title="Visit Instance">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </main>

    <footer class="mt-20 py-10 border-t border-white/5 text-center">
        <p class="text-[10px] text-slate-600 uppercase tracking-[0.5em] font-bold">Pasek Cloud Node Security &copy; 2026</p>
    </footer>

</body>
</html>
