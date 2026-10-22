<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produkList = [
            ['nama_produk' => 'Kopi Soft', 'kategori' => 'Kopi', 'harga_jual' => 12000, 'hpp' => 5000, 'stok_pusat' => 20, 'status' => 'aktif'],
            ['nama_produk' => 'Kopi Strong', 'kategori' => 'Kopi', 'harga_jual' => 15000, 'hpp' => 6000, 'stok_pusat' => 20, 'status' => 'aktif'],
            ['nama_produk' => 'Kopi Susu Butterscotch', 'kategori' => 'Kopi', 'harga_jual' => 16000, 'hpp' => 6500, 'stok_pusat' => 15, 'status' => 'aktif'],
            ['nama_produk' => 'Matcha', 'kategori' => 'Non-Kopi', 'harga_jual' => 15000, 'hpp' => 6000, 'stok_pusat' => 15, 'status' => 'aktif'],
            ['nama_produk' => 'Cokelat', 'kategori' => 'Non-Kopi', 'harga_jual' => 14000, 'hpp' => 5500, 'stok_pusat' => 15, 'status' => 'aktif'],
        ];

        foreach ($produkList as $p) {
            Produk::updateOrCreate(['nama_produk' => $p['nama_produk']], $p);
        }
    }
}