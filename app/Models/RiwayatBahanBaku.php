<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatBahanBaku extends Model
{
    protected $guarded = [];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}