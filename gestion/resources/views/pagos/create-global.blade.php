<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Pago Global - Apartamento {{ $apartamento->numero }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">Propietario: {{ $apartamento->propietario }}</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pagos.store-global') }}">
                        @csrf
                        <input type="hidden" name="apartamento_id" value="{{ $apartamento->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label for="monto_total" class="block text-sm font-medium text-gray-700 mb-2">Monto Total a Pagar <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                        <input type="number" 
                                               class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('monto_total') border-red-500 @enderror" 
                                               id="monto_total" 
                                               name="monto_total" 
                                               value="{{ old('monto_total') }}" 
                                               step="0.01" 
                                               min="0.01" 
                                               required>
                                    </div>
                                    @error('monto_total')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                    <p class="text-sm text-gray-500 mt-1">
                                        Este monto se distribuirá automáticamente entre los recibos más antiguos pendientes.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label for="fecha_pago" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago <span class="text-red-500">*</span></label>
                                    <input type="date" 
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('fecha_pago') border-red-500 @enderror" 
                                           id="fecha_pago" 
                                           name="fecha_pago" 
                                           value="{{ old('fecha_pago', date('Y-m-d')) }}" 
                                           required>
                                    @error('fecha_pago')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="metodo_pago" class="block text-sm font-medium text-gray-700 mb-2">Método de Pago <span class="text-red-500">*</span></label>
                                    <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('metodo_pago') border-red-500 @enderror" 
                                            id="metodo_pago" 
                                            name="metodo_pago" 
                                            required>
                                        <option value="">Seleccione un método</option>
                                        <option value="efectivo" {{ old('metodo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="pago_movil" {{ old('metodo_pago') == 'pago_movil' ? 'selected' : '' }}>Pago Móvil</option>
                                        <option value="transferencia" {{ old('metodo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                    </select>
                                    @error('metodo_pago')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="numero_comprobante" class="block text-sm font-medium text-gray-700 mb-2">Número de Comprobante</label>
                            <input type="text" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('numero_comprobante') border-red-500 @enderror" 
                                   id="numero_comprobante" 
                                   name="numero_comprobante" 
                                   value="{{ old('numero_comprobante') }}" 
                                   placeholder="Número de referencia o comprobante">
            @error('numero_comprobante')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

                        <div class="mb-4">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <textarea class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('observaciones') border-red-500 @enderror" 
                                      id="observaciones" 
                                      name="observaciones" 
                                      rows="3" 
                                      placeholder="Observaciones adicionales sobre el pago">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex items-center mb-4">
                            <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                   type="checkbox" 
                                   id="enviar_correo" 
                                   name="enviar_correo" 
                                   value="1" 
                                   {{ old('enviar_correo') ? 'checked' : '' }}>
                            <label class="ml-2 block text-sm text-gray-900" for="enviar_correo">
                                Enviar comprobante por correo electrónico
                            </label>
                        </div>

                        <!-- Información de recibos pendientes -->
                        <div class="bg-white shadow rounded-lg mt-6">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <h6 class="text-lg font-medium text-gray-900">Recibos Pendientes (se aplicará el pago a los más antiguos)</h6>
                            </div>
                            <div class="p-4">
                                @if($recibos_pendientes->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibo</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Vencimiento</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendiente</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($recibos_pendientes as $recibo)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $recibo->numero_recibo }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($recibo->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($recibo->total_recibo, 2) }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($recibo->saldo_pendiente_apartamento, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-blue-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900" colspan="3">Total Pendiente:</th>
                                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">${{ number_format($total_pendiente, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-gray-500 text-center py-4">No hay recibos pendientes para este apartamento.</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <a href="{{ route('deudas.show', $apartamento->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i> Volver
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-save mr-2"></i> Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación en tiempo real del monto
        const montoInput = document.getElementById('monto_total');
        if (montoInput) {
            montoInput.addEventListener('input', function() {
                const monto = parseFloat(this.value) || 0;
                const totalPendiente = {{ $total_pendiente }};
                
                if (monto > totalPendiente) {
                    this.classList.add('border-red-500');
                    let feedback = this.parentNode.querySelector('.text-red-500');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'text-red-500 text-sm mt-1';
                        feedback.textContent = `El monto no puede ser mayor al total pendiente ($${totalPendiente.toFixed(2)})`;
                        this.parentNode.appendChild(feedback);
                    }
                } else {
                    this.classList.remove('border-red-500');
                    const feedback = this.parentNode.querySelector('.text-red-500');
                    if (feedback) {
                        feedback.remove();
                    }
                }
            });
        }
    });
    </script>
</x-app-with-sidebar>