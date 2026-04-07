<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtDetail extends Model
{
    protected $guarded = [];

    // Relasi balik ke pengutang
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
    protected $fillable = [
        'debt_id',
        'nama_produk',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'tanggal_bon',
        'is_lunas',   // ✅ tambahkan ini
    ];
}
