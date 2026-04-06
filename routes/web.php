<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\KasirUtama;
use App\Livewire\BukuBon;
use App\Livewire\MasterBarang;
use App\Livewire\LaporanPenjualan;
use App\Livewire\Auth\Login;
use App\Http\Controllers\PrintController;

// Halaman Login (Bisa diakses siapa saja yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Halaman Inti (HANYA BISA DIAKSES JIKA SUDAH LOGIN)
Route::middleware('auth')->group(function () {
    // Redirect halaman utama langsung ke kasir
    Route::get('/', function () {
        return redirect('/kasir');
    });

    Route::get('/kasir', KasirUtama::class);
    Route::get('/barang', MasterBarang::class);
    Route::get('/laporan', LaporanPenjualan::class);
    Route::get('/buku-bon', BukuBon::class)->name('buku-bon');

    Route::get('/kasir/print/{id}', [PrintController::class, 'index'])->name('print.struk');

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
    // ---------------------------------------
});
