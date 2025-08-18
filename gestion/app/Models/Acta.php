<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acta extends Model
{
    protected $fillable = [
        'nro_acta',
        'nombre_acta',
        'fecha',
        'descripcion',
        'archivo_contenido',
        'archivo_nombre',
        'archivo_tipo',
        'archivo_tamaño'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];
}
