<?php

namespace Database\Seeders;

use App\Models\Banco;
use Illuminate\Database\Seeder;

class BancoSeeder extends Seeder
{
    public function run(): void
    {
        $bancos = [
            ['banc_nombre' => 'Banco Estado', 'banc_habilitado' => true],
            ['banc_nombre' => 'Banco de Chile', 'banc_habilitado' => false],
            ['banc_nombre' => 'Banco Santander', 'banc_habilitado' => false],
            ['banc_nombre' => 'Banco BCI', 'banc_habilitado' => false],
            ['banc_nombre' => 'Banco Itaú', 'banc_habilitado' => false],
            ['banc_nombre' => 'Scotiabank', 'banc_habilitado' => false],
            ['banc_nombre' => 'Banco Falabella', 'banc_habilitado' => false],
            ['banc_nombre' => 'Banco Ripley', 'banc_habilitado' => false],
        ];

        foreach ($bancos as $banco) {
            Banco::create([
                    ['banc_nombre' => $banco['banc_nombre']],
                    ['banc_habilitado' => $banco['banc_habilitado']]
                ]);
        }
    }
}
