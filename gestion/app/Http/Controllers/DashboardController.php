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
        // Según requisitos: todos los recibos activos + suma del monto de apartamentos con recibos vencidos
        $saldoTotalPendiente = 0;
        
        // 1. Sumar todos los recibos activos (sin importar pagos)
        $recibosActivos = ReciboGastoComun::where('estado', 'activo')->get();
        $totalRecibosActivos = $recibosActivos->sum('total_recibo');
        $saldoTotalPendiente += $totalRecibosActivos;
        
        // 2. Sumar el monto de apartamentos que tienen asignado recibos vencidos
        $apartamentos = Apartamento::with(['pagos'])->get();
        $recibosVencidos = ReciboGastoComun::where('estado', 'vencido')->get();
        
        foreach ($apartamentos as $apartamento) {
            $tieneRecibosVencidos = false;
            $montoApartamentoVencidos = 0;
            
            foreach ($recibosVencidos as $reciboVencido) {
                // Verificar si el apartamento tiene pagos asociados a este recibo vencido
                $tienePagosEnRecibo = $apartamento->pagos
                    ->where('recibo_gasto_comun_id', $reciboVencido->id)
                    ->count() > 0;
                
                if ($tienePagosEnRecibo) {
                    $tieneRecibosVencidos = true;
                    $montoApartamentoVencidos += $reciboVencido->total_recibo;
                }
            }
            
            if ($tieneRecibosVencidos) {
                $saldoTotalPendiente += $montoApartamentoVencidos;
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
