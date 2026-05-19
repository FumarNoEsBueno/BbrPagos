<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('banc_nombre', 150);
            $table->boolean('banc_habilitado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco');
    }
};
