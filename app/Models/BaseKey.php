<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseKey extends Model
{
    protected $table = 'base_key';

    protected $fillable = [
        'bake_base_codigo',
        'bake_habilitado',
    ];

    protected $casts = [
        'bake_habilitado' => 'boolean',
    ];
}
