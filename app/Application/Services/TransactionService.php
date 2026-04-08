<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\Order;
use App\Domain\Entities\Transaction;
use App\Domain\Entities\Payment;
use App\Domain\ValueObjects\PaymentMethod;
use App\Domain\ValueObjects\TransactionStatus;
use App\Domain\Exceptions\TransactionException;
use App\Infrastructure\Gateways\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function __construct(
        private readonly PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Procesa una transacción completa de forma atómica.
     * Usa database transactions para garantizar consistencia.
     */
    public function processPayment(Order $order, array $paymentData): Transaction
    {
        // Validaciones previas
        $this->validateOrderForPayment($order);

        return DB::transaction(function () use ($order, $paymentData) {
            // 1. Crear la transacción pendiente
            $transaction = Transaction::createForOrder($order, $paymentData);

            try {
                // 2. Procesar según método de pago
                $paymentMethod = PaymentMethod::from($paymentData['payment_method'] ?? 'cash');

                if ($paymentMethod->requiresExternalGateway()) {
                    // 3a. Procesar vía gateway externo (card, qr, wallet)
                    $transaction->markAsProcessing();
                    $gatewayResult = $this->paymentGateway->charge(
                        amount: (float) $order->total_amount,
                        paymentMethod: $paymentMethod,
                        metadata: [
                            'order_id' => $order->uuid,
                            'transaction_id' => $transaction->uuid,
                        ]
                    );

                    if ($gatewayResult['success']) {
                        $transaction->markAsCompleted($gatewayResult);
                        
                        // 4a. Crear registro de pago confirmado
                        $this->createPaymentRecord($transaction, $order, $gatewayResult);
                        
                        // 5a. Marcar orden como pagada
                        $order->markAsPaid($transaction);

                        Log::info('Transaction completed successfully', [
                            'transaction_uuid' => $transaction->uuid,
                            'order_uuid' => $order->uuid,
                            'amount' => $order->total_amount,
                        ]);
                    } else {
                        $transaction->markAsFailed(
                            $gatewayResult['error'] ?? 'Unknown error',
                            $gatewayResult
                        );
                        
                        throw new TransactionException(
                            "Payment failed: " . ($gatewayResult['error'] ?? 'Unknown')
                        );
                    }
                } else {
                    // 3b. Procesar efectivo (no requiere gateway externo)
                    // En efectivo, el pago se confirma instantáneamente
                    $transaction->markAsCompleted(['processor' => 'cash']);
                    
                    $this->createPaymentRecord($transaction, $order, ['processor' => 'cash']);
                    $order->markAsPaid($transaction);

                    Log::info('Cash transaction completed', [
                        'transaction_uuid' => $transaction->uuid,
                        'order_uuid' => $order->uuid,
                        'amount' => $order->total_amount,
                    ]);
                }

                return $transaction;

            } catch (\Exception $e) {
                // Si algo falla, la transacción de base de datos hace rollback
                Log::error('Transaction processing failed', [
                    'transaction_uuid' => $transaction->uuid,
                    'order_uuid' => $order->uuid,
                    'error' => $e->getMessage(),
                ]);

                if ($transaction->isPending()) {
                    $transaction->markAsFailed($e->getMessage());
                }

                throw $e;
            }
        });
    }

    /**
     * Reintenta una transacción fallida.
     */
    public function retryTransaction(Transaction $transaction): Transaction
    {
        if (!$transaction->canBeRetried()) {
            throw new TransactionException(
                "Transaction {$transaction->uuid} cannot be retried"
            );
        }

        $order = $transaction->order;

        return $this->processPayment($order, [
            'payment_method' => $transaction->payment_method,
        ]);
    }

    /**
     * Cancela una transacción completada (requiere configuración adicional).
     */
    public function cancelTransaction(Transaction $transaction, string $reason): void
    {
        if ($transaction->isCompleted()) {
            // TODO: Implementar refunds si el gateway lo soporta
            throw new TransactionException(
                "Cannot cancel completed transaction. Use refund instead."
            );
        }

        $transaction->update([
            'status' => TransactionStatus::CANCELLED->value,
        ]);
    }

    private function validateOrderForPayment(Order $order): void
    {
        if ($order->isPaid()) {
            throw new TransactionException("Order {$order->uuid} is already paid");
        }

        if ($order->isCancelled()) {
            throw new TransactionException("Order {$order->uuid} is cancelled");
        }

        if ($order->items->isEmpty()) {
            throw new TransactionException("Order {$order->uuid} has no items");
        }

        if ((float) $order->total_amount <= 0) {
            throw new TransactionException("Order {$order->uuid} has invalid amount");
        }
    }

    private function createPaymentRecord(
        Transaction $transaction,
        Order $order,
        array $gatewayResult
    ): Payment {
        return Payment::create([
            'transaction_id' => $transaction->id,
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'status' => 'confirmed',
            'payment_date' => now(),
        ]);
    }
}
