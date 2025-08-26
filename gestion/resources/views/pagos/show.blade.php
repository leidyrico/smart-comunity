<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Pago') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Botones de acción -->
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <a href="{{ route('pagos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ __('Volver al Historial') }}
                            </a>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($pago->estado === 'pendiente_confirmacion')
                                <form method="POST" action="{{ route('pagos.confirmar', $pago) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ __('Confirmar Pago') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('pagos.rechazar', $pago) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" 
                                            onclick="return confirm('¿Está seguro de rechazar este pago?')">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        {{ __('Rechazar Pago') }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('pagos.edit', $pago) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                {{ __('Editar') }}
                            </a>
                        </div>
                    </div>

                    <!-- Información del Pago -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información Básica -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Pago</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ID del Pago</label>
                                    <p class="text-sm text-gray-900">#{{ $pago->id }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Monto Pagado</label>
                                    <p class="text-lg font-bold text-green-600">${{ number_format($pago->monto_pagado, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                                    <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                                    <p class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $pago->metodo_pago) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($pago->estado === 'confirmado') bg-green-100 text-green-800
                                        @elseif($pago->estado === 'pendiente_confirmacion') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $pago->estado)) }}
                                    </span>
                                </div>
                                @if($pago->numero_comprobante)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Número de Comprobante</label>
                                    <p class="text-sm text-gray-900">{{ $pago->numero_comprobante }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Información del Apartamento -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Apartamento</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Apartamento</label>
                                    <p class="text-sm text-gray-900">{{ $pago->apartamento->numero }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                    <p class="text-sm text-gray-900 capitalize">{{ $pago->apartamento->tipo }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Área</label>
                                    <p class="text-sm text-gray-900">{{ $pago->apartamento->area_m2 }} m²</p>
                                </div>
                                @if($pago->apartamento->propietario)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Propietario</label>
                                    <p class="text-sm text-gray-900">{{ $pago->apartamento->propietario }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información del Recibo -->
                    @if($pago->reciboGastoComun)
                    <div class="mt-6 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Recibo</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Período</label>
                                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($pago->reciboGastoComun->fecha_emision)->format('m/Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Monto Total del Recibo</label>
                                <p class="text-sm text-gray-900">${{ number_format($pago->reciboGastoComun->monto_total, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha de Vencimiento</label>
                                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($pago->reciboGastoComun->fecha_vencimiento)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Observaciones -->
                    @if($pago->observaciones)
                    <div class="mt-6 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Observaciones</h3>
                        <p class="text-sm text-gray-900">{{ $pago->observaciones }}</p>
                    </div>
                    @endif

                    <!-- Fechas de Auditoría -->
                    <div class="mt-6 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Auditoría</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
                                <p class="text-sm text-gray-900">{{ $pago->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Última Actualización</label>
                                <p class="text-sm text-gray-900">{{ $pago->updated_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>