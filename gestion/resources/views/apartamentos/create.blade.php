<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Apartamento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('apartamentos.store') }}">
                        @csrf

                        <!-- Número del Apartamento -->
                        <div class="mb-4">
                            <x-input-label for="numero" :value="__('Número del Apartamento')" />
                            <x-text-input id="numero" class="block mt-1 w-full" type="text" name="numero" :value="old('numero')" required autofocus />
                            <x-input-error :messages="$errors->get('numero')" class="mt-2" />
                        </div>

                        <!-- Piso -->
                        <div class="mb-4">
                            <x-input-label for="piso" :value="__('Piso')" />
                            <x-text-input id="piso" class="block mt-1 w-full" type="number" name="piso" :value="old('piso')" min="0" required />
                            <x-input-error :messages="$errors->get('piso')" class="mt-2" />
                        </div>

                        <!-- Torre -->
                        <div class="mb-4">
                            <x-input-label for="torre" :value="__('Torre (Opcional)')" />
                            <x-text-input id="torre" class="block mt-1 w-full" type="text" name="torre" :value="old('torre')" />
                            <x-input-error :messages="$errors->get('torre')" class="mt-2" />
                        </div>

                        <!-- Propietario -->
                        <div class="mb-4">
                            <x-input-label for="propietario" :value="__('Nombre del Propietario')" />
                            <x-text-input id="propietario" class="block mt-1 w-full" type="text" name="propietario" :value="old('propietario')" required />
                            <x-input-error :messages="$errors->get('propietario')" class="mt-2" />
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-4">
                            <x-input-label for="telefono" :value="__('Teléfono')" />
                            <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono')" />
                            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Correo Electrónico')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Campos ocultos con valores por defecto -->
                        <input type="hidden" name="area_m2" value="80">
                        <input type="hidden" name="tipo" value="estudio">

                        <!-- Estado -->
                        <div class="mb-4">
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="ocupado" {{ old('estado') == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                <option value="desocupado" {{ old('estado') == 'desocupado' ? 'selected' : '' }}>Desocupado</option>
                                <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>

                        <!-- Estatus Financiero -->
                        <div class="mb-4">
                            <x-input-label for="estatus_financiero" :value="__('Estatus Financiero')" />
                            <select id="estatus_financiero" name="estatus_financiero" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="solvente" {{ old('estatus_financiero') == 'solvente' ? 'selected' : '' }}>Solvente</option>
                                <option value="deudor" {{ old('estatus_financiero') == 'deudor' ? 'selected' : '' }}>Deudor</option>
                            </select>
                            <x-input-error :messages="$errors->get('estatus_financiero')" class="mt-2" />
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-4">
                            <x-input-label for="observaciones" :value="__('Observaciones')" />
                            <textarea id="observaciones" name="observaciones" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('observaciones') }}</textarea>
                            <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('apartamentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Crear Apartamento') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>