<x-app-with-sidebar>
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle de Deuda</h1>
                    <p class="mt-1 text-sm text-gray-600">Apartamento {{ $apartamento->numero }} - {{ $apartamento->propietario }}</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('pagos.create', ['apartamento_id' => $apartamento->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Registrar Pago
                    </a>
                    <a href="{{ route('deudas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
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
                <div class="text-2xl font-bold text-blue-600">${{ number_format($totalFacturado, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600">Total Facturado</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">${{ number_format($totalPagado, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600">Total Pagado</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-red-600">${{ number_format($saldoTotal, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600">Saldo Pendiente</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $recibosVencidos }}</div>
                <div class="text-sm text-gray-600">Recibos Vencidos</div>
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($detalleRecibos as $detalle)
                                <tr class="hover:bg-gray-50">
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
                                        ${{ number_format($detalle['recibo']->total_recibo, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        ${{ number_format($detalle['pagos_realizados'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $detalle['saldo_pendiente'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        ${{ number_format($detalle['saldo_pendiente'], 0, ',', '.') }}
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(!$detalle['esta_pagado'])
                                            <a href="{{ route('pagos.create', ['apartamento_id' => $apartamento->id, 'recibo_id' => $detalle['recibo']->id]) }}" 
                                               class="text-blue-600 hover:text-blue-900 mr-3">
                                                Pagar
                                            </a>
                                        @endif
                                        <a href="{{ route('recibos.show', $detalle['recibo']->id) }}" 
                                           class="text-gray-600 hover:text-gray-900">
                                            Ver Recibo
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
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
</x-app-with-sidebar>