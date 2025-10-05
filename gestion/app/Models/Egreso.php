<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    protected $table = 'egresos_temp_1759253507';

    protected $fillable = [
        'nro_factura',
        'fecha',
        'comprobante',
        'monto',
        'monto_en_bs',
        'descripcion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'monto_en_bs' => 'decimal:2'
    ];
}
