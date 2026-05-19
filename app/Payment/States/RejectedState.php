<?php

namespace App\Payment\States;

use App\Models\EstadoPago;

class RejectedState implements PaymentStateInterface
{
    public function getName(): string
    {
        return 'rechazado';
    }

    public function getEstadoPagoId(): int
    {
        return EstadoPago::where('espa_estado', 'rechazado')->first()->id;
    }

    public function canTransitionTo(PaymentStateInterface $newState): bool
    {
        return false;
    }

    public function onEnter(array $context): array
    {
        $context['rejected_at'] = now()->toIso8601String();
        $context['motivo_rechazo'] = $context['motivo_rechazo'] ?? 'Sin motivo especificado';

        return $context;
    }

    public function onExit(array $context): array
    {
        return $context;
    }
}
