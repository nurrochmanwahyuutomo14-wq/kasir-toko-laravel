<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    // Gunakan fillable (lebih aman)
    protected $fillable = [
        'nama_produk',
        'barcode',
        'keterangan',
        // tambahkan kolom lain sesuai tabel
    ];

    // Relasi: Produk punya banyak satuan
    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    // Relasi: Produk punya banyak batch stok
    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }
}
