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
    <nav class="bg-blue-600 shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                <div class="flex">
                    <div class="flex-shrink-0 flex items-center text-white font-black text-xl tracking-wider pr-6 border-r border-blue-500">
                        🛒 TOKO
                    </div>

                    <div class="hidden sm:ml-6 sm:flex sm:space-x-2 items-center">
                        <a href="/kasir" class="{{ request()->is('kasir') ? 'bg-blue-800 text-white shadow-inner' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }} px-4 py-2 rounded-xl text-sm font-bold transition-all">
                            💻 Kasir Utama
                        </a>

                        <a href="/barang" class="{{ request()->is('barang') ? 'bg-blue-800 text-white shadow-inner' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }} px-4 py-2 rounded-xl text-sm font-bold transition-all">
                            📦 Gudang Barang
                        </a>

                        <a href="/laporan" class="{{ request()->is('laporan') ? 'bg-blue-800 text-white shadow-inner' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }} px-4 py-2 rounded-xl text-sm font-bold transition-all">
                            📊 Laporan Penjualan
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-3">

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg border border-red-700 transition-all flex items-center gap-2 transform hover:scale-105">
                            🚪 Keluar
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </nav>
    @endauth

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>