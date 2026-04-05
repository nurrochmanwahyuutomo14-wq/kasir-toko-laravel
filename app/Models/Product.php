<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Tambahkan ini

class Product extends Model
{
    protected $guarded = [];

    // Hubungan: Satu produk punya banyak satuan harga (Pcs, Renteng, Dus)
    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    // Hubungan: Satu produk punya banyak stok (Batch/Kadaluarsa)
    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }
}
