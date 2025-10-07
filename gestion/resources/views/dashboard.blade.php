<x-app-with-sidebar>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('dashboard.pdf') }}" 
               class="inline-flex items-center px-2 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150"
               title="Generar PDF del Dashboard">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    <!-- Estilos adicionales para mejor apariencia -->
    <style>
        .dashboard-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .stat-icon {
            transition: transform 0.2s ease-in-out;
        }
        .dashboard-card:hover .stat-icon {
            transform: scale(1.1);
        }
        .action-button {
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .urgency-high {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensaje de bienvenida -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dashboard-card">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">¡Bienvenido al Sistema de Gestión de Comunidad!</h3>
                    <p class="text-gray-600">Aquí puedes gestionar todos los aspectos de tu comunidad de manera eficiente.</p>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Total de apartamentos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center stat-icon">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total de Apartamentos</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalApartamentos }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo pendiente total -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center stat-icon">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Saldo Pendiente Total</p>
                                <p class="text-2xl font-semibold text-gray-900">${{ number_format($saldoPendienteTotal, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nuevas secciones -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top 5 Apartamentos Morosos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dashboard-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-red-600">Top 5 Apartamentos Morosos</h3>
                        @if($apartamentosMorosos->count() > 0)
                            <div class="space-y-3">
                                @foreach($apartamentosMorosos as $apartamento)
                                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">Apto {{ $apartamento->numero }}</p>
                                            <p class="text-sm text-gray-600">{{ $apartamento->propietario }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-red-600">${{ number_format($apartamento->saldo_pendiente, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No hay apartamentos con deudas pendientes</p>
                        @endif
                    </div>
                </div>

                <!-- Últimos 5 pagos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dashboard-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Últimos 5 Pagos</h3>
                        @if($ultimosPagos->count() > 0)
                            <div class="space-y-3">
                                @foreach($ultimosPagos as $pago)
                                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">Apto {{ $pago->apartamento->numero }}</p>
                                            <p class="text-sm text-gray-600">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'Sin fecha' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-green-600">${{ number_format($pago->monto_pagado, 2) }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst($pago->metodo_pago) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No hay pagos registrados</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estadísticas del Último Recibo -->
            @if($estadisticasUltimoRecibo)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dashboard-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-blue-600">Estadísticas del Último Recibo</h3>
                        <div class="bg-blue-50 rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600">Recibo: {{ $estadisticasUltimoRecibo['recibo']->numero_recibo }}</p>
                            <p class="text-sm text-gray-600">Período: {{ $estadisticasUltimoRecibo['recibo']->periodo }}</p>
                            <p class="text-sm text-gray-600">Valor por apartamento: ${{ number_format($estadisticasUltimoRecibo['recibo']->total_recibo, 2) }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600">${{ number_format($estadisticasUltimoRecibo['total_recaudacion'], 2) }}</p>
                                <p class="text-sm text-gray-600">Total Recaudado</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600">{{ $estadisticasUltimoRecibo['porcentaje_recaudacion'] }}%</p>
                                <p class="text-sm text-gray-600">% de Recaudación</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-purple-600">{{ $estadisticasUltimoRecibo['apartamentos_pagados'] }}/{{ $estadisticasUltimoRecibo['total_apartamentos'] }}</p>
                                <p class="text-sm text-gray-600">Apartamentos Pagados</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-blue-600">Estadísticas del Último Recibo</h3>
                        <p class="text-gray-500 text-center py-4">No hay recibos activos disponibles</p>
                    </div>
                </div>
            @endif

            <!-- Acciones rápidas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dashboard-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Acciones Rápidas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="{{ route('recibos.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center action-button">
                            Crear Recibo
                        </a>
                        <a href="{{ route('recibos.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center action-button">
                            Ver Recibos
                        </a>
                        <a href="{{ route('deudas.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center action-button">
                            Ver Deudas
                        </a>
                        <a href="{{ route('apartamentos.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center action-button">
                            Listar Apartamentos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>
