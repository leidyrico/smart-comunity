<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Egreso') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensajes de éxito/error -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Botones de navegación -->
                    <div class="mb-6 flex justify-between items-center">
                        <a href="{{ route('egresos.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← Volver a la lista
                        </a>
                        @php($user = Auth::user())
                        @php($isPropietario = $user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario())
                        <div class="flex space-x-2">
                            <a href="{{ route('egresos.pdf', $egreso) }}" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                📄 Descargar PDF
                            </a>
                            @unless($isPropietario)
                                <a href="{{ route('egresos.edit', $egreso) }}" 
                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Editar
                                </a>
                                <form action="{{ route('egresos.destroy', $egreso) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('¿Está seguro de que desea eliminar este egreso?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Eliminar
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>

                    <!-- Información del egreso -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Egreso</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Columna izquierda -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Número de Factura</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $egreso->nro_factura }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Número de Comprobante</label>
                                    <p class="text-lg text-gray-900">{{ $egreso->comprobante ?? 'No especificado' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Fecha</label>
                                    <p class="text-lg text-gray-900">{{ $egreso->fecha->format('d/m/Y') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Proveedor</label>
                                    <p class="text-lg text-gray-900">
                                        @if($egreso->proveedor)
                                            @unless($isPropietario)
                                                <a href="{{ route('proveedores.show', $egreso->proveedor) }}" 
                                                   class="text-blue-600 hover:text-blue-800 underline">
                                                    {{ $egreso->proveedor->nombre }}
                                                </a>
                                            @else
                                                {{ $egreso->proveedor->nombre }}
                                            @endunless
                                        @else
                                            Sin proveedor asignado
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Columna derecha -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Monto en USD</label>
                                    <p class="text-2xl font-bold text-red-600">${{ number_format($egreso->monto, 2, ',', '.') }}</p>
                                </div>

                                @if($egreso->monto_en_bs)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Monto en Bolívares</label>
                                        <p class="text-2xl font-bold text-red-600">Bs {{ number_format($egreso->monto_en_bs, 2, ',', '.') }}</p>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Fecha de Registro</label>
                                    <p class="text-sm text-gray-500">{{ $egreso->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                @if($egreso->updated_at != $egreso->created_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Última Modificación</label>
                                        <p class="text-sm text-gray-500">{{ $egreso->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Descripción -->
                        @if($egreso->descripcion)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-600 mb-2">Descripción</label>
                                <div class="bg-white p-4 rounded border">
                                    <p class="text-gray-900">{{ $egreso->descripcion }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Información del proveedor (si existe) -->
                    @if($egreso->proveedor)
                        <div class="mt-6 bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Proveedor</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Nombre</label>
                                    <p class="text-gray-900">{{ $egreso->proveedor->nombre }}</p>
                                </div>

                                @if($egreso->proveedor->telefono)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Teléfono</label>
                                        <p class="text-gray-900">{{ $egreso->proveedor->telefono }}</p>
                                    </div>
                                @endif

                                @if($egreso->proveedor->email)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Email</label>
                                        <p class="text-gray-900">{{ $egreso->proveedor->email }}</p>
                                    </div>
                                @endif

                                @if($egreso->proveedor->direccion)
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-600">Dirección</label>
                                        <p class="text-gray-900">{{ $egreso->proveedor->direccion }}</p>
                                    </div>
                                @endif
                            </div>

                            @unless($isPropietario)
                                <div class="mt-4">
                                    <a href="{{ route('proveedores.show', $egreso->proveedor) }}" 
                                       class="text-blue-600 hover:text-blue-800 underline">
                                        Ver todos los detalles del proveedor →
                                    </a>
                                </div>
                            @endunless
                        </div>
                    @endif

                    <!-- Historial de cambios o notas adicionales -->
                    <div class="mt-6 bg-yellow-50 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">Información Adicional</h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>ID del Egreso:</strong> {{ $egreso->id }}</p>
                            <p><strong>Estado:</strong> 
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Registrado
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>