<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

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
}
