<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Pago') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pagos.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Apartamento -->
                            <div>
                                <x-input-label for="apartamento_id" :value="__('Apartamento')" />
                                <select id="apartamento_id" name="apartamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione un apartamento</option>
                                    @foreach($apartamentos as $apartamento)
                                        <option value="{{ $apartamento->id }}" 
                                            {{ (old('apartamento_id') == $apartamento->id || (isset($apartamentoSeleccionado) && $apartamentoSeleccionado->id == $apartamento->id)) ? 'selected' : '' }}>
                                            {{ $apartamento->numero }} - {{ $apartamento->propietario }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('apartamento_id')" class="mt-2" />
                            </div>

                            <!-- Recibo -->
                            <div>
                                <x-input-label for="recibo_gasto_comun_id" :value="__('Recibo de Gasto Común')" />
                                <select id="recibo_gasto_comun_id" name="recibo_gasto_comun_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione un recibo</option>
                                    @foreach($recibos as $recibo)
                                        <option value="{{ $recibo->id }}" data-total="{{ $recibo->total_recibo }}" 
                                            {{ old('recibo_gasto_comun_id') == $recibo->id ? 'selected' : '' }}>
                                            {{ $recibo->numero_recibo }} - {{ $recibo->periodo }} ({{ number_format($recibo->total_recibo, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('recibo_gasto_comun_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Monto Pagado -->
                            <div>
                                <x-input-label for="monto_pagado" :value="__('Monto Pagado')" />
                                <x-text-input id="monto_pagado" name="monto_pagado" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('monto_pagado')" required />
                                <x-input-error :messages="$errors->get('monto_pagado')" class="mt-2" />
                            </div>

                            <!-- Fecha de Pago -->
                            <div>
                                <x-input-label for="fecha_pago" :value="__('Fecha de Pago')" />
                                <x-text-input id="fecha_pago" name="fecha_pago" type="date" class="mt-1 block w-full" :value="old('fecha_pago', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('fecha_pago')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Método de Pago -->
                            <div>
                                <x-input-label for="metodo_pago" :value="__('Método de Pago')" />
                                <select id="metodo_pago" name="metodo_pago" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione método de pago</option>
                                    <option value="efectivo" {{ old('metodo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                    <option value="transferencia" {{ old('metodo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                    <option value="cheque" {{ old('metodo_pago') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="tarjeta_credito" {{ old('metodo_pago') == 'tarjeta_credito' ? 'selected' : '' }}>Tarjeta de Crédito</option>
                                    <option value="tarjeta_debito" {{ old('metodo_pago') == 'tarjeta_debito' ? 'selected' : '' }}>Tarjeta de Débito</option>
                                </select>
                                <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
                            </div>

                            <!-- Número de Comprobante -->
                            <div>
                                <x-input-label for="numero_comprobante" :value="__('Número de Comprobante')" />
                                <x-text-input id="numero_comprobante" name="numero_comprobante" type="text" class="mt-1 block w-full" :value="old('numero_comprobante')" maxlength="100" />
                                <x-input-error :messages="$errors->get('numero_comprobante')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="confirmado" {{ old('estado', 'confirmado') == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                                <option value="pendiente_confirmacion" {{ old('estado') == 'pendiente_confirmacion' ? 'selected' : '' }}>Pendiente de Confirmación</option>
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <x-input-label for="observaciones" :value="__('Observaciones')" />
                            <textarea id="observaciones" name="observaciones" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" maxlength="1000">{{ old('observaciones') }}</textarea>
                            <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('deudas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Volver a Deudas') }}
                            </a>
                            
                            <div class="flex space-x-4">
                                <a href="{{ route('pagos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Ver Historial') }}
                                </a>
                                
                                <x-primary-button>
                                    {{ __('Registrar Pago') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apartamentoSelect = document.getElementById('apartamento_id');
            const reciboSelect = document.getElementById('recibo_gasto_comun_id');
            
            apartamentoSelect.addEventListener('change', function() {
                const apartamentoId = this.value;
                
                // Limpiar opciones de recibo
                reciboSelect.innerHTML = '<option value="">Cargando recibos...</option>';
                
                if (apartamentoId) {
                    fetch(`/pagos/recibos-por-apartamento?apartamento_id=${apartamentoId}`)
                        .then(response => response.json())
                        .then(data => {
                            reciboSelect.innerHTML = '<option value="">Seleccione un recibo</option>';
                            data.forEach(recibo => {
                                const option = document.createElement('option');
                                option.value = recibo.id;
                                option.textContent = `${recibo.numero_recibo} - ${recibo.periodo} ($${new Intl.NumberFormat('es-CO').format(recibo.total_recibo)})`;
                                option.setAttribute('data-total', recibo.total_recibo);
                                reciboSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            reciboSelect.innerHTML = '<option value="">Error al cargar recibos</option>';
                        });
                } else {
                    reciboSelect.innerHTML = '<option value="">Seleccione un recibo</option>';
                }
            });
        });
    </script>
</x-app-with-sidebar>