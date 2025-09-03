<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventario::query();

        // Filtros
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->search . '%');
            });
        }

        $inventarios = $query->orderBy('nombre')->paginate(15);
        $categorias = Inventario::distinct()->pluck('categoria')->filter();
        $estados = ['disponible', 'en_uso', 'mantenimiento', 'dañado'];

        return view('inventario.index', compact('inventarios', 'categorias', 'estados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventario.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'nullable|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255',
            'estado' => 'required|in:disponible,en_uso,mantenimiento,dañado',
            'fecha_adquisicion' => 'nullable|date',
            'proveedor' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Inventario::create($request->all());

        return redirect()->route('inventario.index')
            ->with('success', 'Elemento de inventario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventario $inventario)
    {
        return view('inventario.show', compact('inventario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventario $inventario)
    {
        return view('inventario.edit', compact('inventario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventario $inventario)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'nullable|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255',
            'estado' => 'required|in:disponible,en_uso,mantenimiento,dañado',
            'fecha_adquisicion' => 'nullable|date',
            'proveedor' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $inventario->update($request->all());

        return redirect()->route('inventario.index')
            ->with('success', 'Elemento de inventario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Inventario $inventario)
    {
        // Validar clave de administrador
        $adminPassword = $request->input('admin_password');
        $configuredPassword = config('app.admin_password', 'admin123'); // Clave por defecto
        
        if (!$adminPassword || $adminPassword !== $configuredPassword) {
            return redirect()->route('inventario.index')
                ->with('error', 'Clave de administrador incorrecta. No se pudo eliminar el elemento.');
        }
        
        $nombreElemento = $inventario->nombre;
        $inventario->delete();

        return redirect()->route('inventario.index')
            ->with('success', "Elemento '{$nombreElemento}' eliminado exitosamente.");
    }
}
