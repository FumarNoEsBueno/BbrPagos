<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPago extends Model
{
    protected $table = 'log_pago';

    protected $fillable = [
        'pagos_id',
        'lopa_descripcion',
        'estado_pago_id',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pagos_id');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPago::class, 'estado_pago_id');
    }
}
