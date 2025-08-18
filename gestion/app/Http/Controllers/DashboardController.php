<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Acta;
use App\Models\Inquilino;

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
        $totalInquilinos = Inquilino::count();
        $pagosPendientes = Inquilino::where('monto_deuda', '>', 0)->count();
        
        // Mostrar información de debug en la vista
        $debugInfo = [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user() ? Auth::user()->email : 'No user',
            'session_id' => session()->getId(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'message' => '🎉 ¡Dashboard cargado exitosamente! El sistema está funcionando.'
        ];
        
        return view('dashboard', compact('debugInfo', 'totalActas', 'totalInquilinos', 'pagosPendientes'));
    }
}
