<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['nama' => 'admin', 'label' => 'Administrator System']);
        Role::create(['nama' => 'management', 'label' => 'Management / Pemilik']);
        Role::create(['nama' => 'produksi', 'label' => 'Tim Produksi / Gudang']);
        Role::create(['nama' => 'sales', 'label' => 'Sales Lapak']);
    }
}