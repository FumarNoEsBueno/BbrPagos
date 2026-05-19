<?php

namespace App\Payment\States;

use App\Models\EstadoPago;

class ProcessingState implements PaymentStateInterface
{
    public function getName(): string
    {
        return 'procesando';
    }

    public function getEstadoPagoId(): int
    {
        return EstadoPago::where('espa_estado', 'procesando')->first()->id;
    }

    public function canTransitionTo(PaymentStateInterface $newState): bool
    {
        return in_array($newState->getName(), ['aprobado', 'rechazado', 'anulado']);
    }

    public function onEnter(array $context): array
    {
        $context['processing_started_at'] = now()->toIso8601String();

        return $context;
    }

    public function onExit(array $context): array
    {
        $context['processing_ended_at'] = now()->toIso8601String();

        return $context;
    }
}
