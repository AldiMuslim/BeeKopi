<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah tipe kolom metode_pembayaran ke VARCHAR(20) agar bisa menerima 'split'
        DB::statement("ALTER TABLE transaksis MODIFY COLUMN metode_pembayaran VARCHAR(20) NOT NULL DEFAULT 'tunai'");

        // 2. Tambahkan kolom bayar_tunai & bayar_qris jika belum ada
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'bayar_tunai')) {
                $table->unsignedBigInteger('bayar_tunai')->default(0)->after('metode_pembayaran');
            }
            if (!Schema::hasColumn('transaksis', 'bayar_qris')) {
                $table->unsignedBigInteger('bayar_qris')->default(0)->after('bayar_tunai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['bayar_tunai', 'bayar_qris']);
        });
    }
};