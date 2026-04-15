<div style="padding-bottom:24px;">

    {{-- ======== GREETING HEADER ======== --}}
    <div style="background:linear-gradient(135deg, #1e40af 0%, #1d4ed8 60%, #2563eb 100%); border-radius:20px; padding:24px 22px; color:white; margin-bottom:20px; box-shadow:0 8px 32px rgba(29,78,216,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap;">
            <div>
                <h1 style="font-size:24px; font-weight:900; margin:0 0 4px;">
                    🏪 Kasir Toko
                </h1>
                <p style="font-size:16px; opacity:0.85; margin:0;" id="greeting-date">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            <div style="text-align:right;">
                <p style="font-size:13px; opacity:0.7; margin:0;">Selamat berjualan!</p>
                <p style="font-size:26px; font-weight:900; margin:4px 0 0;" id="jam-dashboard">--:--</p>
            </div>
        </div>
    </div>

    {{-- ======== KARTU STATISTIK UTAMA ======== --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">

        {{-- Omzet Hari Ini --}}
        <div style="background:white; border-radius:18px; padding:18px 16px; box-shadow:0 2px 12px rgba(0,0,0,0.08); border-top:5px solid #16a34a; grid-column: 1 / -1;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:13px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 6px; letter-spacing:0.5px;">💰 Omzet Hari Ini</p>
                    <p style="font-size:32px; font-weight:900; color:#15803d; margin:0; line-height:1.1;">
                        Rp {{ number_format($omzetHariIni, 0, ',', '.') }}
                    </p>
                    <p style="font-size:15px; color:#6b7280; margin:6px 0 0; font-weight:600;">
                        {{ $jumlahTransaksi }} transaksi hari ini
                    </p>
                </div>
                <div style="font-size:52px; opacity:0.2;">💰</div>
            </div>
        </div>

        {{-- Omzet Minggu --}}
        <div style="background:white; border-radius:18px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid #1d4ed8;">
            <p style="font-size:12px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 6px;">📈 Omzet 7 Hari</p>
            <p style="font-size:22px; font-weight:900; color:#1d4ed8; margin:0;">
                Rp {{ number_format($omzetMinggu, 0, ',', '.') }}
            </p>
        </div>

        {{-- Rata-rata --}}
        <div style="background:white; border-radius:18px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid #7c3aed;">
            <p style="font-size:12px; font-weight:800; color:#6b7280; text-transform:uppercase; margin:0 0 6px;">📊 Rata-rata/Nota</p>
            <p style="font-size:22px; font-weight:900; color:#7c3aed; margin:0;">
                Rp {{ $jumlahTransaksi > 0 ? number_format($omzetHariIni / $jumlahTransaksi, 0, ',', '.') : '0' }}
            </p>
        </div>

    </div>

    {{-- ======== ALERT CARDS ======== --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">

        {{-- Stok Kritis --}}
        <a href="/barang"
            style="background:{{ $stokKritis > 0 ? '#fee2e2' : '#f0fdf4' }}; border-radius:16px; padding:16px; border:2px solid {{ $stokKritis > 0 ? '#fca5a5' : '#86efac' }}; text-decoration:none; display:block; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <p style="font-size:32px; margin:0 0 6px;">{{ $stokKritis > 0 ? '⚠️' : '✅' }}</p>
            <p style="font-size:20px; font-weight:900; color:{{ $stokKritis > 0 ? '#991b1b' : '#15803d' }}; margin:0;">{{ $stokKritis }}</p>
            <p style="font-size:13px; font-weight:700; color:{{ $stokKritis > 0 ? '#ef4444' : '#16a34a' }}; margin:4px 0 0;">Stok Kritis</p>
        </a>

        {{-- Hampir Expired --}}
        <a href="/barang"
            style="background:{{ $hampirExpired > 0 ? '#fff7ed' : '#f0fdf4' }}; border-radius:16px; padding:16px; border:2px solid {{ $hampirExpired > 0 ? '#fed7aa' : '#86efac' }}; text-decoration:none; display:block; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <p style="font-size:32px; margin:0 0 6px;">{{ $hampirExpired > 0 ? '⌛' : '✅' }}</p>
            <p style="font-size:20px; font-weight:900; color:{{ $hampirExpired > 0 ? '#9a3412' : '#15803d' }}; margin:0;">{{ $hampirExpired }}</p>
            <p style="font-size:13px; font-weight:700; color:{{ $hampirExpired > 0 ? '#f97316' : '#16a34a' }}; margin:4px 0 0;">Hampir Expired</p>
        </a>

        {{-- Total Bon --}}
        <a href="{{ route('buku-bon') }}"
            style="background:{{ $jumlahBon > 0 ? '#fefce8' : '#f0fdf4' }}; border-radius:16px; padding:16px; border:2px solid {{ $jumlahBon > 0 ? '#fde047' : '#86efac' }}; text-decoration:none; display:block; grid-column: 1 / -1; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:28px; margin:0 0 4px;">📓</p>
                    <p style="font-size:18px; font-weight:900; color:{{ $jumlahBon > 0 ? '#92400e' : '#15803d' }}; margin:0;">{{ $jumlahBon }} bon belum lunas</p>
                    <p style="font-size:15px; font-weight:700; color:{{ $jumlahBon > 0 ? '#f59e0b' : '#16a34a' }}; margin:4px 0 0;">
                        Total: Rp {{ number_format($totalBon, 0, ',', '.') }}
                    </p>
                </div>
                <span style="font-size:28px; font-weight:900; color:#6b7280; opacity:0.4;">→</span>
            </div>
        </a>

    </div>

    {{-- ======== TOMBOL AKSI CEPAT ======== --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">

        <a href="/kasir"
            style="background:linear-gradient(135deg,#16a34a,#15803d); color:white; border-radius:18px; padding:20px 16px; text-align:center; text-decoration:none; display:block; box-shadow:0 4px 20px rgba(22,163,74,0.35); font-weight:900; font-size:18px; min-height:80px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
            <span style="font-size:32px;">💻</span>
            <span>Buka Kasir</span>
        </a>

        <a href="/rekap-kasir"
            style="background:linear-gradient(135deg,#7c3aed,#6d28d9); color:white; border-radius:18px; padding:20px 16px; text-align:center; text-decoration:none; display:block; box-shadow:0 4px 20px rgba(124,58,237,0.35); font-weight:900; font-size:18px; min-height:80px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
            <span style="font-size:32px;">🧾</span>
            <span>Rekap Kasir</span>
        </a>

        <a href="/barang"
            style="background:linear-gradient(135deg,#0891b2,#0e7490); color:white; border-radius:18px; padding:18px 16px; text-align:center; text-decoration:none; display:block; font-weight:900; font-size:16px; min-height:64px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 16px rgba(8,145,178,0.3);">
            <span style="font-size:24px;">📦</span> Gudang Barang
        </a>

        <a href="/laporan"
            style="background:linear-gradient(135deg,#d97706,#b45309); color:white; border-radius:18px; padding:18px 16px; text-align:center; text-decoration:none; display:block; font-weight:900; font-size:16px; min-height:64px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 16px rgba(217,119,6,0.3);">
            <span style="font-size:24px;">📊</span> Laporan
        </a>

    </div>

    {{-- ======== TRANSAKSI TERBARU ======== --}}
    <div style="background:white; border-radius:20px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
        <div style="padding:18px 20px; border-bottom:2px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:18px; font-weight:900; color:#111827; margin:0;">🧾 Transaksi Terbaru</h3>
            <a href="/laporan" style="font-size:14px; font-weight:800; color:#1d4ed8; text-decoration:none;">Lihat Semua →</a>
        </div>

        @if($transaksiTerbaru->isEmpty())
        <div style="text-align:center; padding:48px 20px; color:#d1d5db;">
            <p style="font-size:44px; margin:0 0 10px;">📭</p>
            <p style="font-size:16px; font-weight:600;">Belum ada transaksi hari ini</p>
        </div>
        @else
        <div style="display:flex; flex-direction:column;">
            @foreach($transaksiTerbaru as $trx)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid #f3f4f6;">
                <div>
                    <p style="font-size:15px; font-weight:800; color:#1d4ed8; margin:0; font-family:monospace;">{{ $trx->invoice_number }}</p>
                    <p style="font-size:14px; color:#6b7280; margin:3px 0 0; font-weight:600;">
                        {{ $trx->created_at->format('H:i') }} ·
                        <span style="background:{{ $trx->payment_method === 'Cash' ? '#dcfce7' : '#eff6ff' }}; color:{{ $trx->payment_method === 'Cash' ? '#15803d' : '#1d4ed8' }}; padding:2px 8px; border-radius:6px; font-size:12px;">
                            {{ $trx->payment_method === 'Cash' ? '💵 Tunai' : '💳 Transfer' }}
                        </span>
                    </p>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:17px; font-weight:900; color:#111827; margin:0;">
                        Rp {{ number_format($trx->total_price, 0, ',', '.') }}
                    </p>
                    <a href="{{ route('print.struk', $trx->id) }}" target="_blank"
                        style="font-size:13px; font-weight:700; color:#1d4ed8; text-decoration:none;">
                        🖨️ Struk
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <script>
        function updateJamDashboard() {
            const now = new Date();
            const el = document.getElementById('jam-dashboard');
            if (el) {
                el.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
        }
        updateJamDashboard();
        setInterval(updateJamDashboard, 60000);
    </script>

</div>
