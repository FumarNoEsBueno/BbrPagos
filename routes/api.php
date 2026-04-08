<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Controllers\Api\PosController;

/*
|--------------------------------------------------------------------------
| API Routes - Caja Registradora (POS)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Health check
    Route::get('/health', [PosController::class, 'health']);

    // Órdenes
    Route::prefix('orders')->group(function () {
        Route::get('/', [PosController::class, 'listOrders']);
        Route::post('/', [PosController::class, 'createOrder']);
        Route::get('/{orderUuid}', [PosController::class, 'getOrder']);
        Route::post('/{orderUuid}/items', [PosController::class, 'addItem']);
        Route::delete('/{orderUuid}/items/{itemId}', [PosController::class, 'removeItem']);
        Route::post('/{orderUuid}/pay', [PosController::class, 'processPayment']);
        Route::post('/{orderUuid}/cancel', [PosController::class, 'cancelOrder']);
    });

    // Productos
    Route::get('/products', [PosController::class, 'listProducts']);
});
