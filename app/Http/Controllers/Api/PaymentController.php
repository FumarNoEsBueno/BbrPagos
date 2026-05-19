<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\InitiateTransactionRequest;
use App\Http\Requests\Payment\RetrieveQrRequest;
use App\Http\Requests\Payment\TransitionStateRequest;
use App\Models\Pago;
use App\Services\PaymentOrchestrator;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    use ApiResponser;

    public function __construct(
        private PaymentOrchestrator $orchestrator
    ) {}

    public function retrieveQr(RetrieveQrRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator
                ->setContext($request->validated())
                ->getQr()
                ->getContext();

            return $this->successResponse([
                'qr_id' => $result['qr_id'],
                'qr_imagen' => $result['qr_imagen'],
                'monto' => $result['monto'],
            ], 'QR generado correctamente.', 200);
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage(), 400, $e, __METHOD__);
        }
    }

    public function initiateTransaction(InitiateTransactionRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator
                ->setContext($request->validated())
                ->initTransaction()
                ->getContext();

            return $this->successResponse([
                'pago_id' => $result['pago_id'],
                'numero_orden' => $result['pago']->pago_numero_orden,
                'estado' => $result['estado_actual'],
                'monto' => $result['monto'],
            ], 'Transacción iniciada correctamente.', 201);
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage(), 400, $e, __METHOD__);
        }
    }

    public function transitionState(TransitionStateRequest $request, int $pagoId): JsonResponse
    {
        try {
            $result = $this->orchestrator
                ->setContext(array_merge($request->validated(), ['pago_id' => $pagoId]))
                ->transitionTo($request->validated('nuevo_estado'))
                ->getContext();

            return $this->successResponse([
                'pago_id' => $result['pago_id'],
                'estado_anterior' => $result['estado_anterior'],
                'estado_actual' => $result['estado_actual'],
            ], 'Estado actualizado correctamente.', 200);
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage(), 400, $e, __METHOD__);
        }
    }

    public function getStatus(int $pagoId): JsonResponse
    {
        try {
            $pago = Pago::with(['estadoPago', 'dispositivo', 'origen', 'banco', 'logPagos'])
                ->findOrFail($pagoId);

            return $this->successResponse($pago, 'Estado del pago.', 200);
        } catch (Exception $e) {
            return $this->errorResponse([], 'Pago no encontrado.', 404, $e, __METHOD__);
        }
    }
}
