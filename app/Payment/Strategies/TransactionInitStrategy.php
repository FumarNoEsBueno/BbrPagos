<?php

namespace App\Payment\Strategies;

use App\Models\EstadoPago;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class TransactionInitStrategy implements PaymentStrategyInterface
{
    public function execute(array $context): array
    {
        $pago = DB::transaction(function () use ($context) {
            $estadoInicial = EstadoPago::where('espa_estado', 'pendiente')->firstOrFail();

            return Pago::create([
                'pago_id_transaccion' => $context['qr_id'] ?? uniqid('txn_'),
                'pago_propina' => $context['propina'] ?? null,
                'pago_monto' => $context['monto'],
                'pago_fecha_ingreso' => now()->toDateString(),
                'pago_iva' => $this->calcularIva($context['monto']),
                'pago_numero_orden' => $this->generarNumeroOrden(),
                'dispositivo_id' => $context['dispositivo_id'],
                'origen_id' => $context['origen_id'],
                'banco_id' => $context['banco_id'],
                'estado_pago_id' => $estadoInicial->id,
            ]);
        });

        return array_merge($context, [
            'pago_id' => $pago->id,
            'pago' => $pago,
            'estado_actual' => 'pendiente',
        ]);
    }

    public function getName(): string
    {
        return 'transaction_init';
    }

    public function canExecute(array $context): bool
    {
        return isset($context['qr_id']) && isset($context['monto']);
    }

    private function calcularIva(int $monto): int
    {
        return (int) round($monto * 0.19);
    }

    private function generarNumeroOrden(): string
    {
        return 'ORD-'.date('Ymd').'-'.str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
