<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Api;

use App\Application\Services\TransactionService;
use App\Domain\Entities\Order;
use App\Domain\Entities\Product;
use App\Domain\Exceptions\TransactionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class PosController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    /**
     * Crear una nueva orden (ticket de caja).
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $order = Order::create([
                    'notes' => $request->input('notes'),
                    'created_by' => $request->input('user_id'), // TODO: obtener de auth
                ]);

                // Agregar items si se envían
                $items = $request->input('items', []);
                foreach ($items as $item) {
                    $order->addItem([
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }

                return $order->fresh(['items']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $this->formatOrder($order),
                ],
                'message' => 'Orden creada exitosamente',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating order', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al crear la orden',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Agregar item a una orden existente.
     */
    public function addItem(Request $request, string $orderUuid): JsonResponse
    {
        $order = Order::where('uuid', $orderUuid)->firstOrFail();

        if (!$order->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => 'La orden no puede ser modificada',
            ], 422);
        }

        try {
            $item = $order->addItem([
                'product_id' => $request->input('product_id'),
                'product_name' => $request->input('product_name'),
                'quantity' => $request->input('quantity'),
                'unit_price' => $request->input('unit_price'),
            ]);

            $order->refresh(['items']);

            return response()->json([
                'success' => true,
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ],
                    'order' => $this->formatOrder($order),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar item de una orden.
     */
    public function removeItem(string $orderUuid, int $itemId): JsonResponse
    {
        $order = Order::where('uuid', $orderUuid)->firstOrFail();

        if (!$order->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => 'La orden no puede ser modificada',
            ], 422);
        }

        try {
            $order->removeItem($itemId);
            $order->refresh(['items']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $this->formatOrder($order),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar pago de una orden.
     */
    public function processPayment(Request $request, string $orderUuid): JsonResponse
    {
        $order = Order::where('uuid', $orderUuid)
            ->with('items')
            ->firstOrFail();

        try {
            $transaction = $this->transactionService->processPayment($order, [
                'payment_method' => $request->input('payment_method', 'cash'),
                'metadata' => $request->input('metadata', []),
            ]);

            $order->refresh(['items', 'transaction']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $this->formatOrder($order),
                    'transaction' => [
                        'uuid' => $transaction->uuid,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'payment_method' => $transaction->payment_method,
                        'external_id' => $transaction->external_id,
                        'processed_at' => $transaction->processed_at?->toIso8601String(),
                    ],
                ],
                'message' => 'Pago procesado exitosamente',
            ]);

        } catch (TransactionException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'order_uuid' => $orderUuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar el pago',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Obtener orden por UUID.
     */
    public function getOrder(string $orderUuid): JsonResponse
    {
        $order = Order::where('uuid', $orderUuid)
            ->with(['items', 'transaction'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Orden no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $this->formatOrder($order),
            ],
        ]);
    }

    /**
     * Listar órdenes (con filtros).
     */
    public function listOrders(Request $request): JsonResponse
    {
        $query = Order::with(['items', 'transaction']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->map(fn($o) => $this->formatOrder($o)),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ],
        ]);
    }

    /**
     * Cancelar una orden.
     */
    public function cancelOrder(Request $request, string $orderUuid): JsonResponse
    {
        $order = Order::where('uuid', $orderUuid)->firstOrFail();

        if ($order->isPaid()) {
            return response()->json([
                'success' => false,
                'error' => 'No se puede cancelar una orden pagada',
            ], 422);
        }

        $order->cancel($request->input('reason', 'Cancelada por el usuario'));

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $this->formatOrder($order),
            ],
            'message' => 'Orden cancelada',
        ]);
    }

    /**
     * Obtener productos disponibles.
     */
    public function listProducts(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true);

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products->map(fn($p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'price' => $p->price,
                    'stock' => $p->stock,
                    'category' => $p->category,
                ]),
            ],
        ]);
    }

    /**
     * Health check para la API.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'uuid' => $order->uuid,
            'status' => $order->status,
            'status_label' => $order->status,
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'notes' => $order->notes,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'created_at' => $order->created_at->toIso8601String(),
            'items' => $order->items->map(fn($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ]),
            'transaction' => $order->transaction ? [
                'uuid' => $order->transaction->uuid,
                'status' => $order->transaction->status,
                'payment_method' => $order->transaction->payment_method,
                'external_id' => $order->transaction->external_id,
            ] : null,
        ];
    }
}
