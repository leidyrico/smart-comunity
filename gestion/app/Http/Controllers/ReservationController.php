<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use App\Mail\ReservationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            ->paginate(10);
            
        $spaces = Space::activos()->orderBy('nombre')->get();
            
        return view('reservations.index', compact('reservations', 'spaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apartamentos = Apartamento::orderBy('numero')->get();
        $spaces = Space::activos()->orderBy('nombre')->get();
        
        // Fechas disponibles (desde hoy hasta 2 meses)
        $fechaMinima = Carbon::today()->format('Y-m-d');
        $fechaMaxima = Carbon::today()->addMonths(2)->format('Y-m-d');
        
        return view('reservations.create', compact('apartamentos', 'spaces', 'fechaMinima', 'fechaMaxima'));
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

            // Enviar correo de notificación al apartamento
            if ($apartamento->email) {
                try {
                    Mail::to($apartamento->email)->send(new ReservationNotification($reservation, $apartamento, $space));
                } catch (\Exception $mailException) {
                    // Log del error pero no fallar la transacción
                    \Log::warning('Error al enviar correo de reserva: ' . $mailException->getMessage());
                }
            }

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
        
        $fechaMinima = Carbon::today()->format('Y-m-d');
        $fechaMaxima = Carbon::today()->addMonths(2)->format('Y-m-d');
        
        return view('reservations.edit', compact('reservation', 'apartamentos', 'spaces', 'fechaMinima', 'fechaMaxima'));
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
        $date = $request->fecha_reserva; // Corregido para coincidir con el JavaScript
        $excludeId = $request->exclude_id;

        $available = Reservation::isDateAvailable($spaceId, $date, $excludeId);

        return response()->json(['available' => $available]);
    }

    /**
     * Generate receipt for reservation
     */
    private function generateReceipt(Reservation $reservation, Apartamento $apartamento, Space $space)
    {
        $fechaCreacion = Carbon::now();
        $fechaReserva = Carbon::parse($reservation->fecha_reserva);
        
        // Generar número de recibo con formato: REC-fecha_creación-SALON
        $numeroRecibo = 'REC-' . $fechaCreacion->format('dmy') . '-SALON';
        
        // Crear el recibo
        $recibo = ReciboGastoComun::create([
            'numero_recibo' => $numeroRecibo,
            'periodo' => $fechaReserva->format('m/Y'),
            'fecha_emision' => $fechaCreacion, // Momento en que se crea la reserva
            'fecha_vencimiento' => $fechaReserva, // Fecha de la reserva
            'valor_administracion' => 0,
            'valor_aseo' => 0,
            'valor_vigilancia' => 0,
            'valor_mantenimiento' => 0,
            'otros_conceptos' => $reservation->monto, // Monto de la reserva
            'total_recibo' => $reservation->monto,
            'observaciones' => 'Recibo generado automáticamente por reserva de ' . $space->nombre . ' para el ' . $fechaReserva->format('d/m/Y'),
            'estado' => 'activo'
        ]);
        
        // Crear el registro de pago para asignar el recibo al apartamento
        // Esto carga el monto al saldo pendiente del apartamento
        Pago::create([
            'apartamento_id' => $apartamento->id,
            'recibo_gasto_comun_id' => $recibo->id,
            'monto_pagado' => 0, // Sin pago inicial
            'fecha_pago' => null,
            'metodo_pago' => 'pendiente',
            'numero_comprobante' => null,
            'observaciones' => 'Recibo de reserva de espacio - ' . $space->nombre,
            'estado' => 'pendiente_confirmacion'
        ]);
        
        return $recibo;
    }
}
