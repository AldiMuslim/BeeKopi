<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    protected $guarded = ['id'];

    public function reseps(): HasMany
    {
        return $this->hasMany(Resep::class, 'bahan_baku_id');
    }
}
