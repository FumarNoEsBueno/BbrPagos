<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositivo', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('disp_nombre', 45);
            $table->string('disp_terminal', 45);
            $table->string('disp_numero_serie', 45);
            $table->boolean('disp_estado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositivo');
    }
};
