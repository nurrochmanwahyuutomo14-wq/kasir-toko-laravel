<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir Pintar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen font-sans">

    @auth

    <!-- Tombol Hamburger -->
    <button onclick="toggleSidebar()"
        class="fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        ☰
    </button>

    <!-- Overlay -->
    <div id="overlay" onclick="toggleSidebar()"
        class="fixed inset-0 bg-black/40 hidden z-40"></div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="fixed top-0 left-[-260px] w-64 h-full bg-blue-600 text-white z-50 transition-all duration-300 shadow-xl">

        <!-- Header -->
        <div class="p-4 font-black text-xl border-b border-blue-500">
            🛒 TOKO
        </div>

        <!-- Menu -->
        <div class="flex flex-col p-3 space-y-2">

            <a href="/kasir" class="menu-sidebar {{ request()->is('kasir') ? 'active' : '' }}">
                💻 Kasir Utama
            </a>

            <a href="/barang" class="menu-sidebar {{ request()->is('barang') ? 'active' : '' }}">
                📦 Gudang Barang
            </a>

            <a href="/laporan" class="menu-sidebar {{ request()->is('laporan') ? 'active' : '' }}">
                📊 Laporan Penjualan
            </a>

            <a href="{{ route('buku-bon') }}" class="menu-sidebar">
                📓 Buku Bon
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button class="w-full bg-red-500 hover:bg-red-600 py-2 rounded-lg font-bold">
                    🚪 Keluar
                </button>
            </form>

        </div>
    </div>

    @endauth

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-260px";
                overlay.classList.add("hidden");
            } else {
                sidebar.style.left = "0px";
                overlay.classList.remove("hidden");
            }
        }
    </script>
</body>
<style>
    .menu-sidebar {
        padding: 10px 14px;
        border-radius: 10px;
        font-weight: bold;
        display: block;
        transition: 0.2s;
    }

    .menu-sidebar:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .menu-sidebar.active {
        background: rgba(0, 0, 0, 0.3);
    }
</style>

</html>