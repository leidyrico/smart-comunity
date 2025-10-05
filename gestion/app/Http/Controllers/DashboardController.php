<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas básicas
        $totalApartamentos = Apartamento::count();
        
        // Calcular saldo pendiente total usando el atributo calculado
        $apartamentos = Apartamento::all();
        $saldoPendienteTotal = $apartamentos->sum(function($apartamento) {
            return $apartamento->saldo_pendiente;
        });
        
        // 1. Top 5 apartamentos morosos con mayor monto de deuda
        // Obtener todos los apartamentos y filtrar por saldo pendiente
        $apartamentosMorosos = $apartamentos->filter(function($apartamento) {
            return $apartamento->saldo_pendiente > 0;
        })->sortByDesc('saldo_pendiente')->take(5)->values();
        
        // 2. Los 5 últimos pagos más recientes
        $ultimosPagos = Pago::with(['apartamento', 'reciboGastoComun'])
            ->where('estado', 'confirmado')
            ->orderBy('fecha_pago', 'desc')
            ->take(5)
            ->get();
        
        // 3. Estadísticas del último recibo más actual
        $ultimoRecibo = ReciboGastoComun::where('estado', 'activo')
            ->orderBy('fecha_emision', 'desc')
            ->first();
        
        $estadisticasUltimoRecibo = null;
        if ($ultimoRecibo) {
            $totalRecaudacion = $ultimoRecibo->pagos()
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            $apartamentosPagados = $ultimoRecibo->pagos()
                ->where('estado', 'confirmado')
                ->where('monto_pagado', '>', 0)
                ->distinct('apartamento_id')
                ->count();
            
            $porcentajeRecaudacion = $ultimoRecibo->total_recibo > 0 
                ? ($totalRecaudacion / ($ultimoRecibo->total_recibo * $totalApartamentos)) * 100 
                : 0;
            
            $estadisticasUltimoRecibo = [
                'recibo' => $ultimoRecibo,
                'total_recaudacion' => $totalRecaudacion,
                'porcentaje_recaudacion' => round($porcentajeRecaudacion, 2),
                'apartamentos_pagados' => $apartamentosPagados,
                'total_apartamentos' => $totalApartamentos
            ];
        }
        
        return view('dashboard', compact(
            'totalApartamentos', 
            'saldoPendienteTotal',
            'apartamentosMorosos',
            'ultimosPagos',
            'estadisticasUltimoRecibo'
        ));
    }
}
