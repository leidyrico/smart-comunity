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
            <div class="mb-8">
                <!-- Primera fila: USD -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-green-100 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-green-800">
                            <h3 class="text-lg font-semibold mb-2">Total Ingresos USD</h3>
                            <p class="text-3xl font-bold">${{ number_format($totalIngresos, 2, ',', '.') }}</p>
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
                </div>
                
                <!-- Segunda fila: Bs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-emerald-100 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-emerald-800">
                            <h3 class="text-lg font-semibold mb-2">Total Ingresos Bs</h3>
                            <p class="text-3xl font-bold">Bs {{ number_format($totalIngresosEnBs ?? 0, 2, ',', '.') }}</p>
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
            </div>

            {{-- Formulario para Nuevo Egreso - Oculto temporalmente, se moverá a un nuevo módulo --}}
            {{--
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
            --}}

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
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Apto</th>
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
                                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900 w-20">
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
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                No hay ingresos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación de Ingresos -->
                        @if($ingresos->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Mostrando {{ $ingresos->firstItem() }} a {{ $ingresos->lastItem() }} de {{ $ingresos->total() }} ingresos
                                    </div>
                                    <div class="flex space-x-1">
                                        {{-- Botón Anterior --}}
                                        @if ($ingresos->onFirstPage())
                                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">Anterior</span>
                                        @else
                                            <a href="{{ $ingresos->appends(request()->query())->previousPageUrl() }}" 
                                               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Anterior</a>
                                        @endif

                                        {{-- Números de página --}}
                                        @foreach ($ingresos->appends(request()->query())->getUrlRange(1, $ingresos->lastPage()) as $page => $url)
                                            @if ($page == $ingresos->currentPage())
                                                <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md">{{ $page }}</span>
                                            @else
                                                <a href="{{ $url }}" 
                                                   class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{{ $page }}</a>
                                            @endif
                                        @endforeach

                                        {{-- Botón Siguiente --}}
                                        @if ($ingresos->hasMorePages())
                                            <a href="{{ $ingresos->appends(request()->query())->nextPageUrl() }}" 
                                               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Siguiente</a>
                                        @else
                                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">Siguiente</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
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
                        
                        <!-- Paginación de Egresos -->
                        @if(method_exists($egresos, 'hasPages') && $egresos->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Mostrando {{ $egresos->firstItem() }} a {{ $egresos->lastItem() }} de {{ $egresos->total() }} egresos
                                    </div>
                                    <div class="flex space-x-1">
                                        {{-- Botón Anterior --}}
                                        @if ($egresos->onFirstPage())
                                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">Anterior</span>
                                        @else
                                            <a href="{{ $egresos->appends(request()->query())->previousPageUrl() }}" 
                                               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Anterior</a>
                                        @endif

                                        {{-- Números de página --}}
                                        @foreach ($egresos->appends(request()->query())->getUrlRange(1, $egresos->lastPage()) as $page => $url)
                                            @if ($page == $egresos->currentPage())
                                                <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md">{{ $page }}</span>
                                            @else
                                                <a href="{{ $url }}" 
                                                   class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{{ $page }}</a>
                                            @endif
                                        @endforeach

                                        {{-- Botón Siguiente --}}
                                        @if ($egresos->hasMorePages())
                                            <a href="{{ $egresos->appends(request()->query())->nextPageUrl() }}" 
                                               class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Siguiente</a>
                                        @else
                                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">Siguiente</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>