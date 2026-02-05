<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403, 'No tiene permisos para crear proveedores.');
        }
        return view('proveedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403, 'No tiene permisos para crear proveedores.');
        }
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'rif' => 'required|string|max:20|unique:proveedors,rif',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'contacto' => 'nullable|string|max:255',
            'estatus' => 'required|in:activo,inactivo'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Proveedor::create($request->all());

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Proveedor $proveedor)
    {
        return view('proveedores.show', compact('proveedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proveedor $proveedor)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403, 'No tiene permisos para editar proveedores.');
        }
        return view('proveedores.edit', compact('proveedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403, 'No tiene permisos para actualizar proveedores.');
        }
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'rif' => 'required|string|max:20|unique:proveedors,rif,' . $proveedor->id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'contacto' => 'nullable|string|max:255',
            'estatus' => 'required|in:activo,inactivo'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $proveedor->update($request->all());

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proveedor $proveedor)
    {
        // Verificar permisos - los propietarios no pueden eliminar proveedores
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403, 'No tiene permisos para eliminar proveedores.');
        }

        try {
            // Log para debugging
            Log::info('Intentando eliminar proveedor', [
                'proveedor_id' => $proveedor->id,
                'proveedor_nombre' => $proveedor->nombre,
                'egresos_count' => $proveedor->egresos()->count()
            ]);

            // Verificar si tiene egresos asociados
            $egresosCount = $proveedor->egresos()->count();
            if ($egresosCount > 0) {
                Log::warning('Proveedor tiene egresos asociados', [
                    'proveedor_id' => $proveedor->id,
                    'egresos_count' => $egresosCount
                ]);
                return redirect()->route('proveedores.index')
                    ->with('error', "No se puede eliminar el proveedor porque tiene {$egresosCount} egreso(s) asociado(s). Primero debe eliminar o reasignar los egresos.");
            }

            // Preparar nombre amigable para el mensaje
            $nombreProveedor = $proveedor->nombre ?: ($proveedor->rif ?: ('ID ' . $proveedor->id));

            // Intentar eliminar y verificar resultado
            $deleted = $proveedor->delete();

            // Confirmar que realmente se eliminó consultando nuevamente
            $existsAfter = Proveedor::find($proveedor->id) !== null;

            if (!$deleted || $existsAfter) {
                Log::error('Fallo al eliminar proveedor', [
                    'proveedor_id' => $proveedor->id,
                    'deleted_return' => $deleted,
                    'exists_after' => $existsAfter
                ]);

                return redirect()->route('proveedores.index')
                    ->with('error', "No se pudo eliminar el proveedor '{$nombreProveedor}'. Revise relaciones o intente nuevamente.");
            }
            
            Log::info('Proveedor eliminado exitosamente', [
                'proveedor_nombre' => $nombreProveedor
            ]);

            return redirect()->route('proveedores.index')
                ->with('success', "Proveedor '{$nombreProveedor}' eliminado exitosamente.");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de base de datos al eliminar proveedor', [
                'proveedor_id' => $proveedor->id,
                'error' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'error_code' => $e->errorInfo[1] ?? null
            ]);
            
            return redirect()->route('proveedores.index')
                ->with('error', 'Error de base de datos: No se puede eliminar el proveedor debido a restricciones de integridad referencial.');
        } catch (\Exception $e) {
            Log::error('Error general al eliminar proveedor', [
                'proveedor_id' => $proveedor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('proveedores.index')
                ->with('error', 'Error inesperado al eliminar el proveedor: ' . $e->getMessage());
        }
    }
}
