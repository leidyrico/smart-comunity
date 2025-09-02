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
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm" id="btnAsignarTop" disabled>
                                            Asignar Recibos Seleccionados
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acción
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
                                        <button type="button" 
                                                class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium rounded-full transition-colors duration-200"
                                                onclick="openAssignmentModal('{{ $recibo->id }}')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Ver {{ $recibo->pagos->count() }} asignación{{ $recibo->pagos->count() > 1 ? 'es' : '' }}
                                        </button>
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
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <button type="button" onclick="asignarReciboIndividual({{ $recibo->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs" id="btnAsignarIndividual_{{ $recibo->id }}" disabled>
                                                        Asignar
                                                    </button>
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

    <!-- Modal para mostrar todas las asignaciones -->
    <div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-6 border max-w-4xl w-full mx-4 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Todas las Asignaciones</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeAssignmentModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent" class="space-y-2">
                    <!-- El contenido se llenará dinámicamente -->
                </div>
                <div class="mt-4">
                    <button type="button" 
                            class="w-full px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
                            onclick="closeAssignmentModal()">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para funcionalidad interactiva -->
    <script>
        // Preparar datos de recibos para el modal
        const recibosData = @json($recibosDisponibles);

        function openAssignmentModal(reciboId) {
            const recibo = recibosData.find(r => r.id == reciboId);
            if (!recibo) return;

            const modalContent = document.getElementById('modalContent');
            
            if (!recibo.pagos || recibo.pagos.length === 0) {
                modalContent.innerHTML = '<p class="text-gray-500 text-center py-8">No hay asignaciones para este recibo.</p>';
            } else {
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
                recibo.pagos.forEach(pago => {
                    const statusColor = pago.apartamento.estatus_financiero === 'solvente' ? 'bg-green-100 text-green-800' : 
                                       pago.apartamento.estatus_financiero === 'deudor' ? 'bg-red-100 text-red-800' : 
                                       pago.apartamento.estatus_financiero === 'parcial' ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-gray-100 text-gray-800';
                    
                    html += `
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-lg font-semibold text-gray-900">Apt. ${pago.apartamento.numero}</p>
                                        <p class="text-sm text-gray-600 truncate">${pago.apartamento.propietario || 'Sin propietario'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor}">
                                    ${pago.apartamento.estatus_financiero.charAt(0).toUpperCase() + pago.apartamento.estatus_financiero.slice(1)}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <button onclick="eliminarAsignacion(${recibo.id}, ${pago.apartamento.id})" 
                                            class="inline-flex items-center px-2 py-1 border border-red-300 rounded text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                            title="Eliminar asignación">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                modalContent.innerHTML = html;
            }

            document.getElementById('assignmentModal').classList.remove('hidden');
        }

        function closeAssignmentModal() {
            document.getElementById('assignmentModal').classList.add('hidden');
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('assignmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAssignmentModal();
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 DEBUG: Iniciando JavaScript de asignación manual');
            
            const selectAll = document.getElementById('selectAll');
            const reciboCheckboxes = document.querySelectorAll('.recibo-checkbox');
            const apartamentoSelects = document.querySelectorAll('.apartamento-select');
            const contadorSeleccionados = document.getElementById('contadorSeleccionados');
            const btnAsignar = document.getElementById('btnAsignar');
            const btnAsignarTop = document.getElementById('btnAsignarTop');
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
                
                // Forzar el estado de ambos botones
                [btnAsignar, btnAsignarTop].forEach(btn => {
                    if (btn) {
                        if (puedeAsignar) {
                            btn.disabled = false;
                            btn.classList.remove('opacity-50', 'cursor-not-allowed');
                            btn.classList.add('hover:bg-blue-700');
                        } else {
                            btn.disabled = true;
                            btn.classList.add('opacity-50', 'cursor-not-allowed');
                            btn.classList.remove('hover:bg-blue-700');
                        }
                    }
                });
                
                // Actualizar estado de botones individuales
                apartamentoSelects.forEach(select => {
                    const reciboId = select.dataset.reciboId;
                    const btnIndividual = document.getElementById(`btnAsignarIndividual_${reciboId}`);
                    if (btnIndividual) {
                        if (select.value) {
                            btnIndividual.disabled = false;
                            btnIndividual.classList.remove('opacity-50', 'cursor-not-allowed');
                        } else {
                            btnIndividual.disabled = true;
                            btnIndividual.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    }
                });
                
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
            
            // Inicializar estado de botones individuales
            actualizarContador();
            
            // Función para asignar recibo individual
            window.asignarReciboIndividual = function(reciboId) {
                const select = document.querySelector(`select[data-recibo-id="${reciboId}"]`);
                if (!select || !select.value) {
                    alert('Por favor selecciona un apartamento para este recibo.');
                    return;
                }
                
                // Crear formulario temporal para enviar la asignación individual
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("recibos.asignar-manual.process") }}';
                form.style.display = 'none';
                
                // Token CSRF
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Datos del recibo
                const reciboInput = document.createElement('input');
                reciboInput.type = 'hidden';
                reciboInput.name = `recibos[${reciboId}]`;
                reciboInput.value = select.value;
                form.appendChild(reciboInput);
                
                // Agregar al DOM y enviar
                document.body.appendChild(form);
                form.submit();
            };

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

        // Función para eliminar asignación
        function eliminarAsignacion(reciboId, apartamentoId) {
            if (confirm('¿Está seguro de que desea eliminar esta asignación? Esta acción no se puede deshacer.')) {
                fetch(`/recibos/eliminar-asignacion/${reciboId}/${apartamentoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Recargar la página para actualizar la vista
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la asignación. Por favor, inténtelo de nuevo.');
                });
            }
        }
    </script>
</x-app-with-sidebar>