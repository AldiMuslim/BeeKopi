<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model {
    protected $guarded = ['id'];

    public function lapak(): BelongsTo {
        return $this->belongsTo(Lapak::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany {
        return $this->hasMany(TransaksiDetail::class);
    }
}