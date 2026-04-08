<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateways;

use App\Domain\ValueObjects\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Gateway de ejemplo para desarrollo/pruebas.
 * Reemplazar con la implementación real del provider de pagos.
 */
class MockPaymentGateway implements PaymentGatewayInterface
{
    private array $transactions = [];

    public function charge(float $amount, PaymentMethod $paymentMethod, array $metadata = []): array
    {
        $transactionId = Str::uuid()->toString();

        Log::info('MockPaymentGateway: Processing charge', [
            'amount' => $amount,
            'paymentMethod' => $paymentMethod->value,
            'metadata' => $metadata,
        ]);

        // Simular procesamiento (en desarrollo, siempre exitoso)
        // En producción, esto llamaría a la API del provider
        $this->transactions[$transactionId] = [
            'id' => $transactionId,
            'amount' => $amount,
            'payment_method' => $paymentMethod->value,
            'status' => 'completed',
            'metadata' => $metadata,
            'created_at' => now()->toIso8601String(),
        ];

        return [
            'success' => true,
            'id' => $transactionId,
            'reference' => 'REF-' . strtoupper(Str::random(8)),
            'status' => 'completed',
        ];
    }

    public function refund(string $transactionId, ?float $amount = null): array
    {
        if (!isset($this->transactions[$transactionId])) {
            return [
                'success' => false,
                'error' => 'Transaction not found',
            ];
        }

        $refundId = 'REF-' . Str::uuid()->toString();

        return [
            'success' => true,
            'refund_id' => $refundId,
        ];
    }

    public function getStatus(string $transactionId): array
    {
        return $this->transactions[$transactionId] ?? [
            'error' => 'Transaction not found',
        ];
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
