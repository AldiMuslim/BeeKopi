<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $guarded = ['id'];

    public function stokLapaks(): HasMany
    {
        return $this->hasMany(StokLapak::class);
    }

    public function transaksiDetails(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class);
    }
    public function reseps()
    {
        return $this->hasMany(Resep::class);
    }

    public function bahanBakus()
    {
        return $this->belongsToMany(BahanBaku::class, 'reseps')
            ->withPivot('takaran_per_cup', 'satuan')
            ->withTimestamps();
    }
}
