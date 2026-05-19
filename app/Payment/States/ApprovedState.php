<?php

namespace App\Payment\States;

use App\Models\EstadoPago;

class ApprovedState implements PaymentStateInterface
{
    public function getName(): string
    {
        return 'aprobado';
    }

    public function getEstadoPagoId(): int
    {
        return EstadoPago::where('espa_estado', 'aprobado')->first()->id;
    }

    public function canTransitionTo(PaymentStateInterface $newState): bool
    {
        return false;
    }

    public function onEnter(array $context): array
    {
        $context['approved_at'] = now()->toIso8601String();

        return $context;
    }

    public function onExit(array $context): array
    {
        return $context;
    }
}
