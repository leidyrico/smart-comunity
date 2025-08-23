<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importación Completa desde Excel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Información sobre la funcionalidad -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-blue-900 mb-3">📊 Importación Completa de Datos</h3>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p><strong>Esta funcionalidad permite importar:</strong></p>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li><strong>Apartamentos:</strong> Información básica de propietarios y unidades</li>
                                <li><strong>Recibos de Gasto Común:</strong> Facturas con todos los conceptos de cobro</li>
                                <li><strong>Pagos:</strong> Historial de pagos realizados por apartamento</li>
                            </ul>
                            <p class="mt-3"><strong>Ventajas:</strong></p>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li>Importación en un solo paso con relaciones automáticas</li>
                                <li>Validación cruzada entre apartamentos, recibos y pagos</li>
                                <li>Cálculo automático de saldos pendientes</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Estructura del archivo Excel -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-green-900 mb-3">📋 Estructura del Archivo Excel</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Hoja Apartamentos -->
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="font-semibold text-green-800 mb-2">Hoja: "Apartamentos"</h4>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <p><strong>Columnas requeridas:</strong></p>
                                    <ul class="list-disc list-inside ml-2">
                                        <li>numero</li>
                                        <li>piso</li>
                                        <li>torre</li>
                                        <li>propietario</li>
                                        <li>telefono</li>
                                        <li>email</li>
                                        <li>area_m2</li>
                                        <li>tipo</li>
                                        <li>estado</li>
                                        <li>observaciones</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Hoja Recibos -->
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="font-semibold text-green-800 mb-2">Hoja: "Recibos"</h4>
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
                                        <li>estado</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Hoja Pagos -->
                            <div class="bg-white p-4 rounded-lg border">
                                <h4 class="font-semibold text-green-800 mb-2">Hoja: "Pagos"</h4>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <p><strong>Columnas requeridas:</strong></p>
                                    <ul class="list-disc list-inside ml-2">
                                        <li>apartamento_numero</li>
                                        <li>recibo_numero</li>
                                        <li>fecha_pago</li>
                                        <li>monto_pagado</li>
                                        <li>metodo_pago</li>
                                        <li>numero_comprobante</li>
                                        <li>estado</li>
                                        <li>observaciones</li>
                                    </ul>
                                </div>
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

                    @if (session('import_errors'))
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Errores durante la importación:</strong>
                            <div class="mt-2 max-h-60 overflow-y-auto">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach (session('import_errors') as $error)
                                        <li class="text-sm">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (session('import_summary'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Resumen de importación exitosa:</strong>
                            <div class="mt-2 text-sm">
                                @php $summary = session('import_summary'); @endphp
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Apartamentos creados: {{ $summary['apartamentos'] ?? 0 }}</li>
                                    <li>Recibos creados: {{ $summary['recibos'] ?? 0 }}</li>
                                    <li>Pagos registrados: {{ $summary['pagos'] ?? 0 }}</li>
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deudas.import.completo.process') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Archivo Excel -->
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
                                            <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls" required class="sr-only">
                                        </label>
                                        <p class="pl-1">o arrastrar y soltar</p>
                                    </div>
                                    <p class="text-xs text-gray-500">Solo archivos Excel (.xlsx, .xls) hasta 10MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de importación -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 space-y-4">
                            <h4 class="text-md font-medium text-gray-900">Opciones de Importación</h4>
                            
                            <!-- Limpiar datos existentes -->
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="limpiar_datos" 
                                               name="limpiar_datos" 
                                               type="checkbox" 
                                               value="1"
                                               class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="limpiar_datos" class="font-medium text-red-900">
                                            Limpiar todos los datos existentes antes de importar
                                        </label>
                                        <p class="text-red-700">
                                            <strong>¡ATENCIÓN!</strong> Esta acción eliminará permanentemente todos los apartamentos, recibos y pagos existentes.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Validar relaciones -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="validar_relaciones" 
                                           name="validar_relaciones" 
                                           type="checkbox" 
                                           value="1"
                                           checked
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="validar_relaciones" class="font-medium text-gray-900">
                                        Validar relaciones entre apartamentos, recibos y pagos
                                    </label>
                                    <p class="text-gray-500">
                                        Recomendado: Verifica que los pagos correspondan a apartamentos y recibos existentes.
                                    </p>
                                </div>
                            </div>

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
                                <a href="{{ route('deudas.template.completo') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Descargar Plantilla Excel
                                </a>
                                <a href="{{ route('deudas.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancelar
                                </a>
                            </div>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    onclick="return confirm('¿Está seguro de proceder con la importación completa? Esta acción puede tomar varios minutos.')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                Importar Datos Completos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>