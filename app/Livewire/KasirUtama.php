<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\ProductBatch;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class KasirUtama extends Component
{
    public $search = '';
    public $cart = [];
    public $lastTransactionId = null;

    // --- VARIABEL BARU UNTUK CHECKOUT ---
    public $showCheckoutModal = false;
    public $paymentMethod = 'Cash';
    public $amountPaid = '';
    public $changeAmount = 0;
    public $customerPhone = '';
    // ------------------------------------

    /** * FITUR SCANNER: Fungsi ini otomatis jalan setiap kali input $search berubah
     */
    public function updatedSearch()
    {
        // Cari produk yang barcodenya SAMA PERSIS dengan yang di-scan
        $product = Product::where('barcode', $this->search)->first();

        if ($product) {
            // Jika ketemu, ambil satuan terkecilnya (misal Pcs)
            $unit = $product->units()->where('conversion_qty', 1)->first();

            if ($unit) {
                $this->addToCart($product->id, $unit->id);

                // KOSONGKAN pencarian agar siap scan barang berikutnya
                $this->search = '';

                // Opsional: Kirim sinyal bunyi beep (jika sudah pasang JS-nya)
                $this->dispatch('audio-play');
            }
        }
    }

    public function addToCart($productId, $unitId)
    {
        $this->lastTransactionId = null; // Reset tombol cetak lama

        $product = Product::find($productId);
        $unit = ProductUnit::find($unitId);
        $cartKey = $productId . '-' . $unitId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty']++;
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $productId,
                'unit_id' => $unitId,
                'name' => $product->name,
                'unit_name' => $unit->unit_name,
                'price' => $unit->price,
                'qty' => 1,
                'conversion_qty' => $unit->conversion_qty,
            ];
        }
    }

    public function removeFromCart($key)
    {
        unset($this->cart[$key]);
    }

    // 1. Fungsi untuk membuka Pop-up
    public function openCheckoutModal()
    {
        if (empty($this->cart)) return;
        $this->showCheckoutModal = true;
        $this->amountPaid = ''; // Kosongkan input uang saat pop-up dibuka
        $this->changeAmount = 0;
        $this->paymentMethod = 'Cash';
        $this->customerPhone = '';
    }

    // 2. Fungsi untuk menutup Pop-up
    public function closeCheckoutModal()
    {
        $this->showCheckoutModal = false;
    }

    // 3. Fungsi otomatis menghitung kembalian saat kasir mengetik nominal uang
    public function updatedAmountPaid()
    {
        $total = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        // Pastikan input berupa angka
        $paid = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid);
        $this->changeAmount = $paid - $total;
    }

    // 4. Proses simpan bayar (Di-update)
    public function processPayment()
    {
        $total = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $paid = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid);

        // Validasi: Jika Cash, uang tidak boleh kurang
        if ($this->paymentMethod == 'Cash' && $paid < $total) {
            session()->flash('error_payment', 'Uang yang diterima kurang!');
            return;
        }

        try {
            DB::transaction(function () use ($total, $paid) {
                // Simpan transaksi beserta data pembayaran baru
                $transaction = Transaction::create([
                    'invoice_number' => 'INV-' . now()->format('YmdHis'),
                    'total_price' => $total,
                    'user_id' => 1,
                    'payment_method' => $this->paymentMethod,
                    'amount_paid' => $this->paymentMethod == 'Cash' ? $paid : $total,
                    'change_amount' => $this->changeAmount > 0 ? $this->changeAmount : 0,
                    'customer_phone' => $this->customerPhone,
                ]);

                $this->lastTransactionId = $transaction->id;

                foreach ($this->cart as $item) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'product_unit_id' => $item['unit_id'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['qty'],
                    ]);

                    $totalQtyToReduce = $item['qty'] * $item['conversion_qty'];
                    $batch = ProductBatch::where('product_id', $item['product_id'])
                        ->where('stock_qty', '>', 0)
                        ->orderBy('expired_date', 'asc')
                        ->first();

                    if ($batch) {
                        $batch->decrement('stock_qty', $totalQtyToReduce);
                    }
                }
            });

            // Tutup pop-up dan bersihkan keranjang
            $this->showCheckoutModal = false;
            $this->cart = [];
            session()->flash('success', 'Transaksi Berhasil Disimpan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Product::with('units')->where('name', 'like', '%' . $this->search . '%')->get();
        $total = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        return view('livewire.kasir-utama', compact('products', 'total'))->layout('layouts.app');
    }

    public function getWarnings()
    {
        // 1. Cek Stok Menipis
        $stokMenipis = \App\Models\Product::with(['batches'])
            ->get()
            ->filter(function ($product) {
                return $product->batches->sum('stock_qty') <= $product->min_stock;
            });

        // 2. Cek Expired (30 hari ke depan)
        $hampirExpired = \App\Models\ProductBatch::where('stock_qty', '>', 0)
            ->where('expired_date', '<=', now()->addDays(30))
            ->with('product')
            ->get();

        return [
            'stok' => $stokMenipis,
            'expired' => $hampirExpired
        ];
    }
}
