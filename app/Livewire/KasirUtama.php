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

    public $showCheckoutModal = false;
    public $paymentMethod = 'Cash';
    public $amountPaid = '';
    public $changeAmount = 0;


    /**
     * FITUR SCANNER: Otomatis jalan setiap kali $search berubah.
     */
    public function updatedSearch()
    {
        // ✅ FIX BUG 1: Jangan proses apapun jika search kosong atau hanya spasi.
        // Ini mencegah query where('barcode', '') yang bisa match produk tanpa barcode (seperti Coki).
        if (empty(trim($this->search))) {
            return;
        }

        // Cari produk yang barcodenya SAMA PERSIS dengan yang di-scan
        // ✅ FIX: Tambahkan ->whereNotNull('barcode') agar produk tanpa barcode tidak ikut dicocokkan
        $product = Product::whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->where('barcode', $this->search)
            ->first();

        if ($product) {
            $unit = $product->units()->where('conversion_qty', 1)->first();

            if ($unit) {
                $this->addToCart($product->id, $unit->id);

                // ✅ FIX BUG 2: Gunakan $this->reset() khusus untuk search
                // agar tidak memicu updatedSearch() lagi secara rekursif.
                $this->resetSearch();

                $this->dispatch('audio-play');
            }
        }
    }

    /**
     * Reset search tanpa memicu updatedSearch() lagi.
     */
    public function resetSearch()
    {
        $this->search = '';
    }

    public function addToCart($productId, $unitId)
    {
        $this->lastTransactionId = null;

        $product = Product::find($productId);
        $unit = ProductUnit::find($unitId);
        $cartKey = $productId . '-' . $unitId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty']++;
        } else {
            $this->cart[$cartKey] = [
                'product_id'     => $productId,
                'unit_id'        => $unitId,
                'name'           => $product->name,
                'unit_name'      => $unit->unit_name,
                'price'          => $unit->price,
                'qty'            => 1,
                'conversion_qty' => $unit->conversion_qty,
            ];
        }
    }

    public function removeFromCart($key)
    {
        unset($this->cart[$key]);
    }

    public function openCheckoutModal()
    {
        if (empty($this->cart)) return;
        $this->showCheckoutModal = true;
        $this->amountPaid    = '';
        $this->changeAmount  = 0;
        $this->paymentMethod = 'Cash';
    }

    public function closeCheckoutModal()
    {
        $this->showCheckoutModal = false;
    }

    public function updatedAmountPaid()
    {
        $total              = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $paid               = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid);
        $this->changeAmount = $paid - $total;
    }

    public function processPayment()
    {
        $total = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $paid  = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid);

        if ($this->paymentMethod == 'Cash' && $paid < $total) {
            session()->flash('error_payment', 'Uang yang diterima kurang!');
            return;
        }

        try {
            DB::transaction(function () use ($total, $paid) {
                $transaction = Transaction::create([
                    'invoice_number' => 'INV-' . now()->format('YmdHis'),
                    'total_price'    => $total,
                    'user_id'        => 1,
                    'payment_method' => $this->paymentMethod,
                    'amount_paid'    => $this->paymentMethod == 'Cash' ? $paid : $total,
                    'change_amount'  => $this->changeAmount > 0 ? $this->changeAmount : 0,
                ]);

                $this->lastTransactionId = $transaction->id;

                foreach ($this->cart as $item) {
                    TransactionDetail::create([
                        'transaction_id'  => $transaction->id,
                        'product_id'      => $item['product_id'],
                        'product_unit_id' => $item['unit_id'],
                        'qty'             => $item['qty'],
                        'price'           => $item['price'],
                        'subtotal'        => $item['price'] * $item['qty'],
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

            $this->showCheckoutModal = false;
            $this->cart = [];
            session()->flash('success', 'Transaksi Berhasil Disimpan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Product::with('units')
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        $total = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);

        return view('livewire.kasir-utama', compact('products', 'total'))
            ->layout('layouts.app');
    }

    public function getWarnings()
    {
        $stokMenipis = \App\Models\Product::with(['batches'])
            ->get()
            ->filter(function ($product) {
                return $product->batches->sum('stock_qty') <= $product->min_stock;
            });

        $hampirExpired = \App\Models\ProductBatch::where('stock_qty', '>', 0)
            ->where('expired_date', '<=', now()->addDays(30))
            ->with('product')
            ->get();

        return [
            'stok'    => $stokMenipis,
            'expired' => $hampirExpired,
        ];
    }
}
