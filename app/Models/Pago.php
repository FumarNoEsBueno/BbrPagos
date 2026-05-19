<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'pago_id_transaccion',
        'pago_propina',
        'pago_monto',
        'pago_fecha_ingreso',
        'pago_iva',
        'pago_numero_orden',
        'dispositivo_id',
        'origen_id',
        'banco_id',
        'estado_pago_id',
    ];

    protected $casts = [
        'pago_fecha_ingreso' => 'date',
        'pago_monto' => 'integer',
        'pago_iva' => 'integer',
        'pago_propina' => 'integer',
    ];

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'dispositivo_id');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Origen::class, 'origen_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado_pago_id');
    }

    public function logPagos(): HasMany
    {
        return $this->hasMany(LogPago::class, 'pagos_id');
    }
}
