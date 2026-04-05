<div class="space-y-6 p-6 bg-gray-50 min-h-screen">

    {{-- ========== HEADER ========== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">

            {{-- Judul --}}
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                    📊 <span>Laporan Penjualan</span>
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">Pantau omzet dan riwayat transaksi toko.</p>
            </div>

            {{-- Filter Tanggal --}}
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="setFilter('today')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                    {{ $tgl_awal == date('Y-m-d') && $tgl_akhir == date('Y-m-d')
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Hari Ini
                </button>
                <button wire:click="setFilter('week')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                    {{ $tgl_awal == date('Y-m-d', strtotime('-7 days'))
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    1 Minggu
                </button>
                <button wire:click="setFilter('month')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                    {{ $tgl_awal == date('Y-m-d', strtotime('-30 days'))
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    1 Bulan
                </button>

                <div class="h-8 w-px bg-gray-200 mx-1 hidden md:block"></div>

                <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-200">
                    <input type="date" wire:model.live="tgl_awal"
                        class="bg-transparent text-xs font-semibold outline-none text-gray-700">
                    <span class="text-gray-300 text-xs">—</span>
                    <input type="date" wire:model.live="tgl_akhir"
                        class="bg-transparent text-xs font-semibold outline-none text-gray-700">
                </div>

                <a href="/kasir"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all border border-blue-100">
                    ← Kasir
                </a>
            </div>
        </div>
    </div>

    {{-- ========== KARTU STATISTIK ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Omzet Hari Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-green-500">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Omzet Hari Ini</p>
                <span class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center text-lg">💰</span>
            </div>
            <h2 class="text-2xl font-black text-gray-800">
                Rp {{ number_format($todaySales, 0, ',', '.') }}
            </h2>
            <p class="text-xs text-gray-400 mt-1">Periode: {{ date('d M Y') }}</p>
        </div>

        {{-- Total Transaksi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Transaksi</p>
                <span class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center text-lg">🧾</span>
            </div>
            <h2 class="text-2xl font-black text-gray-800">
                {{ $todayTransactions }}
                <span class="text-sm font-normal text-gray-400">Nota</span>
            </h2>
            <p class="text-xs text-gray-400 mt-1">Transaksi tercatat hari ini</p>
        </div>

        {{-- Rata-rata per Transaksi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-purple-500">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Rata-rata / Transaksi</p>
                <span class="w-9 h-9 bg-purple-50 rounded-xl flex items-center justify-center text-lg">📈</span>
            </div>
            <h2 class="text-2xl font-black text-gray-800">
                Rp {{ $todayTransactions > 0 ? number_format($todaySales / $todayTransactions, 0, ',', '.') : '0' }}
            </h2>
            <p class="text-xs text-gray-400 mt-1">Nilai rata-rata belanja</p>
        </div>

    </div>

    {{-- ========== TABEL RIWAYAT TRANSAKSI ========== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header Tabel --}}
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h3 class="font-bold text-gray-800 text-base">Riwayat Transaksi</h3>
                <p class="text-xs text-gray-400">Menampilkan transaksi sesuai filter tanggal yang dipilih.</p>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                {{ $history->total() }} transaksi
            </span>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">No. Invoice</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Metode</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Total Bayar</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($history as $trx)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4">
                            <span class="font-mono font-bold text-blue-600 text-sm">{{ $trx->invoice_number }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $trx->created_at->format('d M Y') }}
                            <span class="text-gray-300 mx-1">·</span>
                            <span class="font-semibold text-gray-600">{{ $trx->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if(isset($trx->payment_method))
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                {{ $trx->payment_method == 'Cash' ? 'bg-green-50 text-green-600' : 'bg-blue-50 text-blue-600' }}">
                                {{ $trx->payment_method == 'Cash' ? '💵 Cash' : '💳 Transfer' }}
                            </span>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-gray-800 text-sm">
                                Rp {{ number_format($trx->total_price, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('print.struk', $trx->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg
                                bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition-all">
                                📄 Struk
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <p class="text-4xl mb-3">📭</p>
                            <p class="text-sm font-semibold text-gray-400">Tidak ada transaksi pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $history->links() }}
        </div>

    </div>

</div>