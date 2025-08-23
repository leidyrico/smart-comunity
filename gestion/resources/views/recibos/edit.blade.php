<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Recibo de Gasto Común') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('recibos.update', $recibo) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Número de Recibo -->
                            <div>
                                <x-input-label for="numero_recibo" :value="__('Número de Recibo')" />
                                <x-text-input id="numero_recibo" class="block mt-1 w-full" type="text" name="numero_recibo" :value="old('numero_recibo', $recibo->numero_recibo)" required autofocus />
                                <x-input-error :messages="$errors->get('numero_recibo')" class="mt-2" />
                            </div>

                            <!-- Período -->
                            <div>
                                <x-input-label for="periodo" :value="__('Período')" />
                                <x-text-input id="periodo" class="block mt-1 w-full" type="text" name="periodo" :value="old('periodo', $recibo->periodo)" required placeholder="Ej: 2024-01" />
                                <x-input-error :messages="$errors->get('periodo')" class="mt-2" />
                            </div>

                            <!-- Fecha de Emisión -->
                            <div>
                                <x-input-label for="fecha_emision" :value="__('Fecha de Emisión')" />
                                <x-text-input id="fecha_emision" class="block mt-1 w-full" type="date" name="fecha_emision" :value="old('fecha_emision', $recibo->fecha_emision->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('fecha_emision')" class="mt-2" />
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div>
                                <x-input-label for="fecha_vencimiento" :value="__('Fecha de Vencimiento')" />
                                <x-text-input id="fecha_vencimiento" class="block mt-1 w-full" type="date" name="fecha_vencimiento" :value="old('fecha_vencimiento', $recibo->fecha_vencimiento->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                            </div>

                            <!-- Valor Administración -->
                            <div>
                                <x-input-label for="valor_administracion" :value="__('Valor Administración')" />
                                <x-text-input id="valor_administracion" class="block mt-1 w-full" type="number" name="valor_administracion" :value="old('valor_administracion', $recibo->valor_administracion)" required min="0" step="0.01" />
                                <x-input-error :messages="$errors->get('valor_administracion')" class="mt-2" />
                            </div>

                            <!-- Valor Aseo -->
                            <div>
                                <x-input-label for="valor_aseo" :value="__('Valor Aseo')" />
                                <x-text-input id="valor_aseo" class="block mt-1 w-full" type="number" name="valor_aseo" :value="old('valor_aseo', $recibo->valor_aseo)" min="0" step="0.01" />
                                <x-input-error :messages="$errors->get('valor_aseo')" class="mt-2" />
                            </div>

                            <!-- Valor Vigilancia -->
                            <div>
                                <x-input-label for="valor_vigilancia" :value="__('Valor Vigilancia')" />
                                <x-text-input id="valor_vigilancia" class="block mt-1 w-full" type="number" name="valor_vigilancia" :value="old('valor_vigilancia', $recibo->valor_vigilancia)" min="0" step="0.01" />
                                <x-input-error :messages="$errors->get('valor_vigilancia')" class="mt-2" />
                            </div>

                            <!-- Valor Mantenimiento -->
                            <div>
                                <x-input-label for="valor_mantenimiento" :value="__('Valor Mantenimiento')" />
                                <x-text-input id="valor_mantenimiento" class="block mt-1 w-full" type="number" name="valor_mantenimiento" :value="old('valor_mantenimiento', $recibo->valor_mantenimiento)" min="0" step="0.01" />
                                <x-input-error :messages="$errors->get('valor_mantenimiento')" class="mt-2" />
                            </div>

                            <!-- Otros Conceptos -->
                            <div>
                                <x-input-label for="otros_conceptos" :value="__('Otros Conceptos')" />
                                <x-text-input id="otros_conceptos" class="block mt-1 w-full" type="number" name="otros_conceptos" :value="old('otros_conceptos', $recibo->otros_conceptos)" min="0" step="0.01" />
                                <x-input-error :messages="$errors->get('otros_conceptos')" class="mt-2" />
                            </div>

                            <!-- Estado -->
                            <div>
                                <x-input-label for="estado" :value="__('Estado')" />
                                <select id="estado" name="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="activo" {{ old('estado', $recibo->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="vencido" {{ old('estado', $recibo->estado) == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                    <option value="anulado" {{ old('estado', $recibo->estado) == 'anulado' ? 'selected' : '' }}>Anulado</option>
                                </select>
                                <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="mt-6">
                            <x-input-label for="observaciones" :value="__('Observaciones')" />
                            <textarea id="observaciones" name="observaciones" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" maxlength="1000" placeholder="Observaciones adicionales sobre el recibo...">{{ old('observaciones', $recibo->observaciones) }}</textarea>
                            <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                        </div>

                        <!-- Total Calculado -->
                        <div class="mt-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium text-gray-700">Total del Recibo:</span>
                                    <span id="total_display" class="text-xl font-bold text-green-600">${{ number_format($recibo->total_recibo, 0, ',', '.') }}</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">El total se calcula automáticamente al guardar</p>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <a href="{{ route('recibos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Actualizar Recibo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calcular total en tiempo real
        function calcularTotal() {
            const valorAdministracion = parseFloat(document.getElementById('valor_administracion').value) || 0;
            const valorAseo = parseFloat(document.getElementById('valor_aseo').value) || 0;
            const valorVigilancia = parseFloat(document.getElementById('valor_vigilancia').value) || 0;
            const valorMantenimiento = parseFloat(document.getElementById('valor_mantenimiento').value) || 0;
            const otrosConceptos = parseFloat(document.getElementById('otros_conceptos').value) || 0;
            
            const total = valorAdministracion + valorAseo + valorVigilancia + valorMantenimiento + otrosConceptos;
            
            document.getElementById('total_display').textContent = '$' + total.toLocaleString('es-CO');
        }

        // Agregar event listeners a todos los campos de valor
        document.addEventListener('DOMContentLoaded', function() {
            const camposValor = ['valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos'];
            
            camposValor.forEach(function(campo) {
                document.getElementById(campo).addEventListener('input', calcularTotal);
            });
        });
    </script>
</x-app-with-sidebar>