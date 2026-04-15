<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\Debt;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class RekapKasir extends Component
{
    public $tanggal;

    public function mount()
    {
        $this->tanggal = today()->format('Y-m-d');
    }

    public function render()
    {
        $query = Transaction::whereDate('created_at', $this->tanggal);

        // Total per metode pembayaran
        $totalCash     = (clone $query)->where('payment_method', 'Cash')->sum('amount_paid');
        $totalTransfer = (clone $query)->where('payment_method', 'Transfer')->sum('total_price');
        $jumlahCash    = (clone $query)->where('payment_method', 'Cash')->count();
        $jumlahTransfer= (clone $query)->where('payment_method', 'Transfer')->count();
        $totalDiskon   = (clone $query)->sum('discount_amount');
        $totalKembalian= (clone $query)->sum('change_amount');

        // Grand totals
        $omzetBersih  = (clone $query)->sum('total_price');
        $totalNota    = (clone $query)->count();

        // Kas yang ada di laci (cash diterima - kembalian)
        $kasLaci = $totalCash - $totalKembalian;

        // Bon piutang hari ini
        $bonBaru = Debt::whereDate('created_at', $this->tanggal)->sum('total_hutang');

        // Produk terlaris hari ini
        $produkTerlaris = DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereDate('transaction_details.created_at', $this->tanggal)
            ->select('products.name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_omzet'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Semua transaksi hari ini
        $semuaTransaksi = (clone $query)->orderBy('created_at', 'desc')->get();

        return view('livewire.rekap-kasir', compact(
            'totalCash', 'totalTransfer', 'jumlahCash', 'jumlahTransfer',
            'totalDiskon', 'totalKembalian', 'kasLaci',
            'omzetBersih', 'totalNota',
            'bonBaru', 'produkTerlaris', 'semuaTransaksi'
        ))->layout('layouts.app');
    }
}
