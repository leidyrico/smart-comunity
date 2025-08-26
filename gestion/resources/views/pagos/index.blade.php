<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Pagos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Botones de acción -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <a href="{{ route('pagos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('Registrar Pago') }}
                        </a>
                        
                        <a href="{{ route('deudas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            {{ __('Ver Deudas') }}
                        </a>
                    </div>

                    <!-- Filtros -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <form method="GET" action="{{ route('pagos.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label for="apartamento_id" class="block text-sm font-medium text-gray-700 mb-1">Apartamento</label>
                                <select name="apartamento_id" id="apartamento_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los apartamentos</option>
                                    @foreach($apartamentos as $apartamento)
                                        <option value="{{ $apartamento->id }}" {{ request('apartamento_id') == $apartamento->id ? 'selected' : '' }}>
                                            {{ $apartamento->numero }} - {{ $apartamento->propietario }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="estado" id="estado" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los estados</option>
                                    <option value="confirmado" {{ request('estado') == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="pendiente_confirmacion" {{ request('estado') == 'pendiente_confirmacion' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="rechazado" {{ request('estado') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de pagos -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Apartamento
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Recibo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Monto Pagado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Pago
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Método
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Comprobante
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pagos as $pago)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <div>
                                                <div class="font-semibold">{{ $pago->apartamento->numero }}</div>
                                                <div class="text-gray-500 text-xs">{{ $pago->apartamento->propietario }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">{{ $pago->reciboGastoComun->numero_recibo }}</div>
                                                <div class="text-gray-500 text-xs">{{ $pago->reciboGastoComun->periodo }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                            ${{ number_format($pago->monto_pagado, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @switch($pago->metodo_pago)
                                                    @case('efectivo')
                                                        bg-green-100 text-green-800
                                                        @break
                                                    @case('transferencia')
                                                        bg-blue-100 text-blue-800
                                                        @break
                                                    @case('cheque')
                                                        bg-yellow-100 text-yellow-800
                                                        @break
                                                    @default
                                                        bg-gray-100 text-gray-800
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @switch($pago->estado)
                                                    @case('confirmado')
                                                        bg-green-100 text-green-800
                                                        @break
                                                    @case('pendiente_confirmacion')
                                                        bg-yellow-100 text-yellow-800
                                                        @break
                                                    @case('rechazado')
                                                        bg-red-100 text-red-800
                                                        @break
                                                    @default
                                                        bg-gray-100 text-gray-800
                                                @endswitch">
                                                @switch($pago->estado)
                                                    @case('confirmado')
                                                        Confirmado
                                                        @break
                                                    @case('pendiente_confirmacion')
                                                        Pendiente
                                                        @break
                                                    @case('rechazado')
                                                        Rechazado
                                                        @break
                                                    @default
                                                        {{ $pago->estado }}
                                                @endswitch
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $pago->numero_comprobante ?: 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('pagos.show', $pago) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                
                                                @if($pago->estado !== 'confirmado')
                                                    <a href="{{ route('pagos.edit', $pago) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No se encontraron pagos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($pagos->hasPages())
                        <div class="mt-6">
                            {{ $pagos->links() }}
                        </div>
                    @endif

                    <!-- Resumen estadístico -->
                    @if($pagos->count() > 0)
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-2xl font-bold text-blue-600">{{ $pagos->count() }}</div>
                                    <div class="text-sm text-gray-500">Total Pagos</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-2xl font-bold text-green-600">${{ number_format($pagos->sum('monto_pagado'), 2, ',', '.') }}</div>
                                    <div class="text-sm text-gray-500">Monto Total</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-2xl font-bold text-yellow-600">{{ $pagos->where('estado', 'confirmado')->count() }}</div>
                                    <div class="text-sm text-gray-500">Confirmados</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-2xl font-bold text-orange-600">{{ $pagos->where('estado', 'pendiente_confirmacion')->count() }}</div>
                                    <div class="text-sm text-gray-500">Pendientes</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>