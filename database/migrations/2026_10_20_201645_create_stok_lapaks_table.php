<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stok_lapaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapak_id')->constrained('lapaks')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->integer('jumlah_stok')->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('stok_lapaks');
    }
};