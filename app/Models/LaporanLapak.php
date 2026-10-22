<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanLapak extends Model {
    protected $guarded = ['id'];

    public function lapak(): BelongsTo {
        return $this->belongsTo(Lapak::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}