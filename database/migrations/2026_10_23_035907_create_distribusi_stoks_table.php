<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi_stoks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_distribusi')->unique();
            $table->foreignId('lapak_id')->constrained('lapaks')->onDelete('cascade');
            $table->foreignId('user_id')->comment('Management (Pengirim)')->constrained('users')->onDelete('cascade');
            $table->foreignId('penerima_id')->nullable()->comment('Sales (Penerima)')->constrained('users')->onDelete('set null');
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->enum('status', ['pending', 'diterima', 'ditolak'])->default('pending');
            $table->timestamps();
        });

        Schema::create('distribusi_stok_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_stok_id')->constrained('distribusi_stoks')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->integer('jumlah_cup');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi_stok_details');
        Schema::dropIfExists('distribusi_stoks');
    }
};