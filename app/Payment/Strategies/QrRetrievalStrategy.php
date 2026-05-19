<?php

namespace App\Payment\Strategies;

class QrRetrievalStrategy implements PaymentStrategyInterface
{
    public function execute(array $context): array
    {
        $monto = $context['monto'] ?? 0;
        $dispositivoId = $context['dispositivo_id'] ?? null;
        $origenId = $context['origen_id'] ?? null;

        $qrData = $this->fetchQrFromApi($monto, $dispositivoId, $origenId);

        return array_merge($context, [
            'qr_data' => $qrData,
            'qr_id' => $qrData['id'] ?? null,
            'qr_imagen' => $qrData['imagen'] ?? null,
        ]);
    }

    public function getName(): string
    {
        return 'qr_retrieval';
    }

    public function canExecute(array $context): bool
    {
        return isset($context['monto']) && $context['monto'] > 0;
    }

    private function fetchQrFromApi(int $monto, ?int $dispositivoId, ?int $origenId): array
    {
        // TODO: Implementar llamada a API de QR
        // Este método debe ser reemplazado con la implementación real de la API
        return [
            'id' => uniqid('qr_'),
            'imagen' => 'base64_qr_image_placeholder',
            'monto' => $monto,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
