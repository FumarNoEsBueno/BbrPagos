<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\OrderStatus;
use App\Domain\Events\OrderCreated;
use App\Domain\Events\OrderUpdated;
use App\Domain\Events\OrderPaid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'uuid',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => OrderCreated::class,
        'updated' => OrderUpdated::class,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function create(array $data): self
    {
        $order = new self([
            'uuid' => Str::uuid()->toString(),
            'status' => OrderStatus::DRAFT->value,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);

        $order->calculateTotals();
        $order->save();

        return $order;
    }

    public function addItem(array $itemData): OrderItem
    {
        $item = $this->items()->create([
            'product_id' => $itemData['product_id'],
            'product_name' => $itemData['product_name'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
        ]);

        $this->calculateTotals();
        $this->save();

        return $item;
    }

    public function removeItem(int $itemId): bool
    {
        $item = $this->items()->findOrFail($itemId);
        $item->delete();
        
        $this->calculateTotals();
        $this->save();

        return true;
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $taxRate = 0.16; // 16% IVA configurable
        $taxAmount = $subtotal * $taxRate;
        
        $this->subtotal = round($subtotal, 2);
        $this->tax_amount = round($taxAmount, 2);
        $this->total_amount = round($subtotal + $taxAmount, 2);
    }

    public function markAsPaid(Transaction $transaction): void
    {
        $this->update([
            'status' => OrderStatus::PAID->value,
            'paid_at' => now(),
        ]);

        $this->transaction()->associate($transaction);
        $this->save();

        event(new OrderPaid($this));
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => OrderStatus::CANCELLED->value,
            'notes' => $this->notes . "\n[CANCELADO]: {$reason}",
        ]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            OrderStatus::DRAFT->value,
            OrderStatus::PENDING->value,
        ]);
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID->value;
    }
}
