<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fondo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'saldo_usd',
        'saldo_bs',
    ];

    protected $casts = [
        'saldo_usd' => 'decimal:2',
        'saldo_bs' => 'decimal:2',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoFondo::class)->orderBy('fecha', 'desc')->orderBy('created_at', 'desc');
    }
}