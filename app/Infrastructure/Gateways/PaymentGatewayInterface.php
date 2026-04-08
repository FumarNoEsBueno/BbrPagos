<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateways;

use App\Domain\ValueObjects\PaymentMethod;

interface PaymentGatewayInterface
{
    /**
     * Procesa un cargo/pago.
     * 
     * @param float $amount Monto a cargar
     * @param PaymentMethod $paymentMethod Método de pago
     * @param array $metadata Metadata adicional
     * @return array Resultado con claves: success (bool), id (string|null), error (string|null), reference (string|null)
     */
    public function charge(float $amount, PaymentMethod $paymentMethod, array $metadata = []): array;

    /**
     * Refunde una transacción.
     * 
     * @param string $transactionId ID de la transacción original
     * @param float|null $amount Monto a reembolsar (null = total)
     * @return array Resultado con claves: success (bool), refund_id (string|null), error (string|null)
     */
    public function refund(string $transactionId, ?float $amount = null): array;

    /**
     * Consulta el estado de una transacción.
     * 
     * @param string $transactionId ID de la transacción
     * @return array Resultado con claves: status, amount, payment_method, created_at, etc.
     */
    public function getStatus(string $transactionId): array;

    /**
     * Verifica si el gateway está disponible.
     */
    public function isAvailable(): bool;
}
