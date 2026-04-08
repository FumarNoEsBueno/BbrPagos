<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case TRANSFER = 'transfer';
    case QR = 'qr';
    case WALLET = 'wallet';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Efectivo',
            self::CARD => 'Tarjeta',
            self::TRANSFER => 'Transferencia',
            self::QR => 'Código QR',
            self::WALLET => 'Billetera Digital',
            self::OTHER => 'Otro',
        };
    }

    public function requiresExternalGateway(): bool
    {
        return in_array($this, [self::CARD, self::QR, self::WALLET]);
    }
}
