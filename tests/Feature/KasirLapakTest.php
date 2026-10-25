<?php

namespace Tests\Feature;

use App\Livewire\Sales\KasirLapak;
use App\Models\Lapak;
use App\Models\Produk;
use App\Models\Role;
use App\Models\StokLapak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Buat role Sales jika belum ada
    $this->roleSales = Role::firstOrCreate(
        ['nama_role' => 'Sales']
    );

    // Buat data lapak dummy
    $this->lapak = Lapak::create([
        'nama_lapak' => 'Lapak Utama',
        'alamat' => 'Jl. Sudirman No. 1',
    ]);

    // Buat user kasir sales dummy
    $this->userSales = User::create([
        'name' => 'Kasir Sales',
        'email' => 'sales@beekopi.com',
        'password' => bcrypt('password'),
        'role_id' => $this->roleSales->id,
        'lapak_id' => $this->lapak->id,
    ]);

    // Buat produk dummy
    $this->produk = Produk::create([
        'nama_produk' => 'Kopi Susu Aren',
        'kategori' => 'Kopi',
        'harga' => 15000,
        'stok_pusat' => 50,
        'status' => 'aktif',
    ]);

    // Set stok awal di lapak 10 cup
    $this->stokLapak = StokLapak::create([
        'lapak_id' => $this->lapak->id,
        'produk_id' => $this->produk->id,
        'jumlah' => 10,
    ]);
});

test('user sales dapat mengakses halaman kasir pos', function () {
    $this->actingAs($this->userSales)
        ->get(route('sales.kasir'))
        ->assertStatus(200);
});

test('kasir dapat menambahkan produk dan memproses transaksi', function () {
    Livewire::actingAs($this->userSales)
        ->test(KasirLapak::class)
        ->call('tambahKeranjang', $this->produk->id)
        ->set('bayar', 20000)
        ->call('prosesTransaksi')
        ->assertHasNoErrors();

    // Verifikasi stok lapak berkurang dari 10 menjadi 9
    $this->assertDatabaseHas('stok_lapaks', [
        'lapak_id' => $this->lapak->id,
        'produk_id' => $this->produk->id,
        'jumlah' => 9,
    ]);
});
