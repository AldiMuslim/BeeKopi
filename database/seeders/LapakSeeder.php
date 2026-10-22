<?php

namespace Database\Seeders;

use App\Models\Lapak;
use Illuminate\Database\Seeder;

class LapakSeeder extends Seeder
{
    public function run(): void
    {
        Lapak::create([
            'nama_lapak' => 'Lapak A - Simpang Empat',
            'lokasi' => 'Jl. Merdeka No. 12',
            'status' => 'aktif',
        ]);

        Lapak::create([
            'nama_lapak' => 'Lapak B - Alun-Alun',
            'lokasi' => 'Jl. Sudirman No. 45',
            'status' => 'aktif',
        ]);
    }
}