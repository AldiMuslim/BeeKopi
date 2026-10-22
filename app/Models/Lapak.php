<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lapak extends Model {
    protected $guarded = ['id'];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    public function stokLapaks(): HasMany {
        return $this->hasMany(StokLapak::class);
    }

    public function transaksis(): HasMany {
        return $this->hasMany(Transaksi::class);
    }

    public function laporanLapaks(): HasMany {
        return $this->hasMany(LaporanLapak::class);
    }
}