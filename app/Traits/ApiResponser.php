<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

trait ApiResponser
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse($data, string $mensaje, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'datos' => $data,
            'mensaje' => $mensaje,
        ], $statusCode);
    }

    /**
     * Return an error JSON response with optional exception logging.
     */
    protected function errorResponse(
        $data,
        string $mensaje,
        int $statusCode = 422,
        ?\Throwable $excepcion = null,
        ?string $metodo = null
    ): JsonResponse {
        if ($excepcion !== null && $metodo !== null) {
            Log::error([
                'Mensaje' => $excepcion->getMessage(),
                'Linea' => $excepcion->getLine(),
                'Archivo' => $excepcion->getFile(),
                'Metodo' => $metodo,
            ]);
        }

        return response()->json([
            'success' => false,
            'datos' => $data,
            'mensaje' => $mensaje,
        ], $statusCode);
    }

    /**
     * Return a validation error response from a Validator.
     */
    protected function requestResponse(Validator $validator, string $mensaje = 'No se pueden procesar los campos'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'datos' => $validator->errors()->all(),
            'mensaje' => $mensaje,
        ], 409);
    }
}
