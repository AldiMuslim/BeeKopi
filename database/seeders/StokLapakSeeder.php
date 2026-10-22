<?php

namespace Database\Seeders;

use App\Models\Lapak;
use App\Models\Produk;
use App\Models\StokLapak;
use Illuminate\Database\Seeder;

class StokLapakSeeder extends Seeder
{
    public function run(): void
    {
        $lapaks = Lapak::all();
        $produks = Produk::all();

        foreach ($lapaks as $lapak) {
            foreach ($produks as $produk) {
                StokLapak::updateOrCreate(
                    [
                        'lapak_id' => $lapak->id,
                        'produk_id' => $produk->id,
                    ],
                    [
                        'jumlah_stok' => 20,
                        'stok_minimum' => 5,
                    ]
                );
            }
        }
    }
}