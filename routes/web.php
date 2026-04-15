<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Livewire\KasirUtama;
use App\Livewire\BukuBon;
use App\Livewire\MasterBarang;
use App\Livewire\LaporanPenjualan;
use App\Livewire\Dashboard;
use App\Livewire\RekapKasir;
use App\Livewire\Auth\Login;
use App\Http\Controllers\PrintController;

// Halaman Login
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Halaman Inti (HANYA BISA DIAKSES JIKA SUDAH LOGIN)
Route::middleware('auth')->group(function () {
    // Dashboard sebagai halaman utama
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', Dashboard::class);

    Route::get('/kasir', KasirUtama::class);
    Route::get('/barang', MasterBarang::class);
    Route::get('/laporan', LaporanPenjualan::class);
    Route::get('/buku-bon', BukuBon::class)->name('buku-bon');
    Route::get('/rekap-kasir', RekapKasir::class)->name('rekap-kasir');

    Route::get('/kasir/print/{id}', [PrintController::class, 'index'])->name('print.struk');

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
Route::get('/jalankan-migrasi', function () {
    try {
        // Membersihkan cache konfigurasi lama
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        // Menjalankan perintah pembuatan tabel (migrate)
        Artisan::call('migrate', ['--force' => true]);

        // Memasukkan data awal (seeder), seperti akun admin
        Artisan::call('db:seed', ['--force' => true]);

        return 'MANTAP! Tabel MySQL berhasil dibuat. Silakan hapus /jalankan-migrasi di URL dan kembali ke /login';
    } catch (\Exception $e) {
        return 'Gagal coy: ' . $e->getMessage();
    }
});
