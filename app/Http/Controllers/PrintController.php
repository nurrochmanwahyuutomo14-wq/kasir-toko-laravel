<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function index($id)
    {
        $transaction = Transaction::with(['details.product', 'details.unit'])->findOrFail($id);
        return view('print.struk', compact('transaction'));
    }
}
