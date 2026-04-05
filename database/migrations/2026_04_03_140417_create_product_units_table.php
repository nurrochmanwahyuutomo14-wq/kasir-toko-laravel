<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            // foreignId = Relasi ke tabel products. cascadeOnDelete = Jika produk dihapus, satuan ini ikut terhapus otomatis.
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('unit_name'); // Berisi: Pcs, Renteng, Dus
            $table->integer('conversion_qty'); // Berisi angka: 1, 10, 40
            $table->integer('price'); // Harga jual
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
