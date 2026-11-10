<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribusi_stoks', function (Blueprint $table) {
            if (!Schema::hasColumn('distribusi_stoks', 'catatan_penerimaan')) {
                $table->text('catatan_penerimaan')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('distribusi_stoks', 'catatan_penolakan')) {
                $table->text('catatan_penolakan')->nullable()->after('catatan_penerimaan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribusi_stoks', function (Blueprint $table) {
            $table->dropColumn(['catatan_penerimaan', 'catatan_penolakan']);
        });
    }
};