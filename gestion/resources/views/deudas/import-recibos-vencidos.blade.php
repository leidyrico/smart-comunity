<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importación de Recibos Vencidos con Selección de Apartamentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Información sobre la funcionalidad -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-orange-900 mb-3">⚠️ Importación Selectiva de Recibos Vencidos</h3>
                        <div class="text-sm text-orange-800 space-y-2">
                            <p><strong>Esta funcionalidad permite:</strong></p>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li>Cargar un archivo Excel con recibos vencidos</li>
                                <li>Seleccionar qué apartamentos específicos deben estos recibos</li>
                                <li>Asignar automáticamente solo a los apartamentos seleccionados</li>
                                <li>Mantener el control sobre qué apartamentos quedan como deudores</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Estructura del archivo Excel -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-3">📋 Estructura del Archivo Excel</h3>
                        <div class="bg-white p-4 rounded-lg border">
                            <h4 class="font-semibold text-blue-800 mb-2">Hoja: "Recibos"</h4>
                            <div class="text-xs text-gray-600 space-y-1">
                                <p><strong>Columnas requeridas:</strong></p>
                                <ul class="list-disc list-inside ml-2">
                                    <li>numero_recibo</li>
                                    <li>periodo</li>
                                    <li>fecha_emision</li>
                                    <li>fecha_vencimiento</li>
                                    <li>valor_administracion</li>
                                    <li>valor_aseo</li>
                                    <li>valor_vigilancia</li>
                                    <li>valor_mantenimiento</li>
                                    <li>otros_conceptos</li>
                                    <li>estado (debe ser 'vencido')</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Hay errores en el formulario!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ session('success') }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deudas.import.recibos-vencidos.process') }}" enctype="multipart/form-data" class="space-y-6" id="importForm">
                        @csrf

                        <!-- Paso 1: Cargar archivo Excel -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Paso 1: Cargar Archivo de Recibos Vencidos</h4>
                            
                            <div>
                                <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                                    Archivo Excel (.xlsx)
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Seleccionar archivo Excel </span>
                                                <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls" required class="sr-only" onchange="handleFileSelect()">
                                            </label>
                                            <p class="pl-1">o arrastrar y soltar</p>
                                        </div>
                                        <p class="text-xs text-gray-500">Solo archivos Excel (.xlsx, .xls) hasta 10MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: Seleccionar apartamentos (se muestra después de cargar el archivo) -->
                        <div id="apartamentosSection" class="bg-gray-50 border border-gray-200 rounded-md p-6" style="display: none;">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Paso 2: Seleccionar Apartamentos que Deben los Recibos Vencidos</h4>
                            
                            <div class="mb-4">
                                <div class="flex items-center space-x-4 mb-3">
                                    <button type="button" onclick="selectAllApartments()" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                        Seleccionar Todos
                                    </button>
                                    <button type="button" onclick="deselectAllApartments()" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                                        Deseleccionar Todos
                                    </button>
                                    <span class="text-sm text-gray-600">Total apartamentos: <span id="totalApartamentos">0</span></span>
                                </div>
                            </div>

                            <div id="apartamentosList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto border border-gray-200 rounded p-4">
                                <!-- Los apartamentos se cargarán aquí dinámicamente -->
                            </div>
                        </div>

                        <!-- Opciones adicionales -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 space-y-4">
                            <h4 class="text-md font-medium text-gray-900">Opciones de Importación</h4>
                            
                            <!-- Continuar con errores -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="continuar_con_errores" 
                                           name="continuar_con_errores" 
                                           type="checkbox" 
                                           value="1"
                                           class="focus:ring-yellow-500 h-4 w-4 text-yellow-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="continuar_con_errores" class="font-medium text-gray-900">
                                        Continuar importación aunque haya errores en algunos registros
                                    </label>
                                    <p class="text-gray-500">
                                        Los registros con errores serán omitidos, pero se importarán los válidos.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-between items-center pt-6 border-t">
                            <div class="flex space-x-2">
                                <a href="{{ route('deudas.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancelar
                                </a>
                            </div>
                            <button type="submit" 
                                    id="submitBtn"
                                    class="inline-flex items-center px-6 py-3 bg-orange-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    style="display: none;"
                                    onclick="return confirm('¿Está seguro de proceder con la importación de recibos vencidos a los apartamentos seleccionados?')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                Importar Recibos Vencidos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleFileSelect() {
            const fileInput = document.getElementById('excel_file');
            const apartamentosSection = document.getElementById('apartamentosSection');
            const submitBtn = document.getElementById('submitBtn');
            
            if (fileInput.files.length > 0) {
                // Cargar apartamentos disponibles
                loadApartamentos();
                apartamentosSection.style.display = 'block';
                submitBtn.style.display = 'inline-flex';
            } else {
                apartamentosSection.style.display = 'none';
                submitBtn.style.display = 'none';
            }
        }

        function loadApartamentos() {
            fetch('{{ route("apartamentos.api.list") }}')
                .then(response => response.json())
                .then(data => {
                    const apartamentosList = document.getElementById('apartamentosList');
                    const totalApartamentos = document.getElementById('totalApartamentos');
                    
                    apartamentosList.innerHTML = '';
                    totalApartamentos.textContent = data.length;
                    
                    data.forEach(apartamento => {
                        const div = document.createElement('div');
                        div.className = 'bg-white p-3 rounded border hover:bg-gray-50';
                        div.innerHTML = `
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="apt_${apartamento.id}" 
                                       name="apartamentos_seleccionados[]" 
                                       value="${apartamento.id}" 
                                       class="apartamento-checkbox focus:ring-orange-500 h-4 w-4 text-orange-600 border-gray-300 rounded">
                                <label for="apt_${apartamento.id}" class="ml-3 cursor-pointer">
                                    <div class="text-sm font-medium text-gray-900">
                                        Apartamento ${apartamento.numero}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ${apartamento.propietario || 'Sin propietario'}
                                    </div>
                                    <div class="text-xs ${
                                        apartamento.estatus_financiero === 'moroso' ? 'text-red-600' : 
                                        (apartamento.estatus_financiero === 'deudor' ? 'text-yellow-600' : 'text-green-600')
                                    }">
                                        ${apartamento.estatus_financiero === 'moroso' ? 'Moroso' : 
                                        (apartamento.estatus_financiero === 'deudor' ? 'Deudor' : 'Solvente')}
                                    </div>
                                </label>
                            </div>
                        `;
                        apartamentosList.appendChild(div);
                    });
                })
                .catch(error => {
                    console.error('Error cargando apartamentos:', error);
                    alert('Error al cargar la lista de apartamentos');
                });
        }

        function selectAllApartments() {
            const checkboxes = document.querySelectorAll('.apartamento-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }

        function deselectAllApartments() {
            const checkboxes = document.querySelectorAll('.apartamento-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }
    </script>
</x-app-with-sidebar>