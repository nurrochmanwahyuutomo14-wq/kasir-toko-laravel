<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Debt;
use App\Models\DebtDetail; // ✅ PERBAIKAN 1: Ini wajib ditambahkan

class BukuBon extends Component
{
    public $search = '';
    public $filterStatus = ''; // lunas / belum
    public $sortOrder = 'terbesar'; // terbesar / terkecil / terbaru
    public $tampilkanModalTambah = false;

    public $inputNamaPengutang = '';
    public $inputNamaBarang = '';
    public $inputQty = 1;
    public $inputHarga = 0;

    // Properti untuk Pop-up
    public $bonTerpilih;
    public $tampilkanModal = false;

    public function lihatDetail($id)
    {
        // Ambil data pengutang beserta rincian barangnya
        $this->bonTerpilih = Debt::with('details')->find($id);
        $this->tampilkanModal = true;
    }

    public function tutupModal()
    {
        $this->tampilkanModal = false;
        $this->bonTerpilih = null;
    }

    public function bukaModalTambah()
    {
        $this->tampilkanModalTambah = true;
    }

    public function tutupModalTambah()
    {
        $this->tampilkanModalTambah = false;
        $this->reset(['inputNamaPengutang', 'inputNamaBarang', 'inputQty', 'inputHarga']);
    }

    public function simpanBonBaru()
    {
        // ✅ PERBAIKAN 2: Tambahkan validasi agar tidak error saat form kosong
        $this->validate([
            'inputNamaPengutang' => 'required',
            'inputNamaBarang' => 'required',
            'inputQty' => 'required|numeric|min:1',
            'inputHarga' => 'required|numeric|min:0',
        ]);

        // 1. Hitung total harga
        $totalHarga = $this->inputQty * $this->inputHarga;

        // 2. Cari nama pengutang (kalau belum ada, buat baru otomatis)
        $debt = Debt::firstOrCreate(
            ['nama_pengutang' => $this->inputNamaPengutang, 'status' => 'belum'],
            ['total_hutang' => 0]
        );

        // 3. Simpan rincian barangnya
        DebtDetail::create([
            'debt_id' => $debt->id,
            'nama_produk' => $this->inputNamaBarang,
            'jumlah' => $this->inputQty,
            'harga_satuan' => $this->inputHarga,
            'total_harga' => $totalHarga,
            'tanggal_bon' => now(),
        ]);

        // 4. Update total hutang orang tersebut
        $debt->total_hutang += $totalHarga;
        $debt->tanggal_terakhir_bon = now();
        $debt->save();

        // 5. Tutup modal & bersihkan form
        $this->tutupModalTambah();
    }

    public function lunasiBon($id)
    {
        Debt::find($id)->update(['status' => 'lunas']);
    }

    public function render()
    {
        $query = Debt::query();

        // Fitur Pencarian Nama
        if ($this->search) {
            $query->where('nama_pengutang', 'like', '%' . $this->search . '%');
        }

        // Fitur Filter Status (Lunas/Belum)
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Fitur Urutkan (Terbesar ke Terkecil atau Tanggal)
        if ($this->sortOrder == 'terbesar') {
            $query->orderBy('total_hutang', 'desc');
        } elseif ($this->sortOrder == 'terbaru') {
            $query->orderBy('tanggal_terakhir_bon', 'desc');
        }

        return view('livewire.buku-bon', [
            'daftarBon' => $query->get()
        ])->layout('layouts.app');
    }
}
