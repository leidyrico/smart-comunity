<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Recibos de Gasto Común') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Recibos</h3>
                        <div class="flex space-x-2">
                            <button id="deleteSelectedBtn" class="hidden inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Eliminar Seleccionados
                            </button>
                            <a href="{{ route('recibos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nuevo Recibo
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <form method="GET" action="{{ route('recibos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="numero_recibo" class="block text-sm font-medium text-gray-700">Número de Recibo</label>
                                <input type="text" name="numero_recibo" id="numero_recibo" value="{{ request('numero_recibo') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Buscar por número...">
                            </div>
                            <div>
                                <label for="periodo" class="block text-sm font-medium text-gray-700">Período</label>
                                <input type="text" name="periodo" id="periodo" value="{{ request('periodo') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ej: 2024-01">
                            </div>
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select name="estado" id="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="vencido" {{ request('estado') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                    <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Resumen Estadístico -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-600">Total Recibos</p>
                                    <p class="text-2xl font-semibold text-blue-900">{{ $estadisticas['total_recibos'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-600">Recibos Activos</p>
                                    <p class="text-2xl font-semibold text-green-900">{{ $estadisticas['recibos_activos'] }}</p>
                                    <p class="text-xs text-green-600">${{ number_format($estadisticas['total_valor_activos'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-red-600">Recibos Vencidos</p>
                                    <p class="text-2xl font-semibold text-red-900">{{ $estadisticas['recibos_vencidos'] }}</p>
                                    <p class="text-xs text-red-600">${{ number_format($estadisticas['total_valor_vencidos'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Recibos Anulados</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $estadisticas['recibos_anulados'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    


                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('email_success'))
                        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                            <span class="block sm:inline">{{ session('email_success') }}</span>
                        </div>
                    @endif

                    @if(session('email_warning'))
                        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="block sm:inline">{{ session('email_warning') }}</span>
                        </div>
                    @endif

                    @if(session('email_error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="block sm:inline">{{ session('email_error') }}</span>
                        </div>
                    @endif

                    @if($recibos->count() > 0)
                        <div class="overflow-x-auto">
                            <form id="deleteMultipleForm" action="{{ route('recibos.destroy-multiple') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Emisión</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Vencimiento</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recibos as $recibo)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" name="recibo_ids[]" value="{{ $recibo->id }}" class="recibo-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $recibo->numero_recibo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $recibo->periodo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $recibo->fecha_emision->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="{{ $recibo->estaVencido() ? 'text-red-600 font-medium' : '' }}">
                                                    {{ $recibo->fecha_vencimiento->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="font-medium">${{ number_format($recibo->total_recibo, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    {{ $recibo->estado === 'activo' ? 'bg-green-100 text-green-800' : 
                                                       ($recibo->estado === 'vencido' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($recibo->estado) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('recibos.show', $recibo) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center" title="Ver">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('recibos.edit', $recibo) }}" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center" title="Editar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                @if($recibo->archivo_adjunto)
                                                    <a href="{{ route('recibos.descargar-archivo', $recibo) }}" class="text-green-600 hover:text-green-900 inline-flex items-center" title="Descargar Archivo">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                                <button onclick="deleteReciboSimple({{ $recibo->id }}, '{{ $recibo->numero_recibo }}')" class="text-red-600 hover:text-red-900 inline-flex items-center" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay recibos registrados</h3>
                            <p class="mt-1 text-sm text-gray-500">Comience creando un nuevo recibo o generando recibos masivos.</p>
                            <div class="mt-6 flex justify-center space-x-4">
                                <a href="{{ route('recibos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nuevo Recibo
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado, inicializando funcionalidades...');
        
        // Elementos principales
        const selectAllCheckbox = document.getElementById('selectAll');
        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
        const deleteMultipleForm = document.getElementById('deleteMultipleForm');
        
        console.log('Elementos encontrados:', {
            selectAllCheckbox: !!selectAllCheckbox,
            deleteSelectedBtn: !!deleteSelectedBtn,
            deleteMultipleForm: !!deleteMultipleForm
        });
        
        // Función para actualizar el estado del botón eliminar seleccionados
        function updateDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.recibo-checkbox:checked');
            console.log('Actualizando botón, checkboxes seleccionados:', checkedBoxes.length);
            
            if (checkedBoxes.length > 0) {
                deleteSelectedBtn.classList.remove('hidden');
            } else {
                deleteSelectedBtn.classList.add('hidden');
            }
        }
        
        // Manejar checkbox "Seleccionar Todos"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                console.log('Checkbox selectAll cambiado a:', this.checked);
                const reciboCheckboxes = document.querySelectorAll('.recibo-checkbox');
                console.log('Checkboxes encontrados:', reciboCheckboxes.length);
                
                reciboCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                
                updateDeleteButton();
            });
        }
        
        // Manejar checkboxes individuales
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('recibo-checkbox')) {
                console.log('Checkbox individual cambiado:', e.target.checked);
                
                const reciboCheckboxes = document.querySelectorAll('.recibo-checkbox');
                const checkedBoxes = document.querySelectorAll('.recibo-checkbox:checked');
                console.log('Total:', reciboCheckboxes.length, 'Seleccionados:', checkedBoxes.length);
                
                // Actualizar estado del checkbox "Seleccionar todo"
                if (selectAllCheckbox) {
                    if (checkedBoxes.length === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedBoxes.length === reciboCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                }
                
                updateDeleteButton();
            }
        });
        
        // Manejar botón "Eliminar Seleccionados"
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Botón eliminar seleccionados clickeado');
                
                const checkedBoxes = document.querySelectorAll('.recibo-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Por favor selecciona al menos un recibo para eliminar.');
                    return;
                }

                const count = checkedBoxes.length;
                const message = count === 1 
                    ? '¿Estás seguro de que deseas eliminar el recibo seleccionado?' 
                    : '¿Estás seguro de que deseas eliminar los ' + count + ' recibos seleccionados?';
                
                if (confirm(message)) {
                    // Solicitar clave de administrador
                    const adminPassword = prompt('Por favor, ingrese la clave de administrador para confirmar la eliminación:');
                    
                    if (!adminPassword) {
                        alert('Eliminación cancelada. Se requiere la clave de administrador.');
                        return;
                    }
                    
                    // Limpiar campos de contraseña previos
                    const existingPasswordFields = deleteMultipleForm.querySelectorAll('input[name="admin_password"]');
                    existingPasswordFields.forEach(field => field.remove());
                    
                    // Agregar campo de contraseña al formulario
                    const passwordField = document.createElement('input');
                    passwordField.type = 'hidden';
                    passwordField.name = 'admin_password';
                    passwordField.value = adminPassword;
                    deleteMultipleForm.appendChild(passwordField);
                    
                    deleteMultipleForm.submit();
                }
            });
        }

        
        // Inicializar estado del botón
        updateDeleteButton();
        
        console.log('Inicialización completada');
    });
    
    // Función simple para eliminar recibo individual
    function deleteReciboSimple(reciboId, numeroRecibo) {
        if (confirm(`¿Está seguro de que desea eliminar el recibo ${numeroRecibo}?`)) {
            // Crear formulario para enviar la eliminación
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/recibos') }}/${reciboId}`;
            form.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Agregar método DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            // Enviar formulario
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    

    



    </script>
</x-app-with-sidebar>