<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_key', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('bake_base_codigo', 45);
            $table->boolean('bake_habilitado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_key');
    }
};
