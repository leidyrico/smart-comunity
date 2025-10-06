<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Espacio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Editar: {{ $space->nombre }}</h3>
                        <a href="{{ route('spaces.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </a>
                    </div>

                    <form action="{{ route('spaces.update', $space) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Nombre -->
                            <div>
                                <x-input-label for="nombre" :value="__('Nombre del Espacio')" />
                                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" 
                                    :value="old('nombre', $space->nombre)" required autofocus placeholder="Ej: Salón de fiesta" />
                                <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                            </div>

                            <!-- Descripción -->
                            <div>
                                <x-input-label for="descripcion" :value="__('Descripción')" />
                                <textarea id="descripcion" name="descripcion" rows="4" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Describe las características y servicios del espacio...">{{ old('descripcion', $space->descripcion) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                            </div>

                            <!-- Precio por día -->
                            <div>
                                <x-input-label for="precio_por_dia" :value="__('Precio por Día ($)')" />
                                <x-text-input id="precio_por_dia" name="precio_por_dia" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" :value="old('precio_por_dia', $space->precio_por_dia)" required 
                                    placeholder="0.00" />
                                <x-input-error class="mt-2" :messages="$errors->get('precio_por_dia')" />
                            </div>

                            <!-- Estado activo -->
                            <div class="flex items-center">
                                <input id="activo" name="activo" type="checkbox" value="1" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    {{ old('activo', $space->activo) ? 'checked' : '' }}>
                                <label for="activo" class="ml-2 block text-sm text-gray-900">
                                    Espacio activo (disponible para reservas)
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('spaces.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Espacio') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>