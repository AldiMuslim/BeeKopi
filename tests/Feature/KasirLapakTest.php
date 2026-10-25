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
    // 1. Sesuaikan field 'nama' => 'sales' sesuai pengecekan di routes/web.php
    $this->roleSales = Role::firstOrCreate(
        ['nama' => 'sales'],
        ['nama_role' => 'Sales']
    );

    // 2. Buat data lapak dummy
    $this->lapak = Lapak::create([
        'nama_lapak' => 'Lapak Utama',
        'alamat' => 'Jl. Sudirman No. 1',
    ]);

    // 3. Tambahkan email_verified_at agar lolos dari middleware 'verified'
    $this->userSales = User::create([
        'name' => 'Kasir Sales',
        'email' => 'sales@beekopi.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'role_id' => $this->roleSales->id,
        'lapak_id' => $this->lapak->id,
    ]);

    // 4. Buat produk dummy
    $this->produk = Produk::create([
        'nama_produk' => 'Kopi Susu Aren',
        'kategori' => 'Kopi',
        'harga' => 15000,
        'stok_pusat' => 50,
        'status' => 'aktif',
    ]);

    // 5. Set stok awal di lapak 10 cup
    $this->stokLapak = StokLapak::create([
        'lapak_id' => $this->lapak->id,
        'produk_id' => $this->produk->id,
        'jumlah' => 10,
    ]);
});

test('user sales dapat mengakses halaman kasir pos', function () {
    $this->withoutExceptionHandling();

    $this->actingAs($this->userSales)
        ->get(route('sales.kasir'))
        ->assertStatus(200);
});

test('kasir dapat menambahkan produk dan memproses transaksi', function () {
    $this->withoutExceptionHandling();

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
