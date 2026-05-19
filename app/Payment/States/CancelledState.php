<?php

namespace App\Payment\States;

use App\Models\EstadoPago;

class CancelledState implements PaymentStateInterface
{
    public function getName(): string
    {
        return 'anulado';
    }

    public function getEstadoPagoId(): int
    {
        return EstadoPago::where('espa_estado', 'anulado')->first()->id;
    }

    public function canTransitionTo(PaymentStateInterface $newState): bool
    {
        return false;
    }

    public function onEnter(array $context): array
    {
        $context['cancelled_at'] = now()->toIso8601String();
        $context['motivo_anulacion'] = $context['motivo_anulacion'] ?? 'Sin motivo especificado';

        return $context;
    }

    public function onExit(array $context): array
    {
        return $context;
    }
}
