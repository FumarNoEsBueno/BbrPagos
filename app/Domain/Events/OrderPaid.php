<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Entities\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}
