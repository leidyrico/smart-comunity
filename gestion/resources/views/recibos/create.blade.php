<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Recibo de Gasto Común') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Nuevo Recibo</h3>
                        <a href="{{ route('recibos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </a>
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

                    <!-- Barra de progreso (oculta inicialmente) -->
                    <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="mt-3 text-center">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Creando Recibo</h3>
                                <div class="mt-2 px-7 py-3">
                                    <p class="text-sm text-gray-500">Por favor espere mientras se crea el recibo y se envían los correos electrónicos...</p>
                                    <div class="mt-4">
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                        </div>
                                        <p id="progress-text" class="text-xs text-gray-500 mt-2">Iniciando proceso...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="recibo-form" action="{{ route('recibos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Número de Recibo -->
                            <div>
                                <label for="numero_recibo" class="block text-sm font-medium text-gray-700">Número de Recibo *</label>
                                <input type="text" name="numero_recibo" id="numero_recibo" value="{{ old('numero_recibo') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ej: RGC-2024-001">
                                @error('numero_recibo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Período -->
                            <div>
                                <label for="periodo" class="block text-sm font-medium text-gray-700">Período *</label>
                                <input type="text" name="periodo" id="periodo" value="{{ old('periodo') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ej: 2024-01">
                                @error('periodo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Emisión -->
                            <div>
                                <label for="fecha_emision" class="block text-sm font-medium text-gray-700">Fecha de Emisión *</label>
                                <input type="date" name="fecha_emision" id="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('fecha_emision')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div>
                                <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700">Fecha de Vencimiento *</label>
                                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('fecha_vencimiento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Conceptos de Gastos -->
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Conceptos de Gastos</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Administración -->
                                <div>
                                    <label for="valor_administracion" class="block text-sm font-medium text-gray-700">Administración *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="valor_administracion" id="valor_administracion" value="{{ old('valor_administracion', 0) }}" step="0.01" min="0" required class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                                    </div>
                                    @error('valor_administracion')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Mantenimiento -->
                                <div>
                                    <label for="valor_mantenimiento" class="block text-sm font-medium text-gray-700">Mantenimiento</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="valor_mantenimiento" id="valor_mantenimiento" value="{{ old('valor_mantenimiento', 0) }}" step="0.01" min="0" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                                    </div>
                                    @error('valor_mantenimiento')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Aseo -->
                                <div>
                                    <label for="valor_aseo" class="block text-sm font-medium text-gray-700">Aseo</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="valor_aseo" id="valor_aseo" value="{{ old('valor_aseo', 0) }}" step="0.01" min="0" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                                    </div>
                                    @error('valor_aseo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Vigilancia -->
                                <div>
                                    <label for="valor_vigilancia" class="block text-sm font-medium text-gray-700">Vigilancia</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="valor_vigilancia" id="valor_vigilancia" value="{{ old('valor_vigilancia', 0) }}" step="0.01" min="0" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                                    </div>
                                    @error('valor_vigilancia')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Otros Conceptos -->
                                <div>
                                    <label for="otros_conceptos" class="block text-sm font-medium text-gray-700">Otros Conceptos</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="otros_conceptos" id="otros_conceptos" value="{{ old('otros_conceptos', 0) }}" step="0.01" min="0" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="0.00">
                                    </div>
                                    @error('otros_conceptos')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700">Estado *</label>
                            <select name="estado" id="estado" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="vencido" {{ old('estado') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                <option value="anulado" {{ old('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                            </select>
                            @error('estado')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Archivo Adjunto -->
                        <div>
                            <label for="archivo_adjunto" class="block text-sm font-medium text-gray-700">Archivo Adjunto (Opcional)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="archivo_adjunto" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Subir archivo</span>
                                            <input id="archivo_adjunto" name="archivo_adjunto" type="file" class="sr-only" accept=".pdf,.xlsx,.xls">
                                        </label>
                                        <p class="pl-1">o arrastrar y soltar</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, Excel hasta 10MB</p>
                                </div>
                            </div>
                            @error('archivo_adjunto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Total (calculado automáticamente) -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-gray-900">Total del Recibo:</span>
                                <span id="total-display" class="text-2xl font-bold text-indigo-600">$0.00</span>
                            </div>
                        </div>

                        <!-- Opción de Envío de Correo -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <input type="checkbox" id="enviar_correo" name="enviar_correo" value="1" checked 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="enviar_correo" class="ml-3 text-sm font-medium text-gray-700">
                                    Enviar correo a los propietarios
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-600 ml-7">
                                <svg class="inline w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Si está marcado, cada propietario recibirá un correo individual con el recibo correspondiente.
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('recibos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <button id="submit-btn" type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span id="submit-text">Crear Recibo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calcular total automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['valor_administracion', 'valor_mantenimiento', 'valor_aseo', 'valor_vigilancia', 'otros_conceptos'];
            const totalDisplay = document.getElementById('total-display');
            const form = document.getElementById('recibo-form');
            const loadingOverlay = document.getElementById('loading-overlay');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');

            function calculateTotal() {
                let total = 0;
                inputs.forEach(function(inputId) {
                    const value = parseFloat(document.getElementById(inputId).value) || 0;
                    total += value;
                });
                totalDisplay.textContent = '$' + total.toFixed(2);
            }

            inputs.forEach(function(inputId) {
                document.getElementById(inputId).addEventListener('input', calculateTotal);
            });

            // Calcular total inicial
            calculateTotal();

            // Manejar envío del formulario con barra de progreso
            form.addEventListener('submit', function(e) {
                // Mostrar overlay de carga
                loadingOverlay.classList.remove('hidden');
                
                // Deshabilitar botón de envío
                submitBtn.disabled = true;
                submitText.textContent = 'Procesando...';
                
                // Verificar si se va a enviar correo
                const enviarCorreo = document.getElementById('enviar_correo').checked;
                
                // Simular progreso
                let progress = 0;
                const progressSteps = enviarCorreo ? [
                    { percent: 20, text: 'Validando datos...' },
                    { percent: 40, text: 'Creando recibo...' },
                    { percent: 60, text: 'Generando PDF...' },
                    { percent: 80, text: 'Enviando correos...' },
                    { percent: 100, text: 'Finalizando...' }
                ] : [
                    { percent: 25, text: 'Validando datos...' },
                    { percent: 50, text: 'Creando recibo...' },
                    { percent: 75, text: 'Generando PDF...' },
                    { percent: 100, text: 'Finalizando...' }
                ];
                
                let stepIndex = 0;
                const progressInterval = setInterval(function() {
                    if (stepIndex < progressSteps.length) {
                        const step = progressSteps[stepIndex];
                        progressBar.style.width = step.percent + '%';
                        progressText.textContent = step.text;
                        stepIndex++;
                    } else {
                        clearInterval(progressInterval);
                    }
                }, 800); // Cambiar cada 800ms
                
                // El formulario se enviará normalmente
                // El overlay se ocultará cuando la página se recargue o redirija
            });
        });
    </script>
</x-app-with-sidebar>