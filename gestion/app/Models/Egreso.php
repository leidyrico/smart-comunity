<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    protected $fillable = [
        'nro_factura',
        'fecha',
        'comprobante',
        'monto',
        'descripcion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2'
    ];
}
