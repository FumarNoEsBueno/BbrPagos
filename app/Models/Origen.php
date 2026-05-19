<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Origen extends Model
{
    protected $table = 'origen';

    protected $fillable = [
        'orig_nombre',
        'orig_habilitado',
    ];

    protected $casts = [
        'orig_habilitado' => 'boolean',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'origen_id');
    }
}
