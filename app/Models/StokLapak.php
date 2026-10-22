<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokLapak extends Model {
    protected $guarded = ['id'];

    public function lapak(): BelongsTo {
        return $this->belongsTo(Lapak::class);
    }

    public function produk(): BelongsTo {
        return $this->belongsTo(Produk::class);
    }
}