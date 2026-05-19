<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('pago_id_transaccion', 45);
            $table->integer('pago_propina')->nullable();
            $table->integer('pago_monto');
            $table->date('pago_fecha_ingreso');
            $table->integer('pago_iva');
            $table->string('pago_numero_orden', 45);
            $table->integer('dispositivo_id');
            $table->integer('origen_id');
            $table->integer('banco_id');
            $table->integer('estado_pago_id');

            $table->index('dispositivo_id', 'fk_pagos_dispositivo1_idx');
            $table->index('origen_id', 'fk_pagos_origen1_idx');
            $table->index('banco_id', 'fk_pagos_banco1_idx');
            $table->index('estado_pago_id', 'fk_pagos_estado_pago1_idx');

            $table->foreign('dispositivo_id', 'fk_pagos_dispositivo1')
                ->references('id')->on('dispositivo');
            $table->foreign('origen_id', 'fk_pagos_origen1')
                ->references('id')->on('origen');
            $table->foreign('banco_id', 'fk_pagos_banco1')
                ->references('id')->on('banco');
            $table->foreign('estado_pago_id', 'fk_pagos_estado_pago1')
                ->references('id')->on('estado_pago');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
