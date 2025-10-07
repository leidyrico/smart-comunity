<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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
            // Obtener IDs de apartamentos que deben ser excluidos de las estadísticas
            $apartamentosExcluidos = Apartamento::where(function($query) {
                $query->where('numero', 'like', '%-E%')
                      ->orWhere('numero', 'like', '%-INUN%')
                      ->orWhere('numero', 'like', '%-SALON%')
                      ->orWhere('numero', 'like', '%Abg%');
            })->pluck('id');
            
            // Calcular total de recaudación excluyendo apartamentos específicos
            $totalRecaudacion = $ultimoRecibo->pagos()
                ->where('estado', 'confirmado')
                ->whereNotIn('apartamento_id', $apartamentosExcluidos)
                ->sum('monto_pagado');
            
            // Contar apartamentos pagados excluyendo apartamentos específicos
            $apartamentosPagados = $ultimoRecibo->pagos()
                ->where('estado', 'confirmado')
                ->where('monto_pagado', '>', 0)
                ->whereNotIn('apartamento_id', $apartamentosExcluidos)
                ->distinct('apartamento_id')
                ->count();
            
            // Calcular total de apartamentos válidos (excluyendo los específicos)
            $totalApartamentosValidos = $totalApartamentos - $apartamentosExcluidos->count();
            
            $porcentajeRecaudacion = $ultimoRecibo->total_recibo > 0 && $totalApartamentosValidos > 0
                ? ($totalRecaudacion / ($ultimoRecibo->total_recibo * $totalApartamentosValidos)) * 100 
                : 0;
            
            $estadisticasUltimoRecibo = [
                'recibo' => $ultimoRecibo,
                'total_recaudacion' => $totalRecaudacion,
                'porcentaje_recaudacion' => round($porcentajeRecaudacion, 2),
                'apartamentos_pagados' => $apartamentosPagados,
                'total_apartamentos' => $totalApartamentosValidos
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

    public function generatePdf()
    {
        // Obtener los mismos datos que en el método index
        $totalApartamentos = Apartamento::count();
        
        $apartamentos = Apartamento::all();
        $saldoPendienteTotal = $apartamentos->sum(function($apartamento) {
            return $apartamento->saldo_pendiente;
        });
        
        $apartamentosMorosos = $apartamentos->filter(function($apartamento) {
            return $apartamento->saldo_pendiente > 0;
        })->sortByDesc('saldo_pendiente')->take(5)->values();
        
        $ultimosPagos = Pago::with(['apartamento', 'reciboGastoComun'])
            ->where('estado', 'confirmado')
            ->orderBy('fecha_pago', 'desc')
            ->take(5)
            ->get();
        
        $ultimoRecibo = ReciboGastoComun::where('estado', 'activo')
            ->orderBy('fecha_emision', 'desc')
            ->first();
        
        $estadisticasUltimoRecibo = null;
        if ($ultimoRecibo) {
            $apartamentosExcluidos = Apartamento::where(function($query) {
                $query->where('numero', 'like', '%-E%')
                      ->orWhere('numero', 'like', '%-INUN%')
                      ->orWhere('numero', 'like', '%-SALON%')
                      ->orWhere('numero', 'like', '%Abg%');
            })->pluck('id');
            
            $totalRecaudacion = $ultimoRecibo->pagos()
                ->where('estado', 'confirmado')
                ->whereNotIn('apartamento_id', $apartamentosExcluidos)
                ->sum('monto_pagado');
            
            $apartamentosPagados = $ultimoRecibo->pagos()
                ->where('estado', 'confirmado')
                ->where('monto_pagado', '>', 0)
                ->whereNotIn('apartamento_id', $apartamentosExcluidos)
                ->distinct('apartamento_id')
                ->count();
            
            $totalApartamentosValidos = $totalApartamentos - $apartamentosExcluidos->count();
            
            $porcentajeRecaudacion = $ultimoRecibo->total_recibo > 0 && $totalApartamentosValidos > 0
                ? ($totalRecaudacion / ($ultimoRecibo->total_recibo * $totalApartamentosValidos)) * 100 
                : 0;
            
            $estadisticasUltimoRecibo = [
                'recibo' => $ultimoRecibo,
                'total_recaudacion' => $totalRecaudacion,
                'porcentaje_recaudacion' => round($porcentajeRecaudacion, 2),
                'apartamentos_pagados' => $apartamentosPagados,
                'total_apartamentos' => $totalApartamentosValidos
            ];
        }

        // Generar PDF usando la vista específica para PDF
        $pdf = PDF::loadView('dashboard-pdf', compact(
            'totalApartamentos', 
            'saldoPendienteTotal',
            'apartamentosMorosos',
            'ultimosPagos',
            'estadisticasUltimoRecibo'
        ));

        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('dashboard-' . date('Y-m-d') . '.pdf');
    }
}
