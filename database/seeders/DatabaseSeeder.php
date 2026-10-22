<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LapakSeeder::class,
            UserSeeder::class,
            BahanBakuSeeder::class,
            ProdukSeeder::class,
            StokLapakSeeder::class,
            ResepSeeder::class,
        ]);
    }
}