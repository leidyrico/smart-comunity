<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Consulta de Deudas por Apartamento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Título para impresión (solo visible al imprimir) -->
                    <div class="print-title" style="display: none;">
                        Consulta de Deudas por Apartamento
                    </div>
                    
                    <!-- Información de filtros para impresión (solo visible al imprimir) -->
                    <div class="print-filters" style="display: none;">
                        @if(request('numero_apartamento') || request('nombre_propietario') || request('estado_deuda') || request('numero_recibo'))
                            <strong>Filtros aplicados:</strong>
                            @if(request('numero_apartamento'))
                                Apartamento: {{ request('numero_apartamento') }} |
                            @endif
                            @if(request('nombre_propietario'))
                                Propietario: {{ request('nombre_propietario') }} |
                            @endif
                            @if(request('estado_deuda'))
                                Estado: {{ request('estado_deuda') == 'pendiente' ? 'Con saldo pendiente' : 'Pagado' }} |
                            @endif
                            @if(request('numero_recibo'))
                                Nro. Recibo: {{ request('numero_recibo') }}
                            @endif
                            <br>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}
                        @else
                            <strong>Reporte completo</strong> - Fecha de impresión: {{ now()->format('d/m/Y H:i') }}
                        @endif
                    </div>

                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4 no-print">
                        <h3 class="text-lg font-medium text-gray-900">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Recibos Emitidos
                        </h3>
                        <div class="flex flex-wrap gap-2 justify-end">
                            <a href="{{ route('pagos.create') }}" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="hidden sm:inline">Registrar</span> Pago
                            </a>

                            <button onclick="mostrarModalPagoGlobal()" class="inline-flex items-center px-3 py-2 bg-orange-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 transition ease-in-out duration-150" id="btnPagoGlobal">
                                <i class="fas fa-credit-card w-4 h-4 mr-1"></i>
                                <span class="hidden sm:inline">Pago</span> Global
                            </button>

                            <a href="{{ route('deudas.import.completo') }}" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="hidden sm:inline">Importar</span> Excel
                            </a>

                            <form method="POST" action="{{ route('deudas.enviar-correo') }}" class="inline-block">
                                @csrf
                                @if(request('numero_apartamento'))
                                    <input type="hidden" name="numero_apartamento" value="{{ request('numero_apartamento') }}">
                                @endif
                                @if(request('nombre_propietario'))
                                    <input type="hidden" name="nombre_propietario" value="{{ request('nombre_propietario') }}">
                                @endif
                                @if(request('estado_deuda'))
                                    <input type="hidden" name="estado_deuda" value="{{ request('estado_deuda') }}">
                                @endif
                                @if(request('numero_recibo'))
                                    <input type="hidden" name="numero_recibo" value="{{ request('numero_recibo') }}">
                                @endif
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="hidden sm:inline">Enviar por</span> Correo
                                </button>
                            </form>

                            <button onclick="window.print()" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Imprimir
                            </button>
                        </div>
                    </div>
                
                    <!-- Filtros -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6 no-print">
                        <form method="GET" action="{{ route('deudas.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Filtro por Apartamento -->
                            <div>
                                <label for="numero_apartamento" class="block text-sm font-medium text-gray-700 mb-1">Apartamento</label>
                                <input type="text" 
                                       id="numero_apartamento" 
                                       name="numero_apartamento" 
                                       value="{{ request('numero_apartamento') }}"
                                       placeholder="Buscar por apartamento..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Filtro por Propietario -->
                            <div>
                                <label for="nombre_propietario" class="block text-sm font-medium text-gray-700 mb-1">Propietario</label>
                                <input type="text" 
                                       id="nombre_propietario" 
                                       name="nombre_propietario" 
                                       value="{{ request('nombre_propietario') }}"
                                       placeholder="Buscar por propietario..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Filtro por Estado de Deuda -->
                            <div>
                                <label for="estado_deuda" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select id="estado_deuda" 
                                        name="estado_deuda"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" {{ request('estado_deuda') == 'pendiente' ? 'selected' : '' }}>Con saldo pendiente</option>
                                    <option value="pagado" {{ request('estado_deuda') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                </select>
                            </div>

                            <!-- Filtro por Número de Recibo -->
                            <div>
                                <label for="numero_recibo" class="block text-sm font-medium text-gray-700 mb-1">Nro. Recibo</label>
                                <input type="text" 
                                       id="numero_recibo" 
                                       name="numero_recibo" 
                                       value="{{ request('numero_recibo') }}"
                                       placeholder="Buscar por recibo..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Botones de Acción -->
                            <div class="lg:col-span-4 flex justify-start space-x-3 mt-2">
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Filtrar
                                </button>
                                <a href="{{ route('deudas.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if(count($datosDeuda) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apartamento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nro Recibo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Facturación</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Facturado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Pagado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Actual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($datosDeuda as $dato)
                                    <tr class="hover:bg-gray-50 {{ $dato['saldo_actual'] > 0 ? 'bg-yellow-50' : 'bg-green-50' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $dato['nombre_propietario'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $dato['numero_apartamento'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $dato['numero_recibo'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $dato['fecha_facturacion']->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ${{ number_format($dato['monto_facturado'], 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($dato['monto_pagado'] > 0)
                                                <span class="font-medium text-green-600">
                                                    ${{ number_format($dato['monto_pagado'], 2, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">$0.00</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($dato['fecha_pago'])
                                                {{ $dato['fecha_pago']->format('d/m/Y') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($dato['saldo_actual'] > 0)
                                                <span class="font-medium text-red-600">
                                                    ${{ number_format($dato['saldo_actual'], 2, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="font-medium text-green-600">$0.00</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col space-y-1">
                                                <a href="{{ route('deudas.show', $dato['apartamento_id']) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center" title="Ver detalles">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                @if($dato['saldo_actual'] > 0)
                                                    <a href="{{ route('pagos.create', array_merge(['apartamento_id' => $dato['apartamento_id'], 'recibo_id' => $dato['recibo_id']], request()->only(['numero_apartamento', 'nombre_propietario', 'estado_deuda', 'numero_recibo']))) }}" class="text-green-600 hover:text-green-900 inline-flex items-center" title="Registrar pago">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                                @if($dato['monto_pagado'] > 0 && !empty($dato['pagos']))
                                                    <div class="mt-2">
                                                        <span class="text-xs text-gray-500 font-medium">Pagos registrados:</span>
                                                        @foreach($dato['pagos'] as $pago)
                                                            <div class="flex items-center justify-between mt-1 p-1 bg-gray-50 rounded text-xs">
                                                                <span class="text-gray-700">
                                                                    ${{ number_format($pago['monto'], 2, ',', '.') }} 
                                                                    ({{ \Carbon\Carbon::parse($pago['fecha'])->format('d/m/Y') }})
                                                                </span>
                                                                <button onclick="eliminarPago({{ $pago['id'] }}, '{{ $dato['numero_recibo'] }}', '{{ $pago['monto'] }}')" 
                                                                        class="text-red-600 hover:text-red-900 ml-2" 
                                                                        title="Eliminar pago">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Enlaces de paginación -->
                    <div class="mt-4 no-print">
                        {{ $datosDeuda->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron registros de deudas</h3>
                        <p class="mt-1 text-sm text-gray-500">No hay datos que coincidan con los filtros aplicados.</p>
                    </div>
                @endif
                
                    <!-- Resumen estadístico -->
                    @if($estadisticas['total_registros'] > 0)
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 no-print">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $estadisticas['total_registros'] }}</div>
                                <div class="text-sm text-gray-600">Total Registros</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $estadisticas['con_saldo_pendiente'] }}</div>
                                <div class="text-sm text-gray-600">Con Saldo Pendiente</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $estadisticas['pagados'] }}</div>
                                <div class="text-sm text-gray-600">Pagados</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-yellow-600">${{ number_format($estadisticas['saldo_total_pendiente'], 2, ',', '.') }}</div>
                                <div class="text-sm text-gray-600">Saldo Total Pendiente</div>
                            </div>
                        </div>
                    @endif
            </div>
        </div>
    </div>
</div>


    </div>

<script>
function exportarExcel() {
    // Obtener los parámetros de filtro actuales
    const params = new URLSearchParams();
    
    const apartamento = document.querySelector('input[name="apartamento"]')?.value;
    const mes = document.querySelector('select[name="mes"]')?.value;
    const anio = document.querySelector('select[name="anio"]')?.value;
    const estado = document.querySelector('select[name="estado"]')?.value;
    
    if (apartamento) params.append('apartamento', apartamento);
    if (mes) params.append('mes', mes);
    if (anio) params.append('anio', anio);
    if (estado) params.append('estado', estado);
    
    // Crear la URL con los parámetros
    const url = '{{ route("deudas.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
    
    // Abrir la descarga en una nueva ventana
    window.open(url, '_blank');
}

function mostrarModalPagoGlobal() {
    // Crear el modal dinámicamente
    const modalHtml = `
        <div id="modalPagoGlobal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Seleccionar Apartamento para Pago Global</h3>
                    <div class="mt-2 px-7 py-3">
                        <select id="apartamentoSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Seleccione un apartamento...</option>
                        </select>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="btnConfirmarPago" class="px-4 py-2 bg-orange-400 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-400" disabled>
                            Continuar con Pago Global
                        </button>
                        <button id="btnCancelar" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar el modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Cargar apartamentos con deudas
    cargarApartamentosConDeudas();
    
    // Event listeners
    document.getElementById('apartamentoSelect').addEventListener('change', function() {
        const btnConfirmar = document.getElementById('btnConfirmarPago');
        if (this.value) {
            btnConfirmar.disabled = false;
            btnConfirmar.onclick = function() {
                const apartamentoId = document.getElementById('apartamentoSelect').value;
                window.location.href = `{{ url('/') }}/pagos/global/create/${apartamentoId}`;
            };
        } else {
            btnConfirmar.disabled = true;
        }
    });
    
    document.getElementById('btnCancelar').onclick = function() {
        document.getElementById('modalPagoGlobal').remove();
    };
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('modalPagoGlobal').onclick = function(e) {
        if (e.target === this) {
            this.remove();
        }
    };
}

function cargarApartamentosConDeudas() {
    const select = document.getElementById('apartamentoSelect');
    
    // Obtener apartamentos únicos de la tabla actual
    const apartamentosMap = new Map();
    const filas = document.querySelectorAll('tbody tr');
    
    filas.forEach((fila, index) => {
        const celdas = fila.querySelectorAll('td');
        
        if (celdas.length >= 8) {
            const numeroApartamento = celdas[1].textContent.trim(); // Segunda columna
            const saldoText = celdas[7].textContent.trim(); // Octava columna (saldo actual)
            
            const saldo = parseFloat(saldoText.replace(/[^0-9.-]/g, ''));
            
            if (saldo > 0) {
                // Extraer el ID del apartamento del enlace "Ver"
                const enlaceVer = fila.querySelector('a[href*="/deudas/"]');
                
                if (enlaceVer) {
                    const apartamentoId = enlaceVer.href.split('/').pop();
                    // Solo agregar si no existe ya (para evitar duplicados)
                    if (!apartamentosMap.has(apartamentoId)) {
                        apartamentosMap.set(apartamentoId, numeroApartamento);
                    }
                }
            }
        }
    });
    
    // Agregar opciones al select ordenadas por número de apartamento
    const apartamentosOrdenados = Array.from(apartamentosMap.entries())
        .sort((a, b) => a[1].localeCompare(b[1], undefined, { numeric: true }));
    
    apartamentosOrdenados.forEach(([id, numero]) => {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = `Apartamento ${numero}`;
        select.appendChild(option);
    });
}
</script>

</x-app-with-sidebar>

@push('styles')
<style>
.table th {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

/* Estilos para impresión */
@media print {
    /* Ocultar elementos con clase no-print */
    .no-print {
        display: none !important;
    }
    
    /* Ocultar específicamente el contenedor de filtros */
    .bg-gray-50.p-4.rounded-lg.mb-6 {
        display: none !important;
    }
    
    /* Ocultar el contenedor de botones de acción */
    .flex.flex-col.lg\:flex-row {
        display: none !important;
    }
    
    /* Ocultar todos los botones */
    button {
        display: none !important;
    }
    
    /* Ocultar todos los enlaces con clase inline-flex */
    a.inline-flex {
        display: none !important;
    }
    
    /* Ocultar formularios */
    form {
        display: none !important;
    }
    
    /* Ocultar divs con múltiples clases flex */
    div[class*="flex"][class*="gap"] {
        display: none !important;
    }
    
    /* Ocultar contenedores con bg-gray-50 */
    div[class*="bg-gray-50"] {
        display: none !important;
    }
    
    /* Ocultar la columna de acciones en impresión */
    .min-w-full thead tr th:nth-child(9), /* Acciones */
    .min-w-full tbody tr td:nth-child(9) { /* Acciones */
        display: none !important;
    }
    
    /* Mostrar el resumen estadístico en impresión */
    .mt-6.grid.grid-cols-1.md\:grid-cols-4.gap-4 {
        display: grid !important;
        margin-top: 20px !important;
        page-break-inside: avoid;
    }
    
    /* Ajustar el diseño de las tarjetas estadísticas para impresión */
    .mt-6.grid.grid-cols-1.md\:grid-cols-4.gap-4 > div {
        border: 1px solid #ccc !important;
        padding: 10px !important;
        margin-bottom: 10px !important;
        background-color: #f9f9f9 !important;
    }
    
    /* Ajustar el diseño para impresión */
    body {
        font-size: 12px;
        line-height: 1.3;
    }
    
    .min-w-full {
        width: 100% !important;
        font-size: 10px;
        table-layout: fixed !important;
    }
    
    /* Ajustar anchos específicos de columnas para evitar scroll horizontal */
    .min-w-full thead tr th:nth-child(1), /* Propietario */
    .min-w-full tbody tr td:nth-child(1) {
        width: 15% !important;
        font-size: 9px !important;
    }
    
    .min-w-full thead tr th:nth-child(2), /* Apartamento */
    .min-w-full tbody tr td:nth-child(2) {
        width: 8% !important;
    }
    
    .min-w-full thead tr th:nth-child(3), /* Nro Recibo */
    .min-w-full tbody tr td:nth-child(3) {
        width: 10% !important;
    }
    
    .min-w-full thead tr th:nth-child(4), /* Fecha Facturación */
    .min-w-full tbody tr td:nth-child(4) {
        width: 12% !important;
    }
    
    .min-w-full thead tr th:nth-child(5), /* Monto Facturado */
    .min-w-full tbody tr td:nth-child(5) {
        width: 12% !important;
    }
    
    .min-w-full thead tr th:nth-child(6), /* Monto Pagado */
    .min-w-full tbody tr td:nth-child(6) {
        width: 12% !important;
    }
    
    .min-w-full thead tr th:nth-child(7), /* Fecha Pago */
    .min-w-full tbody tr td:nth-child(7) {
        width: 12% !important;
    }
    
    .min-w-full thead tr th:nth-child(8), /* Saldo Actual */
    .min-w-full tbody tr td:nth-child(8) {
        width: 12% !important;
    }
    
    .px-6 {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }
    
    .py-4 {
        padding-top: 4px !important;
        padding-bottom: 4px !important;
    }
    
    .py-3 {
        padding-top: 3px !important;
        padding-bottom: 3px !important;
    }
    
    /* Ajustar texto para que no se desborde */
    .min-w-full td,
    .min-w-full th {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
    }
    
    /* Título para impresión */
    .print-title {
        display: block !important;
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #000;
    }
    
    /* Información de filtros aplicados para impresión */
    .print-filters {
        display: block !important;
        margin-bottom: 15px;
        font-size: 12px;
        color: #666;
    }
    
    /* Remover colores de fondo en impresión */
    .bg-yellow-50,
    .bg-green-50,
    .bg-gray-50 {
        background-color: transparent !important;
    }
    
    /* Mantener colores de texto importantes */
    .text-red-600 {
        color: #dc2626 !important;
    }
    
    .text-green-600 {
        color: #16a34a !important;
    }
}
</style>

<script>
function eliminarPago(pagoId, numeroRecibo, montoPago) {
    const montoNumerico = parseFloat(montoPago);
    if (confirm('¿Está seguro de que desea eliminar el pago de $' + montoNumerico.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' del recibo ' + numeroRecibo + '?\n\nEsta acción no se puede deshacer y el recibo volverá a tener saldo pendiente.')) {
        
        // Solicitar clave de administrador
        const adminPassword = prompt('Por favor, ingrese la clave de administrador para confirmar la eliminación:');
        
        if (!adminPassword) {
            alert('Eliminación cancelada. Se requiere la clave de administrador.');
            return;
        }
        
        // Crear un formulario para enviar la petición DELETE
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("/") }}/pagos/' + pagoId;
        
        // Agregar token CSRF
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
        
        // Agregar clave de administrador
        const adminPasswordField = document.createElement('input');
        adminPasswordField.type = 'hidden';
        adminPasswordField.name = 'admin_password';
        adminPasswordField.value = adminPassword;
        form.appendChild(adminPasswordField);
        
        // Agregar al DOM y enviar
        document.body.appendChild(form);
        form.submit();
    }
}
</script>