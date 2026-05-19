<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $table = 'parametros';

    protected $fillable = [
        'para_codigo',
        'para_valor',
        'para_habilitado',
    ];

    protected $casts = [
        'para_habilitado' => 'boolean',
    ];
}
