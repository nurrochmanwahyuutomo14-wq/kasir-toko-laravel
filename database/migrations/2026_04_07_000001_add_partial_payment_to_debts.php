<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom di debt_details: centang per item
        Schema::table('debt_details', function (Blueprint $table) {
            $table->boolean('is_lunas')->default(false)->after('tanggal_bon');
        });

        // Kolom di debts: total yang sudah dibayar (untuk bayar sebagian)
        Schema::table('debts', function (Blueprint $table) {
            $table->unsignedBigInteger('sudah_dibayar')->default(0)->after('total_hutang');
        });
    }

    public function down(): void
    {
        Schema::table('debt_details', function (Blueprint $table) {
            $table->dropColumn('is_lunas');
        });
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn('sudah_dibayar');
        });
    }
};
