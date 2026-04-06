<?php
// ═══════════════════════════════════════════════════
//  SaaS LANDING PAGE — Marketing Website
// ═══════════════════════════════════════════════════
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasek SaaS — Buat Toko Online dengan Asisten AI</title>
    
    <!-- SEO & OGP Meta Tags -->
    <meta name="description" content="PasekSaaS adalah platform pembuatan toko online cerdas. Bangun etalase digital Anda seketika, lengkap dengan otomatisasi Asisten AI untuk melayani pelanggan 24/7.">
    <meta name="keywords" content="paseksaas, buat toko online, ai e-commerce, asisten ai, toko digital, jualan online, platform saas indonesia">
    <meta property="og:title" content="Pasek SaaS — Buat Toko Online dengan Asisten AI">
    <meta property="og:description" content="Platform pembuatan toko online cerdas dengan otomatisasi Asisten AI terintegrasi. Bangun toko Anda hari ini!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://websitewayan.my.id">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Pasek SaaS — Buat Toko Online dengan Asisten AI">
    <meta name="twitter:description" content="Platform pembuatan toko online cerdas dengan otomatisasi Asisten AI terintegrasi.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN for standalone landing page) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        neutral: {
                            50: '#fafafa',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            300: '#d4d4d4',
                            400: '#a3a3a3',
                            500: '#737373',
                            600: '#525252',
                            700: '#404040',
                            800: '#262626',
                            900: '#171717',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body { 
            background-color: #0A0A0A; 
            color: #ffffff; 
            overflow-x: hidden;
        }
        
        /* Modern Gradients & Glows */
        .glow-bg {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(56,189,248,0.08) 0%, rgba(0,0,0,0) 70%);
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            z-index: -1;
            border-radius: 50%;
        }
        
        .glass-nav {
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .text-gradient {
            background: linear-gradient(to right, #fff, #a3a3a3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .text-gradient-blue {
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="antialiased selection:bg-neutral-800 selection:text-white">

    <div class="glow-bg"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white text-black flex items-center justify-center font-display font-bold text-lg">P</div>
                <span class="font-display font-bold text-xl tracking-tight">PasekSaaS</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-400">
                <a href="#fitur" class="hover:text-white transition-colors">Fitur</a>
                <a href="#demo" class="hover:text-white transition-colors">Demo</a>
                <a href="#harga" class="hover:text-white transition-colors">Harga</a>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="login.php" class="text-sm font-medium text-neutral-300 hover:text-white transition-colors hidden sm:block">Masuk</a>
                <a href="login.php" class="px-5 py-2.5 bg-white text-black text-sm font-semibold rounded-full hover:bg-neutral-200 transition-colors shadow-[0_0_15px_rgba(255,255,255,0.2)]">Buat Toko</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-40 pb-20 px-6 mt-10">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full glass-card text-xs font-medium text-neutral-300 mb-8">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                Sistem PasekSaaS Versi 2.0 Telah Rilis
            </div>
            
            <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight mb-8 leading-[1.1]">
                Bikin Toko Online Makin Pintar dengan <span class="text-gradient-blue">Asisten AI</span>
            </h1>
            
            <p class="text-lg md:text-xl text-neutral-400 mb-10 max-w-2xl mx-auto leading-relaxed">
                Tingkatkan penjualan dan layani pelanggan 24/7 tanpa henti. Buat halaman toko anda sendiri, atur etalase, dan biarkan AI yang melayani tanya jawab pelanggan.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="login.php" class="w-full sm:w-auto px-8 py-4 bg-white text-black font-semibold rounded-full hover:bg-neutral-200 transition-colors text-lg flex items-center justify-center gap-2">
                    Mulai Sekarang Gratis
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
                <a href="https://iwayanpasek.websitewayan.my.id" target="_blank" class="w-full sm:w-auto px-8 py-4 glass-card text-white font-medium rounded-full transition-colors text-lg flex items-center justify-center gap-2">
                    Lihat Demo Live
                </a>
            </div>
        </div>
    </section>

    <!-- Visual Dashboard Preview -->
    <section class="max-w-6xl mx-auto px-6 pb-24 relative">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0A0A0A] to-transparent z-10 h-full w-full pointer-events-none mt-40"></div>
        <div class="rounded-2xl border border-neutral-800 bg-neutral-900/50 p-2 shadow-2xl overflow-hidden glass-card">
            <div class="rounded-xl overflow-hidden border border-neutral-800 relative bg-neutral-950 aspect-[16/9] flex items-center justify-center">
                <!-- Abstract representation of the dashboard -->
                <div class="absolute top-0 left-0 w-full h-10 border-b border-neutral-800 flex items-center px-4 gap-2 bg-neutral-900">
                    <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/50"></div>
                </div>
                <div class="text-center">
                    <h3 class="font-display text-2xl font-bold text-neutral-600">Dashboard & Storefront Preview</h3>
                    <p class="text-neutral-500 text-sm mt-2">Daftar sekarang untuk melihat interface.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="fitur" class="py-24 px-6 border-t border-neutral-900">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="font-display text-3xl md:text-5xl font-bold mb-4">Fitur Utama</h2>
                <p class="text-neutral-400">Semua yang Anda butuhkan untuk berjualan di era modern.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="glass-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-6">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">AI Chatbot 24/7</h3>
                    <p class="text-neutral-400 leading-relaxed text-sm">Pelanggan tidak perlu menunggu balasan. Asisten AI kami akan melayani pertanyaan, menjelaskan produk, dan memberikan harga secara instan kapan saja.</p>
                </div>
                
                <div class="glass-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-6">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Manajemen Etalase</h3>
                    <p class="text-neutral-400 leading-relaxed text-sm">Dashboard admin minimalis untuk mengelola katalog layanan, kategori, harga, dan FAQ yang digunakan AI sebagai basis pengetahuan cerdas.</p>
                </div>
                
                <div class="glass-card p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-6">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Custom Subdomain</h3>
                    <p class="text-neutral-400 leading-relaxed text-sm">Toko Anda mendapatkan alamat sendiri secara otomatis (nama.websitewayan.my.id) untuk branding yang profesional dan mudah diingat oleh pelanggan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Link -->
    <section id="demo" class="py-24 px-6">
        <div class="max-w-4xl mx-auto glass-card rounded-[2rem] p-8 md:p-14 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px]"></div>
            
            <h2 class="font-display text-3xl md:text-4xl font-bold mb-4 relative z-10">Coba Langsung Aksinya</h2>
            <p class="text-neutral-400 mb-8 max-w-xl mx-auto relative z-10">Lihat bagaimana AI merespons layaknya admin profesional. Kunjungi toko demo kami yang menggunakan template bawaan PasekSaaS.</p>
            
            <a href="https://iwayanpasek.websitewayan.my.id" target="_blank" class="inline-flex items-center gap-3 px-6 py-4 bg-neutral-800 border border-neutral-700 rounded-xl hover:bg-neutral-700 transition-colors group relative z-10">
                <span class="text-neutral-400">Toko Demo:</span>
                <span class="font-medium text-white group-hover:text-blue-400 transition-colors">iwayanpasek.websitewayan.my.id</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
        </div>
    </section>

    <!-- Pricing (Free for now) -->
    <section id="harga" class="py-24 px-6 border-t border-neutral-900">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-14">
                <h2 class="font-display text-3xl md:text-5xl font-bold mb-4">Investasi Masa Depan Bisnis</h2>
                <p class="text-neutral-400">Selama masa beta, seluruh platform dapat digunakan secara gratis.</p>
            </div>
            
            <div class="glass-card rounded-[2rem] p-1 border-blue-500/30 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-b from-blue-500/10 to-transparent"></div>
                
                <div class="bg-[#0A0A0A] rounded-[1.8rem] p-8 md:p-12 relative z-10 border border-neutral-800">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-8">
                        <div>
                            <span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-400 text-xs font-bold uppercase tracking-wider rounded-full mb-3">Promo Eksklusif</span>
                            <h3 class="font-display text-3xl font-bold text-white">Starter Free</h3>
                            <p class="text-neutral-400 mt-2">Dapatkan akses ke fitur premium tanpa biaya bulanan.</p>
                        </div>
                        <div class="text-left md:text-right">
                            <div class="flex items-start md:justify-end">
                                <span class="text-xl font-medium text-neutral-400 mt-1 mr-1">Rp</span>
                                <span class="text-5xl font-display font-bold text-white">0</span>
                                <span class="text-neutral-500 self-end mb-1 ml-1">/bulan</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-px w-full bg-neutral-800 mb-8"></div>
                    
                    <div class="grid sm:grid-cols-2 gap-4 mb-10">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">✓</div>
                            <span class="text-neutral-300">Custom Subdomain Terintegrasi</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">✓</div>
                            <span class="text-neutral-300">AI Chatbot Asisten Super Pintar</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">✓</div>
                            <span class="text-neutral-300">Setup Katalog & Kategori Cepat</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">✓</div>
                            <span class="text-neutral-300">Dashboard Statistik Lengkap</span>
                        </div>
                    </div>
                    
                    <a href="login.php" class="block w-full py-4 text-center bg-white text-black font-semibold rounded-xl hover:bg-neutral-200 transition-colors">
                        Buat Akun Tenant Anda Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-6 border-t border-neutral-900 mt-24">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-neutral-800 text-white flex items-center justify-center font-display font-bold text-xs">P</div>
                <span class="font-display font-bold text-neutral-400">PasekSaaS Engine</span>
            </div>
            <p class="text-neutral-600 text-sm">
                &copy; <?= date('Y') ?> DiKembangkan dengan teknologi AI terkini. Semua hak cipta dilindungi.
            </p>
        </div>
    </footer>

</body>
</html>
