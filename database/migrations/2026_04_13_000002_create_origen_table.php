<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('origen', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('orig_nombre', 255);
            $table->boolean('orig_habilitado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('origen');
    }
};
