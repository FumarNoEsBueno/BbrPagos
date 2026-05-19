<?php

namespace Database\Seeders;

use App\Models\Origen;
use Illuminate\Database\Seeder;

class OrigenSeeder extends Seeder
{
    public function run(): void
    {
        $origenes = [
            ['orig_nombre' => 'Flujo Caja', 'orig_habilitado' => true],
            ['orig_nombre' => 'App Móvil', 'orig_habilitado' => true],
        ];

        foreach ($origenes as $origen) {
            Origen::create([
                ['orig_nombre' => $origen['orig_nombre']],
                ['orig_habilitado' => $origen['orig_habilitado']]
            ]);
        }
    }
}
