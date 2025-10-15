<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Egreso;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class EgresoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Egreso::with('proveedor');

        // Filtros
        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('nro_factura')) {
            $query->where('nro_factura', 'like', '%' . $request->nro_factura . '%');
        }

        $egresos = $query->orderBy('fecha', 'desc')->paginate(15);
        
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();

        return view('egresos.index', compact('egresos', 'proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        return view('egresos.create', compact('proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
        $validator = Validator::make($request->all(), [
            'nro_factura' => 'required|string|max:255',
            'comprobante' => 'nullable|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'monto_en_bs' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'proveedor_id' => 'nullable|exists:proveedors,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $egreso = Egreso::create($request->all());

        return redirect()->route('egresos.index')
            ->with('success', 'Egreso creado exitosamente.')
            ->with('egreso_id', $egreso->id)
            ->with('show_pdf_link', true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Egreso $egreso)
    {
        $egreso->load('proveedor');
        return view('egresos.show', compact('egreso'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Egreso $egreso)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        return view('egresos.edit', compact('egreso', 'proveedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Egreso $egreso)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
        $validator = Validator::make($request->all(), [
            'nro_factura' => 'required|string|max:255',
            'comprobante' => 'nullable|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'monto_en_bs' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'proveedor_id' => 'nullable|exists:proveedors,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $egreso->update($request->all());

        return redirect()->route('egresos.index')
            ->with('success', 'Egreso actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Egreso $egreso)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
        $egreso->delete();

        return redirect()->route('egresos.index')
            ->with('success', 'Egreso eliminado exitosamente.');
    }

    /**
     * Generate PDF for an existing egreso.
     */
    public function generatePdf(Egreso $egreso)
    {
        // Cargar la relación del proveedor para el PDF
        $egreso->load('proveedor');

        // Generar el PDF
        $pdf = Pdf::loadView('egresos.pdf', compact('egreso'));
        
        // Nombre del archivo PDF
        $filename = 'egreso_' . $egreso->nro_factura . '_' . $egreso->fecha->format('Y-m-d') . '.pdf';

        // Descargar el PDF
        return $pdf->download($filename);
    }
}
