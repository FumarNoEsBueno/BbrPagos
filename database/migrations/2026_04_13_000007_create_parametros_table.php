<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('para_codigo', 45);
            $table->string('para_valor', 45);
            $table->boolean('para_habilitado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros');
    }
};
