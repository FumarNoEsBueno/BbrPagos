<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EstadoPagoSeeder::class,
            OrigenSeeder::class,
            DispositivoSeeder::class,
            BancoSeeder::class,
        ]);
    }
}
