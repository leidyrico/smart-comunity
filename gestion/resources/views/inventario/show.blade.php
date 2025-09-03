<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Elemento de Inventario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">{{ $inventario->nombre }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('inventario.edit', $inventario) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Editar
                            </a>
                            <a href="{{ route('inventario.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Volver al Listado
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información Básica -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Información Básica</h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Nombre:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->nombre }}</p>
                                </div>

                                @if($inventario->categoria)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Categoría:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->categoria }}</p>
                                </div>
                                @endif

                                <div>
                                    <span class="text-sm font-medium text-gray-600">Cantidad:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->cantidad }}</p>
                                </div>

                                <div>
                                    <span class="text-sm font-medium text-gray-600">Estado:</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($inventario->estado == 'disponible') bg-green-100 text-green-800
                                        @elseif($inventario->estado == 'en_uso') bg-blue-100 text-blue-800
                                        @elseif($inventario->estado == 'mantenimiento') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $inventario->estado)) }}
                                    </span>
                                </div>

                                @if($inventario->ubicacion)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Ubicación:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->ubicacion }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Información Financiera -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Información Financiera</h4>
                            
                            <div class="space-y-3">
                                @if($inventario->precio_unitario)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Precio Unitario:</span>
                                    <p class="text-sm text-gray-900">${{ number_format($inventario->precio_unitario, 2) }}</p>
                                </div>
                                @else
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Precio:</span>
                                    <p class="text-sm text-gray-500">No especificado</p>
                                </div>
                                @endif

                                @if($inventario->fecha_adquisicion)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Fecha de Adquisición:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->fecha_adquisicion->format('d/m/Y') }}</p>
                                </div>
                                @endif

                                @if($inventario->proveedor)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Proveedor:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->proveedor }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($inventario->descripcion)
                    <div class="mt-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Descripción</h4>
                            <p class="text-sm text-gray-900 leading-relaxed">{{ $inventario->descripcion }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Observaciones -->
                    @if($inventario->observaciones)
                    <div class="mt-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Observaciones</h4>
                            <p class="text-sm text-gray-900 leading-relaxed">{{ $inventario->observaciones }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Información de Auditoría -->
                    <div class="mt-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Información del Sistema</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Creado:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Última Actualización:</span>
                                    <p class="text-sm text-gray-900">{{ $inventario->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <form action="{{ route('inventario.destroy', $inventario) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este elemento del inventario? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Eliminar Elemento
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>