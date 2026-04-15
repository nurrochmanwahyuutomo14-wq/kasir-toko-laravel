<div style="padding-bottom:24px;">

    {{-- ======== HEADER ======== --}}
    <div style="background:linear-gradient(135deg,#5b21b6,#7c3aed); border-radius:20px; padding:22px 20px; color:white; margin-bottom:20px; box-shadow:0 6px 24px rgba(124,58,237,0.3);">
        <h1 style="font-size:24px; font-weight:900; margin:0 0 8px;">🧾 Rekap Kasir</h1>
        <p style="font-size:15px; opacity:0.8; margin:0;">Ringkasan penjualan & uang di laci</p>

        {{-- Pilih Tanggal --}}
        <div style="margin-top:14px;">
            <label style="font-size:13px; font-weight:800; opacity:0.8; display:block; margin-bottom:6px; text-transform:uppercase;">📅 Tanggal Rekap</label>
            <input type="date" wire:model.live="tanggal"
                style="background:rgba(255,255,255,0.2); border:2px solid rgba(255,255,255,0.3); color:white; padding:10px 14px; border-radius:12px; font-size:17px; font-weight:700; outline:none; min-height:50px; width:100%; max-width:260px; box-sizing:border-box;">
        </div>
    </div>

    {{-- ======== UANG DI LACI (Highlight Utama) ======== --}}
    <div style="background:white; border-radius:20px; padding:24px 20px; margin-bottom:16px; box-shadow:0 4px 20px rgba(0,0,0,0.1); border-top:6px solid #16a34a; text-align:center;">
        <p style="font-size:14px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 8px; letter-spacing:0.5px;">💵 Uang Kas di Laci</p>
        <p style="font-size:40px; font-weight:900; color:#15803d; margin:0; line-height:1.1;">
            Rp {{ number_format($kasLaci, 0, ',', '.') }}
        </p>
        <p style="font-size:14px; color:#6b7280; margin:8px 0 0; font-weight:600;">
            = Rp {{ number_format($totalCash, 0, ',', '.') }} diterima − Rp {{ number_format($totalKembalian, 0, ',', '.') }} kembalian
        </p>
    </div>

    {{-- ======== RINGKASAN STATISTIK ======== --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">

        <div style="background:white; border-radius:16px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid #16a34a;">
            <p style="font-size:12px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 4px;">💵 Tunai</p>
            <p style="font-size:22px; font-weight:900; color:#15803d; margin:0;">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">{{ $jumlahCash }} nota</p>
        </div>

        <div style="background:white; border-radius:16px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid #1d4ed8;">
            <p style="font-size:12px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 4px;">💳 Transfer</p>
            <p style="font-size:22px; font-weight:900; color:#1d4ed8; margin:0;">Rp {{ number_format($totalTransfer, 0, ',', '.') }}</p>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">{{ $jumlahTransfer }} nota</p>
        </div>

        <div style="background:white; border-radius:16px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid #7c3aed;">
            <p style="font-size:12px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 4px;">📊 Omzet Bersih</p>
            <p style="font-size:22px; font-weight:900; color:#7c3aed; margin:0;">Rp {{ number_format($omzetBersih, 0, ',', '.') }}</p>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">{{ $totalNota }} nota total</p>
        </div>

        <div style="background:white; border-radius:16px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid #f59e0b;">
            <p style="font-size:12px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 4px;">🏷️ Total Diskon</p>
            <p style="font-size:22px; font-weight:900; color:#d97706; margin:0;">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</p>
            <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">Diskon diberikan</p>
        </div>

    </div>

    {{-- ======== PRODUK TERLARIS ======== --}}
    @if($produkTerlaris->isNotEmpty())
    <div style="background:white; border-radius:20px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); margin-bottom:16px;">
        <div style="padding:16px 18px; border-bottom:2px solid #f3f4f6; background:#fef3c7;">
            <h3 style="font-size:18px; font-weight:900; color:#92400e; margin:0;">🔥 Produk Terlaris Hari Ini</h3>
        </div>
        @foreach($produkTerlaris as $i => $item)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid #f3f4f6;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="width:32px; height:32px; background:{{ $i === 0 ? '#fbbf24' : '#f3f4f6' }}; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:900; color:{{ $i === 0 ? '#78350f' : '#6b7280' }}; flex-shrink:0;">
                    {{ $i + 1 }}
                </span>
                <div>
                    <p style="font-size:16px; font-weight:800; color:#111827; margin:0;">{{ $item->name }}</p>
                    <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">{{ $item->total_qty }} terjual</p>
                </div>
            </div>
            <p style="font-size:16px; font-weight:900; color:#1d4ed8; margin:0;">Rp {{ number_format($item->total_omzet, 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ======== DAFTAR TRANSAKSI ======== --}}
    <div style="background:white; border-radius:20px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
        <div style="padding:16px 18px; border-bottom:2px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:18px; font-weight:900; color:#111827; margin:0;">📋 Semua Transaksi</h3>
            <span style="background:#eff6ff; color:#1d4ed8; font-size:14px; font-weight:800; padding:4px 12px; border-radius:20px;">{{ $semuaTransaksi->count() }} nota</span>
        </div>

        @if($semuaTransaksi->isEmpty())
        <div style="text-align:center; padding:48px 20px; color:#d1d5db;">
            <p style="font-size:44px; margin:0 0 10px;">📭</p>
            <p style="font-size:16px; font-weight:600;">Tidak ada transaksi pada tanggal ini</p>
        </div>
        @else
        @foreach($semuaTransaksi as $trx)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid #f3f4f6;">
            <div>
                <p style="font-size:14px; font-weight:800; color:#1d4ed8; font-family:monospace; margin:0;">{{ $trx->invoice_number }}</p>
                <p style="font-size:13px; color:#6b7280; margin:3px 0 0; font-weight:600;">
                    {{ $trx->created_at->format('H:i') }} ·
                    <span style="background:{{ $trx->payment_method === 'Cash' ? '#dcfce7' : '#eff6ff' }}; color:{{ $trx->payment_method === 'Cash' ? '#15803d' : '#1d4ed8' }}; padding:1px 7px; border-radius:5px; font-size:12px;">
                        {{ $trx->payment_method === 'Cash' ? '💵 Tunai' : '💳 Transfer' }}
                    </span>
                    @if($trx->discount_amount > 0)
                    · <span style="color:#d97706; font-size:12px;">🏷️ Rp {{ number_format($trx->discount_amount, 0, ',', '.') }}</span>
                    @endif
                </p>
                @if($trx->customer_note)
                <p style="font-size:12px; color:#9ca3af; margin:2px 0 0; font-style:italic;">{{ $trx->customer_note }}</p>
                @endif
            </div>
            <div style="text-align:right;">
                <p style="font-size:17px; font-weight:900; color:#111827; margin:0;">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</p>
                <a href="{{ route('print.struk', $trx->id) }}" target="_blank"
                    style="font-size:13px; font-weight:700; color:#1d4ed8; text-decoration:none;">🖨️ Struk</a>
            </div>
        </div>
        @endforeach
        @endif
    </div>

</div>
