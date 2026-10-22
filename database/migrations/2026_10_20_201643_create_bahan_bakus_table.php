<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bahan');
            $table->string('kategori')->nullable();
            $table->decimal('stok_sekarang', 10, 2)->default(0);
            $table->decimal('stok_minimum', 10, 2)->default(5);
            $table->string('satuan'); // liter, kg, box, pcs, dus
            $table->decimal('harga_satuan', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('bahan_bakus');
    }
};