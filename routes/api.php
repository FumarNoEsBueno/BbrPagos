<?php

use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema de Pagos
|--------------------------------------------------------------------------
*/

Route::prefix('v1/pagos')->group(function () {

    // Health check
    Route::get('/health', fn () => response()->json(['status' => 'ok']));

    // QR
    Route::post('/qr', [PaymentController::class, 'retrieveQr']);

    // Transacción
    Route::post('/init', [PaymentController::class, 'initiateTransaction']);

    // Estados
    Route::get('/{pagoId}', [PaymentController::class, 'getStatus']);
    Route::put('/{pagoId}/transition', [PaymentController::class, 'transitionState']);

});
