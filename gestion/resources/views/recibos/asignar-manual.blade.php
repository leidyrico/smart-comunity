<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asignación Manual de Recibos Vencidos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Información sobre la funcionalidad -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-3">📋 Asignación Manual de Recibos</h3>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p><strong>Esta funcionalidad permite:</strong></p>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li>Asignar recibos vencidos existentes a apartamentos específicos</li>
                                <li>Seleccionar manualmente qué apartamento debe cada recibo</li>
                                <li>Los recibos asignados se sumarán al saldo pendiente del apartamento</li>
                                <li>El estatus financiero del apartamento se actualizará automáticamente</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Mensajes de éxito/error -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Estadísticas -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-yellow-800">{{ $recibosDisponibles->count() }}</div>
                            <div class="text-sm text-yellow-600">Recibos Disponibles</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-green-800">{{ $apartamentos->count() }}</div>
                            <div class="text-sm text-green-600">Apartamentos Activos</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-blue-800">${{ number_format($totalMontoDisponible, 0, ',', '.') }}</div>
                            <div class="text-sm text-blue-600">Monto Total Disponible</div>
                        </div>
                    </div>

                    @if($recibosDisponibles->count() > 0)
                        <!-- Formulario de asignación -->
                        <form method="POST" action="{{ route('recibos.asignar-manual.process') }}" id="asignacionForm">
                            @csrf
                            
                            <!-- Controles de selección masiva -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                                <div class="flex flex-wrap items-center gap-4">
                                    <button type="button" id="seleccionarTodos" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Seleccionar Todos
                                    </button>
                                    <button type="button" id="deseleccionarTodos" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Deseleccionar Todos
                                    </button>
                                    <div class="flex items-center gap-2">
                                        <label for="apartamentoMasivo" class="text-sm font-medium text-gray-700">Asignar todos a:</label>
                                        <select id="apartamentoMasivo" class="border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="">Seleccionar apartamento...</option>
                                            @foreach($apartamentos as $apartamento)
                                                <option value="{{ $apartamento->id }}">
                                                    {{ $apartamento->numero }} - {{ $apartamento->propietario }}
                                                    ({{ ucfirst($apartamento->estatus_financiero) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="asignarMasivo" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                            Aplicar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de recibos -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="selectAll" class="rounded">
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Recibo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Período
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fechas
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Monto
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Asignaciones Existentes
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Asignar a Apartamento
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recibosDisponibles as $recibo)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="checkbox" name="recibos_seleccionados[]" value="{{ $recibo->id }}" class="rounded recibo-checkbox">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $recibo->numero_recibo }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $recibo->periodo }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        <div>Emisión: {{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') }}</div>
                                                        <div>Vencimiento: {{ \Carbon\Carbon::parse($recibo->fecha_vencimiento)->format('d/m/Y') }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">${{ number_format($recibo->total_recibo, 0, ',', '.') }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        Adm: ${{ number_format($recibo->valor_administracion, 0, ',', '.') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($recibo->estado === 'vencido') bg-red-100 text-red-800 
                                                        @elseif($recibo->estado === 'activo') bg-yellow-100 text-yellow-800 
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($recibo->estado) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($recibo->pagos->count() > 0)
                                                        <div class="text-xs text-gray-600">
                                                            <div class="font-medium text-gray-900 mb-1">Asignado a:</div>
                                                            @foreach($recibo->pagos as $pago)
                                                                <div class="flex items-center space-x-1 mb-1">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                        Apt. {{ $pago->apartamento->numero }}
                                                                    </span>
                                                                    <span class="text-xs text-gray-500">
                                                                        ({{ ucfirst($pago->apartamento->estatus_financiero) }})
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400 italic">Sin asignaciones</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <select name="apartamentos[{{ $recibo->id }}]" class="border-gray-300 rounded-md shadow-sm text-sm apartamento-select" data-recibo-id="{{ $recibo->id }}">
                                                        <option value="">Seleccionar apartamento...</option>
                                                        @foreach($apartamentos as $apartamento)
                                                            <option value="{{ $apartamento->id }}">
                                                                {{ $apartamento->numero }} - {{ $apartamento->propietario }}
                                                                ({{ ucfirst($apartamento->estatus_financiero) }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Botones de acción -->
                            <div class="mt-6 flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    <span id="contadorSeleccionados">0</span> recibos seleccionados
                                </div>
                                <div class="flex gap-4">
                                    <a href="{{ route('recibos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" id="btnAsignar" disabled>
                                        Asignar Recibos Seleccionados
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <!-- No hay recibos disponibles -->
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg mb-4">📄</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay recibos disponibles para asignar</h3>
                            <p class="text-gray-600 mb-4">Todos los recibos vencidos ya están asignados a apartamentos.</p>
                            <a href="{{ route('recibos.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Volver a Recibos
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para funcionalidad interactiva -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 DEBUG: Iniciando JavaScript de asignación manual');
            
            const selectAll = document.getElementById('selectAll');
            const reciboCheckboxes = document.querySelectorAll('.recibo-checkbox');
            const apartamentoSelects = document.querySelectorAll('.apartamento-select');
            const contadorSeleccionados = document.getElementById('contadorSeleccionados');
            const btnAsignar = document.getElementById('btnAsignar');
            const seleccionarTodos = document.getElementById('seleccionarTodos');
            const deseleccionarTodos = document.getElementById('deseleccionarTodos');
            const apartamentoMasivo = document.getElementById('apartamentoMasivo');
            const asignarMasivo = document.getElementById('asignarMasivo');

            console.log('🔧 DEBUG: Elementos encontrados:');
            console.log('- selectAll:', selectAll);
            console.log('- reciboCheckboxes:', reciboCheckboxes.length);
            console.log('- apartamentoSelects:', apartamentoSelects.length);
            console.log('- btnAsignar:', btnAsignar);
            console.log('- contadorSeleccionados:', contadorSeleccionados);

            // Verificar si existen los elementos antes de agregar event listeners
            if (!selectAll || !btnAsignar || reciboCheckboxes.length === 0) {
                console.log('❌ DEBUG: Faltan elementos esenciales, saliendo del script');
                return; // No hay recibos disponibles, salir del script
            }

            // Función para actualizar contador y estado del botón
            function actualizarContador() {
                const seleccionados = document.querySelectorAll('.recibo-checkbox:checked');
                console.log('🔧 DEBUG: Recibos seleccionados:', seleccionados.length);
                contadorSeleccionados.textContent = seleccionados.length;
                
                // El botón se habilita SOLO si hay al menos un recibo seleccionado
                const puedeAsignar = seleccionados.length > 0;
                
                console.log('🔧 DEBUG: Puede asignar:', puedeAsignar);
                console.log('🔧 DEBUG: Estado actual del botón (disabled):', btnAsignar.disabled);
                
                // Forzar el estado del botón
                if (puedeAsignar) {
                    btnAsignar.disabled = false;
                    btnAsignar.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnAsignar.classList.add('hover:bg-blue-700');
                } else {
                    btnAsignar.disabled = true;
                    btnAsignar.classList.add('opacity-50', 'cursor-not-allowed');
                    btnAsignar.classList.remove('hover:bg-blue-700');
                }
                
                console.log('🔧 DEBUG: Nuevo estado del botón (disabled):', btnAsignar.disabled);
                console.log('🔧 DEBUG: Clases del botón:', btnAsignar.className);
            }

            // Seleccionar/deseleccionar todos
            selectAll.addEventListener('change', function() {
                reciboCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                actualizarContador();
            });

            // Botones de selección masiva
            if (seleccionarTodos) {
                seleccionarTodos.addEventListener('click', function() {
                    reciboCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    selectAll.checked = true;
                    actualizarContador();
                });
            }

            if (deseleccionarTodos) {
                deseleccionarTodos.addEventListener('click', function() {
                    reciboCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    selectAll.checked = false;
                    actualizarContador();
                });
            }

            // Asignación masiva de apartamento
            if (asignarMasivo && apartamentoMasivo) {
                asignarMasivo.addEventListener('click', function() {
                    const apartamentoId = apartamentoMasivo.value;
                    if (!apartamentoId) {
                        alert('Por favor selecciona un apartamento.');
                        return;
                    }

                    const seleccionados = document.querySelectorAll('.recibo-checkbox:checked');
                    seleccionados.forEach(checkbox => {
                        const reciboId = checkbox.value;
                        const apartamentoSelect = document.querySelector(`select[name="apartamentos[${reciboId}]"]`);
                        if (apartamentoSelect) {
                            apartamentoSelect.value = apartamentoId;
                        }
                    });
                    
                    actualizarContador();
                });
            }

            // Escuchar cambios en checkboxes y selects
            reciboCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', actualizarContador);
            });

            apartamentoSelects.forEach(select => {
                select.addEventListener('change', actualizarContador);
            });

            // Validación antes de enviar
            const asignacionForm = document.getElementById('asignacionForm');
            if (asignacionForm) {
                asignacionForm.addEventListener('submit', function(e) {
                    const seleccionados = document.querySelectorAll('.recibo-checkbox:checked');
                    
                    if (seleccionados.length === 0) {
                        e.preventDefault();
                        alert('Por favor selecciona al menos un recibo.');
                        return;
                    }
                    
                    let tieneAsignaciones = false;
                    let recibosConApartamento = [];
                    
                    seleccionados.forEach(checkbox => {
                        const reciboId = checkbox.value;
                        const apartamentoSelect = document.querySelector(`select[name="apartamentos[${reciboId}]"]`);
                        if (apartamentoSelect && apartamentoSelect.value) {
                            tieneAsignaciones = true;
                            recibosConApartamento.push(reciboId);
                        }
                    });
                    
                    if (!tieneAsignaciones) {
                        e.preventDefault();
                        alert('Por favor asigna al menos un apartamento a los recibos seleccionados.');
                        return;
                    }
                    
                    console.log('🔧 DEBUG: Enviando formulario con', recibosConApartamento.length, 'recibos asignados');
                });
            }

            // Inicializar contador
            console.log('🔧 DEBUG: Inicializando contador...');
            console.log('🔧 DEBUG: Estado inicial del botón (disabled):', btnAsignar.disabled);
            console.log('🔧 DEBUG: Checkboxes marcados al inicializar:', document.querySelectorAll('.recibo-checkbox:checked').length);
            actualizarContador();
            
            console.log('🔧 DEBUG: JavaScript de asignación manual completamente inicializado');
        });
    </script>
</x-app-with-sidebar>