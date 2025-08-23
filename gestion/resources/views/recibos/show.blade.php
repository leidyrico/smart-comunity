<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Recibo de Gasto Común') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Recibo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $recibo->numero_recibo }}</h3>
                            <p class="text-gray-600">Período: {{ $recibo->periodo }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($recibo->estado === 'activo') bg-green-100 text-green-800
                                @elseif($recibo->estado === 'vencido') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($recibo->estado) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Fechas -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Fechas</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fecha de Emisión:</span>
                                    <span class="font-medium">{{ $recibo->fecha_emision->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fecha de Vencimiento:</span>
                                    <span class="font-medium">{{ $recibo->fecha_vencimiento->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Valores -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Desglose de Valores</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Administración:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_administracion, 0, ',', '.') }}</span>
                                </div>
                                @if($recibo->valor_aseo > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Aseo:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_aseo, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($recibo->valor_vigilancia > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Vigilancia:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_vigilancia, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($recibo->valor_mantenimiento > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mantenimiento:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_mantenimiento, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($recibo->otros_conceptos > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Otros Conceptos:</span>
                                    <span class="font-medium">${{ number_format($recibo->otros_conceptos, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                <div class="border-t pt-2 mt-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span class="text-gray-800">Total:</span>
                                        <span class="text-green-600">${{ number_format($recibo->total_recibo, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($recibo->observaciones)
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">Observaciones</h4>
                        <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">{{ $recibo->observaciones }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información de Pagos -->
            @if($recibo->pagos->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Historial de Pagos</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apartamento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recibo->pagos as $pago)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $pago->apartamento->numero ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($pago->monto_pagado, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $pago->fecha_pago->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($pago->estado === 'confirmado') bg-green-100 text-green-800
                                            @elseif($pago->estado === 'pendiente_confirmacion') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $pago->estado)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Total Pagado:</span>
                            <span class="text-lg font-bold text-green-600">${{ number_format($recibo->total_pagado, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-700 font-medium">Saldo Pendiente:</span>
                            <span class="text-lg font-bold {{ $recibo->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                                ${{ number_format($recibo->saldo_pendiente, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Apartamentos Asociados -->
            @if(isset($apartamentos) && $apartamentos->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Registrar Pago</h3>
                    
                    <form method="POST" action="{{ route('pagos.store') }}">
                        @csrf
                        <input type="hidden" name="recibo_gasto_comun_id" value="{{ $recibo->id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="apartamento_id" class="block text-sm font-medium text-gray-700">Apartamento</label>
                                <select name="apartamento_id" id="apartamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione un apartamento</option>
                                    @foreach($apartamentos as $apartamento)
                                        <option value="{{ $apartamento->id }}">{{ $apartamento->numero }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="monto_pagado" class="block text-sm font-medium text-gray-700">Monto</label>
                                <input type="number" name="monto_pagado" id="monto_pagado" step="0.01" min="0.01" value="{{ $recibo->total_recibo }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            
                            <div>
                                <label for="metodo_pago" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                                <select name="metodo_pago" id="metodo_pago" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione método</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Botones de Acción -->
            <div class="flex justify-between items-center">
                <a href="{{ route('recibos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Volver a Recibos') }}
                </a>
                
                <div class="space-x-2">
                    <a href="{{ route('recibos.print', $recibo) }}" target="_blank" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        🖨️ {{ __('Imprimir') }}
                    </a>
                    
                    <a href="{{ route('recibos.edit', $recibo) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('Editar') }}
                    </a>
                    
                    <form method="POST" action="{{ route('recibos.destroy', $recibo) }}" class="inline-block" onsubmit="return confirm('¿Está seguro de que desea eliminar este recibo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Eliminar') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>