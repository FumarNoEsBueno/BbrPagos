<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum OrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::PENDING => 'Pendiente',
            self::CONFIRMED => 'Confirmado',
            self::PAID => 'Pagado',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Reembolsado',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::PAID, self::CANCELLED, self::REFUNDED]);
    }
}
