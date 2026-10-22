<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('laporan_lapaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapak_id')->constrained('lapaks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            
            // Rekonsiliasi Stok & Terjual
            $table->integer('stok_awal')->default(0);
            $table->integer('sisa_stok_fisik')->default(0);
            $table->integer('total_terjual_sistem')->default(0);
            $table->integer('total_terjual_fisik')->default(0);
            $table->integer('stok_rusak_cup')->default(0);
            $table->string('alasan_stok_rusak')->nullable();

            // Rincian Keuangan
            $table->decimal('omzet_tunai', 12, 2)->default(0);
            $table->decimal('omzet_qris', 12, 2)->default(0);
            $table->decimal('uang_makan', 12, 2)->default(20000);
            $table->integer('jatah_minum_cup')->default(1);
            $table->decimal('pengeluaran_lain', 12, 2)->default(0);
            $table->string('keterangan_pengeluaran')->nullable();
            $table->decimal('setoran_bersih', 12, 2)->default(0);
            $table->string('foto_qris')->nullable();

            // Status Verifikasi Management
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_management')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('laporan_lapaks');
    }
};