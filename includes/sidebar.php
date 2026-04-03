<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="app-shell">
    
    <aside id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-mark">P</div>
            <span class="logo-text">Pasek<span>SaaS</span></span>
        </div>

        <div class="sidebar-section" style="flex:1">
            <div class="sidebar-label">Menu Utama</div>
            <nav style="display:flex;flex-direction:column;gap:.25rem">
                <button class="nav-item active" onclick="switchTab('tab-produk')">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </button>
                <a href="index.php" target="_blank" class="nav-item">
                    <i class="bi bi-shop"></i> Lihat Website 
                    <i class="bi bi-arrow-up-right" style="margin-left:auto;font-size:.72rem;opacity:.5"></i>
                </a>
            </nav>

            <div class="sidebar-label" style="margin-top:1.5rem">Inventaris</div>
            <nav style="display:flex;flex-direction:column;gap:.25rem">
                <button class="nav-item" onclick="switchTab('tab-produk')">
                    <i class="bi bi-box-seam"></i> Produk 
                    <span class="nav-badge"><?= isset($list_produk) ? count($list_produk) : 0 ?></span>
                </button>
                <button class="nav-item" onclick="switchTab('tab-log')">
                    <i class="bi bi-activity"></i> Log AI 
                    <span class="nav-badge"><?= isset($total_log) ? $total_log : 0 ?></span>
                </button>
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="tenant-card">
                <div class="tenant-avatar"><?= strtoupper(mb_substr($nama_toko, 0, 2)) ?></div>
                <div style="flex:1;min-width:0">
                    <div class="tenant-name"><?= htmlspecialchars($nama_toko) ?></div>
                    <div class="tenant-id">ID #<?= $id_toko ?></div>
                </div>
            </div>
            <a href="logout.php" class="nav-item" style="margin-top:.5rem;color:#f43f5e">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </div>
    </aside>

    <div id="main-content">
        <header class="topbar">
            <div>
                <div class="topbar-title">Manager Dashboard</div>
                <div class="topbar-sub"><?= htmlspecialchars($nama_toko) ?> &middot; <?= date('l, d F Y') ?></div>
            </div>
            <div class="topbar-right">
                <a href="index.php" target="_blank" class="topbar-btn topbar-btn-ghost hidden sm:inline-flex">
                    <i class="bi bi-box-arrow-up-right"></i> Preview Website
                </a>
                <button id="hamburger" onclick="openSidebar()"><i class="bi bi-list"></i></button>
            </div>
        </header>

        <div class="page-body">
