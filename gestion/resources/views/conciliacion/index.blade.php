<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Conciliación Financiera') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtro por mes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <form method="GET" action="{{ route('conciliacion.index') }}" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-48">
                            <label for="mes" class="block text-sm font-medium text-gray-700 mb-2">Filtrar por mes:</label>
                            <select name="mes" id="mes" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos los meses</option>
                                @foreach($mesesDisponibles as $mes)
                                    <option value="{{ $mes['valor'] }}" 
                                        {{ $mesSeleccionado == $mes['valor'] ? 'selected' : '' }}>
                                        {{ $mes['texto'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filtrar
                            </button>
                            @if($mesSeleccionado)
                                <a href="{{ route('conciliacion.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Limpiar filtro
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen de Totales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
                <div class="bg-green-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-green-800">
                        <h3 class="text-lg font-semibold mb-2">Total Ingresos USD</h3>
                        <p class="text-3xl font-bold">${{ number_format($totalIngresos, 2, ',', '.') }}</p>
                        @if($mesSeleccionado)
                            <small class="text-sm">{{ \Carbon\Carbon::createFromFormat('Y-m', $mesSeleccionado)->locale('es')->isoFormat('MMMM YYYY') }}</small>
                        @endif
                    </div>
                </div>
                <div class="bg-emerald-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-emerald-800">
                        <h3 class="text-lg font-semibold mb-2">Total Ingresos Bs</h3>
                        <p class="text-3xl font-bold">Bs {{ number_format($totalIngresosEnBs ?? 0, 2, ',', '.') }}</p>
                        @if($mesSeleccionado)
                            <small class="text-sm">{{ \Carbon\Carbon::createFromFormat('Y-m', $mesSeleccionado)->locale('es')->isoFormat('MMMM YYYY') }}</small>
                        @endif
                    </div>
                </div>
                <div class="bg-red-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-red-800">
                        <h3 class="text-lg font-semibold mb-2">Total Egresos USD</h3>
                        <p class="text-3xl font-bold">${{ number_format($totalEgresos, 2, ',', '.') }}</p>
                        @if($mesSeleccionado)
                            <small class="text-sm">{{ \Carbon\Carbon::createFromFormat('Y-m', $mesSeleccionado)->locale('es')->isoFormat('MMMM YYYY') }}</small>
                        @endif
                    </div>
                </div>
                <div class="bg-rose-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-rose-800">
                        <h3 class="text-lg font-semibold mb-2">Total Egresos Bs</h3>
                        <p class="text-3xl font-bold">Bs {{ number_format($totalEgresosEnBs ?? 0, 2, ',', '.') }}</p>
                        @if($mesSeleccionado)
                            <small class="text-sm">{{ \Carbon\Carbon::createFromFormat('Y-m', $mesSeleccionado)->locale('es')->isoFormat('MMMM YYYY') }}</small>
                        @endif
                    </div>
                </div>
                <div class="bg-blue-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-blue-800">
                        <h3 class="text-lg font-semibold mb-2">Balance USD</h3>
                        <p class="text-3xl font-bold {{ $balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($balance, 2, ',', '.') }}
                        </p>
                        @if($mesSeleccionado)
                            <small class="text-sm">{{ \Carbon\Carbon::createFromFormat('Y-m', $mesSeleccionado)->locale('es')->isoFormat('MMMM YYYY') }}</small>
                        @endif
                    </div>
                </div>
                <div class="bg-indigo-100 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-indigo-800">
                        <h3 class="text-lg font-semibold mb-2">Balance Bs</h3>
                        <p class="text-3xl font-bold {{ ($balanceEnBs ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Bs {{ number_format($balanceEnBs ?? 0, 2, ',', '.') }}
                        </p>
                        @if($mesSeleccionado)
                            <small class="text-sm">{{ \Carbon\Carbon::createFromFormat('Y-m', $mesSeleccionado)->locale('es')->isoFormat('MMMM YYYY') }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Formulario para Nuevo Egreso -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Registrar Nuevo Egreso</h3>
                    
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('conciliacion.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        @csrf
                        <div>
                            <label for="nro_factura" class="block text-sm font-medium text-gray-700">Nro. Factura</label>
                            <input type="text" name="nro_factura" id="nro_factura" value="{{ old('nro_factura') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                            <input type="date" name="fecha" id="fecha" value="{{ old('fecha') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="comprobante" class="block text-sm font-medium text-gray-700">Comprobante</label>
                            <input type="text" name="comprobante" id="comprobante" value="{{ old('comprobante') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="monto" class="block text-sm font-medium text-gray-700">Monto</label>
                            <input type="number" name="monto" id="monto" value="{{ old('monto') }}" step="0.01" min="0.01" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="md:col-span-2 lg:col-span-1">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2 lg:col-span-5 flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Registrar Egreso
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tablas de Ingresos y Egresos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Tabla de Ingresos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-green-800">Ingresos (Pagos Confirmados)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apartamento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto USD</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Bs</th>
                            </tr>
                        </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($ingresos as $ingreso)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($ingreso->fecha_pago)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $ingreso->apartamento->numero ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $ingreso->reciboGastoComun->numero_recibo ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($ingreso->monto_pagado, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                Bs {{ number_format($ingreso->monto_en_bs ?? 0, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                                No hay ingresos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Egresos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-red-800">Egresos</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nro. Factura</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto USD</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Bs</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($egresos as $egreso)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $egreso->fecha->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $egreso->nro_factura }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $egreso->descripcion ?: 'Sin descripción' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                                ${{ number_format($egreso->monto, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                                Bs {{ number_format($egreso->monto_en_bs ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form method="POST" action="{{ route('conciliacion.destroy', $egreso->id) }}" 
                                                      onsubmit="return confirm('¿Está seguro de eliminar este egreso?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                No hay egresos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>