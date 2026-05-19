<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoPago extends Model
{
    protected $table = 'estado_pago';

    protected $fillable = [
        'espa_estado',
        'espa_habilitado',
    ];

    protected $casts = [
        'espa_habilitado' => 'boolean',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'estado_pago_id');
    }

    public function logPagos(): HasMany
    {
        return $this->hasMany(LogPago::class, 'estado_pago_id');
    }
}
