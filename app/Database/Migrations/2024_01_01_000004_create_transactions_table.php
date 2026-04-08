<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])
                  ->default('pending');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'qr', 'wallet', 'other'])
                  ->default('cash');
            $table->json('gateway_response')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('order_id');
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
