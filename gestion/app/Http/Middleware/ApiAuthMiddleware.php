<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si es una petición AJAX o API
        if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
            // Si el usuario no está autenticado, devolver respuesta JSON
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'authentication_required',
                    'message' => 'Debes iniciar sesión para acceder a este recurso.',
                    'redirect' => route('login')
                ], 401);
            }
        } else {
            // Para peticiones normales, redirigir al login si no está autenticado
            if (!Auth::check()) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}