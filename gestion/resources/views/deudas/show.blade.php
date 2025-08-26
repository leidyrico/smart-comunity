<x-app-with-sidebar>
    <div class="container mx-auto px-4 py-6">
        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Cerrar</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        @endif
        <!-- Encabezado -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle de Deuda</h1>
                    <p class="mt-1 text-sm text-gray-600">Apartamento {{ $apartamento->numero }} - {{ $apartamento->propietario }}</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button onclick="window.print()" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 print:hidden">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>
                    <a href="{{ route('pagos.create', ['apartamento_id' => $apartamento->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 print:hidden">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Registrar Pago
                    </a>
                    <a href="{{ route('pagos.create-global', $apartamento->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-orange-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 active:bg-orange-600 focus:outline-none focus:border-orange-500 focus:ring ring-orange-200 disabled:opacity-25 transition ease-in-out duration-150 print:hidden">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Pago Global
                    </a>
                    <a href="{{ route('deudas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 print:hidden">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del Apartamento -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Información del Apartamento</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Número</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $apartamento->numero }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Propietario</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $apartamento->propietario }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $apartamento->telefono ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Piso</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $apartamento->piso }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Torre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $apartamento->torre ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $apartamento->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($apartamento->estado) }}
                            </span>
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Deuda -->
        @php
            $totalFacturado = collect($detalleRecibos)->sum('recibo.total_recibo');
            $totalPagado = collect($detalleRecibos)->sum('pagos_realizados');
            $saldoTotal = collect($detalleRecibos)->sum('saldo_pendiente');
            $recibosVencidos = collect($detalleRecibos)->where('esta_vencido', true)->count();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600">${{ number_format($totalFacturado, 2, ',', '.') }}</div>
                        <div class="text-sm text-gray-600">Total Facturado</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">${{ number_format($totalPagado, 2, ',', '.') }}</div>
                        <div class="text-sm text-gray-600">Total Pagado</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-red-600">${{ number_format($saldoTotal, 2, ',', '.') }}</div>
                <div class="text-sm text-gray-600">Saldo Pendiente</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $recibosVencidos }}</div>
                <div class="text-sm text-gray-600">Recibos Vencidos</div>
            </div>
        </div>

        <!-- Filtros de Recibos -->
        <div class="bg-white shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Filtros de Visualización</h3>
                <div class="mt-4 flex flex-wrap gap-4">
                    <button onclick="filtrarRecibos('todos')" class="filtro-btn bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-200 transition-colors" data-filtro="todos">
                        Todos los Recibos ({{ count($detalleRecibos) }})
                    </button>
                    <button onclick="filtrarRecibos('vencidos')" class="filtro-btn bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium hover:bg-red-200 transition-colors" data-filtro="vencidos">
                        Solo Vencidos ({{ collect($detalleRecibos)->where('esta_vencido', true)->count() }})
                    </button>
                    <button onclick="filtrarRecibos('pendientes')" class="filtro-btn bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium hover:bg-yellow-200 transition-colors" data-filtro="pendientes">
                        Pendientes ({{ collect($detalleRecibos)->where('saldo_pendiente', '>', 0)->where('esta_vencido', false)->count() }})
                    </button>
                    <button onclick="filtrarRecibos('pagados')" class="filtro-btn bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium hover:bg-green-200 transition-colors" data-filtro="pagados">
                        Pagados ({{ collect($detalleRecibos)->where('esta_pagado', true)->count() }})
                    </button>
                </div>
            </div>
        </div>

        <!-- Detalle de Recibos -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Detalle de Recibos</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Estado de pago de todos los recibos emitidos</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Recibo
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Emisión
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Vencimiento
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto Total
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pagado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Saldo
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($detalleRecibos as $detalle)
                                <tr class="hover:bg-gray-50 recibo-row" 
                                    data-estado="{{ $detalle['esta_pagado'] ? 'pagado' : ($detalle['esta_vencido'] ? 'vencido' : 'pendiente') }}"
                                    data-vencido="{{ $detalle['esta_vencido'] ? 'true' : 'false' }}"
                                    data-saldo="{{ $detalle['saldo_pendiente'] }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $detalle['recibo']->numero_recibo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $detalle['recibo']->fecha_emision->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $detalle['recibo']->fecha_vencimiento->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($detalle['recibo']->total_recibo, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        ${{ number_format($detalle['pagos_realizados'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $detalle['saldo_pendiente'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        ${{ number_format($detalle['saldo_pendiente'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($detalle['esta_pagado'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Pagado
                                            </span>
                                        @elseif($detalle['esta_vencido'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Vencido
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No hay recibos registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Estilos específicos para la página -->
    <style>
        /* Estilos para filtros activos */
        .filtro-btn.active {
            background-color: #1f2937 !important;
            color: white !important;
        }
        
        /* Animaciones para las filas */
        .recibo-row {
            transition: all 0.3s ease;
        }
        
        .recibo-row.hidden {
            display: none;
        }
        
        /* Resaltar recibos vencidos */
        .recibo-row[data-vencido="true"] {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        
        .recibo-row[data-estado="pagado"] {
            background-color: #f0fdf4;
        }
        
        @media print {
            .print\:hidden {
                display: none !important;
            }
            /* Ocultar elementos no necesarios en la impresión */
            .print\:hidden {
                display: none !important;
            }
            
            /* Optimizar el layout para impresión */
            body {
                font-size: 12px;
                line-height: 1.4;
            }
            
            .container {
                max-width: none;
                margin: 0;
                padding: 0;
            }
            
            /* Mejorar la presentación de las tablas */
            table {
                page-break-inside: avoid;
                border-collapse: collapse;
                width: 100%;
            }
            
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            
            th {
                background-color: #f5f5f5 !important;
                font-weight: bold;
            }
            
            /* Evitar saltos de página en elementos importantes */
            .bg-white {
                page-break-inside: avoid;
            }
            
            /* Ajustar colores para impresión */
            .text-blue-600, .text-green-600, .text-red-600, .text-yellow-600 {
                color: #000 !important;
            }
            
            /* Mejorar la legibilidad de los badges */
            .bg-green-100, .bg-red-100, .bg-yellow-100 {
                background-color: #f0f0f0 !important;
                border: 1px solid #ccc;
            }
            
            /* Título de la página */
            h1 {
                font-size: 18px;
                margin-bottom: 10px;
            }
            
            h3 {
                font-size: 14px;
                margin-bottom: 8px;
            }
            
            /* Grid responsive para impresión */
            .grid {
                display: block;
            }
            
            .grid > div {
                display: inline-block;
                width: 24%;
                margin-right: 1%;
                vertical-align: top;
            }
        }
    </style>

    <!-- JavaScript para filtros -->
    <script>
        function filtrarRecibos(filtro) {
            // Remover clase active de todos los botones
            document.querySelectorAll('.filtro-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Agregar clase active al botón seleccionado
            document.querySelector(`[data-filtro="${filtro}"]`).classList.add('active');
            
            // Obtener todas las filas de recibos
            const filas = document.querySelectorAll('.recibo-row');
            
            filas.forEach(fila => {
                const estado = fila.getAttribute('data-estado');
                const esVencido = fila.getAttribute('data-vencido') === 'true';
                const saldo = parseFloat(fila.getAttribute('data-saldo'));
                
                let mostrar = false;
                
                switch(filtro) {
                    case 'todos':
                        mostrar = true;
                        break;
                    case 'vencidos':
                        mostrar = esVencido && saldo > 0;
                        break;
                    case 'pendientes':
                        mostrar = !esVencido && saldo > 0;
                        break;
                    case 'pagados':
                        mostrar = estado === 'pagado';
                        break;
                }
                
                if (mostrar) {
                    fila.classList.remove('hidden');
                } else {
                    fila.classList.add('hidden');
                }
            });
        }
        
        // JavaScript para funcionalidad de filtros (sin dropdown de estado)
        
        // Activar filtro "todos" por defecto al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            filtrarRecibos('todos');
        });
    </script>
</x-app-with-sidebar>