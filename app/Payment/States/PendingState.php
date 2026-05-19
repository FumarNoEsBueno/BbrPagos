<?php

namespace App\Payment\States;

use App\Models\EstadoPago;

class PendingState implements PaymentStateInterface
{
    public function getName(): string
    {
        return 'pendiente';
    }

    public function getEstadoPagoId(): int
    {
        return EstadoPago::where('espa_estado', 'pendiente')->first()->id;
    }

    public function canTransitionTo(PaymentStateInterface $newState): bool
    {
        return in_array($newState->getName(), ['procesando', 'anulado']);
    }

    public function onEnter(array $context): array
    {
        $context['entered_at'] = now()->toIso8601String();

        return $context;
    }

    public function onExit(array $context): array
    {
        $context['exited_at'] = now()->toIso8601String();

        return $context;
    }
}
