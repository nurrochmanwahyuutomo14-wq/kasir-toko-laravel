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
}
