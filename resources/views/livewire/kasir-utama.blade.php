<div x-data="{ showMobileCart: false }" @keydown.window.f10.prevent="$wire.openCheckoutModal()">

    {{-- ======== FLASH MESSAGES ======== --}}
    @if(session()->has('success'))
    <div class="flash-success mb-4" style="font-size:18px; display:flex; align-items:center; gap:10px;">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session()->has('warning_stok'))
    <div style="background:#fff3cd; border-left:6px solid #f59e0b; color:#92400e; font-size:17px; font-weight:700; padding:16px 18px; border-radius:14px; margin-bottom:14px;">
        ⚠️ {{ session('warning_stok') }}
    </div>
    @endif
    @if(session()->has('error'))
    <div style="background:#fee2e2; border-left:6px solid #ef4444; color:#991b1b; font-size:17px; font-weight:700; padding:16px 18px; border-radius:14px; margin-bottom:14px;">
        ❌ {{ session('error') }}
    </div>
    @endif

    @php $warnings = $this->getWarnings(); @endphp

    {{-- ======== PERINGATAN STOK/EXPIRED (COMPACT) ======== --}}
    @if($warnings['stok']->count() > 0 || $warnings['expired']->count() > 0)
    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
        @foreach($warnings['stok'] as $p)
        <div wire:key="warn-stok-{{ $p->id }}"
            style="background:#fee2e2; border-left:4px solid #ef4444; padding:10px 14px; border-radius:10px; font-size:14px; font-weight:800; color:#991b1b; display:flex; align-items:center; gap:8px;">
            ⚠️ {{ $p->name }} sisa {{ $p->batches->sum('stock_qty') }}
        </div>
        @endforeach
        @foreach($warnings['expired'] as $e)
        <div wire:key="warn-exp-{{ $e->id }}"
            style="background:#fff3cd; border-left:4px solid #f59e0b; padding:10px 14px; border-radius:10px; font-size:14px; font-weight:800; color:#92400e; display:flex; align-items:center; gap:8px;">
            ⌛ {{ $e->product->name }} exp {{ date('d/m', strtotime($e->expired_date)) }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- ======== LAYOUT UTAMA ======== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        @php
            $kategoriList = ['Semua', 'Umum', 'Makanan', 'Minuman', 'Snack', 'Rokok', 'Sembako'];
        @endphp

        {{-- ===== PANEL KIRI: Produk ===== --}}
        <div class="lg:col-span-8">
            {{-- Search Bar --}}
            <div style="background:white; padding:14px; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,0.08); margin-bottom:12px; border-left:5px solid #1d4ed8;">
                <label style="font-size:13px; font-weight:800; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:8px;">
                    🔍 Scan Barcode / Cari Barang
                </label>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    inputmode="text"
                    autocomplete="off"
                    placeholder="Ketik nama barang atau scan barcode..."
                    style="width:100%; padding:14px 16px; border:2.5px solid #e5e7eb; border-radius:12px; font-size:18px; font-weight:600; background:#f9fafb; outline:none; transition:border-color 0.2s; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#1d4ed8'"
                    onblur="this.style.borderColor='#e5e7eb'">
            </div>

            {{-- Filter Kategori — scroll horizontal --}}
            <div style="overflow-x:auto; white-space:nowrap; margin-bottom:12px; padding-bottom:4px;">
                <div style="display:inline-flex; gap:8px; padding:2px 0;">
                    @foreach($kategoriList as $kat)
                    <button
                        wire:click="setFilterKategori('{{ $kat }}')"
                        style="
                            padding: 10px 20px;
                            border-radius: 50px;
                            font-size: 15px;
                            font-weight: 800;
                            border: 2.5px solid {{ $filterKategori === $kat ? '#1d4ed8' : '#e5e7eb' }};
                            background: {{ $filterKategori === $kat ? '#1d4ed8' : 'white' }};
                            color: {{ $filterKategori === $kat ? 'white' : '#4b5563' }};
                            cursor: pointer;
                            white-space: nowrap;
                            transition: all 0.2s;
                            min-height: 44px;
                        ">
                        {{ $kat }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid Produk — 1 KOLOM di HP, 2 KOLOM di desktop --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                @forelse($products as $product)
                @php
                    $stokTotal = $product->batches->sum('stock_qty');
                    $stokKritis = $stokTotal <= $product->min_stock;
                @endphp
                <div wire:key="product-{{ $product->id }}"
                    style="background:white; border-radius:16px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border:2px solid {{ $stokKritis ? '#fecaca' : '#f3f4f6' }};">

                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div style="flex:1; min-width:0;">
                            <h3 style="font-size:18px; font-weight:800; color:#111827; margin:0; line-height:1.3;">
                                {{ $product->name }}
                            </h3>
                            @if($product->barcode)
                            <span style="font-size:12px; color:#6b7280; font-family:monospace; background:#f3f4f6; padding:2px 8px; border-radius:6px; display:inline-block; margin-top:4px;">
                                {{ $product->barcode }}
                            </span>
                            @endif
                            @if($product->keterangan)
                            <p style="font-size:14px; color:#6b7280; margin:4px 0 0; font-style:italic;">
                                {{ $product->keterangan }}
                            </p>
                            @endif
                        </div>
                        <div style="text-align:right; flex-shrink:0; margin-left:10px;">
                            <span style="background:{{ $stokKritis ? '#fee2e2' : '#dcfce7' }}; color:{{ $stokKritis ? '#991b1b' : '#15803d' }}; font-size:13px; font-weight:800; padding:4px 10px; border-radius:8px; display:inline-block;">
                                {{ $stokKritis ? '⚠️ Kritis' : '✅ Aman' }}
                            </span>
                            <p style="font-size:13px; color:#6b7280; margin:4px 0 0; font-weight:600;">
                                Stok: {{ $stokTotal }}
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Unit — BESAR & jelas --}}
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @foreach($product->units as $unit)
                        <button
                            wire:key="unit-{{ $product->id }}-{{ $unit->id }}"
                            wire:click="addToCart({{ $product->id }}, {{ $unit->id }})"
                            @if($stokTotal <= 0) disabled @endif
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                padding: 14px 16px;
                                background: {{ $stokTotal <= 0 ? '#f3f4f6' : '#eff6ff' }};
                                border: 2px solid {{ $stokTotal <= 0 ? '#e5e7eb' : '#bfdbfe' }};
                                border-radius: 12px;
                                cursor: {{ $stokTotal <= 0 ? 'not-allowed' : 'pointer' }};
                                transition: all 0.15s;
                                width: 100%;
                                text-align: left;
                                min-height: 56px;
                                touch-action: manipulation;
                            "
                            onmousedown="this.style.background='#1d4ed8'; this.style.color='white';"
                            onmouseup="this.style.background='#eff6ff'; this.style.color='';"
                            ontouchstart="this.style.background='#1d4ed8'; this.style.color='white';"
                            ontouchend="this.style.background='#eff6ff'; this.style.color='';">
                            <span style="font-size:16px; font-weight:700; color:{{ $stokTotal <= 0 ? '#9ca3af' : '#1e40af' }};">
                                + {{ $unit->unit_name }}
                            </span>
                            <span style="font-size:18px; font-weight:900; color:{{ $stokTotal <= 0 ? '#9ca3af' : '#1d4ed8' }};">
                                Rp {{ number_format($unit->price, 0, ',', '.') }}
                            </span>
                        </button>
                        @endforeach
                    </div>

                </div>
                @empty
                <div style="text-align:center; padding:48px 20px; background:white; border-radius:16px; grid-column:1/-1;">
                    <p style="font-size:48px; margin:0 0 12px;">🔍</p>
                    <p style="font-size:17px; font-weight:700; color:#9ca3af;">Barang tidak ditemukan</p>
                </div>
                @endforelse

            </div>
        </div>

        {{-- ===== PANEL KANAN: Keranjang (DESKTOP) ===== --}}
        <div class="hidden lg:block lg:col-span-4" style="position:sticky; top:24px; align-self:start;">
            <div style="background:white; border-radius:20px; box-shadow:0 4px 24px rgba(0,0,0,0.12); overflow:hidden; border:1px solid #e5e7eb;">

                {{-- Header Keranjang --}}
                <div style="background:linear-gradient(135deg,#1e40af,#1d4ed8); padding:18px 20px; color:white; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:18px; font-weight:900;">🛒 Keranjang Belanja</span>
                    @if(!empty($cart))
                    <button wire:click="clearCart"
                        style="background:rgba(239,68,68,0.8); color:white; border:none; padding:6px 14px; border-radius:8px; font-size:13px; font-weight:800; cursor:pointer; min-height:36px;">
                        🗑️ Kosongkan
                    </button>
                    @endif
                </div>

                {{-- Daftar Item --}}
                <div style="padding:16px; min-height:200px; max-height:400px; overflow-y:auto;">
                    @if(empty($cart))
                    <div style="text-align:center; padding:48px 20px; color:#d1d5db;">
                        <p style="font-size:52px; margin:0 0 12px;">📥</p>
                        <p style="font-size:16px; font-weight:600;">Belum ada barang dipilih</p>
                    </div>
                    @else
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        @foreach($cart as $key => $item)
                        <div wire:key="cart-{{ $key }}"
                            style="display:flex; align-items:center; gap:10px; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                            <div style="flex:1; min-width:0;">
                                <p style="font-size:16px; font-weight:800; color:#111827; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $item['name'] }}
                                </p>
                                <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">
                                    {{ $item['unit_name'] }} · Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </p>
                            </div>
                            {{-- Kontrol QTY --}}
                            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                                <button wire:click="updateQty('{{ $key }}', -1)"
                                    style="width:36px; height:36px; border-radius:50%; background:#fee2e2; border:none; color:#ef4444; font-size:20px; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1;">−</button>
                                <span style="font-size:18px; font-weight:900; color:#111827; min-width:24px; text-align:center;">{{ $item['qty'] }}</span>
                                <button wire:click="updateQty('{{ $key }}', 1)"
                                    style="width:36px; height:36px; border-radius:50%; background:#dcfce7; border:none; color:#16a34a; font-size:20px; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1;">+</button>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <p style="font-size:16px; font-weight:900; color:#1d4ed8; margin:0;">
                                    Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Footer Total & Bayar --}}
                <div style="background:#f9fafb; padding:18px 20px; border-top:1px solid #e5e7eb;">
                    @if(session()->has('success') && $lastTransactionId)
                    <a href="{{ route('print.struk', $lastTransactionId) }}" target="_blank"
                        style="display:block; text-align:center; background:#fbbf24; color:#78350f; font-weight:900; font-size:16px; padding:14px; border-radius:14px; text-decoration:none; margin-bottom:14px; border-bottom:4px solid #d97706; animation:pulse 1.5s infinite;">
                        🖨️ CETAK STRUK SEKARANG
                    </a>
                    @endif

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <span style="font-size:15px; font-weight:700; color:#6b7280; text-transform:uppercase;">Total</span>
                        <span style="font-size:28px; font-weight:900; color:#1d4ed8;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <button
                        wire:click="openCheckoutModal"
                        @if(empty($cart)) disabled @endif
                        style="
                            width: 100%;
                            background: {{ empty($cart) ? '#d1d5db' : '#16a34a' }};
                            color: {{ empty($cart) ? '#9ca3af' : 'white' }};
                            border: none;
                            padding: 18px;
                            border-radius: 16px;
                            font-size: 18px;
                            font-weight: 900;
                            cursor: {{ empty($cart) ? 'not-allowed' : 'pointer' }};
                            box-shadow: {{ empty($cart) ? 'none' : '0 4px 16px rgba(22,163,74,0.35)' }};
                            transition: all 0.2s;
                            min-height: 58px;
                            letter-spacing: 0.5px;
                        ">
                        💳 SELESAIKAN PEMBAYARAN (F10)
                    </button>
                </div>

            </div>
        </div>

    </div>

    {{-- Audio beep scan --}}
    <audio id="beep-success" src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3"></audio>
    <script>
        window.addEventListener('audio-play', () => {
            document.getElementById('beep-success').play().catch(() => {});
        });
    </script>

    {{-- ========== MODAL CHECKOUT ========== --}}
    @if($showCheckoutModal)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:200; display:flex; align-items:flex-end; justify-content:center; backdrop-filter:blur(4px);"
        class="sm:items-center">

        <div style="background:white; border-radius:24px 24px 0 0; width:100%; max-width:520px; overflow:hidden; max-height:92vh; display:flex; flex-direction:column;"
            class="sm:rounded-3xl sm:max-h-[90vh]">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#1e40af,#1d4ed8); padding:18px 20px; color:white; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                <h2 style="font-size:20px; font-weight:900; margin:0;">💳 Proses Pembayaran</h2>
                <button wire:click="closeCheckoutModal"
                    style="background:rgba(255,255,255,0.2); border:none; color:white; width:40px; height:40px; border-radius:50%; font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-weight:900;">
                    ×
                </button>
            </div>

            {{-- Body Scrollable --}}
            <div style="flex:1; overflow-y:auto; padding:18px 20px;">

                {{-- Total Tagihan --}}
                <div style="background:#eff6ff; padding:18px; border-radius:16px; text-align:center; margin-bottom:18px; border:2px solid #bfdbfe;">
                    <p style="font-size:13px; font-weight:700; color:#6b7280; text-transform:uppercase; margin:0 0 6px;">Total Tagihan</p>
                    <p style="font-size:36px; font-weight:900; color:#1d4ed8; margin:0;">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </p>
                    @if($discountAmount > 0)
                    <p style="font-size:14px; color:#16a34a; font-weight:700; margin:6px 0 0;">
                        ✅ Hemat Rp {{ number_format($discountAmount, 0, ',', '.') }}
                    </p>
                    @endif
                </div>

                {{-- Diskon --}}
                <div style="background:#f9fafb; padding:14px; border-radius:14px; margin-bottom:16px; border:1.5px solid #e5e7eb;">
                    <p style="font-size:14px; font-weight:800; color:#374151; margin:0 0 10px; text-transform:uppercase;">🏷️ Diskon (Opsional)</p>
                    <div style="display:flex; gap:8px; margin-bottom:10px;">
                        <button wire:click="$set('discountType', 'nominal')"
                            style="flex:1; padding:10px; border-radius:10px; border:2.5px solid {{ $discountType === 'nominal' ? '#1d4ed8' : '#e5e7eb' }}; background:{{ $discountType === 'nominal' ? '#eff6ff' : 'white' }}; color:{{ $discountType === 'nominal' ? '#1d4ed8' : '#6b7280' }}; font-size:15px; font-weight:800; cursor:pointer; min-height:48px;">
                            Rp Nominal
                        </button>
                        <button wire:click="$set('discountType', 'persen')"
                            style="flex:1; padding:10px; border-radius:10px; border:2.5px solid {{ $discountType === 'persen' ? '#1d4ed8' : '#e5e7eb' }}; background:{{ $discountType === 'persen' ? '#eff6ff' : 'white' }}; color:{{ $discountType === 'persen' ? '#1d4ed8' : '#6b7280' }}; font-size:15px; font-weight:800; cursor:pointer; min-height:48px;">
                            % Persen
                        </button>
                    </div>
                    <input type="number" inputmode="numeric"
                        wire:model.live.debounce.300ms="discountValue"
                        placeholder="{{ $discountType === 'persen' ? 'Contoh: 10 (untuk 10%)' : 'Contoh: 5000' }}"
                        style="width:100%; padding:12px 14px; border:2px solid #e5e7eb; border-radius:12px; font-size:17px; font-weight:700; outline:none; box-sizing:border-box; min-height:52px;">
                </div>

                {{-- Metode Pembayaran --}}
                <div style="margin-bottom:16px;">
                    <p style="font-size:15px; font-weight:800; color:#374151; margin:0 0 10px; text-transform:uppercase;">Metode Bayar</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <label style="cursor:pointer;">
                            <input type="radio" wire:model.live="paymentMethod" value="Cash" style="display:none;">
                            <div style="padding:14px; text-align:center; border-radius:14px; border:3px solid {{ $paymentMethod === 'Cash' ? '#16a34a' : '#e5e7eb' }}; background:{{ $paymentMethod === 'Cash' ? '#dcfce7' : 'white' }}; font-size:17px; font-weight:800; color:{{ $paymentMethod === 'Cash' ? '#15803d' : '#6b7280' }}; min-height:58px; display:flex; align-items:center; justify-content:center; gap:8px;">
                                💵 Tunai
                            </div>
                        </label>
                        <label style="cursor:pointer;">
                            <input type="radio" wire:model.live="paymentMethod" value="Transfer" style="display:none;">
                            <div style="padding:14px; text-align:center; border-radius:14px; border:3px solid {{ $paymentMethod === 'Transfer' ? '#1d4ed8' : '#e5e7eb' }}; background:{{ $paymentMethod === 'Transfer' ? '#eff6ff' : 'white' }}; font-size:17px; font-weight:800; color:{{ $paymentMethod === 'Transfer' ? '#1d4ed8' : '#6b7280' }}; min-height:58px; display:flex; align-items:center; justify-content:center; gap:8px;">
                                💳 Transfer
                            </div>
                        </label>
                    </div>
                </div>

                @if($paymentMethod == 'Cash')
                {{-- Input Uang Diterima --}}
                <div style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <p style="font-size:15px; font-weight:800; color:#374151; margin:0; text-transform:uppercase;">Uang Diterima (Rp)</p>
                        <button wire:click="resetAmountPaid"
                            style="background:#fee2e2; color:#ef4444; border:none; padding:6px 12px; border-radius:8px; font-size:13px; font-weight:800; cursor:pointer; min-height:36px;">
                            Reset
                        </button>
                    </div>

                    {{-- Display Uang --}}
                    <div style="background:#f0fdf4; border:2.5px solid #86efac; border-radius:14px; padding:14px 18px; text-align:right; margin-bottom:10px;">
                        <p style="font-size:32px; font-weight:900; color:#15803d; margin:0;">
                            Rp {{ $amountPaid ? number_format((float)$amountPaid, 0, ',', '.') : '0' }}
                        </p>
                    </div>

                    {{-- Quick Nominal Buttons — BESAR untuk orang tua --}}
                    <p style="font-size:13px; font-weight:800; color:#9ca3af; text-transform:uppercase; margin:0 0 8px;">Klik nominal uang:</p>
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:10px;">
                        @foreach([1000, 2000, 5000, 10000, 20000, 50000] as $nom)
                        <button wire:click="setAmountPaid({{ $nom }})"
                            style="padding:12px 6px; background:white; border:2px solid #e5e7eb; border-radius:12px; font-size:15px; font-weight:900; color:#374151; cursor:pointer; transition:all 0.15s; min-height:52px; width:100%;"
                            onmousedown="this.style.background='#1d4ed8';this.style.color='white';"
                            onmouseup="this.style.background='white';this.style.color='#374151';"
                            ontouchstart="this.style.background='#1d4ed8';this.style.color='white';"
                            ontouchend="this.style.background='white';this.style.color='#374151';">
                            +{{ number_format($nom, 0, ',', '.') }}
                        </button>
                        @endforeach
                    </div>

                    {{-- Tombol UANG PAS --}}
                    <button wire:click="setAmountPaid('pas')"
                        style="width:100%; padding:14px; background:#1e40af; color:white; border:none; border-radius:14px; font-size:17px; font-weight:900; cursor:pointer; margin-bottom:12px; min-height:54px; letter-spacing:0.5px;">
                        💯 UANG PAS (Total: Rp {{ number_format($total, 0, ',', '.') }})
                    </button>

                    {{-- Input Manual --}}
                    <input type="number" inputmode="numeric"
                        wire:model.live.debounce.300ms="amountPaid"
                        placeholder="Atau ketik nominal manual..."
                        style="width:100%; padding:12px 14px; border:2px solid #e5e7eb; border-radius:12px; font-size:17px; font-weight:700; outline:none; box-sizing:border-box; min-height:52px;">

                    @if(session()->has('error_payment'))
                    <p style="color:#ef4444; font-size:15px; font-weight:800; margin:8px 0 0;">⚠️ {{ session('error_payment') }}</p>
                    @endif
                </div>

                {{-- Kembalian --}}
                <div style="padding:16px 18px; border-radius:14px; background:{{ $changeAmount < 0 ? '#fee2e2' : '#dcfce7' }}; border:2px solid {{ $changeAmount < 0 ? '#fca5a5' : '#86efac' }}; text-align:center;">
                    <p style="font-size:13px; font-weight:800; color:{{ $changeAmount < 0 ? '#991b1b' : '#15803d' }}; text-transform:uppercase; margin:0 0 4px;">Kembalian</p>
                    <p style="font-size:30px; font-weight:900; color:{{ $changeAmount < 0 ? '#ef4444' : '#16a34a' }}; margin:0;">
                        Rp {{ $changeAmount < 0 ? '0' : number_format($changeAmount, 0, ',', '.') }}
                    </p>
                </div>
                @endif

                {{-- Catatan --}}
                <div style="margin-top:14px;">
                    <label style="font-size:14px; font-weight:800; color:#374151; display:block; margin-bottom:6px; text-transform:uppercase;">📝 Catatan (Opsional)</label>
                    <input type="text" wire:model.live="customerNote"
                        placeholder="Contoh: Pelanggan Bu Sari..."
                        style="width:100%; padding:12px 14px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box; min-height:50px;">
                </div>

            </div>

            {{-- Footer Tombol Konfirmasi --}}
            <div style="padding:16px 20px; border-top:2px solid #f3f4f6; background:#f9fafb; flex-shrink:0;">
                <button wire:click="processPayment" wire:loading.attr="disabled"
                    style="width:100%; background:#16a34a; color:white; border:none; padding:18px; border-radius:16px; font-size:20px; font-weight:900; cursor:pointer; box-shadow:0 4px 20px rgba(22,163,74,0.4); min-height:62px; letter-spacing:0.5px;">
                    <span wire:loading.remove wire:target="processPayment">✅ KONFIRMASI BAYAR</span>
                    <span wire:loading wire:target="processPayment">⌛ Memproses...</span>
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- ========== MOBILE CART DRAWER ========== --}}
    <div x-show="showMobileCart"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @click.self="showMobileCart = false"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:150; display:flex; align-items:flex-end; backdrop-filter:blur(4px);"
        class="lg:hidden">

        <div x-show="showMobileCart"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            style="background:white; width:100%; border-radius:24px 24px 0 0; max-height:80vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 -8px 40px rgba(0,0,0,0.2);">

            {{-- Header Drawer --}}
            <div style="padding:16px 18px; border-bottom:2px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:20px; font-weight:900; color:#111827;">🛒 Keranjang</span>
                    <span style="background:#1d4ed8; color:white; font-size:14px; font-weight:900; padding:4px 10px; border-radius:20px;">{{ count($cart) }}</span>
                </div>
                <div style="display:flex; gap:8px;">
                    @if(!empty($cart))
                    <button wire:click="clearCart" @click="showMobileCart = false"
                        style="background:#fee2e2; color:#ef4444; border:none; padding:8px 14px; border-radius:10px; font-size:14px; font-weight:800; cursor:pointer; min-height:40px;">
                        🗑️ Bersihkan
                    </button>
                    @endif
                    <button @click="showMobileCart = false"
                        style="background:#f3f4f6; border:none; width:40px; height:40px; border-radius:50%; font-size:20px; cursor:pointer; font-weight:900; display:flex; align-items:center; justify-content:center;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Item List --}}
            <div style="flex:1; overflow-y:auto; padding:14px 18px;">
                @if(empty($cart))
                <div style="text-align:center; padding:48px 20px; color:#d1d5db;">
                    <p style="font-size:48px; margin:0 0 10px;">📥</p>
                    <p style="font-size:16px; font-weight:600;">Belum ada barang dipilih</p>
                </div>
                @else
                <div style="display:flex; flex-direction:column; gap:12px;">
                    @foreach($cart as $key => $item)
                    <div wire:key="mob-cart-{{ $key }}"
                        style="display:flex; align-items:center; gap:10px; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:17px; font-weight:800; color:#111827; margin:0;">{{ $item['name'] }}</p>
                            <p style="font-size:14px; color:#6b7280; margin:2px 0 0;">{{ $item['unit_name'] }} · Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                            <button wire:click="updateQty('{{ $key }}', -1)"
                                style="width:40px; height:40px; border-radius:50%; background:#fee2e2; border:none; color:#ef4444; font-size:22px; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1;">−</button>
                            <span style="font-size:20px; font-weight:900; min-width:28px; text-align:center;">{{ $item['qty'] }}</span>
                            <button wire:click="updateQty('{{ $key }}', 1)"
                                style="width:40px; height:40px; border-radius:50%; background:#dcfce7; border:none; color:#16a34a; font-size:22px; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1;">+</button>
                        </div>
                        <p style="font-size:17px; font-weight:900; color:#1d4ed8; margin:0; flex-shrink:0;">
                            Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div style="padding:16px 18px; border-top:2px solid #f3f4f6; background:#f9fafb; flex-shrink:0;">
                @if(session()->has('success') && $lastTransactionId)
                <a href="{{ route('print.struk', $lastTransactionId) }}" target="_blank"
                    style="display:block; text-align:center; background:#fbbf24; color:#78350f; font-weight:900; font-size:16px; padding:14px; border-radius:14px; text-decoration:none; margin-bottom:12px;">
                    🖨️ CETAK STRUK
                </a>
                @endif
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <span style="font-size:16px; font-weight:700; color:#6b7280; text-transform:uppercase;">Total</span>
                    <span style="font-size:28px; font-weight:900; color:#1d4ed8;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <button
                    wire:click="openCheckoutModal"
                    @if(empty($cart)) disabled @endif
                    @click="showMobileCart = false"
                    style="width:100%; background:{{ empty($cart) ? '#d1d5db' : '#16a34a' }}; color:white; border:none; padding:18px; border-radius:16px; font-size:20px; font-weight:900; cursor:{{ empty($cart) ? 'not-allowed' : 'pointer' }}; min-height:60px; letter-spacing:0.5px;">
                    💳 BAYAR SEKARANG
                </button>
            </div>
        </div>
    </div>

    {{-- ========== FLOATING BAR BAWAH (MOBILE) ========== --}}
    <div style="position:fixed; bottom:68px; left:0; right:0; z-index:100; padding:0 12px;" class="lg:hidden">
        <button @click="showMobileCart = !showMobileCart"
            style="
                width: 100%;
                background: {{ empty($cart) ? '#94a3b8' : 'linear-gradient(135deg, #1e40af, #1d4ed8)' }};
                color: white;
                border: none;
                border-radius: 18px;
                padding: 0 20px;
                height: 62px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 4px 24px rgba(29,78,216,0.4);
                cursor: pointer;
                font-weight: 900;
                touch-action: manipulation;
            ">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:26px; position:relative;">
                    🛒
                    @if(count($cart) > 0)
                    <span style="position:absolute; top:-6px; right:-6px; background:#ef4444; color:white; font-size:12px; font-weight:900; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center;">{{ count($cart) }}</span>
                    @endif
                </span>
                <div style="text-align:left;">
                    <p style="font-size:12px; opacity:0.8; margin:0;">{{ count($cart) }} item · Tap untuk lihat</p>
                    <p style="font-size:20px; font-weight:900; margin:0;">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <span style="font-size:18px; font-weight:900;">
                {{ empty($cart) ? '—' : 'BAYAR →' }}
            </span>
        </button>
    </div>

    {{-- Spacer bottom --}}
    <div style="height:80px;" class="lg:hidden"></div>

</div>