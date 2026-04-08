<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\TransactionStatus;
use App\Domain\ValueObjects\PaymentMethod;
use App\Domain\Events\TransactionCreated;
use App\Domain\Events\TransactionCompleted;
use App\Domain\Events\TransactionFailed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'order_id',
        'status',
        'amount',
        'payment_method',
        'gateway_response',
        'gateway_reference',
        'external_id',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => TransactionCreated::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public static function createForOrder(Order $order, array $data): self
    {
        $transaction = new self([
            'uuid' => Str::uuid()->toString(),
            'order_id' => $order->id,
            'status' => TransactionStatus::PENDING->value,
            'amount' => $order->total_amount,
            'payment_method' => $data['payment_method'] ?? PaymentMethod::CASH->value,
            'metadata' => $data['metadata'] ?? [],
        ]);

        $transaction->save();

        return $transaction;
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => TransactionStatus::PROCESSING->value]);
    }

    public function markAsCompleted(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => TransactionStatus::COMPLETED->value,
            'gateway_response' => $gatewayResponse,
            'external_id' => $gatewayResponse['id'] ?? $gatewayResponse['reference'] ?? null,
            'processed_at' => now(),
        ]);

        event(new TransactionCompleted($this));
    }

    public function markAsFailed(string $reason, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => TransactionStatus::FAILED->value,
            'gateway_response' => array_merge($gatewayResponse, ['failure_reason' => $reason]),
            'processed_at' => now(),
        ]);

        event(new TransactionFailed($this, $reason));
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED->value;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::FAILED->value;
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING->value;
    }

    public function canBeRetried(): bool
    {
        return in_array($this->status, [
            TransactionStatus::FAILED->value,
            TransactionStatus::PENDING->value,
        ]);
    }
}
