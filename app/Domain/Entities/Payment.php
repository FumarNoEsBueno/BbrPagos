<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id',
        'order_id',
        'amount',
        'status',
        'payment_date',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function confirm(): void
    {
        $this->update([
            'status' => PaymentStatus::CONFIRMED->value,
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => PaymentStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function isConfirmed(): bool
    {
        return $this->status === PaymentStatus::CONFIRMED->value;
    }
}
