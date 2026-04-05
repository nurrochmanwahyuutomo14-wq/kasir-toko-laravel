<div x-data="{
    init() {
        this.$refs.searchInput.focus();
    }
}"
    @keydown.window.f10.prevent="$wire.openCheckoutModal()"
    @keydown.window.escape.prevent="$wire.set('search', '')">

    {{-- ✅ Grid wrapper ditambahkan di sini --}}
    <div class="grid grid-cols-12 gap-6">

        {{-- ========== PANEL KIRI: Produk ========== --}}
        <div class="col-span-12 lg:col-span-8">

            @php $warnings = $this->getWarnings(); @endphp

            @if($warnings['stok']->count() > 0 || $warnings['expired']->count() > 0)
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($warnings['stok'] as $p)
                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded-r-xl flex items-center gap-3 shadow-sm">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <p class="text-[10px] font-bold text-red-400 uppercase leading-none">Stok Kritis!</p>
                        <p class="text-xs font-black text-red-700">{{ $p->name }} sisa {{ $p->batches->sum('stock_qty') }}</p>
                    </div>
                </div>
                @endforeach

                @foreach($warnings['expired'] as $e)
                <div class="bg-orange-50 border-l-4 border-orange-500 p-3 rounded-r-xl flex items-center gap-3 shadow-sm">
                    <span class="text-xl">⌛</span>
                    <div>
                        <p class="text-[10px] font-bold text-orange-400 uppercase leading-none">Hampir Expired!</p>
                        <p class="text-xs font-black text-orange-700">{{ $e->product->name }} ({{ date('d/m', strtotime($e->expired_date)) }})</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="bg-white p-4 rounded-xl shadow-sm mb-6 border-l-4 border-blue-500">
                <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Input Scanner / Pencarian</label>
                <input
                    x-ref="searchInput"
                    wire:model.live="search"
                    type="text"
                    placeholder="Scan Barcode atau Ketik Nama Barang... (F10 untuk Bayar)"
                    class="w-full p-4 border-2 border-gray-100 rounded-lg focus:border-blue-500 outline-none transition-all font-mono text-lg bg-gray-50"
                    @blur="if(!$wire.showCheckoutModal) setTimeout(() => $el.focus(), 100)">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($products as $product)
                <div class="bg-white p-5 rounded-xl shadow-sm border-2 border-transparent hover:border-blue-500 transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $product->name }}</h3>
                            <p class="text-[10px] text-blue-500 font-mono">{{ $product->barcode }}</p>
                        </div>
                        <span class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded font-bold uppercase">Stok Aman</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        @foreach($product->units as $unit)
                        <button wire:click="addToCart({{ $product->id }}, {{ $unit->id }})"
                            class="flex justify-between items-center p-3 text-sm bg-gray-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all group">
                            <span class="font-medium">{{ $unit->unit_name }}</span>
                            <span class="font-bold">Rp {{ number_format($unit->price, 0, ',', '.') }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        {{-- ========== AKHIR PANEL KIRI ========== --}}

        {{-- ========== PANEL KANAN: Keranjang ========== --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden sticky top-6 border border-gray-100">
                <div class="bg-blue-600 p-4 text-white font-bold flex justify-between items-center">
                    <span>🛒 RINCIAN BELANJA</span>
                    <span class="bg-blue-500 px-2 py-1 rounded text-xs">{{ count($cart) }} Item</span>
                </div>

                <div class="p-4 min-h-[350px] max-h-[500px] overflow-y-auto">
                    @if(empty($cart))
                    <div class="text-center py-24 text-gray-300">
                        <p class="text-6xl mb-4">📥</p>
                        <p class="text-sm font-medium">Belum ada barang dipilih</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($cart as $key => $item)
                        <div class="flex justify-between items-center border-b border-gray-50 pb-3">
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $item['qty'] }} x {{ $item['unit_name'] }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <p class="font-bold text-blue-600 text-sm">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</p>
                                <button wire:click="removeFromCart('{{ $key }}')" class="text-red-300 hover:text-red-600 transition">✕</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="bg-gray-50 p-6 border-t border-gray-200">
                    @if(session()->has('success') && $lastTransactionId)
                    @php
                    $trx = \App\Models\Transaction::find($lastTransactionId);
                    $details = \App\Models\TransactionDetail::where('transaction_id', $lastTransactionId)->get();

                    $phone = $trx->customer_phone;
                    if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                    }

                    $pesan = "Halo kak, terima kasih sudah berbelanja.\n\n";
                    $pesan .= "No. Invoice: *" . $trx->invoice_number . "*\n";
                    $pesan .= "Tanggal: " . $trx->created_at->format('d/m/Y H:i') . "\n\n";
                    $pesan .= "🛒 *RINCIAN BELANJA:*\n";
                    $pesan .= "----------------------------------------\n";

                    foreach ($details as $item) {
                    $namaBarang = \App\Models\Product::find($item->product_id)->name ?? 'Barang';
                    $namaSatuan = \App\Models\ProductUnit::find($item->product_unit_id)->unit_name ?? '';
                    $pesan .= "▪️ " . $namaBarang . "\n";
                    $pesan .= " " . $item->qty . " " . $namaSatuan . " x Rp " . number_format($item->price, 0, ',', '.') . " = *Rp " . number_format($item->subtotal, 0, ',', '.') . "*\n";
                    }

                    $pesan .= "----------------------------------------\n";
                    $pesan .= "Total Belanja: *Rp " . number_format($trx->total_price, 0, ',', '.') . "*\n";
                    $pesan .= "Bayar (" . $trx->payment_method . "): Rp " . number_format($trx->amount_paid, 0, ',', '.') . "\n";
                    $pesan .= "Kembali: *Rp " . number_format($trx->change_amount, 0, ',', '.') . "*\n\n";
                    $pesan .= "Semoga barangnya bermanfaat. Ditunggu kedatangannya kembali! 🙏";

                    $waLink = "https://wa.me/" . $phone . "?text=" . urlencode($pesan);
                    @endphp

                    <div class="mb-6 space-y-3">
                        <a href="{{ route('print.struk', $lastTransactionId) }}" target="_blank"
                            class="w-full block text-center bg-yellow-400 text-yellow-900 font-black py-4 rounded-xl border-b-4 border-yellow-600 hover:bg-yellow-300 transition-all shadow-lg animate-pulse">
                            🖨️ CETAK STRUK FISIK
                        </a>

                        @if($trx->customer_phone)
                        <a href="{{ $waLink }}" target="_blank"
                            class="w-full block text-center bg-green-500 text-white font-black py-4 rounded-xl border-b-4 border-green-700 hover:bg-green-600 transition-all shadow-lg">
                            💬 KIRIM STRUK KE WA
                        </a>
                        @endif
                    </div>
                    @endif

                    <div class="flex justify-between text-3xl font-black text-gray-800 mb-6">
                        <span class="text-sm self-center text-gray-400">TOTAL</span>
                        <span class="text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <button
                        wire:click="openCheckoutModal"
                        wire:loading.attr="disabled"
                        @if(empty($cart)) disabled @endif
                        class="w-full bg-green-500 text-white font-bold py-5 rounded-2xl hover:bg-green-600 shadow-xl transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        SELESAIKAN BAYAR (F10)
                    </button>
                </div>
            </div>
        </div>
        {{-- ========== AKHIR PANEL KANAN ========== --}}

    </div>
    {{-- ✅ Tutup grid wrapper --}}

    {{-- ✅ Audio & script dipindah ke dalam x-data div --}}
    <audio id="beep-success" src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3"></audio>
    <script>
        window.addEventListener('audio-play', () => {
            document.getElementById('beep-success').play();
        });
    </script>

    {{-- ========== MODAL CHECKOUT ========== --}}
    @if($showCheckoutModal)
    <div class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

            <div class="bg-blue-600 p-5 text-white flex justify-between items-center">
                <h2 class="text-xl font-black tracking-wide">Penyelesaian Transaksi</h2>
                <button wire:click="closeCheckoutModal" class="text-white hover:text-red-300 font-bold text-2xl">&times;</button>
            </div>

            <div class="p-6 space-y-5">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex justify-between items-center">
                    <span class="text-gray-500 font-bold">TOTAL TAGIHAN</span>
                    <span class="text-3xl font-black text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="Cash" class="peer sr-only">
                            <div class="p-3 text-center rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 font-bold transition-all">💵 Tunai (Cash)</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="Transfer" class="peer sr-only">
                            <div class="p-3 text-center rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 font-bold transition-all">💳 Transfer/QRIS</div>
                        </label>
                    </div>
                </div>

                @if($paymentMethod == 'Cash')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Uang Diterima (Rp)</label>
                    <input type="number" wire:model.live.debounce.300ms="amountPaid" placeholder="Ketik nominal uang..." class="w-full p-4 text-xl font-bold text-gray-800 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none transition-all">

                    @if(session()->has('error_payment'))
                    <p class="text-red-500 text-xs font-bold mt-2">{{ session('error_payment') }}</p>
                    @endif
                </div>

                <div class="flex justify-between items-center p-4 rounded-xl {{ $changeAmount < 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700' }}">
                    <span class="font-bold">KEMBALIAN</span>
                    <span class="text-2xl font-black">
                        Rp {{ $changeAmount < 0 ? '0' : number_format($changeAmount, 0, ',', '.') }}
                    </span>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">No. WhatsApp Pelanggan (Opsional)</label>
                    <input type="text" wire:model="customerPhone" placeholder="Contoh: 081234567890" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 outline-none transition-all">
                    <p class="text-[10px] text-gray-400 mt-1">*Diperlukan jika ingin mengirim struk via WA</p>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50">
                <button wire:click="processPayment" wire:loading.attr="disabled" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl hover:bg-blue-700 shadow-lg transition-all text-lg">
                    <span wire:loading.remove>KONFIRMASI PEMBAYARAN</span>
                    <span wire:loading>MEMPROSES...</span>
                </button>
            </div>

        </div>
    </div>
    @endif
    {{-- ========== AKHIR MODAL ========== --}}

</div>
{{-- ✅ Tutup div x-data utama --}}