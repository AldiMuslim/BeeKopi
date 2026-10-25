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
    $this->roleSales = Role::firstOrCreate(
        ['nama_role' => 'Sales']
    );

    $this->lapak = Lapak::create([
        'nama_lapak' => 'Lapak Utama',
        'alamat' => 'Jl. Sudirman No. 1',
    ]);

    $this->userSales = User::create([
        'name' => 'Kasir Sales',
        'email' => 'sales@beekopi.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
    ]);

    $this->userSales->forceFill([
        'role_id' => $this->roleSales->id,
        'lapak_id' => $this->lapak->id,
    ])->save();

    $this->produk = Produk::create([
        'nama_produk' => 'Kopi Susu Aren',
        'kategori' => 'Kopi',
        'harga' => 15000,
        'stok_pusat' => 50,
        'status' => 'aktif',
    ]);

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

test('komponen kasir lapak dapat dirender dengan benar', function () {
    Livewire::actingAs($this->userSales)
        ->test(KasirLapak::class)
        ->assertStatus(200)
        ->assertSee('Kopi Susu Aren');
});
