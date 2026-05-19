<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_pago', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('espa_estado', 90);
            $table->boolean('espa_habilitado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_pago');
    }
};
