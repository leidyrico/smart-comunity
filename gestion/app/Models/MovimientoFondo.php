<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoFondo extends Model
{
    use HasFactory;

    protected $table = 'movimientos_fondo';

    protected $fillable = [
        'fondo_id',
        'tipo',
        'monto_usd',
        'monto_bs',
        'fecha',
        'descripcion',
    ];

    protected $casts = [
        'monto_usd' => 'decimal:2',
        'monto_bs' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function fondo()
    {
        return $this->belongsTo(Fondo::class);
    }
}