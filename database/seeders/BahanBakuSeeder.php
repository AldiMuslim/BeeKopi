<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use Illuminate\Database\Seeder;

class BahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        $bahanList = [
            ['nama_bahan' => 'Biji Kopi House Blend', 'kategori' => 'Biji Kopi', 'stok_sekarang' => 10, 'stok_minimum' => 2, 'satuan' => 'kg', 'harga_satuan' => 120000],
            ['nama_bahan' => 'Susu UHT Full Cream', 'kategori' => 'Bahan Basah', 'stok_sekarang' => 50, 'stok_minimum' => 10, 'satuan' => 'liter', 'harga_satuan' => 18000],
            ['nama_bahan' => 'Sirup Gula Aren', 'kategori' => 'Sirup', 'stok_sekarang' => 15, 'stok_minimum' => 3, 'satuan' => 'liter', 'harga_satuan' => 35000],
            ['nama_bahan' => 'Simple Syrup Gula', 'kategori' => 'Sirup', 'stok_sekarang' => 5, 'stok_minimum' => 1, 'satuan' => 'liter', 'harga_satuan' => 15000],
            ['nama_bahan' => 'Sirup Butterscotch', 'kategori' => 'Sirup', 'stok_sekarang' => 3, 'stok_minimum' => 1, 'satuan' => 'liter', 'harga_satuan' => 60000],
            ['nama_bahan' => 'Bubuk Matcha Premium', 'kategori' => 'Bubuk', 'stok_sekarang' => 2, 'stok_minimum' => 0.5, 'satuan' => 'kg', 'harga_satuan' => 250000],
            ['nama_bahan' => 'Bubuk Cokelat Dark', 'kategori' => 'Bubuk', 'stok_sekarang' => 2, 'stok_minimum' => 0.5, 'satuan' => 'kg', 'harga_satuan' => 200000],
            ['nama_bahan' => 'Paper Cup 16oz', 'kategori' => 'Kemasan', 'stok_sekarang' => 1000, 'stok_minimum' => 100, 'satuan' => 'pcs', 'harga_satuan' => 500],
        ];

        foreach ($bahanList as $b) {
            BahanBaku::updateOrCreate(['nama_bahan' => $b['nama_bahan']], $b);
        }
    }
}