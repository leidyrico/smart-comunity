<?php

namespace App\Http\Controllers;

use App\Models\Fondo;
use App\Models\MovimientoFondo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FondoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fondos = Fondo::orderBy('nombre')->get();
        return view('fondos.index', compact('fondos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        return view('fondos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'saldo_usd' => 'nullable|numeric|min:0',
            'saldo_bs' => 'nullable|numeric|min:0',
        ]);

        $fondo = Fondo::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'saldo_usd' => $validated['saldo_usd'] ?? 0,
            'saldo_bs' => $validated['saldo_bs'] ?? 0,
        ]);

        return redirect()->route('fondos.show', $fondo)
            ->with('success', 'Fondo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fondo $fondo)
    {
        $fondo->load('movimientos');
        return view('fondos.show', compact('fondo'));
    }

    /**
     * Registrar ingreso al fondo.
     */
    public function registrarIngreso(Request $request, Fondo $fondo)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $validated = $request->validate([
            'monto_usd' => 'nullable|numeric|min:0',
            'monto_bs' => 'nullable|numeric|min:0',
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
        ]);

        if (($validated['monto_usd'] ?? 0) <= 0 && ($validated['monto_bs'] ?? 0) <= 0) {
            return back()->with('error', 'Debe ingresar un monto en USD o en BS.');
        }

        DB::transaction(function () use ($fondo, $validated) {
            MovimientoFondo::create([
                'fondo_id' => $fondo->id,
                'tipo' => 'ingreso',
                'monto_usd' => $validated['monto_usd'] ?? 0,
                'monto_bs' => $validated['monto_bs'] ?? 0,
                'fecha' => $validated['fecha'] ?? now()->toDateString(),
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            $fondo->update([
                'saldo_usd' => ($fondo->saldo_usd ?? 0) + ($validated['monto_usd'] ?? 0),
                'saldo_bs' => ($fondo->saldo_bs ?? 0) + ($validated['monto_bs'] ?? 0),
            ]);
        });

        return redirect()->route('fondos.show', $fondo)->with('success', 'Ingreso registrado exitosamente.');
    }

    /**
     * Registrar egreso del fondo.
     */
    public function registrarEgreso(Request $request, Fondo $fondo)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $validated = $request->validate([
            'monto_usd' => 'nullable|numeric|min:0',
            'monto_bs' => 'nullable|numeric|min:0',
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
        ]);

        if (($validated['monto_usd'] ?? 0) <= 0 && ($validated['monto_bs'] ?? 0) <= 0) {
            return back()->with('error', 'Debe ingresar un monto en USD o en BS.');
        }

        DB::transaction(function () use ($fondo, $validated) {
            MovimientoFondo::create([
                'fondo_id' => $fondo->id,
                'tipo' => 'egreso',
                'monto_usd' => $validated['monto_usd'] ?? 0,
                'monto_bs' => $validated['monto_bs'] ?? 0,
                'fecha' => $validated['fecha'] ?? now()->toDateString(),
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            $fondo->update([
                'saldo_usd' => max(0, ($fondo->saldo_usd ?? 0) - ($validated['monto_usd'] ?? 0)),
                'saldo_bs' => max(0, ($fondo->saldo_bs ?? 0) - ($validated['monto_bs'] ?? 0)),
            ]);
        });

        return redirect()->route('fondos.show', $fondo)->with('success', 'Egreso registrado exitosamente.');
    }

    /**
     * Eliminar un movimiento y ajustar saldos del fondo.
     */
    public function eliminarMovimiento(MovimientoFondo $movimiento)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }

        $fondo = $movimiento->fondo;

        DB::transaction(function () use ($movimiento, $fondo) {
            if ($movimiento->tipo === 'ingreso') {
                $fondo->update([
                    'saldo_usd' => max(0, ($fondo->saldo_usd ?? 0) - ($movimiento->monto_usd ?? 0)),
                    'saldo_bs' => max(0, ($fondo->saldo_bs ?? 0) - ($movimiento->monto_bs ?? 0)),
                ]);
            } else { // egreso
                $fondo->update([
                    'saldo_usd' => ($fondo->saldo_usd ?? 0) + ($movimiento->monto_usd ?? 0),
                    'saldo_bs' => ($fondo->saldo_bs ?? 0) + ($movimiento->monto_bs ?? 0),
                ]);
            }

            $movimiento->delete();
        });

        return redirect()->route('fondos.show', $fondo)->with('success', 'Movimiento eliminado exitosamente.');
    }
}