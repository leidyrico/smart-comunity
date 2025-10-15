<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Elemento de Inventario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Editar: {{ $inventario->nombre }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('inventario.show', $inventario) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ver Detalle
                            </a>
                            <a href="{{ route('inventario.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Volver al Listado
                            </a>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('inventario.update', $inventario) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                                <input type="text" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre', $inventario->nombre) }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                                <input type="text" 
                                       id="categoria" 
                                       name="categoria" 
                                       value="{{ old('categoria', $inventario->categoria) }}"
                                       placeholder="Ej: Mobiliario, Electrónicos, Herramientas"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                                <input type="number" 
                                       id="cantidad" 
                                       name="cantidad" 
                                       value="{{ old('cantidad', $inventario->cantidad) }}"
                                       min="0"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Precio Unitario -->
                            <div>
                                <label for="precio_unitario" class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario</label>
                                <input type="number" 
                                       id="precio_unitario" 
                                       name="precio_unitario" 
                                       value="{{ old('precio_unitario', $inventario->precio_unitario) }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Ubicación -->
                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                                <input type="text" 
                                       id="ubicacion" 
                                       name="ubicacion" 
                                       value="{{ old('ubicacion', $inventario->ubicacion) }}"
                                       placeholder="Ej: Oficina, Almacén, Sala de reuniones"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                                <select id="estado" 
                                        name="estado"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="disponible" {{ old('estado', $inventario->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                    <option value="en_uso" {{ old('estado', $inventario->estado) == 'en_uso' ? 'selected' : '' }}>En Uso</option>
                                    <option value="mantenimiento" {{ old('estado', $inventario->estado) == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                                    <option value="dañado" {{ old('estado', $inventario->estado) == 'dañado' ? 'selected' : '' }}>Dañado</option>
                                </select>
                            </div>

                            <!-- Fecha de Adquisición -->
                            <div>
                                <label for="fecha_adquisicion" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Adquisición</label>
                                <input type="date" 
                                       id="fecha_adquisicion" 
                                       name="fecha_adquisicion" 
                                       value="{{ old('fecha_adquisicion', $inventario->fecha_adquisicion ? $inventario->fecha_adquisicion->format('Y-m-d') : '') }}"
                                       lang="es-VE" 
                                       placeholder="dd/MM/yyyy"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Proveedor -->
                            <div>
                                <label for="proveedor" class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                                <input type="text" 
                                       id="proveedor" 
                                       name="proveedor" 
                                       value="{{ old('proveedor', $inventario->proveedor) }}"
                                       placeholder="Nombre del proveedor"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea id="descripcion" 
                                      name="descripcion" 
                                      rows="3"
                                      placeholder="Descripción detallada del elemento"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('descripcion', $inventario->descripcion) }}</textarea>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea id="observaciones" 
                                      name="observaciones" 
                                      rows="3"
                                      placeholder="Observaciones adicionales"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('observaciones', $inventario->observaciones) }}</textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('inventario.show', $inventario) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Actualizar Elemento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>