<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'apartamento_id',
        'space_id',
        'fecha_reserva',
        'monto',
        'estado',
        'observaciones',
        'recibo_id'
    ];

    protected $casts = [
        'fecha_reserva' => 'date',
        'monto' => 'decimal:2'
    ];

    // Relación con apartamento
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class);
    }

    // Relación con espacio
    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    // Relación con recibo
    public function recibo()
    {
        return $this->belongsTo(ReciboGastoComun::class, 'recibo_id');
    }

    // Scope para reservas confirmadas
    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmada');
    }

    // Scope para reservas pendientes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    // Verificar si una fecha está disponible para un espacio
    public static function isDateAvailable($spaceId, $date, $excludeReservationId = null)
    {
        $query = self::where('space_id', $spaceId)
                    ->where('fecha_reserva', $date)
                    ->whereIn('estado', ['pendiente', 'confirmada']);
        
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }
        
        return $query->count() === 0;
    }
}
