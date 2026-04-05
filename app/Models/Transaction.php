<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    // Tambahkan baris sakti ini untuk membuka pintu keamanan
    protected $guarded = [];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
