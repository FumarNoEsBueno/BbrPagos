<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class Money
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency = 'MXN'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
        
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter code');
        }
    }

    public static function fromCents(int $cents, string $currency = 'MXN'): self
    {
        return new self($cents / 100, $currency);
    }

    public function toCents(): int
    {
        return (int) round($this->amount * 100);
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);
        $result = $this->amount - $other->amount;
        
        if ($result < 0) {
            throw new InvalidArgumentException('Result cannot be negative');
        }
        
        return new self($result, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount < $other->amount;
    }

    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency 
            && abs($this->amount - $other->amount) < 0.001;
    }

    public function format(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($other->currency !== $this->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
