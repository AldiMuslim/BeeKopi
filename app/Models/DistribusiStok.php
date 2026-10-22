<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribusiStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_distribusi',
        'lapak_id',
        'user_id',
        'penerima_id',
        'tanggal',
        'catatan',
        'status',
    ];

    public function lapak()
    {
        return $this->belongsTo(Lapak::class, 'lapak_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function details()
    {
        return $this->hasMany(DistribusiStokDetail::class, 'distribusi_stok_id');
    }
}