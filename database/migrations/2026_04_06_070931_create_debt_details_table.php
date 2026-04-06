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
        Schema::create('debt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->string('nama_produk');
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('total_harga');
            $table->date('tanggal_bon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_details');
    }
};
