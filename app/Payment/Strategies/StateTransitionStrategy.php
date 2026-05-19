<?php

namespace App\Payment\Strategies;

use App\Models\EstadoPago;
use App\Models\LogPago;
use App\Models\Pago;
use App\Payment\Exceptions\InvalidStateTransitionException;
use App\Payment\Exceptions\UnknownStateException;
use App\Payment\States\ApprovedState;
use App\Payment\States\CancelledState;
use App\Payment\States\PaymentStateInterface;
use App\Payment\States\PendingState;
use App\Payment\States\ProcessingState;
use App\Payment\States\RejectedState;
use Illuminate\Support\Facades\DB;

class StateTransitionStrategy implements PaymentStrategyInterface
{
    private array $stateMap = [];

    public function __construct()
    {
        $this->initializeStateMap();
    }

    public function execute(array $context): array
    {
        $pagoId = $context['pago_id'] ?? null;
        $nuevoEstado = $context['nuevo_estado'] ?? null;

        if (! $pagoId || ! $nuevoEstado) {
            throw new \InvalidArgumentException('pago_id y nuevo_estado son requeridos');
        }

        $pago = Pago::findOrFail($pagoId);
        $estadoPago = EstadoPago::where('espa_estado', $nuevoEstado)->firstOrFail();

        $estadoActual = $pago->estadoPago->espa_estado;
        $stateHandler = $this->getStateHandler($nuevoEstado);

        if (! $stateHandler->canTransitionTo($this->getStateHandler($estadoActual))) {
            throw new InvalidStateTransitionException(
                "No se puede transitar de {$estadoActual} a {$nuevoEstado}"
            );
        }

        $result = DB::transaction(function () use ($pago, $estadoPago, $stateHandler, $context) {
            $context = $stateHandler->onExit($context);

            $pago->update(['estado_pago_id' => $estadoPago->id]);

            LogPago::create([
                'pagos_id' => $pago->id,
                'lopa_descripcion' => "Transición de estado: {$context['estado_anterior']} -> {$context['nuevo_estado']}",
                'estado_pago_id' => $estadoPago->id,
            ]);

            $context = $stateHandler->onEnter($context);

            return $context;
        });

        return array_merge($context, [
            'pago' => $pago->fresh(),
            'estado_anterior' => $estadoActual,
            'estado_actual' => $nuevoEstado,
        ]);
    }

    public function getName(): string
    {
        return 'state_transition';
    }

    public function canExecute(array $context): bool
    {
        return isset($context['pago_id']) && isset($context['nuevo_estado']);
    }

    private function initializeStateMap(): void
    {
        $this->stateMap = [
            'pendiente' => PendingState::class,
            'procesando' => ProcessingState::class,
            'aprobado' => ApprovedState::class,
            'rechazado' => RejectedState::class,
            'anulado' => CancelledState::class,
        ];
    }

    private function getStateHandler(string $stateName): PaymentStateInterface
    {
        $handlerClass = $this->stateMap[$stateName] ?? null;

        if (! $handlerClass) {
            throw new UnknownStateException("Estado desconocido: {$stateName}");
        }

        return new $handlerClass;
    }
}
