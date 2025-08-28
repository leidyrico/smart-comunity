<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Apartamento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Editar Información del Apartamento</h3>
                        <a href="{{ route('apartamentos.show', $apartamento) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Hay errores en el formulario!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('apartamentos.update', $apartamento) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Información básica -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Número de Apartamento -->
                                <div>
                                    <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">Número de Apartamento *</label>
                                    <input type="text" 
                                           id="numero" 
                                           name="numero" 
                                           value="{{ old('numero', $apartamento->numero) }}"
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Propietario -->
                                <div>
                                    <label for="propietario" class="block text-sm font-medium text-gray-700 mb-1">Propietario *</label>
                                    <input type="text" 
                                           id="propietario" 
                                           name="propietario" 
                                           value="{{ old('propietario', $apartamento->propietario) }}"
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Piso -->
                                <div>
                                    <label for="piso" class="block text-sm font-medium text-gray-700 mb-1">Piso *</label>
                                    <input type="number" 
                                           id="piso" 
                                           name="piso" 
                                           value="{{ old('piso', $apartamento->piso) }}"
                                           required
                                           min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Torre -->
                                <div>
                                    <label for="torre" class="block text-sm font-medium text-gray-700 mb-1">Torre</label>
                                    <input type="text" 
                                           id="torre" 
                                           name="torre" 
                                           value="{{ old('torre', $apartamento->torre) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Tipo -->
                                <div>
                                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                                    <select id="tipo" 
                                            name="tipo" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="apartamento" {{ old('tipo', $apartamento->tipo) == 'apartamento' ? 'selected' : '' }}>Apartamento</option>
                                        <option value="estudio" {{ old('tipo', $apartamento->tipo) == 'estudio' ? 'selected' : '' }}>Estudio</option>
                                        <option value="penthouse" {{ old('tipo', $apartamento->tipo) == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                                        <option value="duplex" {{ old('tipo', $apartamento->tipo) == 'duplex' ? 'selected' : '' }}>Duplex</option>
                                    </select>
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                                    <select id="estado" 
                                            name="estado" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="ocupado" {{ old('estado', $apartamento->estado) == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                        <option value="desocupado" {{ old('estado', $apartamento->estado) == 'desocupado' ? 'selected' : '' }}>Desocupado</option>
                                        <option value="en_arriendo" {{ old('estado', $apartamento->estado) == 'en_arriendo' ? 'selected' : '' }}>En Arriendo</option>
                                    </select>
                                </div>

                                <!-- Estatus Financiero -->
                                <div>
                                    <label for="estatus_financiero" class="block text-sm font-medium text-gray-700 mb-1">Estatus Financiero *</label>
                                    <select id="estatus_financiero" 
                                            name="estatus_financiero" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="solvente" {{ old('estatus_financiero', $apartamento->estatus_financiero) == 'solvente' ? 'selected' : '' }}>Solvente</option>
                                        <option value="deudor" {{ old('estatus_financiero', $apartamento->estatus_financiero) == 'deudor' ? 'selected' : '' }}>Deudor</option>
                                        <option value="moroso" {{ old('estatus_financiero', $apartamento->estatus_financiero) == 'moroso' ? 'selected' : '' }}>Moroso</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Información de contacto -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Teléfono -->
                                <div>
                                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                    <input type="tel" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="{{ old('telefono', $apartamento->telefono) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $apartamento->email) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3 pt-6">
                            <a href="{{ route('apartamentos.show', $apartamento) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Actualizar Apartamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>