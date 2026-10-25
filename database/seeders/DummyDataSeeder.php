<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\DistribusiStok;
use App\Models\DistribusiStokDetail;
use App\Models\Lapak;
use App\Models\LaporanLapak;
use App\Models\Produk;
use App\Models\Resep;
use App\Models\RiwayatBahanBaku;
use App\Models\Role;
use App\Models\StokLapak;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Seed Roles
            $roles = [
                'admin' => Role::firstOrCreate(['nama' => 'admin'], ['label' => 'Administrator']),
                'management' => Role::firstOrCreate(['nama' => 'management'], ['label' => 'Management / Owner']),
                'produksi' => Role::firstOrCreate(['nama' => 'produksi'], ['label' => 'Tim Produksi & Gudang']),
                'sales' => Role::firstOrCreate(['nama' => 'sales'], ['label' => 'Kasir / Sales Lapak']),
            ];

            // 2. Seed 3 Lapak Cabang
            $lapak1 = Lapak::create(['nama_lapak' => 'Lapak 01 - Monas', 'lokasi' => 'Jl. Medan Merdeka Barat, Jakarta Pusat', 'status' => 'aktif']);
            $lapak2 = Lapak::create(['nama_lapak' => 'Lapak 02 - Sudirman', 'lokasi' => 'Jl. Jend. Sudirman Kav. 50, Jakarta Selatan', 'status' => 'aktif']);
            $lapak3 = Lapak::create(['nama_lapak' => 'Lapak 03 - Blok M', 'lokasi' => 'Kawasan Kuliner Blok M Square, Jakarta Selatan', 'status' => 'aktif']);

            // 3. Seed Users
            $admin = User::create(['name' => 'Admin BeeKopi', 'email' => 'admin@beekopi.com', 'password' => Hash::make('password'), 'role_id' => $roles['admin']->id]);
            $owner = User::create(['name' => 'Owner BeeKopi', 'email' => 'owner@beekopi.com', 'password' => Hash::make('password'), 'role_id' => $roles['management']->id]);
            $produksiUser = User::create(['name' => 'Staf Gudang Pusat', 'email' => 'gudang@beekopi.com', 'password' => Hash::make('password'), 'role_id' => $roles['produksi']->id]);

            $kasir1 = User::create(['name' => 'Siti Kasir Monas', 'email' => 'kasir1@beekopi.com', 'password' => Hash::make('password'), 'role_id' => $roles['sales']->id, 'lapak_id' => $lapak1->id]);
            $kasir2 = User::create(['name' => 'Budi Kasir Sudirman', 'email' => 'kasir2@beekopi.com', 'password' => Hash::make('password'), 'role_id' => $roles['sales']->id, 'lapak_id' => $lapak2->id]);
            $kasir3 = User::create(['name' => 'Rian Kasir Blok M', 'email' => 'kasir3@beekopi.com', 'password' => Hash::make('password'), 'role_id' => $roles['sales']->id, 'lapak_id' => $lapak3->id]);

            // 4. Seed Bahan Baku & Riwayat Masuk/Keluar
            $bb1 = BahanBaku::create(['nama_bahan' => 'Biji Kopi Espresso (House Blend)', 'stok' => 25000, 'satuan' => 'gram', 'stok_minimum' => 3000]);
            $bb2 = BahanBaku::create(['nama_bahan' => 'Susu Fresh Milk UHT', 'stok' => 50000, 'satuan' => 'ml', 'stok_minimum' => 5000]);
            $bb3 = BahanBaku::create(['nama_bahan' => 'Sirup Gula Aren Murni', 'stok' => 15000, 'satuan' => 'ml', 'stok_minimum' => 2000]);
            $bb4 = BahanBaku::create(['nama_bahan' => 'Cup Plastik 16oz + Tutup', 'stok' => 2000, 'satuan' => 'pcs', 'stok_minimum' => 300]);

            // Riwayat Barang Masuk
            RiwayatBahanBaku::create(['bahan_baku_id' => $bb1->id, 'user_id' => $produksiUser->id, 'jenis' => 'masuk', 'jumlah' => 30000, 'total_biaya' => 4500000, 'catatan' => 'Restok Kopi Biji dari Supplier', 'created_at' => now()->subDays(25)]);
            RiwayatBahanBaku::create(['bahan_baku_id' => $bb2->id, 'user_id' => $produksiUser->id, 'jenis' => 'masuk', 'jumlah' => 60000, 'total_biaya' => 1200000, 'catatan' => 'Pembelian Susu UHT 60 Liter', 'created_at' => now()->subDays(20)]);

            // Riwayat Barang Keluar (Produksi)
            RiwayatBahanBaku::create(['bahan_baku_id' => $bb1->id, 'user_id' => $produksiUser->id, 'jenis' => 'keluar', 'jumlah' => 5000, 'total_biaya' => 0, 'catatan' => 'Pemakaian racik kopi batch mingguan', 'created_at' => now()->subDays(10)]);

            // 5. Seed Produk & Resep Takaran
            $p1 = Produk::create(['nama_produk' => 'Es Kopi Susu Gula Aren', 'kategori' => 'Coffee', 'harga' => 18000, 'stok_pusat' => 350, 'status' => 'aktif']);
            $p2 = Produk::create(['nama_produk' => 'Americano Ice', 'kategori' => 'Coffee', 'harga' => 15000, 'stok_pusat' => 200, 'status' => 'aktif']);
            $p3 = Produk::create(['nama_produk' => 'Caffe Latte', 'kategori' => 'Coffee', 'harga' => 20000, 'stok_pusat' => 150, 'status' => 'aktif']);

            // Resep Es Kopi Susu Gula Aren
            Resep::create(['produk_id' => $p1->id, 'bahan_baku_id' => $bb1->id, 'jumlah_takaran' => 15]); // 15gr Kopi
            Resep::create(['produk_id' => $p1->id, 'bahan_baku_id' => $bb2->id, 'jumlah_takaran' => 120]); // 120ml Susu
            Resep::create(['produk_id' => $p1->id, 'bahan_baku_id' => $bb3->id, 'jumlah_takaran' => 25]);  // 25ml Aren
            Resep::create(['produk_id' => $p1->id, 'bahan_baku_id' => $bb4->id, 'jumlah_takaran' => 1]);   // 1 Cup

            // 6. Seed Stok Lapak Cabang
            $lapaks = [$lapak1, $lapak2, $lapak3];
            $produks = [$p1, $p2, $p3];

            foreach ($lapaks as $lpk) {
                foreach ($produks as $pdk) {
                    StokLapak::create([
                        'lapak_id' => $lpk->id,
                        'produk_id' => $pdk->id,
                        'jumlah_stok' => rand(20, 80),
                        'stok_minimum' => 10,
                    ]);
                }
            }

            // 7. Seed Alokasi & Distribusi Stok
            foreach ($lapaks as $index => $lpk) {
                $dist = DistribusiStok::create([
                    'kode_distribusi' => 'DST-' . now()->subDays(5 - $index)->format('YmdHis') . '-' . rand(100, 999),
                    'lapak_id' => $lpk->id,
                    'user_id' => $owner->id,
                    'penerima_id' => ${'kasir' . ($index + 1)}->id,
                    'tanggal' => now()->subDays(5 - $index)->toDateString(),
                    'catatan' => 'Pengiriman rutin stok siap jual dari pusat',
                    'status' => 'diterima',
                ]);

                foreach ($produks as $pdk) {
                    DistribusiStokDetail::create([
                        'distribusi_stok_id' => $dist->id,
                        'produk_id' => $pdk->id,
                        'jumlah_cup' => rand(30, 50),
                    ]);
                }
            }

            // 8. Seed Transaksi Penjualan 30 Hari Terakhir (Untuk Grafik Trend & Laba Rugi)
            for ($i = 30; $i >= 0; $i--) {
                $tanggalTx = now()->subDays($i);

                foreach ($lapaks as $idx => $lpk) {
                    $jumTx = rand(3, 8); // 3-8 transaksi per hari per lapak

                    for ($t = 0; $t < $jumTx; $t++) {
                        $jamTx = $tanggalTx->copy()->setHour(rand(9, 21))->setMinute(rand(0, 59));
                        $metode = rand(0, 1) ? 'tunai' : 'qris';

                        $tx = Transaksi::create([
                            'kode_transaksi' => 'TRX-' . $jamTx->format('YmdHis') . '-' . rand(10, 99),
                            'lapak_id' => $lpk->id,
                            'user_id' => ${'kasir' . ($idx + 1)}->id,
                            'total_harga' => 0,
                            'metode_pembayaran' => $metode,
                            'status' => 'selesai',
                            'created_at' => $jamTx,
                            'updated_at' => $jamTx,
                        ]);

                        $subtotalAll = 0;
                        $randomProducts = array_rand($produks, rand(1, 2));
                        $chosenProducts = is_array($randomProducts) ? $randomProducts : [$randomProducts];

                        foreach ($chosenProducts as $pIdx) {
                            $prod = $produks[$pIdx];
                            $qty = rand(1, 3);
                            $subtotal = $prod->harga * $qty;
                            $subtotalAll += $subtotal;

                            TransaksiDetail::create([
                                'transaksi_id' => $tx->id,
                                'produk_id' => $prod->id,
                                'jumlah' => $qty,
                                'harga_satuan' => $prod->harga,
                                'subtotal' => $subtotal,
                                'created_at' => $jamTx,
                                'updated_at' => $jamTx,
                            ]);
                        }

                        $tx->update(['total_harga' => $subtotalAll]);
                    }

                    // 9. Seed Laporan Penutupan Lapak Harian
                    LaporanLapak::create([
                        'lapak_id' => $lpk->id,
                        'user_id' => ${'kasir' . ($idx + 1)}->id,
                        'tanggal' => $tanggalTx->toDateString(),
                        'total_penjualan_tunai' => rand(150000, 300000),
                        'total_penjualan_qris' => rand(100000, 250000),
                        'uang_makan' => 25000,
                        'pengeluaran_lain' => 10000,
                        'catatan_pengeluaran' => 'Beli es batu kristal tambahan',
                        'status' => 'approved',
                        'verified_by' => $owner->id,
                        'created_at' => $tanggalTx,
                    ]);
                }
            }
        });
    }
}
