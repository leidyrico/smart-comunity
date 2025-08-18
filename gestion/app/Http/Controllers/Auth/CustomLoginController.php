<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class CustomLoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            Log::info('Intento de login', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $request->authenticate();

            $request->session()->regenerate();

            Log::info('Login exitoso', [
                'email' => Auth::user()->email,
                'id' => Auth::user()->id
            ]);

            return redirect()->intended(route('dashboard', absolute: false));
            
        } catch (\Exception $e) {
            Log::error('Error en login', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email')
            ]);
            
            return back()->withErrors([
                'email' => 'Error en el proceso de autenticación: ' . $e->getMessage(),
            ])->onlyInput('email');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}