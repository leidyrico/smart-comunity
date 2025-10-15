<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Reserva de Espacio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Editar Reserva #{{ $reservation->id }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('reservations.show', $reservation) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Ver
                            </a>
                            <a href="{{ route('reservations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('reservations.update', $reservation) }}" method="POST" id="reservationForm">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Formulario de reserva -->
                            <div class="space-y-6">
                                <!-- Apartamento -->
                                <div>
                                    <x-input-label for="apartamento_id" :value="__('Apartamento')" />
                                    @php($user = Auth::user())
                                    @php($isPropietario = $user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario())
                                    @if($isPropietario)
                                        <input type="hidden" id="apartamento_id" name="apartamento_id" value="{{ $reservation->apartamento_id }}" />
                                        <div class="mt-1 block w-full p-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                                            Apartamento {{ $reservation->apartamento->numero }}
                                        </div>
                                    @else
                                        <select id="apartamento_id" name="apartamento_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option value="">Seleccione un apartamento</option>
                                            @foreach($apartamentos as $apartamento)
                                                <option value="{{ $apartamento->id }}" {{ old('apartamento_id', $reservation->apartamento_id) == $apartamento->id ? 'selected' : '' }}>
                                                    Apartamento {{ $apartamento->numero }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('apartamento_id')" />
                                </div>

                                <!-- Espacio -->
                                <div>
                                    <x-input-label for="space_id" :value="__('Espacio a Reservar')" />
                                    <select id="space_id" name="space_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Seleccione un espacio</option>
                                        @foreach($spaces as $space)
                                            <option value="{{ $space->id }}" data-precio="{{ $space->precio_por_dia }}" {{ old('space_id', $reservation->space_id) == $space->id ? 'selected' : '' }}>
                                                {{ $space->nombre }} - ${{ number_format($space->precio_por_dia, 2) }}/día
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('space_id')" />
                                </div>

                                <!-- Fecha de reserva -->
                                <div>
                                    <x-input-label for="fecha_reserva" :value="__('Fecha de Reserva')" />
                                    <input type="date" id="fecha_reserva" name="fecha_reserva" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        min="{{ $fechaMinima }}" max="{{ $fechaMaxima }}" 
                                        value="{{ old('fecha_reserva', $reservation->fecha_reserva->format('Y-m-d')) }}" required>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Puede reservar desde {{ \Carbon\Carbon::parse($fechaMinima)->format('d/m/Y') }} 
                                        hasta {{ \Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('fecha_reserva')" />
                                    <div id="availability-message" class="mt-2"></div>
                                </div>

                                <!-- Monto -->
                                <div>
                                    <x-input-label for="monto" :value="__('Monto ($)')" />
                                    <x-text-input id="monto" name="monto" type="number" step="0.01" min="0"
                                        class="mt-1 block w-full" :value="old('monto', $reservation->monto)" required />
                                    <p class="mt-1 text-sm text-gray-500">El monto se actualiza automáticamente según el espacio seleccionado, pero puede modificarlo si es necesario.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('monto')" />
                                </div>

                                <!-- Estado -->
                                <div>
                                    <x-input-label for="estado" :value="__('Estado')" />
                                    <select id="estado" name="estado" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="pendiente" {{ old('estado', $reservation->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="confirmada" {{ old('estado', $reservation->estado) == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                                        <option value="cancelada" {{ old('estado', $reservation->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                        <option value="completada" {{ old('estado', $reservation->estado) == 'completada' ? 'selected' : '' }}>Completada</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('estado')" />
                                </div>

                                <!-- Observaciones -->
                                <div>
                                    <x-input-label for="observaciones" :value="__('Observaciones (Opcional)')" />
                                    <textarea id="observaciones" name="observaciones" rows="3" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Observaciones adicionales sobre la reserva...">{{ old('observaciones', $reservation->observaciones) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('observaciones')" />
                                </div>
                            </div>

                            <!-- Información adicional -->
                            <div class="space-y-6">
                                <!-- Información de la reserva actual -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Información Actual</h4>
                                    <div class="space-y-2 text-sm">
                                        <div><strong>Reserva ID:</strong> #{{ $reservation->id }}</div>
                                        <div><strong>Apartamento:</strong> {{ $reservation->apartamento->numero }}</div>
                                        <div><strong>Espacio:</strong> {{ $reservation->space->nombre }}</div>
                                        <div><strong>Fecha Original:</strong> {{ $reservation->fecha_reserva->format('d/m/Y') }}</div>
                                        <div><strong>Monto Original:</strong> ${{ number_format($reservation->monto, 2) }}</div>
                                        <div><strong>Estado Actual:</strong> 
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($reservation->estado == 'confirmada') bg-green-100 text-green-800
                                                @elseif($reservation->estado == 'pendiente') bg-yellow-100 text-yellow-800
                                                @elseif($reservation->estado == 'cancelada') bg-red-100 text-red-800
                                                @elseif($reservation->estado == 'completada') bg-blue-100 text-blue-800
                                                @endif">
                                                {{ ucfirst($reservation->estado) }}
                                            </span>
                                        </div>
                                        <div><strong>Creada:</strong> {{ $reservation->created_at->format('d/m/Y H:i') }}</div>
                                        @if($reservation->recibo)
                                            <div><strong>Recibo:</strong> 
                                                <a href="{{ route('recibos.show', $reservation->recibo) }}" class="text-blue-600 hover:text-blue-800">
                                                    Ver Recibo #{{ $reservation->recibo->id }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 space-x-3">
                            <a href="{{ route('reservations.show', $reservation) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Reserva') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const spaceSelect = document.getElementById('space_id');
            const fechaInput = document.getElementById('fecha_reserva');
            const montoInput = document.getElementById('monto');
            const availabilityMessage = document.getElementById('availability-message');
            const originalDate = '{{ $reservation->fecha_reserva->format("Y-m-d") }}';

            // Actualizar monto cuando cambia el espacio
            spaceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const precio = selectedOption.getAttribute('data-precio');
                
                if (precio) {
                    montoInput.value = parseFloat(precio).toFixed(2);
                }
            });

            // Verificar disponibilidad cuando cambia la fecha
            fechaInput.addEventListener('change', function() {
                checkAvailability();
            });

            function checkAvailability() {
                const spaceId = spaceSelect.value;
                const fecha = fechaInput.value;
                
                if (!spaceId || !fecha) {
                    availabilityMessage.innerHTML = '';
                    return;
                }

                // Si es la fecha original, no verificar disponibilidad
                if (fecha === originalDate) {
                    availabilityMessage.innerHTML = '<div class="text-purple-600 text-sm">📅 Fecha actual de la reserva</div>';
                    return;
                }

                // Verificar disponibilidad via AJAX
                fetch('{{ route("reservations.check-availability") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        space_id: spaceId,
                        fecha_reserva: fecha,
                        exclude_reservation_id: {{ $reservation->id }}
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        availabilityMessage.innerHTML = '<div class="text-green-600 text-sm">✓ Fecha disponible</div>';
                    } else {
                        availabilityMessage.innerHTML = '<div class="text-red-600 text-sm">✗ Fecha no disponible</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    availabilityMessage.innerHTML = '<div class="text-yellow-600 text-sm">⚠ Error al verificar disponibilidad</div>';
                });
            }

            // Inicializar
            checkAvailability();
        });
    </script>
    @endpush
</x-app-with-sidebar>