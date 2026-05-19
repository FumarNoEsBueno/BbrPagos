<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    protected $table = 'banco';

    protected $fillable = [
        'banc_nombre',
        'banc_habilitado',
    ];

    protected $casts = [
        'banc_habilitado' => 'boolean',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'banco_id');
    }
}
