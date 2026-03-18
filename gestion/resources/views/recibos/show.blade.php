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
                    @php($user = Auth::user())
                    @php($isPropietario = $user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario())
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
                                    <span class="font-medium">${{ number_format($recibo->valor_administracion, 2, ',', '.') }}</span>
                                </div>
                                @if($recibo->valor_aseo > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Aseo:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_aseo, 2, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($recibo->valor_vigilancia > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Vigilancia:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_vigilancia, 2, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($recibo->valor_mantenimiento > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mantenimiento:</span>
                                    <span class="font-medium">${{ number_format($recibo->valor_mantenimiento, 2, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($recibo->otros_conceptos > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Otros Conceptos:</span>
                                    <span class="font-medium">${{ number_format($recibo->otros_conceptos, 2, ',', '.') }}</span>
                                </div>
                                @endif
                                <div class="border-t pt-2 mt-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span class="text-gray-800">Total:</span>
                                        <span class="text-green-600">${{ number_format($recibo->total_recibo, 2, ',', '.') }}</span>
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

                    @if($recibo->archivo_adjunto)
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">Archivo Adjunto</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <a href="{{ route('recibos.descargar-archivo', $recibo) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Descargar Archivo
                            </a>
                        </div>
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
                                        ${{ number_format($pago->monto_pagado, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'Pendiente' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($pago->estado === 'confirmado')
                                                bg-green-100 text-green-800
                                            @elseif($pago->estado === 'pendiente_confirmacion')
                                                bg-yellow-100 text-yellow-800
                                            @else
                                                bg-red-100 text-red-800
                                            @endif">
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
                            <span class="text-gray-700 font-medium">Total a Recaudar:</span>
                            <span class="text-lg font-bold text-gray-800">${{ number_format($totalEsperado, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-700 font-medium">Total Pagado:</span>
                            <span class="text-lg font-bold text-green-600">${{ number_format($totalRecaudado, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2 border-b border-gray-200 pb-2 mb-2">
                            <span class="text-gray-700 font-medium">Saldo Pendiente:</span>
                            <span class="text-lg font-bold {{ $saldoPendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                                ${{ number_format($saldoPendiente, 2, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center mt-1 text-sm">
                            <span class="text-gray-600">Total Efectivo:</span>
                            <span class="font-medium text-gray-800">${{ number_format($totalEfectivo, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1 text-sm">
                            <span class="text-gray-600">Total Transferencia:</span>
                            <span class="font-medium text-gray-800">${{ number_format($totalTransferencia, 2, ',', '.') }}</span>
                        </div>
                        @if($totalPagoMovil > 0)
                        <div class="flex justify-between items-center mt-1 text-sm">
                            <span class="text-gray-600">Total Pago Móvil:</span>
                            <span class="font-medium text-gray-800">${{ number_format($totalPagoMovil, 2, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if(isset($resumenPagosPorApartamento))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Resumen por Apartamento</h3>
                            <p class="text-sm text-gray-600">Monto del recibo: ${{ number_format($resumenPagosPorApartamento['monto_recibo'], 2, ',', '.') }}</p>
                        </div>
                        <div class="text-sm text-gray-600">
                            Asignados: <span class="font-semibold text-gray-800">{{ $resumenPagosPorApartamento['asignados_total'] }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                            <div class="text-sm text-green-800">Pagaron completo</div>
                            <div class="text-2xl font-bold text-green-900">{{ $resumenPagosPorApartamento['pagaron_completo']->count() }}</div>
                        </div>
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                            <div class="text-sm text-yellow-800">Pagaron parcial</div>
                            <div class="text-2xl font-bold text-yellow-900">{{ $resumenPagosPorApartamento['pagaron_parcial']->count() }}</div>
                        </div>
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                            <div class="text-sm text-red-800">Deben</div>
                            <div class="text-2xl font-bold text-red-900">{{ $resumenPagosPorApartamento['deben']->count() }}</div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <details class="rounded-lg border border-gray-200 bg-white">
                            <summary class="cursor-pointer select-none px-4 py-3 font-semibold text-gray-800">
                                Apartamentos que pagaron completo ({{ $resumenPagosPorApartamento['pagaron_completo']->count() }})
                            </summary>
                            <div class="px-4 pb-4">
                                @if($resumenPagosPorApartamento['pagaron_completo']->count() === 0)
                                    <div class="text-sm text-gray-600">Ninguno.</div>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($resumenPagosPorApartamento['pagaron_completo'] as $apt)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                                                Apto {{ $apt['numero'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>

                        <details class="rounded-lg border border-gray-200 bg-white">
                            <summary class="cursor-pointer select-none px-4 py-3 font-semibold text-gray-800">
                                Apartamentos que pagaron parcial ({{ $resumenPagosPorApartamento['pagaron_parcial']->count() }})
                            </summary>
                            <div class="px-4 pb-4">
                                @if($resumenPagosPorApartamento['pagaron_parcial']->count() === 0)
                                    <div class="text-sm text-gray-600">Ninguno.</div>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($resumenPagosPorApartamento['pagaron_parcial'] as $apt)
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800">
                                                Apto {{ $apt['numero'] }} (${{ number_format($apt['total_pagado'], 2, ',', '.') }} / ${{ number_format($resumenPagosPorApartamento['monto_recibo'], 2, ',', '.') }})
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>

                        <details class="rounded-lg border border-gray-200 bg-white">
                            <summary class="cursor-pointer select-none px-4 py-3 font-semibold text-gray-800">
                                Apartamentos que deben ({{ $resumenPagosPorApartamento['deben']->count() }})
                            </summary>
                            <div class="px-4 pb-4">
                                @if($resumenPagosPorApartamento['deben']->count() === 0)
                                    <div class="text-sm text-gray-600">Ninguno.</div>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($resumenPagosPorApartamento['deben'] as $apt)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">
                                                Apto {{ $apt['numero'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>
                    </div>
                </div>
            </div>
            @endif

            <!-- Apartamentos Asociados -->
            @if(!$isPropietario && isset($apartamentos) && $apartamentos->count() > 0)
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
                <a href="{{ route('recibos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Volver a Recibos') }}
                </a>
                
                <div class="space-x-2">
                    <a href="{{ route('recibos.print', $recibo) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        {{ __('Imprimir') }}
                    </a>
                    @unless($isPropietario)
                    
                    <a href="{{ route('recibos.edit', $recibo) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('Editar') }}
                    </a>
                    
                    <form method="POST" action="{{ route('recibos.destroy', $recibo) }}" class="inline-block" onsubmit="return confirmarEliminacionRecibo(event, '{{ $recibo->numero_recibo }}')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="admin_password" id="admin_password_field">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            {{ __('Eliminar') }}
                        </button>
                    </form>

                    <!-- Modal para contraseña de administrador -->
                    <div id="adminPasswordModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="cancelarEliminacion()">
                        <div style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;" onclick="event.stopPropagation()">
                            <h3 style="margin-top: 0; color: #333;">Confirmación de Eliminación</h3>
                            <p id="modalMessage" style="color: #666; margin-bottom: 20px;"></p>
                            <div style="margin-bottom: 15px;">
                                <label for="modalAdminPassword" style="display: block; margin-bottom: 5px; font-weight: bold;">Clave de Administrador:</label>
                                <input type="password" id="modalAdminPassword" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Ingrese la clave de administrador">
                            </div>
                            <div style="text-align: right;">
                                <button onclick="cancelarEliminacion()" style="background-color: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; margin-right: 10px; cursor: pointer;">Cancelar</button>
                                <button onclick="confirmarConPassword()" style="background-color: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Eliminar</button>
                            </div>
                        </div>
                    </div>

                    <script>
                        let currentForm = null;

                        function confirmarEliminacionRecibo(event, numeroRecibo) {
                            event.preventDefault();
                            
                            currentForm = event.target;
                            document.getElementById('modalMessage').textContent = `Está a punto de eliminar el recibo ${numeroRecibo}. Esta acción no se puede deshacer.`;
                            document.getElementById('modalAdminPassword').value = '';
                            document.getElementById('adminPasswordModal').style.display = 'block';
                            document.getElementById('modalAdminPassword').focus();
                            
                            return false;
                        }

                        function cancelarEliminacion() {
                            document.getElementById('adminPasswordModal').style.display = 'none';
                            currentForm = null;
                        }

                        function confirmarConPassword() {
                            const adminPassword = document.getElementById('modalAdminPassword').value;
                            
                            if (!adminPassword) {
                                alert('Se requiere la clave de administrador.');
                                return;
                            }
                            
                            // Establecer la contraseña en el campo oculto
                            document.getElementById('admin_password_field').value = adminPassword;
                            
                            // Ocultar modal
                            document.getElementById('adminPasswordModal').style.display = 'none';
                            
                            // Enviar el formulario
                            currentForm.submit();
                        }

                        // Permitir envío con Enter
                        document.addEventListener('DOMContentLoaded', function() {
                            document.getElementById('modalAdminPassword').addEventListener('keypress', function(e) {
                                if (e.key === 'Enter') {
                                    confirmarConPassword();
                                }
                            });
                        });
                    </script>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>
