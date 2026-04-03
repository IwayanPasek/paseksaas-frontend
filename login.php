<?php
session_start();

// 1. Koneksi Database
$db_host = 'localhost';
$db_user = 'wayan_user';
$db_pass = 'WayanPass123!';
$db_name = 'websitewayan_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Sistem sedang pemeliharaan.");
}

$error = "";

// 2. Logika Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bersihkan sesi lama sebelum login baru
    session_unset();
    
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    if (!empty($user) && !empty($pass)) {
        
        // --- TAHAP 1: CEK MASTER ADMIN ---
        $stmt_master = $pdo->prepare("SELECT * FROM master_admin WHERE username = ?");
        $stmt_master->execute([$user]);
        $master = $stmt_master->fetch();

        if ($master && password_verify($pass, $master['password'])) {
            $_SESSION['master_logged_in'] = true;
            $_SESSION['role'] = 'master';
            header("Location: master.php");
            exit;
        }

        // --- TAHAP 2: CEK TENANT / TOKO (Username adalah subdomain) ---
        $stmt_toko = $pdo->prepare("SELECT * FROM toko WHERE subdomain = ?");
        $stmt_toko->execute([$user]);
        $toko = $stmt_toko->fetch();

        if ($toko && password_verify($pass, $toko['password'])) {
            $_SESSION['tenant_id'] = $toko['id_toko'];
            $_SESSION['nama_toko'] = $toko['nama_toko'];
            $_SESSION['role'] = 'tenant';
            header("Location: admin.php");
            exit;
        }

        $error = "Identitas atau kunci akses salah.";
    } else {
        $error = "Harap masukkan semua data.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gateway — Pasek SaaS Infrastructure</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #080d1a;
            color: #e2eaf5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Background Glow Effect */
        body::before {
            content: '';
            position: absolute; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(245, 166, 35, 0.05) 0%, transparent 70%);
            z-index: -1;
        }

        .login-card {
            background: rgba(13, 20, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group input {
            background: rgba(8, 13, 26, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: #f5a623;
            outline: none;
            background: rgba(8, 13, 26, 0.8);
            box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.1);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-500 text-slate-900 rounded-2xl mb-4 shadow-lg shadow-amber-500/20">
                <i class="bi bi-shield-lock-fill text-2xl"></i>
            </div>
            <h1 style="font-family: 'Syne', sans-serif;" class="text-3xl font-extrabold tracking-tight">Pasek<span class="text-amber-500">SaaS</span></h1>
            <p class="text-slate-500 text-[10px] mt-1 uppercase tracking-[0.3em] font-bold">Secure Access Node</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[11px] p-3 rounded-xl mb-6 flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div class="input-group">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1 mb-2">Identifier</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-600"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" placeholder="Username or Subdomain" 
                           class="w-full pl-11 pr-5 py-4 rounded-2xl text-sm" required>
                </div>
            </div>

            <div class="input-group">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1 mb-2">Keyphrase</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-600"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" placeholder="••••••••" 
                           class="w-full pl-11 pr-5 py-4 rounded-2xl text-sm" required>
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-amber-500 text-slate-900 font-extrabold py-4 rounded-2xl mt-2 hover:bg-amber-400 transform active:scale-95 transition-all shadow-xl shadow-amber-500/10">
                AUTHORIZE ACCESS
            </button>
        </form>

        <div class="mt-10 text-center opacity-30">
            <p class="text-[9px] tracking-[0.3em] uppercase">Control System v2.6.0</p>
        </div>
    </div>

</body>
</html>
