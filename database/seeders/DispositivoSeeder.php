<?php

namespace Database\Seeders;

use App\Models\Dispositivo;
use Illuminate\Database\Seeder;

class DispositivoSeeder extends Seeder
{
    public function run(): void
    {
        $dispositivos = [
            [
                'disp_nombre' => 'Terminal Principal',
                'disp_terminal' => 'TERM-001',
                'disp_numero_serie' => 'SN-2024-001',
                'disp_estado' => true,
            ],
            [
                'disp_nombre' => 'Terminal Secundaria',
                'disp_terminal' => 'TERM-002',
                'disp_numero_serie' => 'SN-2024-002',
                'disp_estado' => true,
            ],
            [
                'disp_nombre' => 'Terminal Móvil',
                'disp_terminal' => 'TERM-003',
                'disp_numero_serie' => 'SN-2024-003',
                'disp_estado' => true,
            ],
        ];

        foreach ($dispositivos as $dispositivo) {
            Dispositivo::create([
                'disp_nombre' => $dispositivo['disp_nombre'],
                'disp_terminal' => $dispositivo['disp_terminal'],
                'disp_numero_serie' => $dispositivo['disp_numero_serie'],
                'disp_estado' => $dispositivo['disp_estado'],
            ]);
        }
    }
}
