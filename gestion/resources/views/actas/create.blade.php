<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Documento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('actas.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Número de Documento -->
                        <div>
                <x-input-label for="nro_doc" :value="__('Número de Documento')" />
                <x-text-input id="nro_doc" class="block mt-1 w-full" type="text" name="nro_doc" :value="old('nro_doc')" required autofocus />
                <x-input-error :messages="$errors->get('nro_doc')" class="mt-2" />
            </div>

                        <!-- Nombre de Documento -->
                        <div>
                <x-input-label for="nombre_doc" :value="__('Nombre de Documento')" />
                <x-text-input id="nombre_doc" class="block mt-1 w-full" type="text" name="nombre_doc" :value="old('nombre_doc')" required />
                <x-input-error :messages="$errors->get('nombre_doc')" class="mt-2" />
            </div>

                        <!-- Tipo de Documento -->
                        <div>
                            <x-input-label for="tipo_documento" :value="__('Tipo de Documento')" />
                            <select id="tipo_documento" name="tipo_documento" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="Correspondencia" {{ old('tipo_documento') == 'Correspondencia' ? 'selected' : '' }}>Correspondencia</option>
                                <option value="Comunicado" {{ old('tipo_documento') == 'Comunicado' ? 'selected' : '' }}>Comunicado</option>
                                <option value="Actas" {{ old('tipo_documento') == 'Actas' ? 'selected' : '' }}>Actas</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
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

                        <!-- Envío de Correo -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <input id="enviar_correo" name="enviar_correo" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('enviar_correo') ? 'checked' : '' }}>
                                <label for="enviar_correo" class="ml-2 block text-sm text-gray-900">
                                    <span class="font-medium">Enviar comunicación por correo electrónico</span>
                                    <span class="block text-gray-600 text-xs mt-1">Se enviará un correo con el asunto "Nueva comunicación" a todos los apartamentos que tengan email registrado, incluyendo la fecha, tipo de documento y el archivo adjunto (si existe).</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Crear Documento') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>