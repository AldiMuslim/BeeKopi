<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiDetail extends Model {
    protected $guarded = ['id'];

    public function transaksi(): BelongsTo {
        return $this->belongsTo(Transaksi::class);
    }

    public function produk(): BelongsTo {
        return $this->belongsTo(Produk::class);
    }
}