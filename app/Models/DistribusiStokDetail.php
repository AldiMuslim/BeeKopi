<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribusiStokDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribusi_stok_id',
        'produk_id',
        'jumlah_cup',
    ];

    public function distribusiStok()
    {
        return $this->belongsTo(DistribusiStok::class, 'distribusi_stok_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}