<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Reserva #{{ $reservation->id }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('reservations.edit', $reservation) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </a>
                            <a href="{{ route('reservations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Información de la reserva -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Información de la Reserva</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">ID de Reserva:</span>
                                        <span class="text-gray-900">#{{ $reservation->id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Fecha de Reserva:</span>
                                        <span class="text-gray-900">{{ $reservation->fecha_reserva->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Monto:</span>
                                        <span class="text-gray-900 font-semibold">${{ number_format($reservation->monto, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Estado:</span>
                                        <span class="px-3 py-1 text-sm rounded-full
                                            @if($reservation->estado == 'confirmada') bg-green-100 text-green-800
                                            @elseif($reservation->estado == 'pendiente') bg-yellow-100 text-yellow-800
                                            @elseif($reservation->estado == 'cancelada') bg-red-100 text-red-800
                                            @elseif($reservation->estado == 'completada') bg-blue-100 text-blue-800
                                            @endif">
                                            {{ ucfirst($reservation->estado) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Creada:</span>
                                        <span class="text-gray-900">{{ $reservation->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($reservation->updated_at != $reservation->created_at)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-600">Última actualización:</span>
                                            <span class="text-gray-900">{{ $reservation->updated_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Observaciones -->
                            @if($reservation->observaciones)
                                <div class="bg-blue-50 p-6 rounded-lg">
                                    <h4 class="text-lg font-medium text-gray-900 mb-3">Observaciones</h4>
                                    <p class="text-gray-700">{{ $reservation->observaciones }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Información del apartamento y espacio -->
                        <div class="space-y-6">
                            <!-- Apartamento -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Apartamento</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Número:</span>
                                        <span class="text-gray-900">{{ $reservation->apartamento->numero }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Propietario:</span>
                                        <span class="text-gray-900">{{ $reservation->apartamento->propietario }}</span>
                                    </div>
                                    @if($reservation->apartamento->telefono)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-600">Teléfono:</span>
                                            <span class="text-gray-900">{{ $reservation->apartamento->telefono }}</span>
                                        </div>
                                    @endif
                                    @if($reservation->apartamento->email)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-600">Email:</span>
                                            <span class="text-gray-900">{{ $reservation->apartamento->email }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('apartamentos.show', $reservation->apartamento) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Ver detalles del apartamento →
                                    </a>
                                </div>
                            </div>

                            <!-- Espacio -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Espacio Reservado</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Nombre:</span>
                                        <span class="text-gray-900">{{ $reservation->space->nombre }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Precio por día:</span>
                                        <span class="text-gray-900">${{ number_format($reservation->space->precio_por_dia, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Estado:</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $reservation->space->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $reservation->space->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>
                                @if($reservation->space->descripcion)
                                    <div class="mt-4">
                                        <span class="font-medium text-gray-600">Descripción:</span>
                                        <p class="text-gray-700 mt-1">{{ $reservation->space->descripcion }}</p>
                                    </div>
                                @endif
                                <div class="mt-4">
                                    <a href="{{ route('spaces.show', $reservation->space) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Ver detalles del espacio →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recibo asociado -->
                    @if($reservation->recibo)
                        <div class="mt-8">
                            <div class="bg-green-50 border border-green-200 p-6 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Recibo de Gasto Común Asociado</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <span class="font-medium text-gray-600">Recibo ID:</span>
                                        <p class="text-gray-900">#{{ $reservation->recibo->id }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Mes/Año:</span>
                                        <p class="text-gray-900">{{ $reservation->recibo->mes }}/{{ $reservation->recibo->año }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Monto Total:</span>
                                        <p class="text-gray-900 font-semibold">${{ number_format($reservation->recibo->monto_total, 2) }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-3">
                                    <a href="{{ route('recibos.show', $reservation->recibo) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Ver Recibo Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Acciones -->
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('reservations.edit', $reservation) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Reserva
                        </a>

                        @if($reservation->estado != 'cancelada')
                            <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de que desea eliminar esta reserva? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Eliminar Reserva
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('reservations.create', ['space_id' => $reservation->space_id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nueva Reserva para este Espacio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>