<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Proveedor') }}
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
                        <a href="{{ route('proveedores.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← Volver a la lista
                        </a>
                        <div class="flex space-x-2">
                            <a href="{{ route('proveedores.edit', $proveedor) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Editar
                            </a>
                            <form action="{{ route('proveedores.destroy', $proveedor) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('¿Está seguro de que desea eliminar este proveedor? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Información del proveedor -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Proveedor</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Columna izquierda -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Nombre</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $proveedor->nombre }}</p>
                                </div>

                                @if($proveedor->telefono)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Teléfono</label>
                                        <p class="text-lg text-gray-900">{{ $proveedor->telefono }}</p>
                                    </div>
                                @endif

                                @if($proveedor->email)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Email</label>
                                        <p class="text-lg text-gray-900">
                                            <a href="mailto:{{ $proveedor->email }}" 
                                               class="text-blue-600 hover:text-blue-800 underline">
                                                {{ $proveedor->email }}
                                            </a>
                                        </p>
                                    </div>
                                @endif

                                @if($proveedor->rif)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">RIF</label>
                                        <p class="text-lg text-gray-900">{{ $proveedor->rif }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Columna derecha -->
                            <div class="space-y-4">
                                @if($proveedor->direccion)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Dirección</label>
                                        <p class="text-lg text-gray-900">{{ $proveedor->direccion }}</p>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Estado</label>
                                    <p class="text-lg">
                                        @if($proveedor->activo)
                                            <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactivo
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Fecha de Registro</label>
                                    <p class="text-sm text-gray-500">{{ $proveedor->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                @if($proveedor->updated_at != $proveedor->created_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Última Modificación</label>
                                        <p class="text-sm text-gray-500">{{ $proveedor->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Descripción o notas -->
                        @if($proveedor->descripcion)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-600 mb-2">Descripción</label>
                                <div class="bg-white p-4 rounded border">
                                    <p class="text-gray-900">{{ $proveedor->descripcion }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Egresos relacionados -->
                    @if($proveedor->egresos && $proveedor->egresos->count() > 0)
                        <div class="mt-6 bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                Egresos Registrados ({{ $proveedor->egresos->count() }})
                            </h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto USD</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($proveedor->egresos->take(10) as $egreso)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                    {{ $egreso->fecha->format('d/m/Y') }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                    {{ $egreso->nro_factura }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                    ${{ number_format($egreso->monto, 2, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2 text-sm">
                                                    <a href="{{ route('egresos.show', $egreso) }}" 
                                                       class="text-blue-600 hover:text-blue-800 underline">
                                                        Ver detalle
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($proveedor->egresos->count() > 10)
                                <div class="mt-4">
                                    <a href="{{ route('egresos.index', ['proveedor_id' => $proveedor->id]) }}" 
                                       class="text-blue-600 hover:text-blue-800 underline">
                                        Ver todos los egresos de este proveedor ({{ $proveedor->egresos->count() }}) →
                                    </a>
                                </div>
                            @endif

                            <!-- Resumen de totales -->
                            <div class="mt-4 bg-white p-4 rounded border">
                                <h4 class="text-md font-semibold text-gray-800 mb-2">Resumen de Egresos</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Total en USD:</p>
                                        <p class="text-lg font-bold text-red-600">
                                            ${{ number_format($proveedor->egresos->sum('monto'), 2, ',', '.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total en Bs:</p>
                                        <p class="text-lg font-bold text-red-600">
                                            Bs {{ number_format($proveedor->egresos->sum('monto_en_bs'), 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 bg-yellow-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Egresos</h3>
                            <p class="text-gray-600">No hay egresos registrados para este proveedor.</p>
                            <div class="mt-4">
                                <a href="{{ route('egresos.create', ['proveedor_id' => $proveedor->id]) }}" 
                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Crear primer egreso
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Información adicional -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">Información Adicional</h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>ID del Proveedor:</strong> {{ $proveedor->id }}</p>
                            <p><strong>Total de Egresos:</strong> {{ $proveedor->egresos ? $proveedor->egresos->count() : 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>