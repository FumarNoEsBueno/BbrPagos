<?php

namespace App\Payment\Strategies;

interface PaymentStrategyInterface
{
    public function execute(array $context): array;

    public function getName(): string;

    public function canExecute(array $context): bool;
}
