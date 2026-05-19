<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends Model
{
    protected $table = 'dispositivo';

    protected $fillable = [
        'disp_nombre',
        'disp_terminal',
        'disp_numero_serie',
        'disp_estado',
    ];

    protected $casts = [
        'disp_estado' => 'boolean',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'dispositivo_id');
    }
}
