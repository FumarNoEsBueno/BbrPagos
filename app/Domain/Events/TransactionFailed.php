<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Entities\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public string $reason,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}
