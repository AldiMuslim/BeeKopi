<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lapaks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lapak');
            $table->text('lokasi')->nullable();
            $table->enum('status', ['aktif', 'libur', 'perbaikan'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lapaks');
    }
};