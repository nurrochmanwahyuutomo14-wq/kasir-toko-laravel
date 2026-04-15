<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kasir Toko</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        /*========================================
          GLOBAL: Font besar & kontras tinggi
          untuk orang tua di HP
        ========================================*/
        * {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 17px;
            /* Base font lebih besar */
            background: #f0f4f8;
            color: #111827;
        }

        /* Semua tombol minimal 52px tinggi */
        button,
        .btn,
        a.btn {
            min-height: 52px;
            font-weight: 700;
        }

        /* Input lebih besar & mudah tap */
        input,
        select,
        textarea {
            min-height: 52px;
            font-size: 17px !important;
        }

        /*========================================
          BOTTOM NAVIGATION (Mobile HP)
        ========================================*/
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: #ffffff;
            border-top: 2px solid #e5e7eb;
            display: flex;
            box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.10);
            padding-bottom: env(safe-area-inset-bottom);
        }

        .bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 4px 8px;
            gap: 4px;
            color: #9ca3af;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s, background 0.2s;
            border-top: 3px solid transparent;
            min-height: 64px;
        }

        .bottom-nav a.active {
            color: #1d4ed8;
            border-top-color: #1d4ed8;
            background: #eff6ff;
        }

        .bottom-nav a:active {
            background: #dbeafe;
        }

        .bottom-nav .nav-icon {
            font-size: 24px;
            line-height: 1;
        }

        .bottom-nav .nav-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Spacer agar konten tidak tertutup bottom nav */
        .bottom-nav-spacer {
            height: 80px;
        }

        /*========================================
          SIDEBAR DESKTOP
        ========================================*/
        .desktop-sidebar {
            display: none;
        }

        @media (min-width: 1024px) {
            .desktop-sidebar {
                display: flex;
                flex-direction: column;
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                width: 240px;
                background: linear-gradient(180deg, #1e40af 0%, #1d4ed8 100%);
                color: white;
                z-index: 50;
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            }

            .main-content-desktop {
                margin-left: 240px;
            }

            .bottom-nav {
                display: none;
            }

            .bottom-nav-spacer {
                display: none;
            }
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.2s;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .sidebar-menu-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .sidebar-menu-item.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .sidebar-menu-item .menu-icon {
            font-size: 22px;
            width: 28px;
            text-align: center;
        }

        /*========================================
          FLASH MESSAGE — Besar & jelas
        ========================================*/
        .flash-success {
            background: #dcfce7;
            border-left: 6px solid #16a34a;
            color: #15803d;
            font-size: 18px;
            font-weight: 800;
            padding: 18px 20px;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(22, 163, 74, 0.15);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    {{-- ======== SIDEBAR DESKTOP ======== --}}
    @auth
    <aside class="desktop-sidebar">
        <div style="padding: 24px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.15);">
            <div style="font-size: 24px; font-weight: 900; color: white; letter-spacing: -0.5px;">
                🛒 Kasir Toko
            </div>
            <p style="font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 4px;" id="jam-sidebar">--:--</p>
        </div>

        <nav style="flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 4px;">
            <a href="/" class="sidebar-menu-item {{ request()->is('/') || request()->is('dashboard') || (request()->is('/') && !request()->is('kasir') && !request()->is('barang') && !request()->is('laporan') && !request()->is('buku-bon') && !request()->is('rekap-kasir')) ? 'active' : '' }}">
                <span class="menu-icon">🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="/kasir" class="sidebar-menu-item {{ request()->is('kasir') ? 'active' : '' }}">
                <span class="menu-icon">💻</span>
                <span>Kasir Utama</span>
            </a>
            <a href="/barang" class="sidebar-menu-item {{ request()->is('barang') ? 'active' : '' }}">
                <span class="menu-icon">📦</span>
                <span>Gudang Barang</span>
            </a>
            <a href="/laporan" class="sidebar-menu-item {{ request()->is('laporan') ? 'active' : '' }}">
                <span class="menu-icon">📊</span>
                <span>Laporan</span>
            </a>
            <a href="{{ route('buku-bon') }}" class="sidebar-menu-item {{ request()->is('buku-bon') ? 'active' : '' }}">
                <span class="menu-icon">📓</span>
                <span>Buku Bon</span>
            </a>
            <a href="/rekap-kasir" class="sidebar-menu-item {{ request()->is('rekap-kasir') ? 'active' : '' }}">
                <span class="menu-icon">🧾</span>
                <span>Rekap Kasir</span>
            </a>
        </nav>

        <div style="padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.15);">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    style="width:100%; background:rgba(239,68,68,0.8); color:white; border:none; padding:12px; border-radius:12px; font-weight:800; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; min-height:48px;">
                    🚪 Keluar
                </button>
            </form>
        </div>
    </aside>
    @endauth

    {{-- ======== MAIN CONTENT ======== --}}
    <main class="main-content-desktop" style="min-height: 100vh;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 16px 16px 0;">
            {{ $slot }}
        </div>
        <div class="bottom-nav-spacer"></div>
    </main>

    {{-- ======== BOTTOM NAV (Mobile HP) ======== --}}
    @auth
    <nav class="bottom-nav">
        <a href="/" class="{{ request()->is('/') || (request()->is('dashboard')) ? 'active' : '' }}">
            <span class="nav-icon">🏠</span>
            <span class="nav-label">Beranda</span>
        </a>
        <a href="/kasir" class="{{ request()->is('kasir') ? 'active' : '' }}">
            <span class="nav-icon">💻</span>
            <span class="nav-label">Kasir</span>
        </a>
        <a href="/barang" class="{{ request()->is('barang') ? 'active' : '' }}">
            <span class="nav-icon">📦</span>
            <span class="nav-label">Barang</span>
        </a>
        <a href="{{ route('buku-bon') }}" class="{{ request()->is('buku-bon') ? 'active' : '' }}">
            <span class="nav-icon">📓</span>
            <span class="nav-label">Bon</span>
        </a>
        <a href="/laporan" class="{{ request()->is('laporan') ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Laporan</span>
        </a>
    </nav>
    @endauth

    @livewireScripts

    <script>
        // Jam real-time di sidebar
        function updateJam() {
            const now = new Date();
            const jam = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
            const tgl = now.toLocaleDateString('id-ID', {
                weekday: 'short',
                day: 'numeric',
                month: 'short'
            });
            const el = document.getElementById('jam-sidebar');
            if (el) el.textContent = tgl + ' · ' + jam;
        }
        updateJam();
        setInterval(updateJam, 60000);
    </script>
</body>

</html>