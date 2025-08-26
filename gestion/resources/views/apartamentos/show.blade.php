<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Apartamento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Información del Apartamento</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('apartamentos.edit', $apartamento) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </a>
                            <a href="{{ route('apartamentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Información básica del apartamento -->
                    <div class="bg-gray-50 p-6 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número de Apartamento</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $apartamento->numero }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Propietario</label>
                                <p class="text-lg text-gray-900">{{ $apartamento->propietario }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Piso</label>
                                <p class="text-lg text-gray-900">{{ $apartamento->piso }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Torre</label>
                                <p class="text-lg text-gray-900">{{ $apartamento->torre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <p class="text-lg text-gray-900">{{ ucfirst($apartamento->tipo) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $apartamento->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($apartamento->estado) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Información de contacto -->
                    @if($apartamento->telefono || $apartamento->email)
                    <div class="bg-blue-50 p-6 rounded-lg mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($apartamento->telefono)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <p class="text-lg text-gray-900">{{ $apartamento->telefono }}</p>
                            </div>
                            @endif
                            @if($apartamento->email)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <p class="text-lg text-gray-900">{{ $apartamento->email }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Historial de recibos -->
                    @if($apartamento->recibos && $apartamento->recibos->count() > 0)
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Historial de Recibos</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($apartamento->recibos->take(10) as $recibo)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $recibo->numero_recibo }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $recibo->fecha_facturacion ? $recibo->fecha_facturacion->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($recibo->monto_facturado, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $saldo = $recibo->monto_facturado - $recibo->pagos->sum('monto_pagado');
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $saldo <= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $saldo <= 0 ? 'Pagado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('recibos.show', $recibo) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($apartamento->recibos->count() > 10)
                        <div class="mt-4 text-center">
                            <a href="{{ route('deudas.show', $apartamento) }}" class="text-blue-600 hover:text-blue-900">Ver todos los recibos</a>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Sin recibos registrados</h3>
                        <p class="mt-1 text-sm text-gray-500">Este apartamento no tiene recibos asociados.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>