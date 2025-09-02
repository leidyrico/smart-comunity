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
                        <div id="success-message" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                            <div class="mt-2 text-sm">
                                <span id="redirect-countdown">Redirigiendo a la página de deudas en <strong>10</strong> segundos...</span>
                                <button type="button" onclick="cancelRedirect()" class="ml-4 underline hover:no-underline">Cancelar</button>
                            </div>
                        </div>
                        
                        <script>
                            let redirectTimer;
                            let countdown = 10;
                            
                            function updateCountdown() {
                                document.getElementById('redirect-countdown').innerHTML = 
                                    `Redirigiendo a la página de deudas en <strong>${countdown}</strong> segundos...`;
                                
                                if (countdown <= 0) {
                                    window.location.href = '{{ route("deudas.index", $filtros ?? []) }}';
                                    return;
                                }
                                
                                countdown--;
                                redirectTimer = setTimeout(updateCountdown, 1000);
                            }
                            
                            function cancelRedirect() {
                                clearTimeout(redirectTimer);
                                document.getElementById('success-message').style.display = 'none';
                            }
                            
                            // Iniciar el countdown
                            updateCountdown();
                        </script>
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
                        
                        <!-- Campos ocultos para mantener los filtros -->
                        @if(isset($filtros))
                            @foreach($filtros as $key => $value)
                                @if($value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                        @endif

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
                                            {{ (old('recibo_gasto_comun_id') == $recibo->id || (isset($reciboSeleccionado) && $reciboSeleccionado->id == $recibo->id)) ? 'selected' : '' }}>
                                            {{ $recibo->numero_recibo }} - {{ $recibo->periodo }} ({{ number_format($recibo->total_recibo, 2, ',', '.') }})
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
                                <div id="saldo-info" class="mt-1 text-sm text-gray-600" style="display: none;">
                                    <span class="text-blue-600">Saldo pendiente: $<span id="saldo-pendiente">0.00</span></span>
                                </div>
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
                                    <option value="pago_movil" {{ old('metodo_pago') == 'pago_movil' ? 'selected' : '' }}>Pago Móvil</option>
                                    <option value="transferencia" {{ old('metodo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
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
                            <a href="{{ route('deudas.index', $filtros ?? []) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Volver a Deudas') }}
                            </a>
                            
                            <div class="flex space-x-4">
                                <a href="#" id="ver-historial-btn" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="verHistorialApartamento()">
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
            const montoPagadoInput = document.getElementById('monto_pagado');
            const saldoInfo = document.getElementById('saldo-info');
            const saldoPendienteSpan = document.getElementById('saldo-pendiente');

            // Función para cargar recibos de un apartamento
            function cargarRecibosPorApartamento(apartamentoId, reciboIdSeleccionado = null) {
                if (!apartamentoId) {
                    reciboSelect.innerHTML = '<option value="">Seleccione un recibo</option>';
                    return;
                }
                
                reciboSelect.innerHTML = '<option value="">Cargando recibos...</option>';
                saldoInfo.style.display = 'none';
                montoPagadoInput.value = '';
                
                fetch(`/api/recibos-por-apartamento?apartamento_id=${apartamentoId}`)
                    .then(response => response.json())
                    .then(data => {
                        reciboSelect.innerHTML = '<option value="">Seleccione un recibo</option>';
                        
                        if (data.length === 0) {
                            reciboSelect.innerHTML = '<option value="">No hay recibos pendientes para este apartamento</option>';
                            return;
                        }
                        
                        data.forEach(recibo => {
                            const option = document.createElement('option');
                            option.value = recibo.id;
                            option.textContent = `${recibo.numero_recibo} - ${recibo.periodo} (Pendiente: $${new Intl.NumberFormat('es-CO').format(recibo.saldo_pendiente)})`;
                            option.setAttribute('data-total', recibo.total_recibo);
                            option.setAttribute('data-saldo-pendiente', recibo.saldo_pendiente);
                            
                            // Seleccionar el recibo si coincide con el ID proporcionado
                            if (reciboIdSeleccionado && recibo.id == reciboIdSeleccionado) {
                                option.selected = true;
                            }
                            
                            reciboSelect.appendChild(option);
                        });
                        
                        // Si hay un recibo seleccionado, cargar su saldo
                        if (reciboIdSeleccionado) {
                            const selectedOption = reciboSelect.options[reciboSelect.selectedIndex];
                            if (selectedOption && selectedOption.value) {
                                cargarSaldoRecibo(selectedOption);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        reciboSelect.innerHTML = '<option value="">Error al cargar recibos</option>';
                    });
            }
            
            // Función para cargar saldo de un recibo
            function cargarSaldoRecibo(selectedOption) {
                const saldoPendiente = parseFloat(selectedOption.getAttribute('data-saldo-pendiente'));
                
                if (saldoPendiente && saldoPendiente > 0) {
                    // Mostrar saldo pendiente
                    saldoPendienteSpan.textContent = new Intl.NumberFormat('es-CO', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(saldoPendiente);
                    
                    // Cargar saldo pendiente en el campo monto_pagado
                    montoPagadoInput.value = saldoPendiente.toFixed(2);
                    
                    // Mostrar información del saldo
                    saldoInfo.style.display = 'block';
                } else {
                    saldoInfo.style.display = 'none';
                    montoPagadoInput.value = '';
                }
            }
            
            // Cargar recibos automáticamente si hay apartamento seleccionado al cargar la página
            @if(isset($apartamentoSeleccionado) && $apartamentoSeleccionado)
                const reciboIdSeleccionado = {{ isset($reciboSeleccionado) && $reciboSeleccionado ? $reciboSeleccionado->id : 'null' }};
                cargarRecibosPorApartamento({{ $apartamentoSeleccionado->id }}, reciboIdSeleccionado);
            @endif
            
            apartamentoSelect.addEventListener('change', function() {
                const apartamentoId = this.value;
                cargarRecibosPorApartamento(apartamentoId);
            });
            
            // Evento para cargar saldo pendiente cuando se selecciona un recibo
            reciboSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                if (selectedOption && selectedOption.value) {
                    cargarSaldoRecibo(selectedOption);
                } else {
                    saldoInfo.style.display = 'none';
                    montoPagadoInput.value = '';
                }
            });
        });
        
        function verHistorialApartamento() {
            const apartamentoSelect = document.getElementById('apartamento_id');
            const apartamentoId = apartamentoSelect.value;
            
            if (!apartamentoId) {
                alert('Por favor, seleccione un apartamento primero para ver su historial.');
                return;
            }
            
            // Redirigir a la página de historial con el filtro del apartamento
            window.location.href = `{{ route('pagos.index') }}?apartamento_id=${apartamentoId}`;
        }
    </script>
</x-app-with-sidebar>