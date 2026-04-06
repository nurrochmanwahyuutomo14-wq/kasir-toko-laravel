<div class="max-w-7xl mx-auto p-6 space-y-6">

    @if (session()->has('message'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm">
        {{ session('message') }}
    </div>
    @endif

    {{-- ========== HEADER ========== --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-3xl font-black text-gray-800">📓 Buku Bon Toko</h1>
            <p class="text-gray-500 text-sm">Jangan sampai warung hancur karena bon!</p>
        </div>

        <div class="flex flex-wrap gap-3 w-full md:w-auto">
            <button wire:click="bukaModalTambah"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md">
                ➕ Catat Bon Baru
            </button>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Cari Nama Pengutang..."
                class="p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none flex-1 md:flex-none md:w-52">
            <select wire:model.live="filterStatus"
                class="p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold text-gray-600">
                <option value="">Semua Status</option>
                <option value="belum">🔴 Belum Lunas</option>
                <option value="lunas">🟢 Sudah Lunas</option>
            </select>
            <select wire:model.live="sortOrder"
                class="p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold text-gray-600">
                <option value="terbesar">Paling Banyak (Rp)</option>
                <option value="terbaru">Paling Baru</option>
            </select>
        </div>
    </div>

    {{-- ========== DAFTAR BON ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($daftarBon as $bon)
        <div class="bg-white p-6 rounded-2xl shadow-sm border-2 border-transparent hover:border-blue-300 transition-all relative overflow-hidden">
            <div class="absolute top-0 right-0 px-4 py-1 rounded-bl-xl font-bold text-xs uppercase
                {{ $bon->status == 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $bon->status }}
            </div>

            <h3 class="text-xl font-black text-gray-800 mt-2">{{ $bon->nama_pengutang }}</h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase mb-2">
                Terakhir ngutang: {{ $bon->tanggal_terakhir_bon ? \Carbon\Carbon::parse($bon->tanggal_terakhir_bon)->format('d M Y') : '-' }}
            </p>

            <p class="text-3xl font-black {{ $bon->status == 'lunas' ? 'text-gray-400 line-through' : 'text-red-600' }}">
                Rp {{ number_format($bon->total_hutang, 0, ',', '.') }}
            </p>

            <button wire:click="lihatDetail({{ $bon->id }})"
                class="mt-4 w-full bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 font-bold py-3 rounded-xl transition-all">
                👀 Lihat Rincian
            </button>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-gray-200">
            <p class="text-5xl mb-4">🙌</p>
            <p class="font-bold text-gray-500 text-lg">Alhamdulillah, tidak ada yang kasbon!</p>
        </div>
        @endforelse
    </div>

    {{-- ========== MODAL DETAIL BON ========== --}}
    @if($tampilkanModal && $bonTerpilih)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center sm:p-4 backdrop-blur-sm">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-2xl overflow-hidden">

            <div class="bg-blue-600 p-5 text-white flex justify-between items-center">
                <h2 class="text-xl font-black tracking-wide">Rincian Bon: {{ $bonTerpilih->nama_pengutang }}</h2>
                <button wire:click="tutupModal" class="text-white hover:text-red-300 font-bold text-2xl">&times;</button>
            </div>

            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-3 rounded-tl-xl">Tanggal</th>
                            <th class="p-3">Nama Barang</th>
                            <th class="p-3 text-center">Qty</th>
                            <th class="p-3 text-right">Harga</th>
                            <th class="p-3 text-right rounded-tr-xl">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bonTerpilih->details as $detail)
                        <tr class="hover:bg-blue-50 transition">
                            <td class="p-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($detail->tanggal_bon)->format('d/m/Y') }}</td>
                            <td class="p-3 font-bold text-gray-800">{{ $detail->nama_produk }}</td>
                            <td class="p-3 text-center font-bold">{{ $detail->jumlah }}</td>
                            <td class="p-3 text-right text-sm">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-bold text-blue-600">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-400">Rincian barang tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                <div class="text-xl font-black text-red-600">
                    Total: Rp {{ number_format($bonTerpilih->total_hutang, 0, ',', '.') }}
                </div>
                @if($bonTerpilih->status == 'belum')
                <button wire:click="lunasiBon({{ $bonTerpilih->id }})"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all">
                    ✅ TANDAI LUNAS
                </button>
                @else
                <span class="bg-green-100 text-green-700 px-6 py-3 rounded-xl font-black">SUDAH LUNAS 🎉</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ========== MODAL TAMBAH BON ========== --}}
    @if($tampilkanModalTambah)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center sm:p-4 backdrop-blur-sm">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md overflow-hidden">

            <div class="bg-blue-600 p-5 text-white flex justify-between items-center">
                <h2 class="text-xl font-black">📝 Catat Bon Manual</h2>
                <button wire:click="tutupModalTambah" class="text-white hover:text-red-300 font-bold text-2xl">&times;</button>
            </div>

            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pengutang</label>
                    <input wire:model="inputNamaPengutang" type="text" placeholder="Contoh: Pak Budi"
                        class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                    <p class="text-[10px] text-gray-400 mt-1">*Jika nama sudah ada, bon akan otomatis digabungkan.</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Barang</label>
                    <input wire:model="inputNamaBarang" type="text" placeholder="Contoh: Beras 5kg"
                        class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Qty</label>
                        <input wire:model="inputQty" type="number" inputmode="numeric" min="1"
                            class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Harga Satuan (Rp)</label>
                        <input wire:model="inputHarga" type="number" inputmode="numeric" min="0"
                            class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50">
                <button wire:click="simpanBonBaru"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-black py-4 rounded-xl shadow-lg transition-all text-lg">
                    💾 SIMPAN BON
                </button>
            </div>
        </div>
    </div>
    @endif

</div>