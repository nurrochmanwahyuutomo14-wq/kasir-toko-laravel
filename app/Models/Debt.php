<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $guarded = [];

    // Relasi ke detail barang
    public function details()
    {
        return $this->hasMany(DebtDetail::class);
    }
}
