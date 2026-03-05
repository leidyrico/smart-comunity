<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Reserva de Espacio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Crear Nueva Reserva</h3>
                        <a href="{{ route('reservations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </a>
                    </div>

                    <form action="{{ route('reservations.store') }}" method="POST" id="reservationForm">
                        @csrf

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Formulario de reserva -->
                            <div class="space-y-6">
                                <!-- Apartamento -->
                                <div>
                                    <x-input-label for="apartamento_id" :value="__('Apartamento')" />
                                    @php($user = Auth::user())
                                    @php($isPropietario = $user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario())
                                    @if($isPropietario)
                                        <input type="hidden" id="apartamento_id" name="apartamento_id" value="{{ $user->apartamento_id }}" />
                                        <div class="mt-1 block w-full p-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                                            Apartamento {{ optional($apartamentos->first())->numero ?? 'N/A' }}
                                        </div>
                                    @else
                                        <select id="apartamento_id" name="apartamento_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option value="">Seleccione un apartamento</option>
                                            @foreach($apartamentos as $apartamento)
                                                <option value="{{ $apartamento->id }}" {{ old('apartamento_id') == $apartamento->id ? 'selected' : '' }}>
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
                                            <option value="{{ $space->id }}" data-precio="{{ $space->precio_por_dia }}" {{ old('space_id', request('space_id')) == $space->id ? 'selected' : '' }}>
                                                {{ $space->nombre }} - ${{ number_format($space->precio_por_dia, 2) }}/día
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('space_id')" />
                                </div>

                                <!-- Fecha de reserva seleccionada -->
                                <div>
                                    <x-input-label for="fecha_seleccionada_display" :value="__('Fecha de Reserva Seleccionada')" />
                                    <div id="fecha_seleccionada_display" class="mt-1 block w-full p-3 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                                        <span class="text-gray-500">Seleccione una fecha en el calendario</span>
                                    </div>
                                    <input type="hidden" id="fecha_reserva" name="fecha_reserva" value="{{ old('fecha_reserva') }}" required>
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
                                        class="mt-1 block w-full" :value="old('monto')" required readonly />
                                    <p class="mt-1 text-sm text-gray-500">El monto se calcula automáticamente según el espacio seleccionado.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('monto')" />
                                </div>

                                <!-- Observaciones -->
                                <div>
                                    <x-input-label for="observaciones" :value="__('Observaciones (Opcional)')" />
                                    <textarea id="observaciones" name="observaciones" rows="3" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Observaciones adicionales sobre la reserva...">{{ old('observaciones') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('observaciones')" />
                                </div>
                            </div>

                            <!-- Calendario -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Calendario de Disponibilidad</h4>
                                <div id="calendar" class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-center text-gray-500">
                                        Seleccione un espacio para ver la disponibilidad
                                    </div>
                                </div>
                                
                                <!-- Leyenda -->
                                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 bg-green-200 border border-green-300 rounded mr-2"></div>
                                        <span>Disponible</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 bg-red-200 border border-red-300 rounded mr-2"></div>
                                        <span>Ocupado</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 bg-blue-200 border border-blue-300 rounded mr-2"></div>
                                        <span>Seleccionado</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 space-x-3">
                            <a href="{{ route('reservations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button id="submitBtn" type="submit">
                                {{ __('Crear Reserva') }}
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
            console.log('Script de reservas cargado correctamente');
            
            const spaceSelect = document.getElementById('space_id');
            const fechaInput = document.getElementById('fecha_reserva');
            const fechaSeleccionadaDisplay = document.getElementById('fecha_seleccionada_display');
            const montoInput = document.getElementById('monto');
            const calendarDiv = document.getElementById('calendar');
            const submitBtn = document.getElementById('submitBtn');
            const availabilityMessage = document.getElementById('availability-message');
            
            // Variables globales para navegación del calendario
            let currentCalendarYear = new Date().getFullYear();
            let currentCalendarMonth = new Date().getMonth();
            const today = new Date();
            
            // Rango de fechas: dinámico desde el controlador
            const maxDateStr = '{{ $fechaMaxima }}';
            const maxDateParts = maxDateStr.split('-');
            const maxDate = new Date(maxDateParts[0], maxDateParts[1] - 1, maxDateParts[2]);
            const maxYear = maxDate.getFullYear();
            const maxMonth = maxDate.getMonth();
            
            console.log('Elementos encontrados:', {
                spaceSelect: !!spaceSelect,
                fechaInput: !!fechaInput,
                fechaSeleccionadaDisplay: !!fechaSeleccionadaDisplay,
                montoInput: !!montoInput,
                calendarDiv: !!calendarDiv,
                submitBtn: !!submitBtn,
                availabilityMessage: !!availabilityMessage
            });

            // Actualizar monto cuando cambia el espacio
            spaceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const precio = selectedOption.getAttribute('data-precio');
                
                if (precio) {
                    montoInput.value = parseFloat(precio).toFixed(2);
                    // Resetear calendario al mes actual
                    currentCalendarYear = new Date().getFullYear();
                    currentCalendarMonth = new Date().getMonth();
                    loadCalendar();
                } else {
                    montoInput.value = '';
                    calendarDiv.innerHTML = '<div class="text-center text-gray-500">Seleccione un espacio para ver la disponibilidad</div>';
                }
                
                checkFormValidity();
            });

            // La verificación de disponibilidad ahora se maneja en selectDate()
            // cuando se selecciona una fecha en el calendario

            function loadCalendar() {
                const spaceId = spaceSelect.value;
                if (!spaceId) return;

                // Mostrar mensaje de carga
                calendarDiv.innerHTML = '<div class="text-center text-gray-500">Cargando calendario...</div>';

                // Obtener fechas ocupadas del servidor
                fetch('{{ route("reservations.get-occupied-dates") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        space_id: spaceId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    calendarDiv.innerHTML = generateCalendar(currentCalendarYear, currentCalendarMonth, spaceId, data.occupied_dates || []);
                })
                .catch(error => {
                    console.error('Error al cargar fechas ocupadas:', error);
                    calendarDiv.innerHTML = generateCalendar(currentCalendarYear, currentCalendarMonth, spaceId, []);
                });
            }

            function generateCalendar(year, month, spaceId, occupiedDates = []) {
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDayOfWeek = firstDay.getDay();

                const monthNames = [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];

                // Calcular si se pueden mostrar botones de navegación
                const currentDate = new Date();
                const isCurrentMonth = year === currentDate.getFullYear() && month === currentDate.getMonth();
                const currentMonthDate = new Date(year, month);
                const isMaxMonth = currentMonthDate >= maxDate;
                
                let html = `
                    <div class="flex items-center justify-between mb-4">
                        <button type="button" onclick="navigateCalendar(-1)" 
                                class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded ${isCurrentMonth ? 'invisible' : ''}"
                                ${isCurrentMonth ? 'disabled' : ''}>
                            ← Anterior
                        </button>
                        <h5 class="text-lg font-semibold">${monthNames[month]} ${year}</h5>
                        <button type="button" onclick="navigateCalendar(1)" 
                                class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded ${isMaxMonth ? 'invisible' : ''}"
                                ${isMaxMonth ? 'disabled' : ''}>
                            Siguiente →
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-sm">
                        <div class="font-semibold p-2">Dom</div>
                        <div class="font-semibold p-2">Lun</div>
                        <div class="font-semibold p-2">Mar</div>
                        <div class="font-semibold p-2">Mié</div>
                        <div class="font-semibold p-2">Jue</div>
                        <div class="font-semibold p-2">Vie</div>
                        <div class="font-semibold p-2">Sáb</div>
                `;

                // Días vacíos al inicio
                for (let i = 0; i < startingDayOfWeek; i++) {
                    html += '<div class="p-2"></div>';
                }

                // Días del mes
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const dateStr = date.toISOString().split('T')[0];
                    const isToday = date.toDateString() === new Date().toDateString();
                    const isPast = date < new Date().setHours(0,0,0,0);
                    const isOccupied = occupiedDates.includes(dateStr);
                    const isOutOfRange = date > maxDate; // Verificar si está fuera del rango permitido
                    
                    let classes = 'p-2 border rounded';
                    let clickable = true;
                    
                    if (isPast) {
                        classes += ' bg-gray-100 text-gray-400 cursor-not-allowed';
                        clickable = false;
                    } else if (isOccupied) {
                        classes += ' bg-red-200 border-red-300 text-red-800 cursor-not-allowed';
                        clickable = false;
                    } else if (isOutOfRange) {
                        classes += ' bg-gray-100 text-gray-400 cursor-not-allowed';
                        clickable = false;
                    } else {
                        classes += ' bg-green-200 border-green-300 text-green-800 cursor-pointer hover:bg-green-100';
                    }
                    
                    if (isToday) {
                        classes += ' ring-2 ring-blue-500';
                    }

                    const onclick = clickable ? `onclick="selectDate('${dateStr}')"` : '';
                    html += `<div class="${classes}" data-date="${dateStr}" ${onclick}>${day}</div>`;
                }

                html += '</div>';
                return html;
            }

            window.navigateCalendar = function(direction) {
                const spaceId = spaceSelect.value;
                if (!spaceId) return;
                
                // Calcular nuevo mes y año
                currentCalendarMonth += direction;
                if (currentCalendarMonth > 11) {
                    currentCalendarMonth = 0;
                    currentCalendarYear++;
                } else if (currentCalendarMonth < 0) {
                    currentCalendarMonth = 11;
                    currentCalendarYear--;
                }
                
                // Verificar límites
                const currentDate = new Date();
                const newDate = new Date(currentCalendarYear, currentCalendarMonth);
                const minDate = new Date(currentDate.getFullYear(), currentDate.getMonth());
                
                if (newDate < minDate) {
                    currentCalendarMonth = currentDate.getMonth();
                    currentCalendarYear = currentDate.getFullYear();
                    return;
                }
                
                if (newDate > maxDate) {
                    currentCalendarMonth = maxDate.getMonth();
                    currentCalendarYear = maxDate.getFullYear();
                    return;
                }
                
                // Recargar calendario
                loadCalendar();
            };

            window.selectDate = function(dateStr) {
                fechaInput.value = dateStr;
                
                // Actualizar la visualización de la fecha seleccionada en formato dd/MM/yyyy
                const fechaSeleccionadaDisplay = document.getElementById('fecha_seleccionada_display');
                const date = new Date(dateStr + 'T00:00:00'); // Agregar tiempo para evitar problemas de zona horaria
                const formattedDate = date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
                fechaSeleccionadaDisplay.innerHTML = `<span class="font-semibold text-blue-600">${formattedDate}</span>`;
                
                // Actualizar estilos del calendario
                document.querySelectorAll('[data-date]').forEach(el => {
                    el.classList.remove('bg-blue-200', 'border-blue-300', 'text-blue-800');
                    if (!el.classList.contains('bg-gray-100') && !el.classList.contains('bg-red-200')) {
                        el.classList.remove('bg-green-100');
                        el.classList.add('bg-green-200', 'border-green-300', 'text-green-800');
                    }
                });
                
                const selectedEl = document.querySelector(`[data-date="${dateStr}"]`);
                if (selectedEl && !selectedEl.classList.contains('bg-gray-100') && !selectedEl.classList.contains('bg-red-200')) {
                    selectedEl.classList.remove('bg-green-200', 'border-green-300', 'text-green-800');
                    selectedEl.classList.add('bg-blue-200', 'border-blue-300', 'text-blue-800');
                }
                
                checkAvailability();
                checkFormValidity();
            };

            function checkAvailability() {
                const spaceId = spaceSelect.value;
                const fecha = fechaInput.value;
                
                if (!spaceId || !fecha) {
                    availabilityMessage.innerHTML = '';
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
                        fecha_reserva: fecha
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

            function checkFormValidity() {
                const apartamento = document.getElementById('apartamento_id').value;
                const space = spaceSelect.value;
                const fecha = fechaInput.value;
                const monto = montoInput.value;
                
                console.log('Validando formulario:', {
                    apartamento: apartamento,
                    space: space,
                    fecha: fecha,
                    monto: monto
                });
                
                // Hacer el botón siempre habilitado para facilitar las pruebas
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                
                // Opcional: mostrar advertencia si faltan campos
                if (!apartamento || !space || !fecha || !monto) {
                    console.warn('Algunos campos están vacíos, pero el botón permanece habilitado');
                }
            }

            // Inicializar
            if (spaceSelect.value) {
                const selectedOption = spaceSelect.options[spaceSelect.selectedIndex];
                const precio = selectedOption.getAttribute('data-precio');
                if (precio) {
                    montoInput.value = parseFloat(precio).toFixed(2);
                    loadCalendar();
                }
            }
            
            checkFormValidity();
        });
    </script>
    @endpush
</x-app-with-sidebar>