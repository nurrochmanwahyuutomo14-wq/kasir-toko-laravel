<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Debt;
use App\Models\DebtDetail;

class BukuBon extends Component
{
    public $search       = '';
    public $filterStatus = '';
    public $sortOrder    = 'terbesar';

    // Modal tambah bon
    public $tampilkanModalTambah = false;
    public $inputNamaPengutang   = '';
    public $inputNamaBarang      = '';
    public $inputQty             = 1;
    public $inputHarga           = 0;

    // Modal detail
    public $bonTerpilih    = null;
    public $tampilkanModal = false;

    // Konfirmasi hapus
    public $konfirmasiHapusId = null;

    // ✅ BARU: Bayar sebagian
    public $tampilkanModalBayar = false;
    public $bonBayarId          = null;
    public $inputJumlahBayar    = '';

    // ✅ BARU: Centang item yang mau dilunasi
    public $selectedDetails = [];

    // ============================================================
    // MODAL DETAIL
    // ============================================================

    public function lihatDetail($id)
    {
        $this->bonTerpilih     = Debt::with('details')->find($id);
        $this->tampilkanModal  = true;
        $this->selectedDetails = [];
    }

    public function tutupModal()
    {
        $this->tampilkanModal  = false;
        $this->bonTerpilih     = null;
        $this->selectedDetails = [];
    }

    // ============================================================
    // TAMBAH BON BARU
    // ============================================================

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
        $this->validate([
            'inputNamaPengutang' => 'required',
            'inputNamaBarang'    => 'required',
            'inputQty'           => 'required|numeric|min:1',
            'inputHarga'         => 'required|numeric|min:0',
        ]);

        $totalHarga = $this->inputQty * $this->inputHarga;

        $debt = Debt::firstOrCreate(
            ['nama_pengutang' => $this->inputNamaPengutang, 'status' => 'belum'],
            ['total_hutang' => 0, 'sudah_dibayar' => 0]
        );

        DebtDetail::create([
            'debt_id'      => $debt->id,
            'nama_produk'  => $this->inputNamaBarang,
            'jumlah'       => $this->inputQty,
            'harga_satuan' => $this->inputHarga,
            'total_harga'  => $totalHarga,
            'tanggal_bon'  => now(),
            'is_lunas'     => false,
        ]);

        $debt->total_hutang        += $totalHarga;
        $debt->tanggal_terakhir_bon = now();
        $debt->save();

        $this->tutupModalTambah();
        session()->flash('message', 'Bon baru berhasil dicatat.');
    }

    // ============================================================
    // LUNAS SEMUA SEKALIGUS
    // ============================================================

    public function lunasiBon($id)
    {
        $debt = Debt::with('details')->find($id);
        if (!$debt) return;

        $debt->details()->update(['is_lunas' => true]);
        $debt->update([
            'status'        => 'lunas',
            'sudah_dibayar' => $debt->total_hutang,
        ]);

        if ($this->tampilkanModal && $this->bonTerpilih?->id == $id) {
            $this->bonTerpilih = Debt::with('details')->find($id);
        }

        session()->flash('message', 'Semua bon ' . $debt->nama_pengutang . ' ditandai lunas.');
    }

    // ============================================================
    // ✅ BARU: CENTANG ITEM — lunas per barang
    // ============================================================

    public function lunaskanItemTerpilih()
    {
        if (empty($this->selectedDetails) || !$this->bonTerpilih) return;

        $debt = Debt::with('details')->find($this->bonTerpilih->id);

        foreach ($this->selectedDetails as $detailId) {
            $detail = $debt->details->find($detailId);
            if ($detail && !$detail->is_lunas) {
                $detail->update(['is_lunas' => true]);
                $debt->sudah_dibayar += $detail->total_harga;
            }
        }

        // Auto lunas jika semua item sudah lunas
        if ($debt->details()->where('is_lunas', false)->count() === 0) {
            $debt->status = 'lunas';
        }

        $debt->save();

        $this->bonTerpilih     = Debt::with('details')->find($debt->id);
        $this->selectedDetails = [];

        session()->flash('message', 'Item terpilih berhasil dilunasi.');
    }

    // ============================================================
    // ✅ BARU: BAYAR SEBAGIAN dengan nominal
    // ============================================================

    public function bukaBayarSebagian($id)
    {
        $this->bonBayarId          = $id;
        $this->inputJumlahBayar    = '';
        $this->tampilkanModalBayar = true;
        $this->tampilkanModal      = false;
    }

    public function tutupModalBayar()
    {
        $this->tampilkanModalBayar = false;
        $this->bonBayarId          = null;
        $this->inputJumlahBayar    = '';
    }

    public function prosessBayarSebagian()
    {
        $this->validate([
            'inputJumlahBayar' => 'required|numeric|min:1',
        ], [
            'inputJumlahBayar.required' => 'Masukkan jumlah bayar.',
            'inputJumlahBayar.min'      => 'Jumlah bayar minimal Rp 1.',
        ]);

        $debt = Debt::find($this->bonBayarId);
        if (!$debt) return;

        $sisaHutang  = $debt->total_hutang - $debt->sudah_dibayar;
        $jumlahBayar = min((int) $this->inputJumlahBayar, $sisaHutang);

        $debt->sudah_dibayar += $jumlahBayar;

        if ($debt->sudah_dibayar >= $debt->total_hutang) {
            $debt->status = 'lunas';
            $debt->details()->update(['is_lunas' => true]);
        }

        $debt->save();

        $this->tutupModalBayar();
        session()->flash('message', 'Pembayaran Rp ' . number_format($jumlahBayar, 0, ',', '.') . ' berhasil dicatat.');
    }

    // ============================================================
    // HAPUS BON
    // ============================================================

    public function konfirmasiHapus($id)
    {
        $this->konfirmasiHapusId = $id;
    }
    public function batalHapus()
    {
        $this->konfirmasiHapusId = null;
    }

    public function hapusBon($id)
    {
        $debt = Debt::find($id);
        if ($debt && $debt->status === 'lunas') {
            $debt->details()->delete();
            $debt->delete();
            session()->flash('message', 'Data bon berhasil dihapus.');
        }

        $this->konfirmasiHapusId = null;
        if ($this->tampilkanModal && $this->bonTerpilih?->id == $id) {
            $this->tutupModal();
        }
    }

    // ============================================================
    // RENDER
    // ============================================================

    public function render()
    {
        $query = Debt::query();

        if ($this->search) {
            $query->where('nama_pengutang', 'like', '%' . $this->search . '%');
        }
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        if ($this->sortOrder == 'terbesar') {
            $query->orderBy('total_hutang', 'desc');
        } elseif ($this->sortOrder == 'terbaru') {
            $query->orderBy('tanggal_terakhir_bon', 'desc');
        }

        return view('livewire.buku-bon', [
            'daftarBon' => $query->get(),
        ])->layout('layouts.app');
    }
}
