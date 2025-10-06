<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $spaces = Space::orderBy('nombre')->paginate(10);
        return view('spaces.index', compact('spaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('spaces.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:spaces,nombre',
            'descripcion' => 'nullable|string',
            'precio_por_dia' => 'required|numeric|min:0',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Space::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_por_dia' => $request->precio_por_dia,
            'activo' => $request->has('activo') ? true : false
        ]);

        return redirect()->route('spaces.index')
            ->with('success', 'Espacio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Space $space)
    {
        $reservations = $space->reservations()
            ->with('apartamento')
            ->orderBy('fecha_reserva', 'desc')
            ->get();
            
        return view('spaces.show', compact('space', 'reservations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Space $space)
    {
        return view('spaces.edit', compact('space'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Space $space)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:spaces,nombre,' . $space->id,
            'descripcion' => 'nullable|string',
            'precio_por_dia' => 'required|numeric|min:0',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $space->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_por_dia' => $request->precio_por_dia,
            'activo' => $request->has('activo') ? true : false
        ]);

        return redirect()->route('spaces.index')
            ->with('success', 'Espacio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Space $space)
    {
        // Verificar si el espacio tiene reservas
        if ($space->reservations()->count() > 0) {
            return redirect()->route('spaces.index')
                ->with('error', 'No se puede eliminar el espacio porque tiene reservas asociadas.');
        }

        $space->delete();

        return redirect()->route('spaces.index')
            ->with('success', 'Espacio eliminado exitosamente.');
    }
}
