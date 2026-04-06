<?php
// ═══════════════════════════════════════════════════
//  SaaS LANDING PAGE — Marketing Website (Vercel/Linear Aesthetic)
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
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Outfit', 'sans-serif'] },
                    colors: {
                        neutral: {
                            50: '#fafafa', 100: '#f5f5f5', 200: '#e5e5e5', 300: '#d4d4d4', 400: '#a3a3a3',
                            500: '#737373', 600: '#525252', 700: '#404040', 800: '#262626', 900: '#171717',
                            950: '#0a0a0a', 990: '#050505'
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body { 
            background-color: #050505; 
            color: #ededed; 
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* Cosmics Glows */
        .cosmic-glow-primary {
            position: absolute;
            width: 800px; height: 800px;
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, rgba(0,0,0,0) 60%);
            top: -200px; left: 50%; transform: translateX(-50%);
            border-radius: 50%; z-index: -1; filter: blur(60px);
        }
        .cosmic-glow-secondary {
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(45,212,191,0.07) 0%, rgba(0,0,0,0) 60%);
            bottom: -100px; right: -100px;
            border-radius: 50%; z-index: -1; filter: blur(60px);
        }

        /* Glassmorphism & Borders */
        .glass-nav {
            background: rgba(5, 5, 5, 0.7);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .bento-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .bento-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.1);
        }
        /* Subtle Inner Glow on Hover */
        .bento-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            opacity: 0; transition: opacity 0.4s;
        }
        .bento-card:hover::before { opacity: 1; }

        /* Typography Gradients */
        .text-gradient-hero {
            background: linear-gradient(to right bottom, #ffffff 30%, #a3a3a3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .text-gradient-accent {
            background: linear-gradient(135deg, #818cf8 0%, #2dd4bf 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Scroll Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #050505; }
        ::-webkit-scrollbar-thumb { background: #262626; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #404040; }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500/30 selection:text-white">

    <div class="cosmic-glow-primary"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 cursor-pointer">
                <div class="w-7 h-7 rounded-md bg-white text-black flex items-center justify-center font-display font-bold text-sm">P</div>
                <span class="font-display font-semibold text-lg tracking-tight text-white">PasekSaaS</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-400">
                <a href="#fitur" class="hover:text-white transition-colors">Fitur</a>
                <a href="#arsitektur" class="hover:text-white transition-colors">Arsitektur</a>
                <a href="#harga" class="hover:text-white transition-colors">Harga</a>
            </div>
            
            <div class="flex items-center gap-5">
                <a href="login.php" class="text-sm font-medium text-neutral-400 hover:text-white transition-colors hidden sm:block">Masuk</a>
                <a href="login.php" class="px-4 py-2 bg-white text-black text-sm font-medium rounded-md hover:bg-neutral-200 transition-colors shadow-[0_0_15px_rgba(255,255,255,0.15)]">Buka Toko Gratis</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-40 pb-16 px-6 relative flex flex-col items-center text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bento-card text-xs font-medium text-neutral-300 mb-8 reveal shadow-lg backdrop-blur-xl border border-indigo-500/30">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
            </span>
            <span class="text-indigo-300 font-semibold text-[13px]">Akses Beta Terbuka: Buat Toko 100% Gratis</span>
        </div>
        
        <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight mb-8 leading-[1.05] max-w-4xl reveal delay-100">
            Membangun Etalase Masa Depan dengan <span class="text-gradient-accent">Kecerdasan Buatan</span>
        </h1>
        
        <p class="text-lg md:text-xl text-neutral-400 mb-10 max-w-2xl mx-auto leading-relaxed reveal delay-200">
            Infrastruktur yang mengubah cara Anda melayani pelanggan. Bangun toko Anda tanpa coding, diotomatisasi penuh oleh interaksi Asisten AI berkecepatan instan.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 reveal delay-200">
            <a href="login.php" class="w-full sm:w-auto px-6 py-3 bg-white text-black font-medium tracking-wide rounded-md hover:bg-neutral-200 transition-colors text-base flex items-center justify-center gap-2">
                Mulai Sekarang (Gratis)
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
            <a href="https://iwayanpasek.websitewayan.my.id" target="_blank" class="w-full sm:w-auto px-6 py-3 bg-neutral-900 border border-neutral-800 text-white font-medium rounded-md hover:bg-neutral-800 transition-colors text-base flex items-center justify-center gap-2 group">
                Lihat Demo AI
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="group-hover:translate-x-1 transition-transform"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
        </div>
    </section>

    <!-- UI Mockup (Interactive CSS) -->
    <section class="max-w-6xl mx-auto px-6 pb-32 reveal">
        <div class="rounded-xl border border-neutral-800 bg-[#0A0A0A]/80 p-2 shadow-2xl relative overflow-hidden backdrop-blur-3xl transform perspective-1000">
            <div class="absolute inset-0 bg-gradient-to-t from-indigo-500/5 to-transparent pointer-events-none"></div>
            
            <div class="rounded-lg border border-neutral-800/80 bg-[#050505] flex flex-col md:flex-row w-full aspect-[16/10] md:aspect-[21/9] overflow-hidden">
                <!-- Sidebar Mockup -->
                <div class="w-full md:w-56 border-r border-neutral-800/50 p-4 hidden md:flex flex-col gap-4">
                    <div class="h-6 w-24 bg-neutral-800/50 rounded animate-pulse"></div>
                    <div class="space-y-3 mt-6">
                        <div class="h-4 w-full bg-indigo-500/20 rounded border border-indigo-500/20"></div>
                        <div class="h-4 w-3/4 bg-neutral-900 rounded"></div>
                        <div class="h-4 w-5/6 bg-neutral-900 rounded"></div>
                    </div>
                </div>
                
                <!-- Main Content Mockup -->
                <div class="flex-1 p-6 flex flex-col gap-6">
                    <div class="flex justify-between items-center border-b border-neutral-800/50 pb-4">
                        <div class="h-6 w-32 bg-neutral-800/80 rounded"></div>
                        <div class="h-8 w-24 bg-white/10 rounded-full"></div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="h-24 bg-neutral-900 border border-neutral-800/50 rounded-lg p-4 flex flex-col justify-end">
                            <div class="h-3 w-16 bg-emerald-500/20 rounded mb-2"></div>
                            <div class="h-6 w-20 bg-neutral-800 rounded"></div>
                        </div>
                        <div class="h-24 bg-neutral-900 border border-neutral-800/50 rounded-lg p-4 flex flex-col justify-end">
                            <div class="h-3 w-16 bg-blue-500/20 rounded mb-2"></div>
                            <div class="h-6 w-20 bg-neutral-800 rounded"></div>
                        </div>
                        <div class="h-24 bg-neutral-900 border border-neutral-800/50 rounded-lg p-4 flex flex-col justify-end">
                            <div class="h-3 w-16 bg-purple-500/20 rounded mb-2"></div>
                            <div class="h-6 w-20 bg-neutral-800 rounded"></div>
                        </div>
                    </div>
                    
                    <!-- Chat AI Simulation -->
                    <div class="flex-1 border border-neutral-800/50 bg-[#0A0A0A] rounded-lg mt-2 p-4 flex flex-col gap-4 relative overflow-hidden">
                        <div class="flex gap-3 items-end">
                            <div class="w-8 h-8 rounded-full bg-neutral-800 shrink-0"></div>
                            <div class="bg-neutral-800/60 p-3 rounded-tr-lg rounded-br-lg rounded-bl-lg text-xs text-neutral-400 w-2/3">"Berapa harga paket audit?"</div>
                        </div>
                        <div class="flex gap-3 items-end flex-row-reverse">
                            <div class="w-8 h-8 rounded-full bg-indigo-500/20 border border-indigo-500/30 shrink-0 flex items-center justify-center">
                                <span class="w-3 h-3 bg-indigo-400 rounded-full animate-pulse"></span>
                            </div>
                            <div class="bg-indigo-500/10 border border-indigo-500/20 p-3 rounded-tl-lg rounded-bl-lg rounded-br-lg text-xs text-indigo-200/80 w-3/4">"Halo! Paket audit komprehensif kami senilai Rp 750.000. Tersedia jadwal minggu ini, ingin saya buatkan order?"</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section id="fitur" class="py-24 px-6 relative">
        <div class="cosmic-glow-secondary"></div>
        <div class="max-w-6xl mx-auto">
            <div class="mb-16 reveal">
                <h2 class="font-display text-4xl md:text-5xl font-bold mb-4 tracking-tight">Kinerja Tinggi.<br/><span class="text-neutral-500">Nirgesekan.</span></h2>
                <p class="text-neutral-400 text-lg max-w-xl">Kami telah menghapus batas antara manajemen produk dan kecerdasan buatan.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 auto-rows-[minmax(200px,auto)]">
                
                <!-- Bento 1: AI (Span 2) -->
                <div class="bento-card rounded-2xl p-8 md:col-span-2 md:row-span-2 flex flex-col reveal delay-100 relative group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl group-hover:bg-indigo-500/20 transition-all"></div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mb-auto">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg>
                    </div>
                    <div class="mt-12 relative z-10">
                        <h3 class="text-2xl font-bold mb-3 text-white">Otomasi Otak AI</h3>
                        <p class="text-neutral-400 text-sm leading-relaxed">
                            Latih model persona AI spesifik untuk toko Anda. Dari penawaran harga hingga panduan navigasi produk, serahkan sepenuhnya percakapan pelanggan 24/7 kepada asisten latensi sangat rendah.
                        </p>
                    </div>
                </div>

                <!-- Bento 2: Subdomain -->
                <div class="bento-card rounded-2xl p-8 md:col-span-2 flex flex-col reveal delay-200 group">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">Alamat Eksklusif</h3>
                        <div class="px-2 py-1 bg-neutral-800/50 border border-neutral-700/50 rounded text-xs text-neutral-400 font-mono">*.websitewayan.my.id</div>
                    </div>
                    <p class="text-neutral-400 text-sm mt-auto">Membangun kredibilitas merek yang kokoh di mesin pencari sejak hari pertama.</p>
                </div>

                <!-- Bento 3: Dark Mode UI -->
                <div class="bento-card rounded-2xl p-8 flex flex-col justify-end reveal delay-100 overflow-hidden relative group">
                    <div class="absolute inset-0 bg-gradient-to-tr from-[#050505] to-neutral-800/20 z-0"></div>
                    <div class="relative z-10">
                        <h3 class="text-lg font-bold text-white mb-2">Desain Premium</h3>
                        <p class="text-neutral-500 text-sm">Glassmorphism UI.</p>
                    </div>
                </div>

                <!-- Bento 4: Zero Trust Security -->
                <div class="bento-card rounded-2xl p-8 flex flex-col justify-end reveal delay-200">
                    <h3 class="text-lg font-bold text-white mb-2">Keamanan Skala Enterprise</h3>
                    <p class="text-neutral-500 text-sm">Insulasi data antar tenant & proteksi XSS berlapis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing / CTA -->
    <section id="harga" class="py-32 px-6 relative border-t border-white/5">
        <div class="max-w-4xl mx-auto text-center reveal">
            <h2 class="font-display text-4xl md:text-5xl font-bold mb-6">Mulai Era Baru Bisnis Anda</h2>
            <p class="text-neutral-400 text-lg mb-10 max-w-2xl mx-auto">Selama tahap *Early Access*, seluruh fasilitas *Cloud* PasekSaaS dan *Artificial Intelligence* dapat dikonsumsi 100% gratis.</p>
            <a href="login.php" class="px-8 py-4 bg-white text-black font-semibold rounded-md hover:bg-neutral-200 transition-colors shadow-lg shadow-white/10 text-lg inline-flex items-center gap-2">
                Deploy Halaman Anda
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 px-6 border-t border-white/5 bg-[#050505]">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-white text-black flex items-center justify-center font-display font-bold text-xs">P</div>
                <span class="font-display font-semibold text-sm text-neutral-300">PasekSaaS © 2026</span>
            </div>
            <div class="text-xs text-neutral-500">
                Arsitektur Masa Depan. Dibangun untuk Skalabilitas.
            </div>
        </div>
    </footer>

    <!-- Scroll Reveal Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.15
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Optional: Stop observing once revealed
                        // observer.unobserve(entry.target); 
                    }
                });
            }, observerOptions);

            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach(reveal => {
                observer.observe(reveal);
            });
        });
    </script>
</body>
</html>
