<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class LaporanPenjualan extends Component
{
    use WithPagination;

    // 1. Deklarasi Properti (Ini yang bikin garis merah kalau hilang)
    public $tgl_awal;
    public $tgl_akhir;

    // 2. Fungsi mount() untuk setting awal saat halaman dibuka pertama kali
    public function mount()
    {
        $this->tgl_awal = date('Y-m-d');
        $this->tgl_akhir = date('Y-m-d');
    }

    // 3. Fungsi Filter Cepat
    public function setFilter($range)
    {
        if ($range == 'today') {
            $this->tgl_awal = date('Y-m-d');
            $this->tgl_akhir = date('Y-m-d');
        } elseif ($range == 'week') {
            $this->tgl_awal = date('Y-m-d', strtotime('-7 days'));
            $this->tgl_akhir = date('Y-m-d');
        } elseif ($range == 'month') {
            $this->tgl_awal = date('Y-m-d', strtotime('-30 days'));
            $this->tgl_akhir = date('Y-m-d');
        }

        $this->resetPage(); // Reset ke halaman 1 setiap ganti filter
    }

    public function render()
    {
        // 4. Logika penarikan data berdasarkan filter tanggal
        $query = Transaction::whereBetween('created_at', [
            $this->tgl_awal . ' 00:00:00',
            $this->tgl_akhir . ' 23:59:59'
        ]);

        // Hitung total ringkasan
        $todaySales = $query->sum('total_price');
        $todayTransactions = $query->count();

        // Ambil riwayat transaksi terbaru (history)
        $history = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.laporan-penjualan', [
            'history' => $history,
            'todaySales' => $todaySales,
            'todayTransactions' => $todayTransactions
        ])->layout('layouts.app');
    }

    public function getProdukTerlaris()
    {
        return DB::table('transaction_details')
            ->select('product_id', DB::raw('SUM(qty) as total'))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    public $filterPeriode = 'hari'; // hari | bulan
    public $limit = 5;

    public function getProdukTerlarisProperty()
    {
        $query = DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->select(
                'products.name as nama_produk',
                DB::raw('SUM(transaction_details.qty) as total_terjual'),
                DB::raw('SUM(transaction_details.subtotal) as total_omzet')
            )
            ->groupBy('products.id', 'products.name');

        if ($this->filterPeriode === 'hari') {
            $query->whereDate('transaction_details.created_at', now()->toDateString());
        }

        if ($this->filterPeriode === 'bulan') {
            $query->whereMonth('transaction_details.created_at', now()->month);
        }

        return $query
            ->orderByDesc('total_terjual')
            ->limit((int) $this->limit)
            ->get();
    }
    public function getProdukTidakLakuProperty()
    {
        return DB::table('products')
            ->leftJoin('transaction_details', 'products.id', '=', 'transaction_details.product_id')
            ->select('products.name', DB::raw('COALESCE(SUM(qty),0) as total'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total')
            ->limit(10)
            ->get();
    }
}
