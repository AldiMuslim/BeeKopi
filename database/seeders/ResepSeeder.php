<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Resep;
use Illuminate\Database\Seeder;

class ResepSeeder extends Seeder
{
    public function run(): void
    {
        $bahan = [
            'biji_kopi'    => BahanBaku::where('nama_bahan', 'Biji Kopi House Blend')->first(),
            'susu_uht'     => BahanBaku::where('nama_bahan', 'Susu UHT Full Cream')->first(),
            'gula_aren'    => BahanBaku::where('nama_bahan', 'Sirup Gula Aren')->first(),
            'simple_syrup' => BahanBaku::where('nama_bahan', 'Simple Syrup Gula')->first(),
            'butterscotch' => BahanBaku::where('nama_bahan', 'Sirup Butterscotch')->first(),
            'matcha'       => BahanBaku::where('nama_bahan', 'Bubuk Matcha Premium')->first(),
            'cokelat'      => BahanBaku::where('nama_bahan', 'Bubuk Cokelat Dark')->first(),
            'cup'          => BahanBaku::where('nama_bahan', 'Paper Cup 16oz')->first(),
        ];

        $produk = [
            'soft'         => Produk::where('nama_produk', 'Kopi Soft')->first(),
            'strong'       => Produk::where('nama_produk', 'Kopi Strong')->first(),
            'butterscotch' => Produk::where('nama_produk', 'Kopi Susu Butterscotch')->first(),
            'matcha'       => Produk::where('nama_produk', 'Matcha')->first(),
            'cokelat'      => Produk::where('nama_produk', 'Cokelat')->first(),
        ];

        $reseps = [
            // Kopi Soft
            ['produk_id' => $produk['soft']->id, 'bahan_baku_id' => $bahan['biji_kopi']->id, 'takaran_per_cup' => 15, 'satuan' => 'gram'],
            ['produk_id' => $produk['soft']->id, 'bahan_baku_id' => $bahan['susu_uht']->id, 'takaran_per_cup' => 120, 'satuan' => 'ml'],
            ['produk_id' => $produk['soft']->id, 'bahan_baku_id' => $bahan['gula_aren']->id, 'takaran_per_cup' => 20, 'satuan' => 'ml'],
            ['produk_id' => $produk['soft']->id, 'bahan_baku_id' => $bahan['cup']->id, 'takaran_per_cup' => 1, 'satuan' => 'pcs'],

            // Kopi Strong
            ['produk_id' => $produk['strong']->id, 'bahan_baku_id' => $bahan['biji_kopi']->id, 'takaran_per_cup' => 25, 'satuan' => 'gram'],
            ['produk_id' => $produk['strong']->id, 'bahan_baku_id' => $bahan['susu_uht']->id, 'takaran_per_cup' => 90, 'satuan' => 'ml'],
            ['produk_id' => $produk['strong']->id, 'bahan_baku_id' => $bahan['gula_aren']->id, 'takaran_per_cup' => 15, 'satuan' => 'ml'],
            ['produk_id' => $produk['strong']->id, 'bahan_baku_id' => $bahan['cup']->id, 'takaran_per_cup' => 1, 'satuan' => 'pcs'],

            // Kopi Susu Butterscotch
            ['produk_id' => $produk['butterscotch']->id, 'bahan_baku_id' => $bahan['biji_kopi']->id, 'takaran_per_cup' => 18, 'satuan' => 'gram'],
            ['produk_id' => $produk['butterscotch']->id, 'bahan_baku_id' => $bahan['susu_uht']->id, 'takaran_per_cup' => 110, 'satuan' => 'ml'],
            ['produk_id' => $produk['butterscotch']->id, 'bahan_baku_id' => $bahan['butterscotch']->id, 'takaran_per_cup' => 15, 'satuan' => 'ml'],
            ['produk_id' => $produk['butterscotch']->id, 'bahan_baku_id' => $bahan['cup']->id, 'takaran_per_cup' => 1, 'satuan' => 'pcs'],

            // Matcha
            ['produk_id' => $produk['matcha']->id, 'bahan_baku_id' => $bahan['matcha']->id, 'takaran_per_cup' => 20, 'satuan' => 'gram'],
            ['produk_id' => $produk['matcha']->id, 'bahan_baku_id' => $bahan['susu_uht']->id, 'takaran_per_cup' => 120, 'satuan' => 'ml'],
            ['produk_id' => $produk['matcha']->id, 'bahan_baku_id' => $bahan['simple_syrup']->id, 'takaran_per_cup' => 20, 'satuan' => 'ml'],
            ['produk_id' => $produk['matcha']->id, 'bahan_baku_id' => $bahan['cup']->id, 'takaran_per_cup' => 1, 'satuan' => 'pcs'],

            // Cokelat
            ['produk_id' => $produk['cokelat']->id, 'bahan_baku_id' => $bahan['cokelat']->id, 'takaran_per_cup' => 25, 'satuan' => 'gram'],
            ['produk_id' => $produk['cokelat']->id, 'bahan_baku_id' => $bahan['susu_uht']->id, 'takaran_per_cup' => 120, 'satuan' => 'ml'],
            ['produk_id' => $produk['cokelat']->id, 'bahan_baku_id' => $bahan['simple_syrup']->id, 'takaran_per_cup' => 15, 'satuan' => 'ml'],
            ['produk_id' => $produk['cokelat']->id, 'bahan_baku_id' => $bahan['cup']->id, 'takaran_per_cup' => 1, 'satuan' => 'pcs'],
        ];

        foreach ($reseps as $data) {
            Resep::updateOrCreate(
                ['produk_id' => $data['produk_id'], 'bahan_baku_id' => $data['bahan_baku_id']],
                ['takaran_per_cup' => $data['takaran_per_cup'], 'satuan' => $data['satuan']]
            );
        }
    }
}