<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= htmlspecialchars($nama_toko) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Mono:wght@400;500&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --bg:          #080d1a;
            --surface:     #0d1427;
            --surface-2:   #111c33;
            --border:      rgba(255,255,255,.06);
            --border-2:    rgba(255,255,255,.1);
            --text:        #e2eaf5;
            --muted:       #4a5e7a;
            --muted-2:     #2d3f58;
            --amber:       #f5a623;
            --amber-dim:   rgba(245,166,35,.12);
            --amber-glow:  rgba(245,166,35,.22);
            --green:       #22c55e;
            --red:         #f43f5e;
            --sidebar-w:   260px;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { height: 100%; }
        body { margin: 0; font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; -webkit-font-smoothing: antialiased; }
        body::before { content: ''; position: fixed; top: -20vh; left: -10vw; width: 60vw; height: 60vh; background: radial-gradient(ellipse, rgba(245,166,35,.045) 0%, transparent 65%); pointer-events: none; z-index: 0; }
        body::after { content: ''; position: fixed; bottom: -20vh; right: -10vw; width: 55vw; height: 55vh; background: radial-gradient(ellipse, rgba(56,189,248,.03) 0%, transparent 65%); pointer-events: none; z-index: 0; }
        
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--muted-2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--amber); }

        .app-shell { display: flex; min-height: 100vh; position: relative; z-index: 1; }
        
        /* Sidebar */
        #sidebar { width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transition: transform .3s cubic-bezier(.4,0,.2,1); }
        #sidebar.mobile-hidden { transform: translateX(-100%); }
        .sidebar-logo { padding: 1.75rem 1.5rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: .75rem; }
        .logo-mark { width: 36px; height: 36px; border-radius: 10px; background: var(--amber); color: var(--bg); font-family: 'Syne', sans-serif; font-weight: 800; font-size: .95rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .logo-text { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.1rem; letter-spacing: -.02em; color: #fff; }
        .logo-text span { color: var(--amber); }
        .sidebar-section { padding: 1.25rem 1rem 0; }
        .sidebar-label { font-size: .62rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); padding: 0 .5rem; margin-bottom: .5rem; }
        .nav-item { display: flex; align-items: center; gap: .75rem; padding: .65rem .85rem; border-radius: 10px; font-size: .85rem; font-weight: 500; color: var(--muted); text-decoration: none; cursor: pointer; transition: background .15s, color .15s; border: none; background: none; width: 100%; text-align: left; }
        .nav-item:hover { background: var(--surface-2); color: var(--text); }
        .nav-item.active { background: var(--amber-dim); color: var(--amber); border: 1px solid var(--amber-glow); }
        .nav-item i { font-size: 1rem; width: 20px; text-align: center; }
        .nav-badge { margin-left: auto; font-size: .65rem; font-weight: 700; background: var(--amber); color: var(--bg); padding: .1rem .45rem; border-radius: 100px; font-family: 'DM Mono', monospace; }
        .sidebar-footer { margin-top: auto; padding: 1.25rem 1rem; border-top: 1px solid var(--border); }
        .tenant-card { background: var(--surface-2); border: 1px solid var(--border); border-radius: 12px; padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem; }
        .tenant-avatar { width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0; background: var(--amber-dim); border: 1px solid var(--amber-glow); display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 700; color: var(--amber); font-size: .8rem; }
        .tenant-name { font-size: .82rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tenant-id { font-size: .65rem; color: var(--muted); font-family: 'DM Mono', monospace; }

        /* Main Content & Topbar */
        #main-content { margin-left: var(--sidebar-w); flex: 1; min-height: 100vh; padding: 0; display: flex; flex-direction: column; }
        .topbar { position: sticky; top: 0; z-index: 40; background: rgba(8,13,26,.85); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); padding: .9rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        .topbar-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1.15rem; color: #fff; letter-spacing: -.02em; }
        .topbar-sub { font-size: .78rem; color: var(--muted); margin-top: .05rem; }
        .topbar-right { display: flex; align-items: center; gap: .75rem; }
        .topbar-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .9rem; border-radius: 8px; font-size: .8rem; font-weight: 500; text-decoration: none; border: none; cursor: pointer; transition: all .15s; }
        .topbar-btn-ghost { background: var(--surface-2); color: var(--muted); border: 1px solid var(--border); }
        .topbar-btn-ghost:hover { color: var(--text); border-color: var(--border-2); }
        #hamburger { display: none; width: 36px; height: 36px; align-items: center; justify-content: center; border-radius: 8px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text); cursor: pointer; font-size: 1rem; }
        .page-body { padding: 2rem; flex: 1; }

        /* Stat Cards */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; transition: border-color .2s, transform .2s; animation: fadeUp .5s ease both; }
        .stat-card:hover { border-color: var(--border-2); transform: translateY(-2px); }
        .stat-card:nth-child(1) { animation-delay: .05s }
        .stat-card:nth-child(2) { animation-delay: .10s }
        .stat-card:nth-child(3) { animation-delay: .15s }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
        .stat-icon.amber { background: var(--amber-dim); color: var(--amber); border: 1px solid var(--amber-glow); }
        .stat-icon.green { background: rgba(34,197,94,.1); color: var(--green); border: 1px solid rgba(34,197,94,.2); }
        .stat-icon.blue  { background: rgba(56,189,248,.08); color: #38bdf8; border: 1px solid rgba(56,189,248,.15); }
        .stat-label { font-size: .75rem; color: var(--muted); margin-bottom: .25rem; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; }
        .stat-value { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1.4rem; color: #fff; line-height: 1; }
        .stat-sub { font-size: .72rem; color: var(--muted); margin-top: .3rem; font-family: 'DM Mono', monospace; }

        /* Panels */
        .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; animation: fadeUp .5s ease both; }
        .panel-head { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .panel-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: .95rem; color: #fff; letter-spacing: -.01em; display: flex; align-items: center; gap: .6rem; }
        .panel-title i { color: var(--amber); }
        .panel-badge { font-family: 'DM Mono', monospace; font-size: .65rem; font-weight: 500; background: var(--surface-2); border: 1px solid var(--border); color: var(--muted); padding: .2rem .65rem; border-radius: 100px; }
        .panel-body { padding: 1.5rem; }

        /* Forms */
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .75rem; font-weight: 600; color: var(--muted); letter-spacing: .04em; text-transform: uppercase; margin-bottom: .45rem; }
        .form-input { width: 100%; background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 10px; padding: .7rem .9rem; color: var(--text); font-family: 'DM Sans', sans-serif; font-size: .875rem; outline: none; transition: border-color .2s, box-shadow .2s; }
        .form-input:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(245,166,35,.1); }
        .form-input::placeholder { color: var(--muted); }
        textarea.form-input { resize: vertical; min-height: 90px; }
        .file-label { display: flex; align-items: center; gap: .75rem; width: 100%; background: var(--surface-2); border: 1.5px dashed var(--border); border-radius: 10px; padding: .7rem .9rem; cursor: pointer; transition: border-color .2s, background .2s; font-size: .83rem; color: var(--muted); }
        .file-label:hover { border-color: var(--amber); background: var(--amber-dim); color: var(--amber); }
        .file-label i { font-size: 1.1rem; }
        input[type="file"] { display: none; }
        .btn-submit { width: 100%; padding: .8rem; background: var(--amber); color: var(--bg); font-family: 'Syne', sans-serif; font-weight: 700; font-size: .9rem; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem; transition: background .2s, transform .15s, box-shadow .2s; box-shadow: 0 4px 20px rgba(245,166,35,.22); letter-spacing: -.01em; }
        .btn-submit:hover { background: #f0b945; transform: translateY(-1px); box-shadow: 0 8px 28px rgba(245,166,35,.32); }
        .btn-submit:active { transform: translateY(0); }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead tr { background: rgba(255,255,255,.025); border-bottom: 1px solid var(--border); }
        .data-table thead th { padding: .75rem 1.25rem; font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); text-align: left; white-space: nowrap; }
        .data-table thead th:last-child { text-align: center; }
        .data-table tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: rgba(255,255,255,.025); }
        .data-table td { padding: .9rem 1.25rem; vertical-align: middle; }
        .prod-thumb { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; background: var(--surface-2); border: 1px solid var(--border); flex-shrink: 0; }
        .prod-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .prod-name-cell { font-size: .875rem; font-weight: 600; color: var(--text); }
        .prod-id-cell { font-family: 'DM Mono', monospace; font-size: .65rem; color: var(--muted); margin-top: .15rem; }
        .price-cell { font-family: 'Syne', sans-serif; font-weight: 700; color: var(--amber); font-size: .9rem; }
        .price-cell small { font-family: 'DM Sans', sans-serif; font-size: .65rem; font-weight: 400; color: var(--muted); margin-right: .15rem; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer; font-size: .85rem; transition: background .15s, color .15s; text-decoration: none; }
        .action-delete { background: rgba(244,63,94,.1); color: #f43f5e; border: 1px solid rgba(244,63,94,.15); }
        .action-delete:hover { background: rgba(244,63,94,.2); }

        /* Empty & Log UI */
        .empty-state { padding: 3.5rem 2rem; text-align: center; color: var(--muted); }
        .empty-state i { font-size: 2.5rem; opacity: .25; display: block; margin-bottom: .75rem; }
        .empty-state p { font-size: .875rem; }
        .log-list { display: flex; flex-direction: column; gap: .85rem; }
        .log-card { background: var(--surface-2); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: border-color .2s; }
        .log-card:hover { border-color: var(--border-2); }
        .log-card-head { padding: .7rem 1rem; background: rgba(255,255,255,.02); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .log-user-q { font-size: .8rem; font-weight: 500; color: var(--text); display: flex; align-items: flex-start; gap: .5rem; flex: 1; min-width: 0; }
        .log-user-q i { color: var(--amber); flex-shrink: 0; margin-top: .05rem; }
        .log-user-q span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .log-time { font-family: 'DM Mono', monospace; font-size: .65rem; color: var(--muted); white-space: nowrap; flex-shrink: 0; }
        .log-card-body { padding: .85rem 1rem; }
        .log-ai-badge { display: inline-flex; align-items: center; gap: .35rem; font-size: .62rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #38bdf8; margin-bottom: .45rem; }
        .log-ai-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 6px #38bdf8; }
        .log-reply { font-size: .8rem; color: var(--muted); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        /* Tabs System */
        .tabs-nav {
            display: flex; gap: .25rem; margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .tabs-nav::-webkit-scrollbar { display: none; }
        .tab-btn {
            padding: .85rem 1.25rem; font-size: .85rem; font-weight: 600;
            color: var(--muted); background: transparent; border: none;
            border-bottom: 2px solid transparent; cursor: pointer;
            transition: all .2s; white-space: nowrap;
            display: flex; align-items: center; gap: .5rem;
            margin-bottom: -1px;
        }
        .tab-btn:hover { color: var(--text); background: var(--surface-2); border-radius: 8px 8px 0 0; }
        .tab-btn.active { color: var(--amber); border-bottom-color: var(--amber); }
        .tab-content { display: none; animation: fadeUp .4s ease; }
        .tab-content.active { display: block; }

        /* Overlays & Animations */
        #sidebar-overlay { display: none; position: fixed; inset: 0; z-index: 45; background: rgba(8,13,26,.7); backdrop-filter: blur(4px); }
        #sidebar-overlay.show { display: block; }
        @keyframes fadeUp { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); } }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin .7s linear infinite; display: inline-block; }

        /* Responsive */
        @media (max-width: 900px) {
            :root { --sidebar-w: 0px; }
            #sidebar { width: 260px; transform: translateX(-100%); }
            #sidebar.mobile-open { transform: translateX(0); }
            #main-content { margin-left: 0; }
            #hamburger { display: flex; }
            .page-body { padding: 1.25rem; }
            .topbar { padding: .75rem 1.25rem; }
            .stats-row { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

