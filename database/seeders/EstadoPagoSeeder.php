<?php

namespace Database\Seeders;

use App\Models\EstadoPago;
use Illuminate\Database\Seeder;

class EstadoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['espa_estado' => 'pre-generada', 'espa_habilitado' => true],
            ['espa_estado' => 'generada', 'espa_habilitado' => true],
            ['espa_estado' => 'impresa', 'espa_habilitado' => true],
            ['espa_estado' => 'en proceso', 'espa_habilitado' => true],
            ['espa_estado' => 'pagado', 'espa_habilitado' => true],
            ['espa_estado' => 'anulado', 'espa_habilitado' => true],
            ['espa_estado' => 'ingreso manual', 'espa_habilitado' => true],
        ];

        foreach ($estados as $estado) {
            EstadoPago::create([
                ['espa_estado' => $estado['espa_estado']],
                ['espa_habilitado' => $estado['espa_habilitado']]
            ]);
        }
    }
}
