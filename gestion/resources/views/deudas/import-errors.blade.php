<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de Errores de Importación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <svg class="w-5 h-5 inline mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Errores Encontrados Durante la Importación
                        </h3>
                        <a href="{{ route('deudas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a Deudas
                        </a>
                    </div>

                    @if(session('import_errors') && count(session('import_errors')) > 0)
                        <!-- Resumen de errores -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                            <div class="flex items-center mb-4">
                                <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h4 class="text-lg font-semibold text-red-800">Resumen de Errores</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="bg-white p-4 rounded border">
                                    <div class="text-2xl font-bold text-red-600">{{ count(session('import_errors')) }}</div>
                                    <div class="text-gray-600">Total de Errores</div>
                                </div>
                                <div class="bg-white p-4 rounded border">
                                    <div class="text-2xl font-bold text-orange-600">
                                        {{ count(array_filter(session('import_errors'), function($error) { return strpos($error, 'Apartamento') !== false; })) }}
                                    </div>
                                    <div class="text-gray-600">Errores en Apartamentos</div>
                                </div>
                                <div class="bg-white p-4 rounded border">
                                    <div class="text-2xl font-bold text-yellow-600">
                                        {{ count(array_filter(session('import_errors'), function($error) { return strpos($error, 'Recibo') !== false || strpos($error, 'Pago') !== false; })) }}
                                    </div>
                                    <div class="text-gray-600">Errores en Recibos/Pagos</div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista detallada de errores -->
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h4 class="text-lg font-medium text-gray-900">Detalle de Errores por Fila</h4>
                                <p class="text-sm text-gray-600 mt-1">Revise cada error para corregir los datos en su archivo Excel</p>
                            </div>
                            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                                @foreach(session('import_errors') as $index => $error)
                                    <div class="px-6 py-4 hover:bg-gray-50">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600 text-sm font-medium">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    @if(strpos($error, 'Apartamento') !== false)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">
                                                            Apartamento
                                                        </span>
                                                    @elseif(strpos($error, 'Recibo') !== false)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                            Recibo
                                                        </span>
                                                    @elseif(strpos($error, 'Pago') !== false)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                                            Pago
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                                                            General
                                                        </span>
                                                    @endif
                                                    {{ $error }}
                                                </div>
                                                @if(preg_match('/fila (\d+)/i', $error, $matches))
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Ubicación: Fila {{ $matches[1] }} del archivo Excel
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Recomendaciones -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                            <h4 class="text-lg font-medium text-blue-900 mb-3">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Recomendaciones para Corregir Errores
                            </h4>
                            <div class="text-sm text-blue-800 space-y-2">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Errores de formato:</strong> Verifique que las fechas estén en formato YYYY-MM-DD</li>
                                    <li><strong>Campos requeridos:</strong> Asegúrese de que todas las columnas obligatorias tengan datos</li>
                                    <li><strong>Relaciones:</strong> Los apartamentos deben existir antes de crear recibos o pagos</li>
                                    <li><strong>Tipos de datos:</strong> Los montos deben ser números válidos</li>
                                    <li><strong>Duplicados:</strong> Evite números de apartamento o recibo duplicados</li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay errores para mostrar</h3>
                            <p class="text-gray-600">La última importación se completó sin errores o no hay datos de errores disponibles.</p>
                        </div>
                    @endif

                    <!-- Acciones adicionales -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('deudas.template.completo') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Descargar Plantilla Excel
                        </a>
                        <a href="{{ route('deudas.import.completo') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Intentar Nueva Importación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>