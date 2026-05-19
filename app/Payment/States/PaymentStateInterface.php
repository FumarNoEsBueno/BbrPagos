<?php

namespace App\Payment\States;

interface PaymentStateInterface
{
    public function getName(): string;

    public function getEstadoPagoId(): int;

    public function canTransitionTo(PaymentStateInterface $newState): bool;

    public function onEnter(array $context): array;

    public function onExit(array $context): array;
}
