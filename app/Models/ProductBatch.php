<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    // Mengizinkan semua kolom diisi (sesuai gaya Mas Nur sebelumnya)
    protected $guarded = [];

    /**
     * Relasi ke Product (Sangat Penting!)
     * Ini adalah "Jembatan" agar kita bisa mengambil nama produk
     * dari data Batch/Stok yang expired.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
