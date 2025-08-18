<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nueva Acta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('actas.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Número de Acta -->
                        <div>
                            <x-input-label for="nro_acta" :value="__('Número de Acta')" />
                            <x-text-input id="nro_acta" class="block mt-1 w-full" type="text" name="nro_acta" :value="old('nro_acta')" required autofocus />
                            <x-input-error :messages="$errors->get('nro_acta')" class="mt-2" />
                        </div>

                        <!-- Nombre de Acta -->
                        <div>
                            <x-input-label for="nombre_acta" :value="__('Nombre de Acta')" />
                            <x-text-input id="nombre_acta" class="block mt-1 w-full" type="text" name="nombre_acta" :value="old('nombre_acta')" required />
                            <x-input-error :messages="$errors->get('nombre_acta')" class="mt-2" />
                        </div>

                        <!-- Fecha -->
                        <div>
                            <x-input-label for="fecha" :value="__('Fecha')" />
                            <x-text-input id="fecha" class="block mt-1 w-full" type="date" name="fecha" :value="old('fecha')" required />
                            <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                        </div>

                        <!-- Descripción -->
                        <div>
                            <x-input-label for="descripcion" :value="__('Descripción')" />
                            <textarea id="descripcion" name="descripcion" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('descripcion') }}</textarea>
                            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                        </div>

                        <!-- Archivo (Opcional) -->
                        <div>
                            <x-input-label for="archivo" :value="__('Archivo (Opcional)')" />
                            <input id="archivo" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="file" name="archivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                            <p class="mt-1 text-sm text-gray-600">Tipos permitidos: PDF, DOC, DOCX, JPG, PNG (máximo 10MB). Este campo es opcional.</p>
                            <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Crear Acta') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>