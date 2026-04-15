<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Debt;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function render()
    {
        // Omzet hari ini
        $omzetHariIni = Transaction::whereDate('created_at', today())->sum('total_price');
        $jumlahTransaksi = Transaction::whereDate('created_at', today())->count();

        // Stok kritis
        $stokKritis = Product::with('batches')->get()->filter(function ($p) {
            return $p->batches->sum('stock_qty') <= $p->min_stock;
        })->count();

        // Produk hampir expired (30 hari)
        $hampirExpired = ProductBatch::where('stock_qty', '>', 0)
            ->where('expired_date', '<=', now()->addDays(30))
            ->count();

        // Bon belum lunas
        $totalBon = Debt::where('status', 'belum')->sum(DB::raw('total_hutang - sudah_dibayar'));
        $jumlahBon = Debt::where('status', 'belum')->count();

        // 5 transaksi terakhir
        $transaksiTerbaru = Transaction::with('details')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Omzet minggu ini (7 hari)
        $omzetMinggu = Transaction::whereBetween('created_at', [
            now()->subDays(6)->startOfDay(),
            now()->endOfDay(),
        ])->sum('total_price');

        return view('livewire.dashboard', compact(
            'omzetHariIni',
            'jumlahTransaksi',
            'stokKritis',
            'hampirExpired',
            'totalBon',
            'jumlahBon',
            'transaksiTerbaru',
            'omzetMinggu'
        ))->layout('layouts.app');
    }
}
