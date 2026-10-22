<?php

namespace Database\Seeders;

use App\Models\Lapak;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('nama', 'admin')->first();
        $managementRole = Role::where('nama', 'management')->first();
        $produksiRole = Role::where('nama', 'produksi')->first();
        $salesRole = Role::where('nama', 'sales')->first();

        $lapakA = Lapak::where('nama_lapak', 'like', '%Lapak A%')->first();
        $lapakB = Lapak::where('nama_lapak', 'like', '%Lapak B%')->first();

        User::create([
            'name' => 'Admin Bee Kopi',
            'email' => 'admin@beekopi.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Manager Bee Kopi',
            'email' => 'management@beekopi.com',
            'password' => Hash::make('password'),
            'role_id' => $managementRole->id,
        ]);

        User::create([
            'name' => 'Tim Gudang Pusat',
            'email' => 'produksi@beekopi.com',
            'password' => Hash::make('password'),
            'role_id' => $produksiRole->id,
        ]);

        User::create([
            'name' => 'Sales Lapak A',
            'email' => 'salesa@beekopi.com',
            'password' => Hash::make('password'),
            'role_id' => $salesRole->id,
            'lapak_id' => $lapakA?->id,
        ]);

        User::create([
            'name' => 'Sales Lapak B',
            'email' => 'salesb@beekopi.com',
            'password' => Hash::make('password'),
            'role_id' => $salesRole->id,
            'lapak_id' => $lapakB?->id,
        ]);
    }
}