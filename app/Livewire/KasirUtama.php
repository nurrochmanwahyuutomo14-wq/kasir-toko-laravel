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
    public $filterKategori = 'Semua';

    // Checkout Modal
    public $showCheckoutModal = false;
    public $paymentMethod = 'Cash';
    public $amountPaid = '';
    public $changeAmount = 0;

    // Fitur Diskon
    public $discountType = 'nominal'; // nominal | persen
    public $discountValue = 0;
    public $discountAmount = 0;

    // Catatan pelanggan
    public $customerNote = '';

    /**
     * FITUR SCANNER: Otomatis jalan setiap kali $search berubah.
     */
    public function updatedSearch()
    {
        if (empty(trim($this->search))) {
            return;
        }

        $product = Product::whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->where('barcode', $this->search)
            ->first();

        if ($product) {
            $unit = $product->units()->where('conversion_qty', 1)->first();

            if ($unit) {
                $this->addToCart($product->id, $unit->id);
                $this->resetSearch();
                $this->dispatch('audio-play');
            }
        }
    }

    public function resetSearch()
    {
        $this->search = '';
    }

    public function setFilterKategori($kategori)
    {
        $this->filterKategori = $kategori;
    }

    public function addToCart($productId, $unitId)
    {
        $this->lastTransactionId = null;

        $product = Product::find($productId);
        $unit = ProductUnit::find($unitId);
        $cartKey = $productId . '-' . $unitId;

        // Cek stok
        $stokTotal = $product->batches->sum('stock_qty');
        $qtyDiKeranjang = isset($this->cart[$cartKey]) ? $this->cart[$cartKey]['qty'] : 0;
        if ($stokTotal <= 0 || $qtyDiKeranjang * $unit->conversion_qty >= $stokTotal) {
            session()->flash('warning_stok', 'Stok ' . $product->name . ' tidak mencukupi!');
            return;
        }

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

        // Recalculate diskon setelah cart berubah
        $this->hitungDiskon();
    }

    public function updateQty($key, $delta)
    {
        if (!isset($this->cart[$key])) return;

        $newQty = $this->cart[$key]['qty'] + $delta;

        if ($newQty <= 0) {
            $this->removeFromCart($key);
            return;
        }

        $this->cart[$key]['qty'] = $newQty;
        $this->hitungDiskon();
    }

    public function removeFromCart($key)
    {
        unset($this->cart[$key]);
        $this->hitungDiskon();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->discountValue = 0;
        $this->discountAmount = 0;
        $this->customerNote = '';
        $this->lastTransactionId = null;
    }

    public function openCheckoutModal()
    {
        if (empty($this->cart)) return;
        $this->showCheckoutModal = true;
        $this->amountPaid    = '';
        $this->changeAmount  = 0;
        $this->paymentMethod = 'Cash';
        $this->discountType  = 'nominal';
        $this->discountValue = 0;
        $this->discountAmount = 0;
    }

    public function closeCheckoutModal()
    {
        $this->showCheckoutModal = false;
    }

    public function updatedDiscountValue()
    {
        $this->hitungDiskon();
        $this->hitungKembalian();
    }

    public function updatedDiscountType()
    {
        $this->discountValue = 0;
        $this->discountAmount = 0;
        $this->hitungKembalian();
    }

    public function hitungDiskon()
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $val = (float) $this->discountValue;

        if ($this->discountType === 'persen') {
            $persen = min($val, 100);
            $this->discountAmount = round($subtotal * $persen / 100);
        } else {
            $this->discountAmount = min($val, $subtotal);
        }

        $this->hitungKembalian();
    }

    public function updatedAmountPaid()
    {
        $this->hitungKembalian();
    }

    public function hitungKembalian()
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $total    = $subtotal - $this->discountAmount;
        $paid     = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid);
        $this->changeAmount = $paid - $total;
    }

    /**
     * Quick payment: set amountPaid langsung dari tombol nominal
     */
    public function setAmountPaid($amount)
    {
        if ($amount === 'pas') {
            $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
            $this->amountPaid = (string)($subtotal - $this->discountAmount);
        } else {
            $current = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid ?? '0');
            $this->amountPaid = (string)($current + $amount);
        }
        $this->hitungKembalian();
    }

    public function resetAmountPaid()
    {
        $this->amountPaid = '';
        $this->changeAmount = 0;
    }

    public function processPayment()
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $total    = $subtotal - $this->discountAmount;
        $paid     = (float) preg_replace('/[^0-9.]/', '', $this->amountPaid);

        if ($this->paymentMethod == 'Cash' && $paid < $total) {
            session()->flash('error_payment', 'Uang yang diterima kurang!');
            return;
        }

        try {
            DB::transaction(function () use ($subtotal, $total, $paid) {
                $transaction = Transaction::create([
                    'invoice_number' => 'INV-' . now()->format('YmdHis'),
                    'total_price'    => $total,
                    'user_id'        => auth()->id() ?? 1,
                    'payment_method' => $this->paymentMethod,
                    'amount_paid'    => $this->paymentMethod == 'Cash' ? $paid : $total,
                    'change_amount'  => $this->changeAmount > 0 ? $this->changeAmount : 0,
                    'discount_type'  => $this->discountType,
                    'discount_value' => $this->discountValue,
                    'discount_amount'=> $this->discountAmount,
                    'customer_note'  => $this->customerNote,
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
            $this->discountValue  = 0;
            $this->discountAmount = 0;
            $this->customerNote   = '';
            session()->flash('success', 'Transaksi Berhasil! 🎉');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Product::with(['units', 'batches']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filterKategori && $this->filterKategori !== 'Semua') {
            $query->where('category', $this->filterKategori);
        }

        $products = $query->get();

        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $total    = $subtotal - $this->discountAmount;

        return view('livewire.kasir-utama', compact('products', 'subtotal', 'total'))
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
