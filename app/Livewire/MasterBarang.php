<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductBatch;
use Livewire\Component;
use Livewire\WithPagination;

class MasterBarang extends Component
{
    use WithPagination;

    public $search = '';

    // Variabel Form (✅ $keterangan ditambahkan di sini)
    public $productId, $name, $barcode, $category = 'Umum', $keterangan;
    public $price_pcs = 0, $price_renteng = 0, $price_dus = 0;
    public $stock_initial = 0, $expired_date;

    // Kontrol Modal
    public $isOpen = false;
    public $isStockOpen = false;

    protected $rules = [
        'name' => 'required|min:3',
        'price_pcs' => 'required|numeric|min:1',
    ];

    public function create()
    {
        $this->resetInput();
        $this->isOpen = true;
    }

    public function edit($id)
    {
        $product = Product::with('units')->findOrFail($id);
        $this->productId = $id;
        $this->name = $product->name;
        $this->barcode = $product->barcode;
        $this->category = $product->category;

        // ✅ Ambil data keterangan saat tombol edit ditekan
        $this->keterangan = $product->keterangan;

        // Ambil harga dari relasi
        $this->price_pcs = $product->units->where('unit_name', 'Pcs')->first()->price ?? 0;
        $this->price_renteng = $product->units->where('unit_name', 'Renteng')->first()->price ?? 0;
        $this->price_dus = $product->units->where('unit_name', 'Kardus')->first()->price ?? 0;

        $this->isOpen = true;
    }

    public function openStock($id)
    {
        $this->productId = $id;
        $this->stock_initial = 0;
        $this->expired_date = now()->addYear()->format('Y-m-d');
        $this->isStockOpen = true;
    }

    public function store()
    {
        $this->validate();

        // ✅ Masukkan keterangan agar ikut tersimpan ke database
        $product = Product::updateOrCreate(['id' => $this->productId], [
            'name' => $this->name,
            'barcode' => $this->barcode,
            'category' => $this->category,
            'keterangan' => $this->keterangan,
        ]);

        // Simpan/Update Harga Bertingkat
        $units = [
            ['name' => 'Pcs', 'price' => $this->price_pcs, 'conv' => 1],
            ['name' => 'Renteng', 'price' => $this->price_renteng, 'conv' => 10],
            ['name' => 'Kardus', 'price' => $this->price_dus, 'conv' => 100],
        ];

        foreach ($units as $u) {
            ProductUnit::updateOrCreate(
                ['product_id' => $product->id, 'unit_name' => $u['name']],
                ['price' => $u['price'], 'conversion_qty' => $u['conv']]
            );
        }

        // Jika barang baru, tambahkan stok awal
        if (!$this->productId && $this->stock_initial > 0) {
            ProductBatch::create([
                'product_id' => $product->id,
                'stock_qty' => $this->stock_initial,
                'expired_date' => $this->expired_date ?? now()->addYear(),
            ]);
        }

        $this->isOpen = false;
        session()->flash('message', 'Data Barang Berhasil Disimpan!');
    }

    public function addStockOnly()
    {
        ProductBatch::create([
            'product_id' => $this->productId,
            'stock_qty' => $this->stock_initial,
            'expired_date' => $this->expired_date,
        ]);

        $this->isStockOpen = false;
        session()->flash('message', 'Stok Berhasil Ditambahkan!');
    }

    private function resetInput()
    {
        $this->productId = null;
        $this->name = '';
        $this->barcode = '';
        $this->category = 'Umum';

        // ✅ Kosongkan kolom keterangan saat form ditutup/reset
        $this->keterangan = '';

        $this->price_pcs = 0;
        $this->price_renteng = 0;
        $this->price_dus = 0;
        $this->stock_initial = 0;
    }

    public function render()
    {
        $products = Product::with(['units', 'batches'])
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')->paginate(10);

        return view('livewire.master-barang', compact('products'))->layout('layouts.app');
    }
}
