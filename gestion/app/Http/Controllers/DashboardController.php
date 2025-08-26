<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Acta;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

class DashboardController extends Controller
{
    public function index()
    {
        // Debug: Verificar autenticación
        Log::info('Dashboard accessed', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user' => Auth::user() ? Auth::user()->email : 'No user',
            'session_id' => session()->getId(),
            'ip' => request()->ip()
        ]);
        
        // Obtener estadísticas
        $totalActas = Acta::count();
        $totalApartamentos = Apartamento::count();
        
        // Calcular saldo total pendiente
        $saldoTotalPendiente = 0;
        $apartamentos = Apartamento::with(['pagos'])->get();
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
        
        foreach ($apartamentos as $apartamento) {
            foreach ($recibos as $recibo) {
                $pagosTotales = $apartamento->pagos
                    ->where('recibo_gasto_comun_id', $recibo->id)
                    ->where('estado', 'confirmado')
                    ->sum('monto_pagado');
                
                $saldoPendiente = $recibo->total_recibo - $pagosTotales;
                
                if ($saldoPendiente > 0) {
                    $saldoTotalPendiente += $saldoPendiente;
                }
            }
        }
        
        // Mostrar información de debug en la vista
        $debugInfo = [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user() ? Auth::user()->email : 'No user',
            'session_id' => session()->getId(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'message' => '🎉 ¡Dashboard cargado exitosamente! El sistema está funcionando.'
        ];
        
        return view('dashboard', compact('debugInfo', 'totalActas', 'totalApartamentos', 'saldoTotalPendiente'));
    }
}
