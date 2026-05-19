<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_pago', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('pagos_id');
            $table->string('lopa_descripcion', 500);
            $table->integer('estado_pago_id');

            $table->index('pagos_id', 'fk_log_pago_pagos1_idx');
            $table->index('estado_pago_id', 'fk_log_pago_estado_pago1_idx');

            $table->foreign('pagos_id', 'fk_log_pago_pagos1')
                ->references('id')->on('pagos');
            $table->foreign('estado_pago_id', 'fk_log_pago_estado_pago1')
                ->references('id')->on('estado_pago');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_pago');
    }
};
