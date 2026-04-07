<div class="max-w-7xl mx-auto p-6 space-y-6">

    @if (session()->has('message'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm flex items-center gap-2">
        ✅ {{ session('message') }}
    </div>
    @endif

    {{-- ========== HEADER ========== --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-3xl font-black text-gray-800">📓 Buku Bon Toko</h1>
            <p class="text-gray-500 text-sm">Jangan sampai warung hancur karena bon!</p>
        </div>
        <div class="flex flex-wrap gap-3 w-full md:w-auto">
            <button wire:click="bukaModalTambah" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md">
                ➕ Catat Bon Baru
            </button>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Nama Pengutang..."
                class="p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none flex-1 md:flex-none md:w-52">
            <select wire:model.live="filterStatus" class="p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold text-gray-600">
                <option value="">Semua Status</option>
                <option value="belum">🔴 Belum Lunas</option>
                <option value="lunas">🟢 Sudah Lunas</option>
            </select>
            <select wire:model.live="sortOrder" class="p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none font-bold text-gray-600">
                <option value="terbesar">Paling Banyak (Rp)</option>
                <option value="terbaru">Paling Baru</option>
            </select>
        </div>
    </div>

    {{-- ========== DAFTAR BON ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($daftarBon as $bon)
        @php
        $sisa = $bon->total_hutang - ($bon->sudah_dibayar ?? 0);
        $persen = $bon->total_hutang > 0 ? min(100, round(($bon->sudah_dibayar ?? 0) / $bon->total_hutang * 100)) : 0;
        @endphp
        <div wire:key="bon-{{ $bon->id }}"
            class="bg-white p-6 rounded-2xl shadow-sm border-2 transition-all relative overflow-hidden
            {{ $bon->status == 'lunas' ? 'border-transparent hover:border-green-200 opacity-75' : 'border-transparent hover:border-blue-300' }}">

            {{-- Badge Status --}}
            <div class="absolute top-0 right-0 px-3 py-1 rounded-bl-xl font-bold text-xs uppercase
                {{ $bon->status == 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $bon->status == 'lunas' ? '✅ Lunas' : '🔴 Belum' }}
            </div>

            <h3 class="text-xl font-black text-gray-800 mt-2">{{ $bon->nama_pengutang }}</h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase mb-3">
                Terakhir: {{ $bon->tanggal_terakhir_bon ? \Carbon\Carbon::parse($bon->tanggal_terakhir_bon)->format('d M Y') : '-' }}
            </p>

            {{-- Total & Sisa --}}
            <div class="mb-3">
                <p class="text-2xl font-black {{ $bon->status == 'lunas' ? 'text-gray-400 line-through' : 'text-red-600' }}">
                    Rp {{ number_format($bon->total_hutang, 0, ',', '.') }}
                </p>
                @if(($bon->sudah_dibayar ?? 0) > 0 && $bon->status != 'lunas')
                <p class="text-xs text-gray-500 mt-0.5">
                    Sudah bayar: <span class="font-bold text-green-600">Rp {{ number_format($bon->sudah_dibayar, 0, ',', '.') }}</span>
                    · Sisa: <span class="font-bold text-red-500">Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                </p>
                {{-- Progress bar --}}
                <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 bg-green-400 rounded-full transition-all" style="width: {{ $persen }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $persen }}% terbayar</p>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-col gap-2">
                <button wire:click="lihatDetail({{ $bon->id }})"
                    class="w-full bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 font-bold py-2.5 rounded-xl transition-all text-sm">
                    👀 Lihat Rincian
                </button>

                @if($bon->status != 'lunas')
                <button wire:click="bukaBayarSebagian({{ $bon->id }})"
                    class="w-full bg-yellow-50 hover:bg-yellow-400 text-yellow-700 hover:text-yellow-900 font-bold py-2.5 rounded-xl transition-all text-sm border border-yellow-200">
                    💵 Bayar Sebagian
                </button>
                @endif

                @if($bon->status == 'lunas')
                @if($konfirmasiHapusId == $bon->id)
                <div class="flex gap-2">
                    <button wire:click="hapusBon({{ $bon->id }})"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-black py-2.5 rounded-xl transition-all text-sm">
                        🗑️ Ya, Hapus
                    </button>
                    <button wire:click="batalHapus"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2.5 rounded-xl transition-all text-sm">
                        Batal
                    </button>
                </div>
                @else
                <button wire:click="konfirmasiHapus({{ $bon->id }})"
                    class="w-full bg-red-50 hover:bg-red-100 text-red-400 font-bold py-2.5 rounded-xl transition-all text-sm border border-red-100">
                    🗑️ Hapus Data
                </button>
                @endif
                @endif
            </div>
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
    @php
    $sisaModal = $bonTerpilih->total_hutang - ($bonTerpilih->sudah_dibayar ?? 0);
    $itemBelumLunas = $bonTerpilih->details->where('is_lunas', false);
    $totalTerpilih = $bonTerpilih->details->whereIn('id', $selectedDetails)->sum('total_harga');
    @endphp
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center sm:p-4 backdrop-blur-sm">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-2xl overflow-hidden">

            <div class="bg-blue-600 p-5 text-white flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black">Rincian Bon: {{ $bonTerpilih->nama_pengutang }}</h2>
                    @if($bonTerpilih->status != 'lunas')
                    <p class="text-blue-200 text-xs mt-0.5">
                        Sisa hutang: <span class="font-black text-white">Rp {{ number_format($sisaModal, 0, ',', '.') }}</span>
                    </p>
                    @endif
                </div>
                <button wire:click="tutupModal" class="text-white hover:text-red-300 font-bold text-2xl">&times;</button>
            </div>

            {{-- Info pilihan centang --}}
            @if($bonTerpilih->status != 'lunas' && $itemBelumLunas->count() > 0)
            <div class="px-5 pt-4 pb-0">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700 flex flex-wrap items-center justify-between gap-2">
                    <span>☑️ Centang item yang ingin dilunasi, lalu klik <strong>Lunasi Item Terpilih</strong></span>
                    @if(count($selectedDetails) > 0)
                    <span class="font-black text-blue-800 bg-blue-100 px-2 py-1 rounded-lg">
                        {{ count($selectedDetails) }} item · Rp {{ number_format($totalTerpilih, 0, ',', '.') }}
                    </span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Tabel rincian --}}
            <div class="p-5 max-h-[50vh] overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            @if($bonTerpilih->status != 'lunas')
                            <th class="p-3 w-8"></th>
                            @endif
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Nama Barang</th>
                            <th class="p-3 text-center">Qty</th>
                            <th class="p-3 text-right">Total</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bonTerpilih->details as $detail)
                        <tr class="transition {{ $detail->is_lunas ? 'bg-green-50/50 opacity-60' : 'hover:bg-blue-50' }}">
                            @if($bonTerpilih->status != 'lunas')
                            <td class="p-3">
                                @if(!$detail->is_lunas)
                                <input
                                    type="checkbox"
                                    value="{{ $detail->id }}"
                                    wire:model.live="selectedDetails"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer">
                                @endif
                            </td>
                            @endif
                            <td class="p-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($detail->tanggal_bon)->format('d/m/Y') }}</td>
                            <td class="p-3 font-bold text-gray-800 {{ $detail->is_lunas ? 'line-through text-gray-400' : '' }}">
                                {{ $detail->nama_produk }}
                            </td>
                            <td class="p-3 text-center font-bold text-sm">{{ $detail->jumlah }}</td>
                            <td class="p-3 text-right font-bold text-sm {{ $detail->is_lunas ? 'text-gray-400 line-through' : 'text-blue-600' }}">
                                Rp {{ number_format($detail->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                @if($detail->is_lunas)
                                <span class="text-xs bg-green-100 text-green-600 font-bold px-2 py-0.5 rounded-full">✅ Lunas</span>
                                @else
                                <span class="text-xs bg-red-100 text-red-500 font-bold px-2 py-0.5 rounded-full">Belum</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-400">Rincian tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer modal --}}
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex flex-wrap justify-between items-center gap-3">
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">Total Hutang</p>
                    <p class="text-xl font-black {{ $bonTerpilih->status == 'lunas' ? 'text-gray-400 line-through' : 'text-red-600' }}">
                        Rp {{ number_format($bonTerpilih->total_hutang, 0, ',', '.') }}
                    </p>
                    @if(($bonTerpilih->sudah_dibayar ?? 0) > 0)
                    <p class="text-xs text-green-600 font-bold">
                        Sudah dibayar: Rp {{ number_format($bonTerpilih->sudah_dibayar, 0, ',', '.') }}
                    </p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($bonTerpilih->status == 'belum')

                    {{-- Lunasi item terpilih --}}
                    @if(count($selectedDetails) > 0)
                    <button wire:click="lunaskanItemTerpilih"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-xl transition-all text-sm shadow">
                        ☑️ Lunasi Terpilih (Rp {{ number_format($totalTerpilih, 0, ',', '.') }})
                    </button>
                    @endif

                    {{-- Bayar sebagian --}}
                    <button wire:click="bukaBayarSebagian({{ $bonTerpilih->id }})"
                        class="bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-bold py-3 px-4 rounded-xl transition-all text-sm shadow">
                        💵 Bayar Sebagian
                    </button>

                    {{-- Lunas semua --}}
                    <button wire:click="lunasiBon({{ $bonTerpilih->id }})"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-xl transition-all text-sm shadow">
                        ✅ Lunas Semua
                    </button>

                    @else
                    <span class="bg-green-100 text-green-700 px-4 py-3 rounded-xl font-black text-sm">LUNAS 🎉</span>

                    @if($konfirmasiHapusId == $bonTerpilih->id)
                    <button wire:click="hapusBon({{ $bonTerpilih->id }})"
                        class="bg-red-500 hover:bg-red-600 text-white font-black py-3 px-4 rounded-xl transition-all text-sm">
                        🗑️ Ya, Hapus
                    </button>
                    <button wire:click="batalHapus"
                        class="bg-gray-200 text-gray-600 font-bold py-3 px-4 rounded-xl transition-all text-sm">
                        Batal
                    </button>
                    @else
                    <button wire:click="konfirmasiHapus({{ $bonTerpilih->id }})"
                        class="bg-red-50 hover:bg-red-100 text-red-400 font-bold py-3 px-4 rounded-xl border border-red-100 transition-all text-sm">
                        🗑️ Hapus
                    </button>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========== MODAL BAYAR SEBAGIAN ========== --}}
    @if($tampilkanModalBayar && $bonBayarId)
    @php
    $bonBayar = $daftarBon->find($bonBayarId) ?? \App\Models\Debt::find($bonBayarId);
    $sisaBayar = $bonBayar ? $bonBayar->total_hutang - ($bonBayar->sudah_dibayar ?? 0) : 0;
    @endphp
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center sm:p-4 backdrop-blur-sm">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-md overflow-hidden">

            <div class="bg-yellow-400 p-5 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black text-yellow-900">💵 Bayar Sebagian</h2>
                    @if($bonBayar)
                    <p class="text-yellow-800 text-sm font-bold">{{ $bonBayar->nama_pengutang }}</p>
                    @endif
                </div>
                <button wire:click="tutupModalBayar" class="text-yellow-900 hover:text-red-700 font-bold text-2xl">&times;</button>
            </div>

            <div class="p-6 space-y-4">

                {{-- Info hutang --}}
                @if($bonBayar)
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-red-50 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-red-400 uppercase">Total Hutang</p>
                        <p class="text-lg font-black text-red-600">Rp {{ number_format($bonBayar->total_hutang, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-orange-50 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-orange-400 uppercase">Sisa Belum Bayar</p>
                        <p class="text-lg font-black text-orange-600">Rp {{ number_format($sisaBayar, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if(($bonBayar->sudah_dibayar ?? 0) > 0)
                <div class="bg-green-50 p-3 rounded-xl">
                    <p class="text-[10px] font-bold text-green-500 uppercase">Sudah Dibayar Sebelumnya</p>
                    <p class="text-base font-black text-green-600">Rp {{ number_format($bonBayar->sudah_dibayar, 0, ',', '.') }}</p>
                    {{-- Progress bar --}}
                    @php $persenBayar = $bonBayar->total_hutang > 0 ? min(100, round($bonBayar->sudah_dibayar / $bonBayar->total_hutang * 100)) : 0; @endphp
                    <div class="mt-2 h-2 bg-green-100 rounded-full overflow-hidden">
                        <div class="h-2 bg-green-400 rounded-full" style="width: {{ $persenBayar }}%"></div>
                    </div>
                    <p class="text-[10px] text-green-400 mt-0.5">{{ $persenBayar }}% terbayar</p>
                </div>
                @endif
                @endif

                {{-- Input jumlah bayar --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah yang Dibayar (Rp)</label>
                    <input wire:model.live="inputJumlahBayar" type="number" inputmode="numeric"
                        placeholder="Contoh: 20000"
                        class="w-full p-4 text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-yellow-400 outline-none transition-all">
                    @error('inputJumlahBayar')
                    <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Preview sisa setelah bayar --}}
                    @if($inputJumlahBayar > 0 && $bonBayar)
                    @php $sisaSetelah = max(0, $sisaBayar - (int)$inputJumlahBayar); @endphp
                    <div class="mt-2 bg-blue-50 p-3 rounded-xl text-sm">
                        <p class="text-gray-600">
                            Sisa setelah dibayar:
                            <span class="font-black {{ $sisaSetelah == 0 ? 'text-green-600' : 'text-red-500' }}">
                                Rp {{ number_format($sisaSetelah, 0, ',', '.') }}
                            </span>
                            @if($sisaSetelah == 0)
                            <span class="text-green-600 font-bold"> · LUNAS! 🎉</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Tombol nominal cepat --}}
                @if($bonBayar)
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Nominal Cepat</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach([5000, 10000, 20000, 50000, 100000] as $nominal)
                        @if($nominal <= $sisaBayar)
                            <button wire:click="$set('inputJumlahBayar', {{ $nominal }})"
                            class="px-3 py-1.5 bg-gray-100 hover:bg-yellow-100 hover:text-yellow-800 text-gray-600 font-bold rounded-lg text-xs transition-all border border-gray-200">
                            Rp {{ number_format($nominal, 0, ',', '.') }}
                            </button>
                            @endif
                            @endforeach
                            {{-- Tombol lunas sekaligus --}}
                            <button wire:click="$set('inputJumlahBayar', {{ $sisaBayar }})"
                                class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 font-bold rounded-lg text-xs transition-all border border-green-200">
                                Bayar Lunas (Rp {{ number_format($sisaBayar, 0, ',', '.') }})
                            </button>
                    </div>
                </div>
                @endif
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50">
                <button wire:click="prosessBayarSebagian"
                    class="w-full bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-black py-4 rounded-xl shadow-lg transition-all text-lg">
                    💾 CATAT PEMBAYARAN
                </button>
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
                    @error('inputNamaPengutang') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    <p class="text-[10px] text-gray-400 mt-1">*Jika nama sudah ada, bon akan otomatis digabungkan.</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Barang</label>
                    <input wire:model="inputNamaBarang" type="text" placeholder="Contoh: Beras 5kg"
                        class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                    @error('inputNamaBarang') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Qty</label>
                        <input wire:model="inputQty" type="number" inputmode="numeric" min="1"
                            class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                        @error('inputQty') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Harga Satuan (Rp)</label>
                        <input wire:model="inputHarga" type="number" inputmode="numeric" min="0"
                            class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none">
                        @error('inputHarga') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
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