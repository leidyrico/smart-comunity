<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::with(['apartamento', 'space', 'recibo'])
            ->orderBy('fecha_reserva', 'desc')
            ->get();
            
        return view('reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apartamentos = Apartamento::orderBy('numero')->get();
        $spaces = Space::activos()->orderBy('nombre')->get();
        
        // Fechas disponibles (desde hoy hasta 2 meses)
        $fechaInicio = Carbon::today();
        $fechaFin = Carbon::today()->addMonths(2);
        
        return view('reservations.create', compact('apartamentos', 'spaces', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apartamento_id' => 'required|exists:apartamentos,id',
            'space_id' => 'required|exists:spaces,id',
            'fecha_reserva' => 'required|date|after_or_equal:today|before_or_equal:' . Carbon::today()->addMonths(2)->format('Y-m-d'),
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar disponibilidad
        if (!Reservation::isDateAvailable($request->space_id, $request->fecha_reserva)) {
            return redirect()->back()
                ->with('error', 'El espacio no está disponible en la fecha seleccionada.')
                ->withInput();
        }

        $space = Space::findOrFail($request->space_id);
        $apartamento = Apartamento::findOrFail($request->apartamento_id);

        DB::beginTransaction();
        try {
            // Crear la reserva
            $reservation = Reservation::create([
                'apartamento_id' => $request->apartamento_id,
                'space_id' => $request->space_id,
                'fecha_reserva' => $request->fecha_reserva,
                'monto' => $space->precio_por_dia,
                'estado' => 'confirmada',
                'observaciones' => $request->observaciones
            ]);

            // Generar recibo automáticamente
            $recibo = $this->generateReceipt($reservation, $apartamento, $space);
            
            // Actualizar la reserva con el ID del recibo
            $reservation->update(['recibo_id' => $recibo->id]);

            DB::commit();

            return redirect()->route('reservations.index')
                ->with('success', 'Reserva creada exitosamente y recibo generado.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error al crear la reserva: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['apartamento', 'space', 'recibo']);
        return view('reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        $apartamentos = Apartamento::orderBy('numero')->get();
        $spaces = Space::activos()->orderBy('nombre')->get();
        
        $fechaInicio = Carbon::today();
        $fechaFin = Carbon::today()->addMonths(2);
        
        return view('reservations.edit', compact('reservation', 'apartamentos', 'spaces', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validator = Validator::make($request->all(), [
            'apartamento_id' => 'required|exists:apartamentos,id',
            'space_id' => 'required|exists:spaces,id',
            'fecha_reserva' => 'required|date|after_or_equal:today|before_or_equal:' . Carbon::today()->addMonths(2)->format('Y-m-d'),
            'estado' => 'required|in:pendiente,confirmada,cancelada',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar disponibilidad (excluyendo la reserva actual)
        if (!Reservation::isDateAvailable($request->space_id, $request->fecha_reserva, $reservation->id)) {
            return redirect()->back()
                ->with('error', 'El espacio no está disponible en la fecha seleccionada.')
                ->withInput();
        }

        $space = Space::findOrFail($request->space_id);

        $reservation->update([
            'apartamento_id' => $request->apartamento_id,
            'space_id' => $request->space_id,
            'fecha_reserva' => $request->fecha_reserva,
            'monto' => $space->precio_por_dia,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones
        ]);

        return redirect()->route('reservations.index')
            ->with('success', 'Reserva actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Reserva eliminada exitosamente.');
    }

    /**
     * Check availability for a specific space and date
     */
    public function checkAvailability(Request $request)
    {
        $spaceId = $request->space_id;
        $date = $request->date;
        $excludeId = $request->exclude_id;

        $available = Reservation::isDateAvailable($spaceId, $date, $excludeId);

        return response()->json(['available' => $available]);
    }

    /**
     * Generate receipt for reservation
     */
    private function generateReceipt(Reservation $reservation, Apartamento $apartamento, Space $space)
    {
        $fechaVencimiento = Carbon::parse($reservation->fecha_reserva)->addDays(30);
        
        return ReciboGastoComun::create([
            'apartamento_id' => $apartamento->id,
            'mes' => Carbon::parse($reservation->fecha_reserva)->format('m'),
            'año' => Carbon::parse($reservation->fecha_reserva)->format('Y'),
            'monto_gasto_comun' => $reservation->monto,
            'monto_total' => $reservation->monto,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => 'pendiente',
            'concepto' => 'Reserva de espacio: ' . $space->nombre . ' - Fecha: ' . Carbon::parse($reservation->fecha_reserva)->format('d/m/Y')
        ]);
    }
}
